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

class GrnController extends Controller
{
    use AuditLogsTrait;

    public function index(Request $request){

        // $goodReceiptNotes = GoodReceiptNote::select('good_receipt_notes.id','receipt_number', 'purchase_requisitions.request_number', 'purchase_orders.po_number', 'good_receipt_notes.date', 'external_doc_number', 'master_suppliers.name', 'qc_status', 'good_receipt_notes.type', 'good_receipt_notes.status')
        // ->leftJoin('purchase_requisitions', 'good_receipt_notes.reference_number', '=', 'purchase_requisitions.id')
        // ->leftJoin('purchase_orders', 'good_receipt_notes.id_purchase_orders', '=', 'purchase_orders.id')
        // ->leftJoin('master_suppliers', 'good_receipt_notes.id_master_suppliers', '=', 'master_suppliers.id')
        // ->orderBy('good_receipt_notes.created_at', 'desc')
        // ->limit(10)  // Menambahkan limit di sini
        // ->get();

        if (request()->ajax()) {
            $orderColumn = $request->input('order')[0]['column'];
            $orderDirection = $request->input('order')[0]['dir'];
            $columns = ['id', 'receipt_number', 'request_number', 'po_number', 'date', 'external_doc_number', 'name', 'qc_status', 'type', 'status', ''];

            // Query dasar
            $query = GoodReceiptNote::select('good_receipt_notes.id','receipt_number', 'purchase_requisitions.request_number', 'purchase_orders.po_number', 'good_receipt_notes.date', 'external_doc_number', 'master_suppliers.name', 'qc_status', 'good_receipt_notes.type', 'good_receipt_notes.status')
            ->leftJoin('purchase_requisitions', 'good_receipt_notes.reference_number', '=', 'purchase_requisitions.id')
            ->leftJoin('purchase_orders', 'good_receipt_notes.id_purchase_orders', '=', 'purchase_orders.id')
            ->leftJoin('master_suppliers', 'good_receipt_notes.id_master_suppliers', '=', 'master_suppliers.id')
            ->orderBy($columns[$orderColumn], $orderDirection);

            // Handle pencarian
            if ($request->has('search') && $request->input('search')) {
                $searchValue = $request->input('search');
                $query->where(function ($query) use ($searchValue) {
                    $query->where('receipt_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('purchase_requisitions.request_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('purchase_orders.po_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('good_receipt_notes.date', 'like', '%' . $searchValue . '%')
                        ->orWhere('external_doc_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('master_suppliers.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('qc_status', 'like', '%' . $searchValue . '%')
                        ->orWhere('good_receipt_notes.type', 'like', '%' . $searchValue . '%')
                        ->orWhere('good_receipt_notes.status', 'like', '%' . $searchValue . '%');
                });
            }

            return DataTables::of($query)
                ->addColumn('action', function ($data) {
                    return view('grn.action', compact('data'));
                })
                ->addColumn('status', function ($data) {
                    $badgeColor = $data->status == 'Hold' ? 'info' : ($data->status == 'Un Posted' ? 'warning' : 'success');
                    return '<span class="badge bg-' . $badgeColor . '" style="font-size: smaller;width: 100%">' . $data->status . '</span>';
                })
                ->addColumn('statusLabel', function ($data) {
                    return $data->status;
                })
                ->rawColumns(['action', 'status', 'statusLabel'])
                ->make(true);
        }

        // return view('grn.index',compact('goodReceiptNotes'));
        return view('grn.index');
    }
    public function grn_pr_add(){

        $pr = PurchaseRequisitions::where('status', 'Posted')->get();
 

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
        $po = DB::table('purchase_orders')
        ->where('status', 'Posted')
        ->get();

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

                        // return response()->json($data_lengkap_pr->isEmpty());
    
                        if($data_lengkap_pr->isEmpty()==true) {
                            return response()->json(['message' => 'Data not found for the given ID'], 500);
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
        $id = $idValue->id;

        $details = PurchaseRequisitionsDetail::select( 'type_product', 'master_products_id', 'outstanding_qty', 'qty', 'master_units_id')
            ->where('id_purchase_requisitions', $request->reference_number)
            ->get();

            // Simpan hasil query ke dalam tabel good_receipt_note_details
        foreach ($details as $result) {
            DB::table('good_receipt_note_details')->insert([
                'id_good_receipt_notes' => $id,
                'type_product' => $result->type_product,
                'id_master_products' => $result->master_products_id,
                'note' => '',
                'outstanding_qty' => $result->outstanding_qty,
                'receipt_qty' => $result->qty,
                'master_units_id' => $result->master_units_id,
            ]);
        }

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

        $id = $idValue->id;

        $details = PurchaseRequisitionsDetail::select( 'type_product', 'master_products_id', 'outstanding_qty', 'qty', 'master_units_id')
            ->where('id_purchase_requisitions', $request->reference_number)
            ->get();

                // Simpan hasil query ke dalam tabel good_receipt_note_details
        foreach ($details as $result) {
            DB::table('good_receipt_note_details')->insert([
                'id_good_receipt_notes' => $id,
                'type_product' => $result->type_product,
                'id_master_products' => $result->master_products_id,
                'note' => '',
                'outstanding_qty' => $result->outstanding_qty,
                'receipt_qty' => $result->qty,
                'master_units_id' => $result->master_units_id,
            ]);
        }
        

        if ($idValue) {
            
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

        $data_detail_ta = DB::table('good_receipt_note_details as a')
                        ->leftJoin('master_tool_auxiliaries as b', 'a.id_master_products', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.receipt_qty','a.outstanding_qty', 'b.description', 'c.unit','a.note')
                        ->where('a.id_good_receipt_notes', $id)
                        ->get();

        $data_detail_rm = DB::table('good_receipt_note_details as a')
                        ->leftJoin('master_raw_materials as b', 'a.id_master_products', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.receipt_qty','a.outstanding_qty', 'b.description', 'c.unit','a.note')
                        ->where('a.id_good_receipt_notes', $id)
                        ->get();

        $data_detail_fg = DB::table('good_receipt_note_details as a')
                        ->leftJoin('master_product_fgs as b', 'a.id_master_products', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.receipt_qty','a.outstanding_qty', 'b.description', 'c.unit','a.note')
                        ->where('a.id_good_receipt_notes', $id)
                        ->get();

        $data_detail_wip = DB::table('good_receipt_note_details as a')
                        ->leftJoin('master_wips as b', 'a.id_master_products', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.receipt_qty','a.outstanding_qty', 'b.description', 'c.unit','a.note')
                        ->where('a.id_good_receipt_notes', $id)
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
                        ,'data_detail_wip','rm','ta','fg','wip','typex','id'));
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

        $data_detail_ta = DB::table('good_receipt_note_details as a')
                        ->leftJoin('master_tool_auxiliaries as b', 'a.id_master_products', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.receipt_qty','a.outstanding_qty', 'b.description', 'c.unit','a.note')
                        ->where('a.id_good_receipt_notes', $id)
                        ->get();

                        $data_detail_rm = DB::table('good_receipt_note_details as a')
                        ->leftJoin('master_raw_materials as b', 'a.id_master_products', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.receipt_qty','a.outstanding_qty', 'b.description', 'c.unit','a.note')
                        ->where('a.id_good_receipt_notes', $id)
                        ->get();

        $data_detail_fg = DB::table('good_receipt_note_details as a')
                        ->leftJoin('master_product_fgs as b', 'a.id_master_products', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.receipt_qty','a.outstanding_qty', 'b.description', 'c.unit','a.note')
                        ->where('a.id_good_receipt_notes', $id)
                        ->get();

        $data_detail_wip = DB::table('good_receipt_note_details as a')
                        ->leftJoin('master_wips as b', 'a.id_master_products', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.receipt_qty','a.outstanding_qty', 'b.description', 'c.unit','a.note')
                        ->where('a.id_good_receipt_notes', $id)
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
        ,'data_detail_wip','rm','ta','fg','wip','typex','id'));
    }
    public function hapus_grn_detail(Request $request, $id, $idx)
    {
        // dd('test');
        // die;
        GoodReceiptNoteDetail::destroy($id);

        if ($id) {
            //redirect dengan pesan sukses
            return Redirect::to('/detail-grn-pr/'.$idx)->with('pesan', 'Data berhasil dihapus.');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/good-receipt-note')->with('pesan', 'Data gagal berhasil dihapus.');
        }

    }
    public function hapus_grn_detail_po(Request $request, $id, $idx)
    {
        // dd('test');
        // die;
        GoodReceiptNoteDetail::destroy($id);

        if ($id) {
            //redirect dengan pesan sukses
            return Redirect::to('/detail-grn-po/'.$idx)->with('pesan', 'Data berhasil dihapus.');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/good-receipt-note')->with('pesan', 'Data gagal berhasil dihapus.');
        }

    }
    public function hapus_grn(Request $request, $id)
    {
        // dd('test');
        // die;
        GoodReceiptNote::destroy($id);
        GoodReceiptNoteDetail::where('id_good_receipt_notes', $id)->delete();

        if ($id) {
            //redirect dengan pesan sukses
            return Redirect::to('/good-receipt-note')->with('pesan', 'Data berhasil dihapus.');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/good-receipt-note')->with('pesan', 'Data gagal berhasil dihapus.');
        }

    }
    public function simpan_detail_grn(Request $request, $id)
    {
        // dd('test');
        // die;
        $request->merge([
            'id_good_receipt_notes' => $id,
        ]);

        $pesan = [
            'id_good_receipt_notes.required' => 'type product masih kosong',
            'type_product.required' => 'type product masih kosong',
            'id_master_products.required' => 'master products masih kosong',
            'receipt_qty.required' => 'reference number masih kosong',
            'outstanding_qty.required' => 'date masih kosong',
            'master_units_id.required' => 'external doc number masih kosong',
            'note.required' => 'note masih kosong',
        ];

        $validatedData = $request->validate([
            'id_good_receipt_notes' => 'required',
            'type_product' => 'required',
            'id_master_products' => 'required',
            'receipt_qty' => 'required',
            'outstanding_qty' => 'required',
            'master_units_id' => 'required',
            'note' => 'required',

        ], $pesan);

        GoodReceiptNoteDetail::create($validatedData);
        if ($id) {
            return redirect('/detail-grn-pr/'.$id)->with('pesan', 'Data berhasil ditambahkan');
        } else {
            // Penanganan jika $id tidak ditemukan
            return redirect()->back()->with('error', 'ID tidak ditemukan');
        }
    }
    public function simpan_detail_grn_po(Request $request, $id)
    {
        // dd('test');
        // die;
        $request->merge([
            'id_good_receipt_notes' => $id,
        ]);

        $pesan = [
            'id_good_receipt_notes.required' => 'type product masih kosong',
            'type_product.required' => 'type product masih kosong',
            'id_master_products.required' => 'master products masih kosong',
            'receipt_qty.required' => 'reference number masih kosong',
            'outstanding_qty.required' => 'date masih kosong',
            'master_units_id.required' => 'external doc number masih kosong',
            'note.required' => 'note masih kosong',
        ];

        $validatedData = $request->validate([
            'id_good_receipt_notes' => 'required',
            'type_product' => 'required',
            'id_master_products' => 'required',
            'receipt_qty' => 'required',
            'outstanding_qty' => 'required',
            'master_units_id' => 'required',
            'note' => 'required',

        ], $pesan);

        GoodReceiptNoteDetail::create($validatedData);
        if ($id) {
            return redirect('/detail-grn-po/'.$id)->with('pesan', 'Data berhasil ditambahkan');
        } else {
            // Penanganan jika $id tidak ditemukan
            return redirect()->back()->with('error', 'ID tidak ditemukan');
        }
    }

    public function good_lote_number(Request $request){

        // $receiptDetails = DB::table('good_receipt_notes as a')
        //         ->leftJoin('good_receipt_note_details as c', 'a.id', '=', 'c.id_good_receipt_notes')
        //         ->leftJoin('master_raw_materials as b', 'b.id', '=', 'c.id_master_products')
        //         ->leftJoin('master_units as d', 'c.master_units_id', '=', 'd.id')
        //         ->select(
        //             'c.id',
        //             'a.receipt_number',
        //             DB::raw("CONCAT(b.rm_code, '-', b.description) as description"),
        //             'c.receipt_qty',
        //             'd.unit_code',
        //             'c.qc_passed',
        //             'c.lot_number',
        //             'c.note'
        //         )
        //         ->where('a.type', 'RM')
        //         ->get();

            // Menggunakan DB::raw untuk menggabungkan nilai kolom b.rm_code dan b.description dengan CONCAT dalam SQL
            // Hasilnya disimpan dalam alias product_desc

            if (request()->ajax()) {
                $orderColumn = $request->input('order')[0]['column'];
                $orderDirection = $request->input('order')[0]['dir'];
                $columns = ['id', 'receipt_number', 'description', 'receipt_qty', 'unit_code', 'qc_passed', 'lot_number', 'status', ''];
    
                // Query dasar
                $query = DB::table('good_receipt_notes as a')
                ->leftJoin('good_receipt_note_details as c', 'a.id', '=', 'c.id_good_receipt_notes')
                ->leftJoin('master_raw_materials as b', function($join) {
                    $join->on('b.id', '=', 'c.id_master_products')
                         ->where('a.type', 'RM');
                })
                ->leftJoin('master_product_fgs as fg', function($join) {
                    $join->on('fg.id', '=', 'c.id_master_products')
                         ->where('a.type', 'FG');
                })
                ->leftJoin('master_wips as w', function($join) {
                    $join->on('w.id', '=', 'c.id_master_products')
                         ->where('a.type', 'WIP');
                })
                ->leftJoin('master_tool_auxiliaries as ta', function($join) {
                    $join->on('ta.id', '=', 'c.id_master_products')
                         ->where('a.type', 'TA');
                })
                ->leftJoin('master_units as d', 'c.master_units_id', '=', 'd.id')
                ->select(
                    'c.id',
                    'c.id_good_receipt_notes',
                    'a.receipt_number',
                    DB::raw("CASE 
                        WHEN a.type = 'RM' THEN CONCAT(b.rm_code, '-', b.description)
                        WHEN a.type = 'FG' THEN CONCAT(fg.product_code, '-', fg.description)
                        WHEN a.type = 'WIP' THEN CONCAT(w.wip_code, '-', w.description)
                        WHEN a.type = 'TA' THEN CONCAT(ta.code, '-', ta.description)
                    END as description"),
                    'c.receipt_qty',
                    'd.unit_code',
                    'c.qc_passed',
                    'c.lot_number',
                    'c.note'
                )
                ->whereIn('a.type', ['RM', 'FG', 'WIP', 'TA'])
                ->orderBy($columns[$orderColumn], $orderDirection);
            
    
                // Handle pencarian
                if ($request->has('search') && $request->input('search')) {
                    $searchValue = $request->input('search');
                    $query->where(function ($query) use ($searchValue) {
                        $query->where('a.receipt_number', 'like', '%' . $searchValue . '%')
                            ->orWhere('description', 'like', '%' . $searchValue . '%')
                            ->orWhere('c.receipt_qty', 'like', '%' . $searchValue . '%')
                            ->orWhere('d.unit_code', 'like', '%' . $searchValue . '%')
                            ->orWhere('c.qc_passed', 'like', '%' . $searchValue . '%')
                            ->orWhere('c.lot_number', 'like', '%' . $searchValue . '%')
                            ->orWhere('c.note', 'like', '%' . $searchValue . '%');
                    });
                }
    
                return DataTables::of($query)
                    ->addColumn('action', function ($data) {
                        return view('grn.action_gln', compact('data'));
                        // return "ACTION";
                    })
                    ->addColumn('generate_lot', function ($data) {
                        return view('grn.action_generate_lot', compact('data'));
                        // return "ACTION";
                    })
                    
                    ->rawColumns(['action'])
                    ->make(true);
            }

        return view('grn.good_lote_number');
    }
    public function generateCode()
    {
       // Ambil tahun 2 digit terakhir
        $year = date('y');

        // Ambil nomor urut terakhir dari database
        $lastCode = GoodReceiptNoteDetail::whereNotNull('lot_number')
            ->orderBy('id', 'desc')
            ->value(DB::raw('MID(lot_number, 5, 5)'));
            
        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = str_pad($lastCode + 1, 5, '0', STR_PAD_LEFT);

        // Ambil bulan saat ini dalam format dua digit
        $currentMonth = date('m');

        // Format kode dengan urutan tahun, bulan, nomor urut, dan karakter konstan
        $formattedCode = sprintf('%02d%s%05dM', $year, $currentMonth, $nextCode);
        $data['find'] = $formattedCode;

        return response()->json(['data' => $data]);


        // return response()->json(['code' => $formattedCode]);
    }
    public function get_edit_grn_pr($id)
    {
        $data['find'] = GoodReceiptNote::find($id);
        $data['finddetail'] = GoodReceiptNoteDetail::find($id);
        $data['produk'] = DB::select("SELECT master_raw_materials.description, master_raw_materials.id FROM master_raw_materials");
        $data['unit'] = DB::select("SELECT master_units.unit_code, master_units.id FROM master_units");
        return response()->json(['data' => $data]);
    }
    public function update_lot_number(Request $request)
    {
        // dd($request->lot_number);
        // die;
        $receiptQty = DB::table('good_receipt_note_details')
                    ->where('id', $request->id)
                    ->value('receipt_qty'); // Mengambil nilai receipt_qty

        $jumlah = DB::table('detail_good_receipt_note_details')
                    ->where('lot_number', $request->lot_number)
                    ->sum('qty');

        $totaljumlah = $jumlah+$request->qty_generate_barcode;

        // dd($totaljumlah);
        // die;

        if ($request->qty_generate_barcode > $receiptQty) {
            // Jika total qty sudah mencapai atau melebihi batas, proses insert dan update tidak boleh dilanjutkan
            return redirect()->back()->with('error', 'Total qty sudah mencapai batas, tidak bisa melakukan update atau insert.');
        }elseif ($totaljumlah > $receiptQty) {
            // Jika total qty sudah mencapai atau melebihi batas, proses insert dan update tidak boleh dilanjutkan
            return redirect()->back()->with('error', 'Total qty sudah mencapai batas, tidak bisa melakukan update atau insert.');
        }


        $validatedData = DB::update("UPDATE `good_receipt_note_details` SET `lot_number` = '$request->lot_number',qty_generate_barcode='$request->qty_generate_barcode' WHERE `id` = '$request->id';");

        $validatedData = DB::table('detail_good_receipt_note_details')->insert([
            'id_grn' => $request->id_grn,
            'id_grn_detail' => $request->id,
            'lot_number' => $request->lot_number,
            'ext_lot_number' => $request->qty_generate_barcode,
            'qty' => $request->qty_generate_barcode
        ]);
        

        if ($validatedData) {
            return redirect('/good-lote-number')->with('pesan', 'Data berhasil ditambahkan');
        } else {
            // Penanganan jika $id tidak ditemukan
            return redirect()->back()->with('error', 'ID tidak ditemukan');
        }
        
    }
    public function good_lote_number_detail($id)
    {
        // dd('test');
        // die;
        $goodReceiptNoteDetail = GoodReceiptNoteDetail::find($id);

        $goodReceiptNoteDetail = GoodReceiptNoteDetail::select('good_receipt_note_details.*', 'master_units.unit_code')
        ->leftJoin('master_units', 'good_receipt_note_details.master_units_id', '=', 'master_units.id')
        ->where('good_receipt_note_details.id', $id)
        ->first();

        $receipt_qty = $goodReceiptNoteDetail->receipt_qty;
        $qc_passed = $goodReceiptNoteDetail->qc_passed;
        $outstanding_qty = $goodReceiptNoteDetail->outstanding_qty;
        $note = $goodReceiptNoteDetail->note;
        $unit_code = $goodReceiptNoteDetail->unit_code;
        $id_good_receipt_notes=$goodReceiptNoteDetail->id_good_receipt_notes;

        // dd($receipt_qty);
        // die;

        return view('grn.good_lote_number_detail',compact('goodReceiptNoteDetail','receipt_qty','qc_passed',
        'outstanding_qty','note','unit_code','id_good_receipt_notes'));
    }
    // public function generateBarcode($lot_number)
    // {
    //     $generator = new BarcodeGeneratorHTML();
    //     $barcode = $generator->getBarcode($lot_number, $generator::TYPE_CODE_128);

    //     $qtyGenerateBarcode = GoodReceiptNoteDetail::select('qty_generate_barcode')
    //     ->where('lot_number', $lot_number)
    //     ->first();

    //     return view('grn.barcode_grn', compact('barcode','lot_number','qtyGenerateBarcode'));
    // }
    // public function generateBarcode($lot_number)
    // {
    //     // dd('test');
    //     // die;
    //     $generator = new BarcodeGeneratorHTML();
    //     $barcode = $generator->getBarcode($lot_number, $generator::TYPE_CODE_128);

    //     $qtyGenerateBarcode = DetailGoodReceiptNoteDetail::select('ext_lot_number')
    //     ->where('lot_number', $lot_number)
    //     ->first();

    //     $data = DB::select("SELECT
    //     COUNT(ext_lot_number) AS total_ext_lot_number
    //     FROM
    //         `detail_good_receipt_note_details`
    //     WHERE
    //         `lot_number` = '$lot_number'");

    //     $qty=$data[0]->total_ext_lot_number;
    //     // dd($qty);
    //     // die;

    //     return view('grn.barcode_grn', compact('barcode','lot_number','qtyGenerateBarcode','qty'));
    // }

    public function generateBarcode(Request $request, $lot_number)
{
    $generator = new BarcodeGeneratorHTML();
    $barcode = $generator->getBarcode($lot_number, $generator::TYPE_CODE_128);

    $qty = $request->input('qty', 1); // Jika tidak ada input, default adalah 1

    return view('grn.barcode_grn', compact('barcode','lot_number','qty'));
}
    public function grn_qc(Request $request)
    {
        // dd('test');
        // die;

        // $receiptDetails = DB::table('good_receipt_notes as a')
        //         ->leftJoin('good_receipt_note_details as c', 'a.id', '=', 'c.id_good_receipt_notes')
        //         ->leftJoin('master_raw_materials as b', 'b.id', '=', 'c.id_master_products')
        //         ->leftJoin('master_units as d', 'c.master_units_id', '=', 'd.id')
        //         ->leftJoin('cms_users as e', 'e.id', '=', 'c.qc_check_by')
        //         ->select(
        //             'c.id',
        //             'a.receipt_number',
        //             DB::raw("CONCAT(b.rm_code, '-', b.description) as description"),
        //             'c.receipt_qty',
        //             'd.unit_code',
        //             'c.qc_passed',
        //             'c.lot_number',
        //             'c.note',
        //             'e.name'
        //         )
        //         ->where('a.type', 'RM')
        //         ->where('a.qc_status', 'Y')
        //         ->get();

        if (request()->ajax()) {
            $orderColumn = $request->input('order')[0]['column'];
            $orderDirection = $request->input('order')[0]['dir'];
            $columns = ['id', 'receipt_number', 'description', 'receipt_qty', 'unit_code', 'qc_passed', 'lot_number','note', ''];

            // Query dasar
            $query = DB::table('good_receipt_notes as a')
                    ->leftJoin('good_receipt_note_details as c', 'a.id', '=', 'c.id_good_receipt_notes')
                    ->leftJoin('master_raw_materials as b', 'b.id', '=', 'c.id_master_products')
                    ->leftJoin('master_units as d', 'c.master_units_id', '=', 'd.id')
                    ->leftJoin('cms_users as e', 'e.id', '=', 'c.qc_check_by')
                    ->select(
                        'c.id',
                        'a.receipt_number',
                        DB::raw("CONCAT(b.rm_code, '-', b.description) as description"),
                        'c.receipt_qty',
                        'd.unit_code',
                        'c.qc_passed',
                        'c.lot_number',
                        'c.note',
                        'e.name'
                    )
                    ->where('a.type', 'RM')
                    ->where('a.qc_status', 'Y')
            ->orderBy($columns[$orderColumn], $orderDirection);

            // Handle pencarian
            if ($request->has('search') && $request->input('search')) {
                $searchValue = $request->input('search');
                $query->where(function ($query) use ($searchValue) {
                    $query->where('a.receipt_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('description', 'like', '%' . $searchValue . '%')
                        ->orWhere('c.receipt_qty', 'like', '%' . $searchValue . '%')
                        ->orWhere('d.unit_code', 'like', '%' . $searchValue . '%')
                        ->orWhere('c.qc_passed', 'like', '%' . $searchValue . '%')
                        ->orWhere('c.lot_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('c.note', 'like', '%' . $searchValue . '%');
                });
            }

            return DataTables::of($query)
                ->addColumn('action', function ($data) {
                    return view('grn.action_grn_qc', compact('data'));
                    // return "ACTION";
                })
                
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('grn.grn_qc');
    }
    public function qc_passed($id)
    {
        // dd($id);
        // die;

        $validatedData = DB::update("UPDATE `good_receipt_note_details` SET `qc_passed` = 'Y', qc_check_by = '51' WHERE `id` = '$id';");

        if ($validatedData) {
            //redirect dengan pesan sukses
            return Redirect::to('/grn-qc')->with('pesan', 'Data berhasil di QC .');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/grn-qc')->with('pesan', 'Data gagal berhasil di QC.');
        }
    }
    public function un_qc_passed($id)
    {
        // dd($id);
        // die;

        $validatedData = DB::update("UPDATE `good_receipt_note_details` SET `qc_passed` = 'N', qc_check_by = '51' WHERE `id` = '$id';");

        if ($validatedData) {
            //redirect dengan pesan sukses
            return Redirect::to('/grn-qc')->with('pesan', 'Data berhasil di un QC .');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/grn-qc')->with('pesan', 'Data gagal berhasil di un QC.');
        }
    }
public function external_no_lot (Request $request)
{
    if ($request->ajax()) {
        $orderColumn = $request->input('order')[0]['column'];
        $orderDirection = $request->input('order')[0]['dir'];
        $columns = ['id', 'id_grn_detail', 'lot_number', 'ext_lot_number', 'qty'];

        $query = DB::table('detail_good_receipt_note_details')
            ->select('id', 'id_grn_detail', 'lot_number', 'ext_lot_number', 'qty')
            ->distinct()
            ->orderBy($columns[$orderColumn], $orderDirection);

        if ($searchValue = $request->input('search.value')) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('id_grn_detail', 'like', '%' . $searchValue)
                      ->orWhere('lot_number', 'like', '%' . $searchValue)
                      ->orWhere('ext_lot_number', 'like', '%' . $searchValue)
                      ->orWhere('qty', 'like', '%' . $searchValue);
            });
        }

        return DataTables::of($query)
            ->addColumn('action', function ($data) {
                return view('grn.action_external_nolot', compact('data'));
            })
            ->addColumn('action_generate', function ($data) {
                return view('grn.action_generate_ext_lot', compact('data'));
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    return view('grn.external_no_lot');
}

    public function update_ext_lot_number(Request $request)
    {
        // Validasi data input
        $request->validate([
            'id' => 'required|integer',
            'ext_lot_number' => 'required|string',
            'qty' => 'required|numeric',
        ]);

        // Update tabel detail_good_receipt_note_details
        $statusUpdate = DB::table('detail_good_receipt_note_details')
            ->where('id', $request->id)
            ->update([
                'ext_lot_number' => $request->ext_lot_number,
                'qty' => $request->qty,
            ]);

        // Cek apakah update berhasil
        if (!$statusUpdate) {
            return redirect()->back()->with('error', 'Gagal mengupdate data');
        }

        // Ambil id_grn_detail
        $id_grn_detail = DetailGoodReceiptNoteDetail::select('id_grn_detail')->where('id', $request->id)->first();

        if (!$id_grn_detail) {
            return redirect()->back()->with('error', 'ID tidak ditemukan');
        }

        // Ambil id_master_products
        $result = GoodReceiptNoteDetail::select('id_master_products')->where('id', $id_grn_detail->id_grn_detail)->first();
        $id_master_products = $result->id_master_products;

        // Ambil type_product
        $type_product = GoodReceiptNoteDetail::select('type_product')->where('id', $id_grn_detail->id_grn_detail)->first();

        if (!$type_product) {
            return redirect()->back()->with('error', 'Tipe produk tidak ditemukan');
        }

        // Ambil stok saat ini dan tabel yang sesuai berdasarkan tipe produk
        switch ($type_product->type_product) {
            case 'RM':
                $stock = DB::table('master_raw_materials')->select('stock')->where('id', $id_master_products)->first();
                $table = 'master_raw_materials';
                break;
            case 'WIP':
                $stock = DB::table('master_wips')->select('stock')->where('id', $id_master_products)->first();
                $table = 'master_wips';
                break;
            case 'TA':
                $stock = DB::table('master_tool_auxiliaries')->select('stock')->where('id', $id_master_products)->first();
                $table = 'master_tool_auxiliaries';
                break;
            case 'FG':
                $stock = DB::table('master_product_fgs')->select('stock')->where('id', $id_master_products)->first();
                $table = 'master_product_fgs';
                break;
            default:
                return redirect()->back()->with('error', 'Tipe produk tidak valid');
        }

        if (!$stock) {
            return redirect()->back()->with('error', 'Stok tidak ditemukan');
        }

        // Update stok
        $stokBaru = $stock->stock + $request->qty;
        $statusUpdateStok = DB::table($table)
            ->where('id', $id_master_products)
            ->update(['stock' => $stokBaru]);

        if (!$statusUpdateStok) {
            return redirect()->back()->with('error', 'Gagal mengupdate stok');
        }

        // Ambil data good_receipt_note_details
        $allStocks = DB::table('good_receipt_note_details')
            ->where('id', $id_grn_detail->id_grn_detail)
            ->first();

        if (!$allStocks) {
            return redirect()->back()->with('error', 'Detail penerimaan barang tidak ditemukan');
        }

        // Insert record baru ke tabel history_stocks
        $statusInsert = DB::table('history_stocks')->insert([
            'id_good_receipt_notes_details' => $allStocks->id,
            'type_product' => $allStocks->type_product,
            'id_master_products' => $allStocks->id_master_products,
            'qty' => $request->qty,
            'type_stock' => 'IN',
            'date' => DB::raw('CURRENT_DATE()')
        ]);

        if (!$statusInsert) {
            return redirect()->back()->with('error', 'Gagal menambahkan catatan riwayat stok');
        }

        return redirect('/external-no-lot')->with('pesan', 'Data berhasil ditambahkan');
    }

    public function detail_external_no_lot ($lot_number)
    {
        // dd($id);
        // die;

        $details = DB::select("SELECT * FROM `detail_good_receipt_note_details` where lot_number='$lot_number'");

        return view('grn.detail_external_no_lot',compact('details'));
    }
    public function print_grn($receipt_number)
    {
        // dd('tets');
        // die;
        $type = DB::table('good_receipt_notes')
        ->where('receipt_number', $receipt_number)
        ->get();

        // dd($type[0]->type);
        // die;

        $data_ta = DB::table('good_receipt_note_details as a')
                ->select('b.code', 'b.description', 'a.receipt_qty', 'c.unit')
                ->leftJoin('master_tool_auxiliaries as b', 'a.id_master_products', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->leftJoin('good_receipt_notes as d', 'a.id_good_receipt_notes', '=', 'd.id')
                ->where('d.receipt_number', '=', $receipt_number)
                ->get();

        $data_rm = DB::table('good_receipt_note_details as a')
                ->select('b.rm_code', 'b.description', 'a.receipt_qty', 'c.unit')
                ->leftJoin('master_raw_materials as b', 'a.id_master_products', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->leftJoin('good_receipt_notes as d', 'a.id_good_receipt_notes', '=', 'd.id')
                ->where('d.receipt_number', '=', $receipt_number)
                ->get();

        $data_wip = DB::table('good_receipt_note_details as a')
                ->select('b.wip_code', 'b.description', 'a.receipt_qty', 'c.unit')
                ->leftJoin('master_wips as b', 'a.id_master_products', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->leftJoin('good_receipt_notes as d', 'a.id_good_receipt_notes', '=', 'd.id')
                ->where('d.receipt_number', '=', $receipt_number)
                ->get();

        $data_fg = DB::table('good_receipt_note_details as a')
                ->select('b.product_code', 'b.description', 'a.receipt_qty', 'c.unit')
                ->leftJoin('master_product_fgs as b', 'a.id_master_products', '=', 'b.id')
                ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                ->leftJoin('good_receipt_notes as d', 'a.id_good_receipt_notes', '=', 'd.id')
                ->where('d.receipt_number', '=', $receipt_number)
                ->get();

        // echo json_encode($data_ta);
        // exit();
        return view('grn.grn_print',compact('data_ta','data_rm','data_wip','data_fg','type'));
    }
    public function posted_grn($id)
    {
        $idx = $id;

        // Ambil po_number dari tabel good_receipt_notes berdasarkan id
        $poNumber = DB::table('good_receipt_notes')
            ->where('id', $idx)
            ->value('id_purchase_orders');

        // Perbarui status pada good_receipt_notes
        $validatedData1 = DB::update("UPDATE `good_receipt_notes` SET `status` = 'Posted' WHERE `id` = ?", [$idx]);

        // Perbarui status pada purchase_orders
        $validatedData2 = DB::update("UPDATE `purchase_orders` SET `status` = 'Closed' WHERE `id` = ?", [$poNumber]);

        if ($validatedData1 && $validatedData2) {
            // Redirect dengan pesan sukses
            return Redirect::to('/good-receipt-note')->with('pesan', 'Data berhasil diposted.');
        } else {
            // Redirect dengan pesan error
            return Redirect::to('/good-receipt-note')->with('pesan', 'Data gagal diposted.');
        }
    }

    public function unposted_grn($id)
    {
        $idx=$id;

        // Ambil po_number dari tabel good_receipt_notes berdasarkan id
        $poNumber = DB::table('good_receipt_notes')
        ->where('id', $idx)
        ->value('id_purchase_orders');


        $validatedData = DB::update("UPDATE `good_receipt_notes` SET `status` = 'Un Posted' WHERE `id` = '$idx';");

        // Perbarui status pada purchase_orders
        $validatedData2 = DB::update("UPDATE `purchase_orders` SET `status` = 'Posted' WHERE `id` = ?", [$poNumber]);

        if ($validatedData && $validatedData2) {
            //redirect dengan pesan sukses
            return Redirect::to('/good-receipt-note')->with('pesan', 'Data berhasil diunposted.');
        } else {
            //redirect dengan pesan error
            return Redirect::to('/good-receipt-note')->with('pesan', 'Data gagal diunposted.');
        }
    }
    public function edit_grn($id)
    {
        // dd($receipt_number);
        // die;
        $pr = PurchaseRequisitions::all();
        $po = DB::table('purchase_orders')->get();
        $mst_supplier = DB::table('master_suppliers')->get();
        $goodReceiptNote = GoodReceiptNote::where('id', $id)->first();

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

        $unit = MstUnits::all();

        $data_detail_ta = DB::table('good_receipt_note_details as a')
                        ->leftJoin('master_tool_auxiliaries as b', 'a.id_master_products', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.receipt_qty','a.outstanding_qty', 'b.description', 'c.unit','a.note')
                        ->where('a.id_good_receipt_notes', $id)
                        ->get();

        $data_detail_rm = DB::table('good_receipt_note_details as a')
                        ->leftJoin('master_raw_materials as b', 'a.id_master_products', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.receipt_qty','a.outstanding_qty', 'b.description', 'c.unit','a.note')
                        ->where('a.id_good_receipt_notes', $id)
                        ->get();

        $data_detail_fg = DB::table('good_receipt_note_details as a')
                        ->leftJoin('master_product_fgs as b', 'a.id_master_products', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.receipt_qty','a.outstanding_qty', 'b.description', 'c.unit','a.note')
                        ->where('a.id_good_receipt_notes', $id)
                        ->get();

        $data_detail_wip = DB::table('good_receipt_note_details as a')
                        ->leftJoin('master_wips as b', 'a.id_master_products', '=', 'b.id')
                        ->leftJoin('master_units as c', 'a.master_units_id', '=', 'c.id')
                        ->select('a.id','a.type_product','a.receipt_qty','a.outstanding_qty', 'b.description', 'c.unit','a.note')
                        ->where('a.id_good_receipt_notes', $id)
                        ->get();

        return view('grn.edit_grn',compact('goodReceiptNote','pr','po','mst_supplier','rm','ta','fg','wip',
        'unit','data_detail_ta','data_detail_rm','data_detail_fg','data_detail_wip'));
    }
    public function simpan_detail_po_fix()
    {
        // dd('test');
        // die;
        return Redirect::to('/good-receipt-note')->with('pesan', 'Data berhasil disimpan.');
    }
    public function edit_detail_ext_no_lot($id)
    {
        $detail_ext_nolot = DB::table('detail_good_receipt_note_details')
                        ->where('id', $id)  // Sesuaikan dengan kolom id yang ada
                        ->first();  // Ambil hanya satu baris

        return view('grn.edit_detail_ext_nolot',compact('id','detail_ext_nolot'));
    }
    public function update_detail_ext_nolot(Request $request)
    {
        // Validasi input jika diperlukan
        $validatedData = $request->validate([
            'id' => 'required',
            'id_grn_detail' => 'required',
            'lot_number' => 'required|string',
            'ext_lot_number' => 'required|string',
            'qty' => 'required|numeric'
        ]);

        // Lakukan update ke database
        $affected = DB::update(
            "UPDATE `detail_good_receipt_note_details` 
            SET `id_grn_detail` = ?, 
                `lot_number` = ?, 
                `ext_lot_number` = ?, 
                `qty` = ? 
            WHERE `id` = ?",
            [
                $request->id_grn_detail,
                $request->lot_number,
                $request->ext_lot_number,
                $request->qty,
                $request->id
            ]
        );

        // Cek apakah ada baris yang ter-update
        if ($affected) {
            return redirect('/detail-external-no-lot/' . $request->lot_number)->with('pesan', 'Data berhasil diperbarui');
        } else {
            return redirect()->back()->with('error', 'ID tidak ditemukan atau tidak ada perubahan');
        }
    }
    
    
}
