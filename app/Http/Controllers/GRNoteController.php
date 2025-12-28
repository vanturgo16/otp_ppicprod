<?php

namespace App\Http\Controllers;

use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\GRNExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

use App\Traits\AuditLogsTrait;

use App\Models\GoodReceiptNote;
use App\Models\GoodReceiptNoteDetail;
use App\Models\MstSupplier;
use App\Models\PurchaseOrders;
use App\Models\PurchaseOrderDetails;
use App\Models\PurchaseRequisitions;
use App\Models\PurchaseRequisitionsDetail;
use App\Models\DetailGoodReceiptNoteDetail;
use App\Models\MstRawMaterial;
use App\Models\MstProductFG;
use App\Models\MstToolAux;
use App\Models\MstWip;
use App\Models\HistoryStocks;
use App\Models\MstCompanies;
use App\Models\TransPurchase;

class GRNoteController extends Controller
{
    use AuditLogsTrait;

    // GRN DATA
    public function index(Request $request)
    {
        $idUpdated = $request->get('idUpdated');

        $datas = GoodReceiptNote::select('good_receipt_notes.id', 'good_receipt_notes.receipt_number', 'good_receipt_notes.date', 'good_receipt_notes.type', 'good_receipt_notes.status',
                'good_receipt_notes.id_purchase_orders', 'good_receipt_notes.external_doc_number', 'good_receipt_notes.qc_status',
                'purchase_requisitions.request_number', 'purchase_orders.po_number', 'master_suppliers.name as supplier_name',
                DB::raw('(SELECT COUNT(*) FROM good_receipt_note_details 
                    WHERE good_receipt_note_details.id_good_receipt_notes = good_receipt_notes.id 
                    AND good_receipt_note_details.status IS NOT NULL) as count'))
            ->leftJoin('purchase_requisitions', 'good_receipt_notes.reference_number', 'purchase_requisitions.id')
            ->leftJoin('purchase_orders', 'good_receipt_notes.id_purchase_orders', 'purchase_orders.id')
            ->leftJoin('master_suppliers', 'good_receipt_notes.id_master_suppliers', 'master_suppliers.id');

        if ($request->has('filterType') && $request->filterType != '' && $request->filterType != 'All') {
            $datas->where('good_receipt_notes.type', $request->filterType);
        }
        if ($request->has('filterStatus') && $request->filterStatus != '' && $request->filterStatus != 'All') {
            $datas->where('good_receipt_notes.status', $request->filterStatus);
        }

        $datas = $datas->orderBy('good_receipt_notes.created_at', 'desc')->get();

        // Get Page Number
        $page_number = 1;
        if ($idUpdated) {
            $page_size = 5;
            $item = $datas->firstWhere('id', $idUpdated);
            if ($item) {
                $index = $datas->search(function ($value) use ($idUpdated) {
                    return $value->id == $idUpdated;
                });
                $page_number = (int) ceil(($index + 1) / $page_size);
            } else {
                $page_number = 1;
            }
        }

        // Datatables
        if ($request->ajax()) {
            return DataTables::of($datas)
                ->addColumn('action', function ($data){
                    return view('gr_note.action', compact('data'));
                })->make(true);
        }

        //Audit Log
        $this->auditLogsShort('View List Good Receipt Note');
        return view('gr_note.index', compact('idUpdated', 'page_number'));
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
            $postedPO = PurchaseOrders::select('id', 'po_number')->whereIn('status', ['Posted', 'Created GRN'])->get();
            $postedPRs = PurchaseRequisitions::select('id', 'request_number')->get();
        } else {
            $postedPO = [];
            $postedPRs = PurchaseRequisitions::select('id', 'request_number')->whereIn('status', ['Posted', 'Created GRN'])->get();
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

        // Check Data Has Inserted GRN, If Exist Get Last Data
        $hasGRNDataBefore = GoodReceiptNote::where('reference_number', $request->reference_number)->latest()->first();
        if($hasGRNDataBefore){
            if($hasGRNDataBefore->status != 'Posted'){
                return redirect()->back()->with('fail', 'Data GRN Terakhir yang menggunakan Reference Number ini masih dalam status '. $hasGRNDataBefore->status .', Silahkan tunggu hingga status berubah menjadi Posted.');
            }
        }
        
        // Check Data Detail Item Produk
        $detailItems = PurchaseRequisitionsDetail::where('id_purchase_requisitions', $request->reference_number)->where('status', 'Open')->get();
        if ($detailItems->isEmpty()) {
            return redirect()->back()->with('fail', 'Tidak ada item produk yang ditemukan dalam Reference Number Atau Outsanding Qty Telah 0, Silahkan periksa kembali data PR.');
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
                PurchaseRequisitions::where('id', $request->reference_number)->update(['status' => 'Created GRN']);
            }
            // Update Recent GRN Before This GRN With Same Reference Number to Closed
            if(GoodReceiptNote::where('reference_number', $request->reference_number)->exists()){
                GoodReceiptNote::where('reference_number', $request->reference_number)
                    ->where('status', 'Posted')->latest('id')->limit(1)->update(['status' => 'Closed']);
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
                    'qty' => $item->outstanding_qty,
                    'outstanding_qty' => $item->outstanding_qty,
                    'receipt_qty' => 0,
                    'master_units_id' => $item->master_units_id,
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
        $data = GoodReceiptNote::select('good_receipt_notes.*', 
                'purchase_orders.po_number',
                'purchase_requisitions.request_number',
                'master_suppliers.name as supplier_name')
            ->leftjoin('purchase_orders', 'good_receipt_notes.id_purchase_orders', 'purchase_orders.id')
            ->leftjoin('purchase_requisitions', 'good_receipt_notes.reference_number', 'purchase_requisitions.id')
            ->leftjoin('master_suppliers', 'good_receipt_notes.id_master_suppliers', 'master_suppliers.id')
            ->where('good_receipt_notes.id', $id)
            ->first();

        if($data->id_purchase_orders){
            $source = 'PO';
        } else {
            $source = 'PR';
        }

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
                        ->whereIn('good_receipt_note_details.type_product', ['TA', 'Other']);
            })
            ->leftJoin('master_units', 'good_receipt_note_details.master_units_id', '=', 'master_units.id')
            ->where('good_receipt_note_details.id_good_receipt_notes', $id)
            ->orderBy('good_receipt_note_details.created_at')
            ->get();

        return view('gr_note.edit', compact('source', 'data', 'products', 'itemDatas'));
    }
    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $request->validate([
            'date' => 'required',
            'external_doc_number' => 'required',
        ], [
            'date.required' => 'Date harus diisi.',
            'external_doc_number.required' => 'External Doc Number harus diisi.',
        ]);

        // Compare With Data Before
        $dataBefore = GoodReceiptNote::where('id', $id)->first();

        $dataBefore->date = $request->date;
        $dataBefore->external_doc_number = $request->external_doc_number;
        $dataBefore->remarks = $request->remarks;

        if($dataBefore->isDirty()){
            DB::beginTransaction();
            try{
                // Update ITEM
                GoodReceiptNote::where('id', $id)->update([
                    'date' => $request->date,
                    'external_doc_number' => $request->external_doc_number,
                    'remarks' => $request->remarks,
                ]);
    
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
            $idPRDetails = GoodReceiptNoteDetail::where('id_good_receipt_notes', $id)->pluck('id_purchase_requisition_details');
            // Rollback Status PR / PO
            if($data->id_purchase_orders){
                if(!GoodReceiptNote::where('id_purchase_orders', $data->id_purchase_orders)->where('id', '!=', $id)->exists()){
                    PurchaseOrders::where('id', $data->id_purchase_orders)->update(['status' => 'Posted']);
                    // Update To Open IF has been Close
                    if ($idPRDetails->isNotEmpty()) {
                        PurchaseOrderDetails::whereIn('id_purchase_requisition_details', $idPRDetails)->where('status', 'Close')->update([
                            'status' => 'Open'
                        ]);
                    }
                }
            } else {
                if(!GoodReceiptNote::where('reference_number', $data->reference_number)->where('id', '!=', $id)->exists()){
                    PurchaseRequisitions::where('id', $data->reference_number)->update(['status' => 'Posted']);
                }
            }
            // Update To Open IF has been Close
            if ($idPRDetails->isNotEmpty()) {
                PurchaseRequisitionsDetail::whereIn('id', $idPRDetails)->where('status', 'Close')->update([
                    'status' => 'Open'
                ]);
            }
            // Delete
            GoodReceiptNote::where('id', $id)->delete();
            GoodReceiptNoteDetail::where('id_good_receipt_notes', $id)->delete();
            DetailGoodReceiptNoteDetail::where('id_grn', $id)->delete();

            // Update Recent GRN Before This GRN With Same Reference Number to Posted
            if(GoodReceiptNote::where('reference_number', $data->reference_number)->where('id', '!=', $id)->exists()){
                // Get grn before this
                $lastGRN = GoodReceiptNote::where('reference_number', $data->reference_number)->where('status', 'Closed')->latest('id')->limit(1)->first();
                // Check if has created invoice or not
                if ($lastGRN && !TransPurchase::where('id_good_receipt_notes', $lastGRN->id)->exists()) {
                    $lastGRN->update(['status' => 'Posted']);
                }
            }

            // Audit Log
            $this->auditLogsShort('Hapus Good Receipt Note ID ('.$id.')');
            DB::commit();
            return redirect()->route('grn.index', ['idUpdated' => $id])->with(['success' => 'Berhasil Hapus Data GRN']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->route('grn.index', ['idUpdated' => $id])->with(['fail' => 'Gagal Hapus Data GRN!']);
        }
    }
    public function posted($id)
    {
        $id = decrypt($id);
        $data = GoodReceiptNote::where('id', $id)->first();

        // VALIDATION
        if($data->qc_status == 'Y'){
            // Check All Product GRN With Status Open / Close Has QC Passes
            if(GoodReceiptNoteDetail::where('id_good_receipt_notes', $id)
                ->whereIn('status', ['Open', 'Close'])
                ->where(function ($query) {
                    $query->where('qc_passed', '!=', 'Y')
                        ->orWhereNull('qc_passed');
                })->exists()){
                return redirect()->back()->with(['fail' => 'Masih ada produk yang diterima belum lulus QC!']);
            }
        }
        // Check All Product GRN With Status Open / Close Has Generate Lot Number
        if(GoodReceiptNoteDetail::where('id_good_receipt_notes', $id)->whereNotNull('status')->whereIn('status', ['Open', 'Close'])->whereNull('lot_number')->exists()){
            return redirect()->back()->with(['fail' => 'Masih ada produk yang diterima belum generate Lot Number!']);
        }
        // Check All Product GRN With Status Open / Close Has Generate All Receipt Qty
        if (GoodReceiptNoteDetail::where('id_good_receipt_notes', $id)->whereNotNull('status')->whereIn('status', ['Open', 'Close'])
            ->whereColumn('receipt_qty', '!=', 'qty_generate_barcode')->exists()) {
            return redirect()->back()->with(['fail' => 'Masih ada sisa Qty produk yang diterima belum generate Lot!']);
        }

        DB::beginTransaction();
        try{
            // UPDATE STATUS SOURCE IF ALL PRODUCT CLOSE
            if (!GoodReceiptNoteDetail::where('id_good_receipt_notes', $id)
                ->where(function ($query) {
                    $query->where('status', 'Open')
                        ->orWhereNull('status');
                })->exists()){
                if ($data->id_purchase_orders){
                    // Update Outstanding Item PO
                    PurchaseOrders::where('id', $data->id_purchase_orders)->update(['status' => 'Closed']);
                } else {
                    PurchaseRequisitions::where('id', $data->reference_number)->update(['status' => 'Closed']);
                }
            }
            // UPDATE OUTSTANDING ITEM PRODUCT
            $itemGRNs = GoodReceiptNoteDetail::where('id_good_receipt_notes', $id)->whereIn('status', ['Open', 'Close'])->get();
            foreach($itemGRNs as $item){
                $detailPR = PurchaseRequisitionsDetail::where('id', $item->id_purchase_requisition_details)->first();
                // $outstanding = (float) $detailPR->outstanding_qty - (float) $item->receipt_qty;
                // $outstanding = rtrim(rtrim(sprintf("%.6f", $outstanding), '0'), '.');
                
                $qty = (float) $item->qty;
                $receiptQty = (float) $item->receipt_qty;
                // 🔹 Decrease source outstanding only up to ordered qty
                $decreaseQty = min($receiptQty, $qty);
                $outstanding = (float) $detailPR->outstanding_qty - $decreaseQty;
                // Safety: never negative
                $outstanding = max($outstanding, 0);
                // Format to 6 decimals
                $outstanding = rtrim(rtrim(sprintf('%.6f', $outstanding), '0'), '.');

                if ($data->id_purchase_orders){
                    // IF Source PO Update Item Product PO Also
                    PurchaseOrderDetails::where('id_purchase_requisition_details', $item->id_purchase_requisition_details)->update([
                        'outstanding_qty' => $outstanding, 'status' => $item->status
                    ]);
                }
                PurchaseRequisitionsDetail::where('id', $item->id_purchase_requisition_details)->update([
                    'outstanding_qty' => $outstanding, 'status' => $item->status
                ]);

                // INSERT HISTORY STOCK
                HistoryStocks::create([
                    'id_good_receipt_notes_details' => $item->id,
                    'type_product' => $item->type_product,
                    'id_master_products' => $item->id_master_products,
                    'qty' => $item->receipt_qty,
                    'type_stock' => 'IN',
                    'is_closed' => 1,
                    'remarks' => $item->note,
                    'date' => DB::raw('CURRENT_DATE()')
                ]);
                // UPDATE STOCK
                if($item->type_product == 'FG'){
                    $dataFG = MstProductFG::find($item->id_master_products);
                    if ($dataFG) {
                        $dataFG->stock += $item->receipt_qty;
                        $dataFG->save();
                    }
                } elseif($item->type_product == 'RM'){
                    $dataRM = MstRawMaterial::find($item->id_master_products);
                    if ($dataRM) {
                        $dataRM->stock += $item->receipt_qty;
                        $dataRM->save();
                    }
                } elseif($item->type_product == 'WIP'){
                    $dataWIP = MstWip::find($item->id_master_products);
                    if ($dataWIP) {
                        $dataWIP->stock += $item->receipt_qty;
                        $dataWIP->save();
                    }
                } elseif($item->type_product == 'TA' || $item->type_product == 'Other'){
                    $dataTA = MstToolAux::find($item->id_master_products);
                    if ($dataTA) {
                        $dataTA->stock += $item->receipt_qty;
                        $dataTA->save();
                    }
                }
            }
            
            // UPDATE GRN
            GoodReceiptNote::where('id', $id)->update(['status' => 'Posted']);

            // Audit Log
            $this->auditLogsShort('Posted GRN ('.$id.')');
            DB::commit();
            return redirect()->route('grn.index', ['idUpdated' => $id])->with(['success' => 'Berhasil Posted Data GRN']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->route('grn.index', ['idUpdated' => $id])->with(['fail' => 'Gagal Posted Data GRN!']);
        }
    }
    public function unposted($id)
    {
        $id = decrypt($id);
        $data = GoodReceiptNote::where('id', $id)->first();
        DB::beginTransaction();
        try{
            // ROLLBACK STATUS SOURCE
            if($data->id_purchase_orders){
                PurchaseOrders::where('id', $data->id_purchase_orders)->where('status', '!=', 'Created GRN')->update(['status' => 'Created GRN']);
            } else {
                PurchaseRequisitions::where('id', $data->reference_number)->where('status', '!=', 'Created GRN')->update(['status' => 'Created GRN']);
            }
            // ROLLBACK OUTSTANDING ITEM PRODUCT
            $itemGRNs = GoodReceiptNoteDetail::where('id_good_receipt_notes', $id)->whereIn('status', ['Open', 'Close'])->get();
            foreach($itemGRNs as $item){
                $detailPR = PurchaseRequisitionsDetail::where('id', $item->id_purchase_requisition_details)->first();
                // $outstanding = (float) $detailPR->outstanding_qty + (float) $detailPR->cancel_qty + (float) $item->receipt_qty;
                // $outstanding = rtrim(rtrim(sprintf("%.6f", $outstanding), '0'), '.');

                $qty = (float) $item->qty;
                $receiptQty = (float) $item->receipt_qty;
                // 🔹 Same cap logic as POST
                $increaseQty = min($receiptQty, $qty);
                // 🔹 Rollback outstanding
                $outstanding = (float) $detailPR->outstanding_qty + $increaseQty;
                // 🔹 Safety: never exceed original qty
                $outstanding = max($outstanding, 0);
                // 🔹 Format
                $outstanding = rtrim(rtrim(sprintf('%.6f', $outstanding), '0'), '.');


                if ($data->id_purchase_orders){
                    // IF Source PO Update Item Product PO Also
                    PurchaseOrderDetails::where('id_purchase_requisition_details', $item->id_purchase_requisition_details)->update([
                        'cancel_qty' => null,
                        'outstanding_qty' => $outstanding
                    ]);
                }
                PurchaseRequisitionsDetail::where('id', $item->id_purchase_requisition_details)->update([
                    'cancel_qty' => null,
                    'outstanding_qty' => $outstanding
                ]);

                // DELETE HISTORY STOCK
                HistoryStocks::where('id_good_receipt_notes_details', $item->id)->delete();
                // UPDATE STOCK
                if($item->type_product == 'FG'){
                    $dataFG = MstProductFG::find($item->id_master_products);
                    if ($dataFG) {
                        $dataFG->stock -= $item->receipt_qty;
                        $dataFG->save();
                    }
                } elseif($item->type_product == 'RM'){
                    $dataRM = MstRawMaterial::find($item->id_master_products);
                    if ($dataRM) {
                        $dataRM->stock -= $item->receipt_qty;
                        $dataRM->save();
                    }
                } elseif($item->type_product == 'WIP'){
                    $dataWIP = MstWip::find($item->id_master_products);
                    if ($dataWIP) {
                        $dataWIP->stock -= $item->receipt_qty;
                        $dataWIP->save();
                    }
                } elseif($item->type_product == 'TA' || $item->type_product == 'Other'){
                    $dataTA = MstToolAux::find($item->id_master_products);
                    if ($dataTA) {
                        $dataTA->stock -= $item->receipt_qty;
                        $dataTA->save();
                    }
                }
            }

            // UPDATE STATUS GRN
            GoodReceiptNote::where('id', $id)->update(['status' => 'Un Posted']);

            // Audit Log
            $this->auditLogsShort('Un-Posted GRN ('.$id.')');
            DB::commit();
            return redirect()->route('grn.index', ['idUpdated' => $id])->with(['success' => 'Berhasil Un-Posted Data GRN']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->route('grn.index', ['idUpdated' => $id])->with(['fail' => 'Gagal Un-Posted Data GRN!']);
        }
    }
    public function print($lang, $id)
    {
        $id = decrypt($id);

        $dataCompany = MstCompanies::select('master_companies.company_name', 'master_companies.address', 
                'master_provinces.province', 'master_companies.city','master_companies.postal_code', 'master_countries.country', 
                'master_companies.telephone', 'master_companies.fax')
            ->leftjoin('master_provinces', 'master_companies.id_master_provinces', '=', 'master_provinces.id')
            ->leftjoin('master_countries', 'master_companies.id_master_countries', '=', 'master_countries.id')
            ->where('master_companies.is_active', 1)
            ->first();

        $data = GoodReceiptNote::select('good_receipt_notes.*', 
                'purchase_orders.po_number',
                'master_suppliers.name')
            ->leftJoin('purchase_orders', 'good_receipt_notes.id_purchase_orders','purchase_orders.id')
            ->leftJoin('master_suppliers', 'good_receipt_notes.id_master_suppliers', 'master_suppliers.id')
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
                        ->whereIn('good_receipt_note_details.type_product', ['TA', 'Other']);
            })
            ->leftJoin('master_units', 'good_receipt_note_details.master_units_id', '=', 'master_units.id')
            ->where('good_receipt_note_details.id_good_receipt_notes', $id)
            ->whereNotNull('good_receipt_note_details.status')
            ->orderBy('good_receipt_note_details.created_at')
            ->get();

        $view = ($lang === 'en') ? 'gr_note.print' : 'gr_note.printIDN';
        return view($view, compact('dataCompany', 'data', 'itemDatas'));
    }
    public function export(Request $request)
    {
        // dd($request->all());

        $datas = GoodReceiptNoteDetail::select(
            'good_receipt_notes.id', 'good_receipt_notes.receipt_number', 'good_receipt_notes.date', 'purchase_orders.po_number', 'purchase_requisitions.request_number', 'master_suppliers.name as supplier_name',
            'good_receipt_notes.qc_status', 'good_receipt_notes.external_doc_number', 'good_receipt_notes.non_invoiceable', 'good_receipt_notes.type', 'good_receipt_notes.status as statusGRN',
            'good_receipt_notes.created_at as createdGRN', 'good_receipt_notes.updated_at as updatedGRN', 
            DB::raw('
                CASE 
                    WHEN good_receipt_note_details.type_product = "RM" THEN master_raw_materials.description 
                    WHEN good_receipt_note_details.type_product = "WIP" THEN master_wips.description 
                    WHEN good_receipt_note_details.type_product = "FG" THEN master_product_fgs.description 
                    WHEN good_receipt_note_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.description 
                END as product_desc'),
            'good_receipt_note_details.qty', 'good_receipt_note_details.receipt_qty', 'good_receipt_note_details.outstanding_qty',
            'master_units.unit', 'master_units.unit_code',
            'good_receipt_note_details.qc_passed', 'good_receipt_note_details.lot_number', 'good_receipt_note_details.external_no_lot',
            'good_receipt_note_details.qty_generate_barcode', 'good_receipt_note_details.status as statusItem',
            'good_receipt_note_details.created_at as createdItem', 'good_receipt_note_details.updated_at as updatedItem', 
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
                        ->whereIn('good_receipt_note_details.type_product', ['TA', 'Other']);
            })
            ->leftJoin('good_receipt_notes', 'good_receipt_note_details.id_good_receipt_notes', 'good_receipt_notes.id')
            ->leftJoin('master_units', 'good_receipt_note_details.master_units_id', 'master_units.id')
            ->leftJoin('master_suppliers', 'good_receipt_notes.id_master_suppliers', 'master_suppliers.id')
            ->leftJoin('purchase_orders', 'good_receipt_notes.id_purchase_orders', 'purchase_orders.id')
            ->leftJoin('purchase_requisitions', 'good_receipt_notes.reference_number', 'purchase_requisitions.id')
            ->whereNotNull('good_receipt_note_details.status')
            ->orderBy('good_receipt_notes.created_at');

            if ($request->has('typeItem') && $request->typeItem != '' && $request->typeItem != 'Semua Type') {
                $datas->where('good_receipt_notes.type', $request->typeItem);
            }
            if ($request->has('status') && $request->status != '' && $request->status != 'Semua Status') {
                $datas->where('good_receipt_notes.status', $request->status);
            }
            if($request->has('dateFrom') && $request->dateFrom != '' && $request->has('dateTo') && $request->dateTo != ''){
                $datas->whereBetween('good_receipt_notes.date', [$request->dateFrom, $request->dateTo]);
            }

        $filename = 'Export_GRN_' . Carbon::now()->format('d_m_Y_H_i') . '.xlsx';
        return Excel::download(new GRNExport($datas->get(), $request), $filename);
    }

    //ITEM GRN
    public function updateItem(Request $request, $id)
    {
        // dd($request->all());
        $id = decrypt($id);
        $request->validate([
            'receipt_qty' => 'required',
            'outstanding_qty' => 'required',
        ], [
            'receipt_qty.required' => 'Receipt Qty harus diisi.',
            'outstanding_qty.required' => 'Outstanding Qty harus diisi.',
        ]);

        $receiptQty = (float) (str_replace(['.', ','], ['', '.'], $request->receipt_qty));
        $osQty      = (float) (str_replace(['.', ','], ['', '.'], $request->outstanding_qty));
        $newStatus  = $receiptQty == 0.0 ? null : ($osQty == 0.0 ? 'Close' : 'Open');

        $dataBefore = GoodReceiptNoteDetail::where('id', $id)->first();
        $dataBefore->receipt_qty        = $receiptQty;
        $dataBefore->outstanding_qty    = $osQty;
        $dataBefore->note               = $request->note;

        if($dataBefore->isDirty()){
            DB::beginTransaction();
            try{
                GoodReceiptNoteDetail::where('id', $id)->update([
                    'receipt_qty'     => $receiptQty,
                    'outstanding_qty' => $osQty,
                    'status'          => $newStatus,
                    'note'            => $request->note,
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
