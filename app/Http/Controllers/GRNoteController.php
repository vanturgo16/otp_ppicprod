<?php

namespace App\Http\Controllers;

use DataTables;
use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use RealRashid\SweetAlert\Facades\Alert;
use Browser;
use Illuminate\Support\Facades\Crypt;
use Picqer\Barcode\BarcodeGeneratorHTML;

use App\Models\GoodReceiptNote;
use App\Models\GoodReceiptNoteDetail;
use App\Models\GoodReceiptNoteDetailSmt;
use App\Models\MstSupplier;
use App\Models\PurchaseOrders;
use App\Models\PurchaseRequisitions;
use App\Models\MstUnits;
use App\Models\PurchaseRequisitionsDetail;
use App\Models\DetailGoodReceiptNoteDetail;
use App\Models\ReportMaterialUseDeatail;

use App\Models\MstRawMaterial;
use App\Models\MstProductFG;
use App\Models\MstToolAux;
use App\Models\MstWip;

class GRNoteController extends Controller
{
    use AuditLogsTrait;

    // GRN DATA
    public function index(Request $request)
    {
        $datas = GoodReceiptNote::select('good_receipt_notes.id', 'good_receipt_notes.receipt_number', 'good_receipt_notes.date', 'good_receipt_notes.type', 'good_receipt_notes.status',
                'good_receipt_notes.id_purchase_orders', 'good_receipt_notes.external_doc_number', 'good_receipt_notes.qc_status',
                'purchase_requisitions.request_number', 'purchase_orders.po_number', 'master_suppliers.name as supplier_name',
                DB::raw('(SELECT COUNT(*) FROM good_receipt_note_details WHERE good_receipt_note_details.id_good_receipt_notes = good_receipt_notes.id) as count'))
            ->leftJoin('purchase_requisitions', 'good_receipt_notes.reference_number', 'purchase_requisitions.id')
            ->leftJoin('purchase_orders', 'good_receipt_notes.id_purchase_orders', 'purchase_orders.id')
            ->leftJoin('master_suppliers', 'good_receipt_notes.id_master_suppliers', 'master_suppliers.id');

        if ($request->has('filterType') && $request->filterType != '' && $request->filterType != 'All') {
            $datas->where('good_receipt_notes.type', $request->filterType);
        }
        if ($request->has('filterStatus') && $request->filterStatus != '' && $request->filterStatus != 'All') {
            $datas->where('good_receipt_notes.status', $request->filterStatus);
        }

        $datas = $datas->orderBy('good_receipt_notes.created_at', 'desc')->limit(10)->get();

        // Datatables
        if ($request->ajax()) {
            return DataTables::of($datas)
                ->addColumn('action', function ($data){
                    return view('gr_note.action', compact('data'));
                })->make(true);
        }

        //Audit Log
        $this->auditLogsShort('View List Good Receipt Note');
        return view('gr_note.index');
    }
    public function add($type)
    {
        if(!in_array($type, ['PR', 'PO'])){
            return redirect()->route('dashboard')->with(['fail' => 'Tidak Ada Sumber GRN dari '. $type]);
        }
        $lastCode = GoodReceiptNote::orderBy('created_at', 'desc')->value(DB::raw('RIGHT(receipt_number, 7)'));
        $lastCode = $lastCode ? $lastCode : 0;
        $nextCode = $lastCode + 1;
        $formattedCode = 'GR'.date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        if($type == 'PO'){
            $postedPO = PurchaseOrders::select('id', 'po_number')->where('status', 'Posted')->get();
            $postedPRs = PurchaseRequisitions::select('id', 'request_number')->whereIn('status', ['Posted', 'Created PO', 'Closed'])->get();
        } else {
            $postedPO = [];
            $postedPRs = PurchaseRequisitions::select('id', 'request_number')->where('status', 'Posted')->get();
        }
        $suppliers = MstSupplier::get();

        return view('gr_note.add',compact('type', 'formattedCode', 'postedPO', 'postedPRs', 'suppliers'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'receipt_number' => 'required',
            'date' => 'required',
            'id_purchase_orders' => $request->type == 'PR' ? '' : 'required',
            'requester' => 'required',
            'qc_check' => 'required',
            'status' => 'required',
            'type' => 'required',
        ], [
            'receipt_number.required' => 'Request Number masih kosong.',
            'date.required' => 'Date harus diisi.',
            'id_purchase_orders.required' => 'Supplier harus diisi.',
            'requester.required' => 'Requester harus diisi.',
            'qc_check.required' => 'QC Check harus diisi.',
            'status.required' => 'Status harus diisi.',
            'type.required' => 'Type masih kosong.',
        ]);
        
        $lastCode = PurchaseRequisitions::orderBy('created_at', 'desc')->value(DB::raw('RIGHT(request_number, 7)'));
        $lastCode = $lastCode ? $lastCode : 0;
        $nextCode = $lastCode + 1;
        $formattedCode = 'PR' . date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);
        
        DB::beginTransaction();
        try{
            $storeData = PurchaseRequisitions::create([
                'request_number' => $formattedCode,
                'date' => $request->date,
                'id_master_suppliers' => $request->id_master_suppliers,
                'requester' => $request->requester,
                'qc_check' => $request->qc_check,
                'note' => $request->note,
                'status' => $request->status,
                'type' => $request->type,
            ]);

            // Audit Log
            $this->auditLogsShort('Tambah Purchase Requisitions');
            DB::commit();
            return redirect()->route('pr.edit', encrypt($storeData->id))->with(['success' => 'Berhasil Tambah Data PR, Silahkan Tambahkan Item Produk']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Tambah Data PR!']);
        }
    }
    public function edit($id)
    {
        $id = decrypt($id);
        $data = GoodReceiptNote::where('id', $id)->first();
        if($data->id_purchase_orders){
            $type = 'PO';
            $postedPO = PurchaseOrders::select('id', 'po_number')->where('status', 'Posted')->get();
            $postedPRs = PurchaseRequisitions::select('id', 'request_number')->whereIn('status', ['Posted', 'Created PO', 'Closed'])->get();
        } else {
            $type = 'PR';
            $postedPO = [];
            $postedPRs = PurchaseRequisitions::select('id', 'request_number')->where('status', 'Posted')->get();
        }
        $suppliers = MstSupplier::get();

        if($data->type == 'RM'){
            $products = MstRawMaterial::select('id', 'description')->get();
        } elseif($data->type == 'WIP'){
            $products = MstWip::select('id', 'description')->get();
        } elseif($data->type == 'FG'){
            $products = MstProductFG::select('id', 'description', 'perforasi', 'group_sub_code')->get();
        } elseif($data->type == 'TA'){
            $products = MstToolAux::select('id', 'description')->where('type', '!=', 'Other')->get();
        } elseif($data->type == 'Other'){
            $products = MstToolAux::select('id', 'description')->where('type', 'Other')->get();
        } else {
            $products = [];
        }
        $units = MstUnits::select('id', 'unit_code')->get();

        $itemDatas = GoodReceiptNoteDetail::select(
            'good_receipt_note_details.*',
            'master_units.unit',
            'master_units.unit_code',
            DB::raw('
                CASE 
                    WHEN good_receipt_note_details.type_product = "RM" THEN master_raw_materials.description 
                    WHEN good_receipt_note_details.type_product = "WIP" THEN master_wips.description 
                    WHEN good_receipt_note_details.type_product = "FG" THEN master_product_fgs.description 
                    WHEN good_receipt_note_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.description 
                END as product_desc')
            )
            ->leftJoin('master_raw_materials', function ($join) {
                $join->on('good_receipt_note_details.id_master_products', '=', 'master_raw_materials.id')
                    ->on('good_receipt_note_details.type_product', '=', DB::raw('"RM"'));
            })
            ->leftJoin('master_wips', function ($join) {
                $join->on('good_receipt_note_details.id_master_products', '=', 'master_wips.id')
                    ->on('good_receipt_note_details.type_product', '=', DB::raw('"WIP"'));
            })
            ->leftJoin('master_product_fgs', function ($join) {
                $join->on('good_receipt_note_details.id_master_products', '=', 'master_product_fgs.id')
                    ->on('good_receipt_note_details.type_product', '=', DB::raw('"FG"'));
            })
            ->leftJoin('master_tool_auxiliaries', function ($join) {
                $join->on('good_receipt_note_details.id_master_products', '=', 'master_tool_auxiliaries.id')
                    ->on('good_receipt_note_details.type_product', '=', DB::raw('"TA"'))
                    ->orOn('good_receipt_note_details.type_product', '=', DB::raw('"Other"'));
            })
            ->leftJoin('master_units', 'good_receipt_note_details.master_units_id', '=', 'master_units.id')
            ->where('good_receipt_note_details.id_good_receipt_notes', $id)
            ->orderBy('good_receipt_note_details.created_at')
            ->get();

        return view('gr_note.edit', compact('type', 'data', 'postedPO', 'postedPRs', 'suppliers', 'products', 'units', 'itemDatas'));
    }

    //ITEM GRN
    public function storeItem(Request $request, $id)
    {
        $id = decrypt($id); //ID GRN
        $request->validate([
            'type_product' => 'required',
            'id_master_products' => 'required',
            'qty' => 'required',
            'master_units_id' => 'required',
        ], [
            'type_product.required' => 'Type Produk masih kosong.',
            'id_master_products.required' => 'Produk harus diisi.',
            'qty.required' => 'Qty harus diisi.',
            'master_units_id.required' => 'Unit harus diisi.',
        ]);
        
        DB::beginTransaction();
        try{
            $storeData = GoodReceiptNoteDetail::create([
                'id_good_receipt_notes' => $id,
                'type_product' => $request->type_product,
                'id_master_products' => $request->id_master_products,
                'qty' => $request->qty,
                'master_units_id' => $request->master_units_id,
                'note' => $request->note,
            ]);

            // Audit Log
            $this->auditLogsShort('Tambah Good Receipt Note Detail ID : (' . $storeData->id . ')');
            DB::commit();
            return redirect()->route('grn.edit', encrypt($id))->with(['success' => 'Berhasil Tambah Item GRN Ke Dalam Tabel', 'scrollTo' => 'tableItem']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Tambah Item GRN!']);
        }
    }
    public function editItem($id)
    {
        $id = decrypt($id);
        $data = GoodReceiptNoteDetail::where('id', $id)->first();
        if($data->type_product == 'RM'){
            $products = MstRawMaterial::select('id', 'description')->get();
        } elseif($data->type_product == 'WIP'){
            $products = MstWip::select('id', 'description')->get();
        } elseif($data->type_product == 'FG'){
            $products = MstProductFG::select('id', 'description', 'perforasi', 'group_sub_code')->get();
        } elseif($data->type_product == 'TA'){
            $products = MstToolAux::select('id', 'description')->where('type', '!=', 'Other')->get();
        } elseif($data->type_product == 'Other'){
            $products = MstToolAux::select('id', 'description')->where('type', 'Other')->get();
        } else {
            $products = [];
        }
        $units = MstUnits::select('id', 'unit_code')->get();
            
        return view('gr_note.item.edit', compact('data', 'products', 'units'));
    }
    public function updateItem(Request $request, $id)
    {
        $id = decrypt($id);
        $request->validate([
            'id_master_products' => 'required',
        ], [
            'id_master_products.required' => 'Produk harus diisi.',
        ]);

        $dataBefore = GoodReceiptNoteDetail::where('id', $id)->first();
        $dataBefore->id_master_products = $request->id_master_products;

        if($dataBefore->isDirty()){
            DB::beginTransaction();
            try{
                GoodReceiptNoteDetail::where('id', $id)->update([
                    'id_master_products' => $request->id_master_products,
                ]);

                // Audit Log
                $this->auditLogsShort('Update Good Receipt Note Detail ID : (' . $id . ')');
                DB::commit();
                return redirect()->route('grn.edit', encrypt($dataBefore->id_good_receipt_notes))->with(['success' => 'Berhasil Update Item GRN', 'scrollTo' => 'tableItem']);
            } catch (Exception $e) {
                DB::rollback();
                return redirect()->back()->with(['fail' => 'Gagal Update Item GRN!']);
            }
        } else {
            return redirect()->back()->with(['info' => 'Tidak Ada Yang Dirubah, Data Sama Dengan Sebelumnya']);
        }
    }
    public function deleteItem($id)
    {
        $id = decrypt($id);
        DB::beginTransaction();
        try{
            GoodReceiptNoteDetail::where('id', $id)->delete();

            // Audit Log
            $this->auditLogsShort('Hapus Good Receipt Note Detail ID : (' . $id . ')');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Hapus Item GRN', 'scrollTo' => 'tableItem']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Hapus Item GRN!']);
        }
    }





    public function getPODetails(Request $request)
    {
        $idPO = $request->input('idPO');
        $purchaseOrder = PurchaseOrders::select('reference_number', 'id_master_suppliers', 'qc_check', 'type')->where('id', $idPO)->first();
        if ($purchaseOrder) {
            return response()->json([
                'success' => true,
                'data' => [
                    'reference_number' => $purchaseOrder->reference_number,
                    'id_master_suppliers' => $purchaseOrder->id_master_suppliers,
                    'qc_check' => $purchaseOrder->qc_check,
                    'type' => $purchaseOrder->type,
                ]
            ]);
        } else {
            return response()->json(['success' => false]);
        }
    }
    public function getPRDetails(Request $request)
    {
        $referenceId = $request->input('reference_id');
        $purchaseRequest = PurchaseRequisitions::select('id_master_suppliers', 'qc_check', 'type')->where('id', $referenceId)->first();
        if ($purchaseRequest) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id_master_suppliers' => $purchaseRequest->id_master_suppliers,
                    'qc_check' => $purchaseRequest->qc_check,
                    'type' => $purchaseRequest->type,
                ]
            ]);
        } else {
            return response()->json(['success' => false]);
        }
    }

}
