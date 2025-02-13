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
use App\Models\PurchaseRequisitionsPrice;

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
    public function add($source)
    {
        if(!in_array($source, ['PR', 'PO'])){
            return redirect()->route('dashboard')->with(['fail' => 'Tidak Ada Sumber GRN dari '. $source]);
        }
        $lastCode = GoodReceiptNote::orderBy('created_at', 'desc')->value(DB::raw('RIGHT(receipt_number, 7)'));
        $lastCode = $lastCode ? $lastCode : 0;
        $nextCode = $lastCode + 1;
        $formattedCode = 'GR'.date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        if($source == 'PO'){
            $postedPO = PurchaseOrders::select('id', 'po_number')->where('status', 'Posted')->get();
            $postedPRs = PurchaseRequisitions::select('id', 'request_number')->get();
        } else {
            $postedPO = [];
            $postedPRs = PurchaseRequisitionsPrice::select('purchase_requisitions.id', 'purchase_requisitions.request_number')
                ->leftjoin('purchase_requisitions', 'purchase_requisitions_price.id_purchase_requisitions', 'purchase_requisitions.id')
                ->where('purchase_requisitions_price.status', 'Posted')->get();
        }
        $suppliers = MstSupplier::get();

        return view('gr_note.add',compact('source', 'formattedCode', 'postedPO', 'postedPRs', 'suppliers'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'receipt_number' => 'required',
            'date' => 'required',
            'id_purchase_orders' => $request->source == 'PR' ? '' : 'required',
            'reference_number' => 'required',
            'external_doc_number' => 'required',
            'qc_status' => 'required',
            'status' => 'required',
            'type' => 'required',
        ], [
            'receipt_number.required' => 'Request Number masih kosong.',
            'date.required' => 'Date harus diisi.',
            'id_purchase_orders.required' => 'Purchase Order masih kosong.',
            'reference_number.required' => 'Reference Number masih kosong.',
            'external_doc_number.required' => 'External Doc Number masih kosong.',
            'qc_status.required' => 'QC Check harus diisi.',
            'status.required' => 'Status harus diisi.',
            'type.required' => 'Type masih kosong.',
        ]);

        // Check Data Detail Item Produk
        $detailItems = PurchaseRequisitionsDetail::where('id_purchase_requisitions', $request->reference_number)->get();
        if ($detailItems->isEmpty()) {
            return redirect()->back()->with('fail', 'Tidak ada item produk yang ditemukan dalam Reference Number, Silahkan periksa kembali data PR.');
        }
        
        $lastCode = GoodReceiptNote::orderBy('created_at', 'desc')->value(DB::raw('RIGHT(receipt_number, 7)'));
        $lastCode = $lastCode ? $lastCode : 0;
        $nextCode = $lastCode + 1;
        $formattedCode = 'GR'.date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);
        
        DB::beginTransaction();
        try{
            // Update Source
            if($request->source == 'PO'){
                PurchaseOrders::where('id', $request->id_purchase_orders)->update(['status' => 'Created GRN']);
            } else {
                PurchaseRequisitionsPrice::where('id_purchase_requisitions', $request->reference_number)->update(['status' => 'Created GRN']);
            }
            // Store Data
            $storeData = GoodReceiptNote::create([
                'receipt_number' => $formattedCode,
                'date' => $request->date,
                'id_purchase_orders' => $request->id_purchase_orders,
                'reference_number' => $request->reference_number,
                'id_master_suppliers' => $request->id_master_suppliers,
                'qc_status' => $request->qc_status,
                'non_invoiceable' => $request->non_invoiceable,
                'external_doc_number' => $request->external_doc_number,
                'status' => $request->status,
                'type' => $request->type,
                'remarks' => $request->remarks,
            ]);
            // Store Data Detail
            foreach($detailItems as $item) {
                GoodReceiptNoteDetail::create([
                    'id_good_receipt_notes' => $storeData->id,
                    'type_product' => $item->type_product,
                    'id_master_products' => $item->master_products_id,
                    'qty' => $item->qty,
                    'outstanding_qty' => $item->qty,
                    'receipt_qty' => 0,
                    'master_units_id' => $item->master_units_id,
                    'status' => 'Open',
                    'id_purchase_requisition_details' => $item->id,
                ]);
            }

            // Audit Log
            $this->auditLogsShort('Tambah Good Receipt Note ID ('.$storeData->id.')');
            DB::commit();
            return redirect()->route('grn.edit', encrypt($storeData->id))->with(['success' => 'Berhasil Tambah Data GRN, Silahkan Update Item Produk']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Tambah Data GRN!']);
        }
    }
    public function edit($id)
    {
        $id = decrypt($id);
        $data = GoodReceiptNote::where('id', $id)->first();
        if($data->id_purchase_orders){
            $source = 'PO';
            $postedPO = PurchaseOrders::select('id', 'po_number')->where('status', 'Posted')->orWhere('id', $data->id_purchase_orders)->get();
            $postedPRs = PurchaseRequisitions::select('id', 'request_number')->get();
        } else {
            $source = 'PR';
            $postedPO = [];
            $postedPRs = PurchaseRequisitionsPrice::select('purchase_requisitions.id', 'purchase_requisitions.request_number')
                ->leftjoin('purchase_requisitions', 'purchase_requisitions_price.id_purchase_requisitions', 'purchase_requisitions.id')
                ->where('purchase_requisitions_price.status', 'Posted')
                ->orWhere('purchase_requisitions.id', $data->reference_number)->get();
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
                END as product_desc'),
                DB::raw($source == 'PO' ? 'purchase_order_details.price' : 'purchase_requisition_details.price'),
                DB::raw($source == 'PO' ? 'purchase_order_details.sub_total' : 'purchase_requisition_details.sub_total'),
                DB::raw($source == 'PO' ? 'purchase_order_details.discount' : 'purchase_requisition_details.discount'),
                DB::raw($source == 'PO' ? 'purchase_order_details.amount' : 'purchase_requisition_details.amount'),
                DB::raw($source == 'PO' ? 'purchase_order_details.tax' : 'purchase_requisition_details.tax'),
                DB::raw($source == 'PO' ? 'purchase_order_details.tax_rate' : 'purchase_requisition_details.tax_rate'),
                DB::raw($source == 'PO' ? 'purchase_order_details.tax_value' : 'purchase_requisition_details.tax_value'),
                DB::raw($source == 'PO' ? 'purchase_order_details.total_amount' : 'purchase_requisition_details.total_amount'),
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
            ->leftJoin('master_units', 'good_receipt_note_details.master_units_id', '=', 'master_units.id');
            if ($source == 'PO') {
                $itemDatas->leftJoin('purchase_order_details', 'good_receipt_note_details.id_purchase_requisition_details', '=', 'purchase_order_details.id_purchase_requisition_details');
            } else {
                $itemDatas->leftJoin('purchase_requisition_details', 'good_receipt_note_details.id_purchase_requisition_details', '=', 'purchase_requisition_details.id');
            }
            $itemDatas = $itemDatas->where('good_receipt_note_details.id_good_receipt_notes', $id)
            ->orderBy('good_receipt_note_details.created_at')
            ->get();

        return view('gr_note.edit', compact('source', 'data', 'postedPO', 'postedPRs', 'suppliers', 'products', 'units', 'itemDatas'));
    }
    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $request->validate([
            'date' => 'required',
            'id_purchase_orders' => $request->source == 'PR' ? '' : 'required',
            'reference_number' => 'required',
            'qc_status' => 'required',
            'status' => 'required',
            'type' => 'required',
        ], [
            'date.required' => 'Date harus diisi.',
            'id_purchase_orders.required' => 'Purchase Order masih kosong.',
            'reference_number.required' => 'Reference Number masih kosong.',
            'qc_status.required' => 'QC Check harus diisi.',
            'status.required' => 'Status harus diisi.',
            'type.required' => 'Type masih kosong.',
        ]);

        // Compare With Data Before
        $dataBefore = GoodReceiptNote::where('id', $id)->first();
        $changePONumber = $dataBefore->id_purchase_orders != $request->id_purchase_orders;
        $changeRefNumber = $dataBefore->reference_number != $request->reference_number;

        $dataBefore->date = $request->date;
        $dataBefore->id_purchase_orders = $request->id_purchase_orders;
        $dataBefore->reference_number = $request->reference_number;
        $dataBefore->id_master_suppliers = $request->id_master_suppliers;
        $dataBefore->qc_status = $request->qc_status;
        $dataBefore->non_invoiceable = $request->non_invoiceable;
        $dataBefore->external_doc_number = $request->external_doc_number;
        $dataBefore->remarks = $request->remarks;

        if($dataBefore->isDirty()){
            DB::beginTransaction();
            try{
                // Update ITEM
                GoodReceiptNote::where('id', $id)->update([
                    'date' => $request->date,
                    'id_purchase_orders' => $request->id_purchase_orders,
                    'reference_number' => $request->reference_number,
                    'id_master_suppliers' => $request->id_master_suppliers,
                    'qc_status' => $request->qc_status,
                    'non_invoiceable' => $request->non_invoiceable,
                    'external_doc_number' => $request->external_doc_number,
                    'type' => $request->type,
                    'remarks' => $request->remarks,
                ]);

                // IF PO / Ref Number Change
                if($changePONumber || $changeRefNumber){
                    // Rollback Status PO / PR Before
                    if($changePONumber){
                        PurchaseOrders::where('id', $request->id_purchase_orders_before)->update(['status' => 'Posted']);
                        PurchaseOrders::where('id', $request->id_purchase_orders)->update(['status' => 'Created GRN']);
                    } else {
                        if($changeRefNumber){
                            PurchaseRequisitionsPrice::where('id_purchase_requisitions', $request->reference_number_before)->update(['status' => 'Posted']);
                            PurchaseRequisitionsPrice::where('id_purchase_requisitions', $request->reference_number)->update(['status' => 'Created GRN']);
                        }
                    }
                    // Delete GRN Detail Before
                    GoodReceiptNoteDetail::where('id_good_receipt_notes', $id)->delete();
                    // Get Item PR After
                    $dataItemPR = PurchaseRequisitionsDetail::where('id_purchase_requisitions', $request->reference_number)->get();
                    foreach($dataItemPR as $item){
                        GoodReceiptNoteDetail::create([
                            'id_good_receipt_notes' => $id,
                            'type_product' => $item->type_product,
                            'id_master_products' => $item->master_products_id,
                            'qty' => $item->qty,
                            'outstanding_qty' => $item->qty,
                            'receipt_qty' => 0,
                            'master_units_id' => $item->master_units_id,
                            'status' => 'Open',
                            'id_purchase_requisition_details' => $item->id,
                        ]);
                    }
                }
    
                // Audit Log
                $this->auditLogsShort('Update Data GRN ID ('. $id . ')');
                DB::commit();
                return redirect()->back()->with(['success' => 'Berhasil Perbaharui Data GRN']);
            } catch (Exception $e) {
                DB::rollback();
                return redirect()->back()->with(['fail' => 'Gagal Perbaharui Data GRN!']);
            }
        } else {
            return redirect()->back()->with(['info' => 'Tidak Ada Yang Dirubah, Data Sama Dengan Sebelumnya']);
        }
    }
    public function delete($id)
    {
        $id = decrypt($id);
        $data = GoodReceiptNote::where('id', $id)->first();
        DB::beginTransaction();
        try{
            // Rollback Status PR / PO
            if($data->id_purchase_orders){
                PurchaseOrders::where('id', $data->id_purchase_orders)->update(['status' => 'Posted']);
            } else {
                PurchaseRequisitionsPrice::where('id_purchase_requisitions', $data->reference_number)->update(['status' => 'Posted']);
            }
            // Delete
            GoodReceiptNote::where('id', $id)->delete();
            GoodReceiptNoteDetail::where('id_good_receipt_notes', $id)->delete();

            // Audit Log
            $this->auditLogsShort('Hapus Good Receipt Note ID ('.$id.')');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Hapus Data GRN']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Hapus Data GRN!']);
        }
    }
    public function posted($id)
    {
        $id = decrypt($id);
        $data = GoodReceiptNote::where('id', $id)->first();
        DB::beginTransaction();
        try{
            // Update Status PR / PO
            if($data->id_purchase_orders){
                PurchaseOrders::where('id', $data->id_purchase_orders)->update(['status' => 'Closed']);
            } else {
                PurchaseRequisitionsPrice::where('id_purchase_requisitions', $data->reference_number)->update(['status' => 'Closed']);
            }
            GoodReceiptNote::where('id', $id)->update(['status' => 'Posted']);

            // Audit Log
            $this->auditLogsShort('Posted GRN ('.$id.')');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Posted Data GRN']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Posted Data GRN!']);
        }
    }
    public function unposted($id)
    {
        $id = decrypt($id);
        $data = GoodReceiptNote::where('id', $id)->first();
        DB::beginTransaction();
        try{
            // Update Status PR / PO
            if($data->id_purchase_orders){
                PurchaseOrders::where('id', $data->id_purchase_orders)->update(['status' => 'Created GRN']);
            } else {
                PurchaseRequisitionsPrice::where('id_purchase_requisitions', $data->reference_number)->update(['status' => 'Created GRN']);
            }
            GoodReceiptNote::where('id', $id)->update(['status' => 'Un Posted']);

            // Audit Log
            $this->auditLogsShort('Un-Posted GRN ('.$id.')');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Un-Posted Data GRN']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Un-Posted Data GRN!']);
        }
    }
    public function print($lang, $id)
    {
        $id = decrypt($id);
        $data = GoodReceiptNote::select('good_receipt_notes.*', 
                'purchase_orders.po_number',
                'master_suppliers.name')
            ->leftJoin('purchase_orders', 'good_receipt_notes.id_purchase_orders','purchase_orders.id')
            ->leftJoin('master_suppliers', 'purchase_orders.id_master_suppliers', 'master_suppliers.id')
            ->where('good_receipt_notes.id', $id)
            ->first();
            
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
                    END as product_desc'),
                DB::raw('
                    CASE 
                        WHEN good_receipt_note_details.type_product = "RM" THEN master_raw_materials.rm_code 
                        WHEN good_receipt_note_details.type_product = "WIP" THEN master_wips.wip_code 
                        WHEN good_receipt_note_details.type_product = "FG" THEN master_product_fgs.product_code 
                        WHEN good_receipt_note_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.code 
                    END as code'),
                DB::raw('
                    CASE 
                        WHEN good_receipt_note_details.type_product = "FG" THEN master_product_fgs.perforasi 
                    END as perforasi')
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

        $view = ($lang === 'en') ? 'gr_note.print' : 'gr_note.printIDN';
        return view($view, compact('data', 'itemDatas'));
    }

    //ITEM GRN
    public function updateItem(Request $request, $id)
    {
        $id = decrypt($id);
        $request->validate([
            'receipt_qty' => 'required',
            'outstanding_qty' => 'required',
            'status' => 'required',
        ], [
            'receipt_qty.required' => 'Receipt Qty harus diisi.',
            'outstanding_qty.required' => 'Outstanding Qty harus diisi.',
            'status.required' => 'Status harus diisi.',
        ]);

        $dataBefore = GoodReceiptNoteDetail::where('id', $id)->first();
        $dataBefore->receipt_qty = $request->receipt_qty;
        $dataBefore->outstanding_qty = $request->outstanding_qty;
        $dataBefore->status = $request->status;
        $dataBefore->note = $request->note;

        if($dataBefore->isDirty()){
            DB::beginTransaction();
            try{
                GoodReceiptNoteDetail::where('id', $id)->update([
                    'receipt_qty' => $request->receipt_qty,
                    'outstanding_qty' => $request->outstanding_qty,
                    'status' => $request->status,
                    'note' => $request->note,
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
            return redirect()->back()->with(['info' => 'Tidak Ada Yang Dirubah, Data Sama Dengan Sebelumnya', 'scrollTo' => 'tableItem']);
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
