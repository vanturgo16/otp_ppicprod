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
use App\Models\MstSupplier;
use App\Models\PurchaseOrders;
use App\Models\PurchaseRequisitions;

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
    
}
