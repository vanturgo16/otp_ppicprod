<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use RealRashid\SweetAlert\Facades\Alert;
use Browser;
use Illuminate\Support\Facades\Crypt;

use App\Models\GoodReceiptNote;
use App\Models\GoodReceiptNoteDetail;
use App\Models\GoodReceiptNoteDetailSmt;
use App\Models\MstSupplier;
use App\Models\PurchaseOrders;
use App\Models\PurchaseRequisitions;
use App\Models\MstUnits;

class GrnController extends Controller
{
    use AuditLogsTrait;

    public function index(){

        $goodReceiptNotes = GoodReceiptNote::select('receipt_number', 'purchase_requisitions.request_number', 'purchase_orders.po_number', 'good_receipt_notes.date', 'external_doc_number', 'master_suppliers.name', 'qc_status', 'good_receipt_notes.type', 'good_receipt_notes.status')
        ->leftJoin('purchase_requisitions', 'good_receipt_notes.reference_number', '=', 'purchase_requisitions.id')
        ->leftJoin('purchase_orders', 'good_receipt_notes.id_purchase_orders', '=', 'purchase_orders.id')
        ->leftJoin('master_suppliers', 'good_receipt_notes.id_master_suppliers', '=', 'master_suppliers.id')
        ->get();

        return view('grn.index',compact('goodReceiptNotes'));
    }
    public function grn_pr_add(){

        $pr = PurchaseRequisitions::all();

        // Ambil nomor urut terakhir dari database
        $lastCode = GoodReceiptNote::orderBy('created_at', 'desc')
        ->value(DB::raw('RIGHT(receipt_number, 7)'));

        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;


        // Format kode dengan panjang 7 karakter
        $formattedCode = 'GR'.date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        return view('grn.add_pr_grn',compact('formattedCode','pr'));
    }
    public function grn_po_add(){
        // Ambil nomor urut terakhir dari database
        $po = DB::table('purchase_orders')->get();

        $lastCode = GoodReceiptNote::orderBy('created_at', 'desc')
        ->value(DB::raw('RIGHT(receipt_number, 7)'));

        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;


        // Format kode dengan panjang 7 karakter
        $formattedCode = 'GR'.date('y') . str_pad($nextCode, 7, '0', STR_PAD_LEFT);

        return view('grn.add_po_grn',compact('formattedCode','po'));
    }
    public function get_data(){
        $data = DB::table('purchase_orders')->select('po_number', 'id')->get();
        $data_pr = DB::table('purchase_requisitions')->select('request_number', 'id')->get();
        $data_sp = DB::table('master_suppliers')->select('name', 'id')->get();
        $id = request()->get('id');
    
        $data_lengkap = DB::table('purchase_orders as a')
                        ->select('b.request_number', 'c.name', 'a.qc_check', 'a.type')
                        ->leftJoin('purchase_requisitions as b', 'a.reference_number', '=', 'b.id')
                        ->leftJoin('master_suppliers as c', 'a.id_master_suppliers', '=', 'c.id')
                        ->where('a.id', $id)
                        ->get();

        $data_lengkap_pr = PurchaseRequisitions::select('master_suppliers.name', 'purchase_requisitions.qc_check', 'purchase_requisitions.type')
                        ->leftJoin('master_suppliers', 'purchase_requisitions.id_master_suppliers', '=', 'master_suppliers.id')
                        ->where('purchase_requisitions.id', $id)
                        ->get();

    
        if(($data_lengkap->isEmpty()) or ($data_lengkap_pr->isEmpty())) {
            return response()->json(['message' => 'Data not found for the given ID'], 404);
        }
    
        return response()->json(['data' => $data, 'data_lengkap' => $data_lengkap,'data_pr' => $data_pr,
        'data_sp' => $data_sp, 'data_lengkap_pr' => $data_lengkap_pr]);
    }
    public function simpan_pr_grn(Request $request)
    {
        // dd($request);
        // die;
        $pesan = [
            'receipt_number.required' => 'receipt number masih kosong',
            'reference_number.required' => 'reference number masih kosong',
            'date.required' => 'date masih kosong',
            'external_doc_number.required' => 'external doc number masih kosong',
            'id_master_suppliers.required' => 'master suppliers masih kosong',
            'note.required' => 'note masih kosong',
            'qc_status.required' => 'qc check masih kosong',
            'non_invoiceable.required' => 'qc check masih kosong',
            'status.required' => 'status masih kosong',
            'type.required' => 'type masih kosong',
        ];

        $validatedData = $request->validate([
            'receipt_number' => 'required',
            'reference_number' => 'required',
            'date' => 'required',
            'external_doc_number' => 'required',
            'id_master_suppliers' => 'required',
            'note' => 'required',
            'qc_status' => 'required',
            'non_invoiceable' => 'required',
            'status' => 'required',
            'type' => 'required',

        ], $pesan);

        GoodReceiptNote::create($validatedData);

        $receipt_number = $request->input('receipt_number');
       
        $idValue = DB::table('good_receipt_notes')
            ->select('id')
            ->where('receipt_number', $receipt_number)
            ->first();

        if ($idValue) {
            $id = $idValue->id;
            return redirect('/detail-grn-pr/'.$id);
        } else {
            // Penanganan jika $id tidak ditemukan
            return redirect()->back()->with('error', 'ID tidak ditemukan');
        }
    }
    public function simpan_po_grn(Request $request)
    {
        // dd($request);
        // die;

        $pesan = [
            'receipt_number.required' => 'receipt number masih kosong',
            'id_purchase_orders.required' => 'purchase orders masih kosong',
            'reference_number.required' => 'reference number masih kosong',
            'date.required' => 'date masih kosong',
            'external_doc_number.required' => 'external doc number masih kosong',
            'id_master_suppliers.required' => 'master suppliers masih kosong',
            'note.required' => 'note masih kosong',
            'qc_status.required' => 'qc check masih kosong',
            'non_invoiceable.required' => 'qc check masih kosong',
            'status.required' => 'status masih kosong',
            'type.required' => 'type masih kosong',
        ];

        $validatedData = $request->validate([
            'receipt_number' => 'required',
            'id_purchase_orders' => 'required',
            'reference_number' => 'required',
            'date' => 'required',
            'external_doc_number' => 'required',
            'id_master_suppliers' => 'required',
            'note' => 'required',
            'qc_status' => 'required',
            'non_invoiceable' => 'required',
            'status' => 'required',
            'type' => 'required',

        ], $pesan);

        // dd($validatedData);
        // die;

        GoodReceiptNote::create($validatedData);

        $receipt_number = $request->input('receipt_number');
       
        $idValue = DB::table('good_receipt_notes')
            ->select('id')
            ->where('receipt_number', $receipt_number)
            ->first();

        if ($idValue) {
            $id = $idValue->id;
            return redirect('/detail-grn-po/'.$id);
        } else {
            // Penanganan jika $id tidak ditemukan
            return redirect()->back()->with('error', 'ID tidak ditemukan');
        }
    }
    public function detail_grn_pr($id)
    {
        // dd($id);
        // die;

        $grn_po = GoodReceiptNote::select('purchase_requisitions.id','receipt_number', 'purchase_requisitions.request_number', 'purchase_orders.po_number', 'good_receipt_notes.date', 'external_doc_number', 'master_suppliers.name', 'qc_status', 'good_receipt_notes.type', 'good_receipt_notes.status', 'good_receipt_notes.remarks')
        ->leftJoin('purchase_requisitions', 'good_receipt_notes.reference_number', '=', 'purchase_requisitions.id')
        ->leftJoin('purchase_orders', 'good_receipt_notes.id_purchase_orders', '=', 'purchase_orders.id')
        ->leftJoin('master_suppliers', 'good_receipt_notes.id_master_suppliers', '=', 'master_suppliers.id')
        ->where('good_receipt_notes.id', $id)
        ->get();

        $typex = $grn_po[0]->type;
        $request_numbers = $grn_po[0]->request_number;
        $id_purchase_requisitions = $grn_po[0]->id;
        

        $unit = MstUnits::all();

        $data_detail_ta = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.qty','a.outstanding_qty', 'b.description', 'c.unit')
                        ->where('a.id_purchase_requisitions', $id_purchase_requisitions)
                        ->get();

        $data_detail_rm = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.qty','a.outstanding_qty', 'b.description', 'c.unit')
                        ->where('a.id_purchase_requisitions', $id_purchase_requisitions)
                        ->get();

        $data_detail_fg = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.qty','a.outstanding_qty', 'b.description', 'c.unit')
                        ->where('a.id_purchase_requisitions', $id_purchase_requisitions)
                        ->get();

        $data_detail_wip = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.qty','a.outstanding_qty', 'b.description', 'c.unit')
                        ->where('a.id_purchase_requisitions', $id_purchase_requisitions)
                        ->get();

        $rm = DB::table('master_raw_materials')
                        ->select('description','id')
                        ->get();
        $ta = DB::table('master_tool_auxiliaries')
                        ->select('description','id')
                        ->get();
        $fg = DB::table('master_product_fgs')
                        ->select('description','id')
                        ->get();
        $wip = DB::table('master_wips')
                        ->select('description','id')
                        ->get();

        return view('grn.detail_pr_grn',compact('grn_po','unit','data_detail_ta','data_detail_rm','data_detail_fg'
                        ,'data_detail_wip','rm','ta','fg','wip','typex'));
    }
    public function detail_grn_po($id)
    {
        // dd($id);
        // die;
        $grn_po = GoodReceiptNote::select('purchase_requisitions.id','receipt_number', 'purchase_requisitions.request_number', 'purchase_orders.po_number', 'good_receipt_notes.date', 'external_doc_number', 'master_suppliers.name', 'qc_status', 'good_receipt_notes.type', 'good_receipt_notes.status', 'good_receipt_notes.remarks')
        ->leftJoin('purchase_requisitions', 'good_receipt_notes.reference_number', '=', 'purchase_requisitions.id')
        ->leftJoin('purchase_orders', 'good_receipt_notes.id_purchase_orders', '=', 'purchase_orders.id')
        ->leftJoin('master_suppliers', 'good_receipt_notes.id_master_suppliers', '=', 'master_suppliers.id')
        ->where('good_receipt_notes.id', $id)
        ->get();

        $typex = $grn_po[0]->type;
        $request_numbers = $grn_po[0]->request_number;
        $id_purchase_requisitions = $grn_po[0]->id;

        $unit = MstUnits::all();

        $data_detail_ta = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_tool_auxiliaries as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.qty','a.outstanding_qty', 'b.description', 'c.unit')
                        ->where('a.id_purchase_requisitions', $id_purchase_requisitions)
                        ->get();

        $data_detail_rm = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_raw_materials as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.qty','a.outstanding_qty', 'b.description', 'c.unit')
                        ->where('a.id_purchase_requisitions', $id_purchase_requisitions)
                        ->get();

        $data_detail_fg = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_product_fgs as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.qty','a.outstanding_qty', 'b.description', 'c.unit')
                        ->where('a.id_purchase_requisitions', $id_purchase_requisitions)
                        ->get();

        $data_detail_wip = DB::table('purchase_requisition_details as a')
                        ->leftJoin('master_wips as b', 'a.master_products_id', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.qty','a.outstanding_qty', 'b.description', 'c.unit')
                        ->where('a.id_purchase_requisitions', $id_purchase_requisitions)
                        ->get();

        $rm = DB::table('master_raw_materials')
                        ->select('description','id')
                        ->get();
        $ta = DB::table('master_tool_auxiliaries')
                        ->select('description','id')
                        ->get();
        $fg = DB::table('master_product_fgs')
                        ->select('description','id')
                        ->get();
        $wip = DB::table('master_wips')
                        ->select('description','id')
                        ->get();

       

        // dd($grn_po);
        // die;

        return view('grn.detail_po_grn',compact('grn_po','unit','data_detail_ta','data_detail_rm','data_detail_fg'
        ,'data_detail_wip','rm','ta','fg','wip','typex'));
    }
    
}
