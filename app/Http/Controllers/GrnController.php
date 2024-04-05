<?php

namespace App\Http\Controllers;

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

class GrnController extends Controller
{
    use AuditLogsTrait;

    public function index(){

        $goodReceiptNotes = GoodReceiptNote::select('good_receipt_notes.id','receipt_number', 'purchase_requisitions.request_number', 'purchase_orders.po_number', 'good_receipt_notes.date', 'external_doc_number', 'master_suppliers.name', 'qc_status', 'good_receipt_notes.type', 'good_receipt_notes.status')
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

    public function good_lote_number(){

        $receiptDetails = DB::table('good_receipt_notes as a')
                ->leftJoin('good_receipt_note_details as c', 'a.id', '=', 'c.id_good_receipt_notes')
                ->leftJoin('master_raw_materials as b', 'b.id', '=', 'c.id_master_products')
                ->leftJoin('master_units as d', 'c.master_units_id', '=', 'd.id')
                ->select(
                    'c.id',
                    'a.receipt_number',
                    DB::raw("CONCAT(b.rm_code, '-', b.description) as description"),
                    'c.receipt_qty',
                    'd.unit_code',
                    'c.qc_passed',
                    'c.lot_number',
                    'c.note'
                )
                ->where('a.type', 'RM')
                ->get();

            // Menggunakan DB::raw untuk menggabungkan nilai kolom b.rm_code dan b.description dengan CONCAT dalam SQL
            // Hasilnya disimpan dalam alias product_description


        return view('grn.good_lote_number',compact('receiptDetails'));
    }
    public function generateCode()
    {
       // Ambil tahun 2 digit terakhir
        $year = date('y');

        // Ambil nomor urut terakhir dari database
        $lastCode = GoodReceiptNoteDetail::whereNotNull('lot_number')
            ->orderBy('created_at', 'desc')
            ->value(DB::raw('LEFT(lot_number, 3)'));
            
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
        $validatedData = DB::update("UPDATE `good_receipt_note_details` SET `lot_number` = '$request->lot_number',qty_generate_barcode='$request->qty_generate_barcode' WHERE `id` = '$request->id';");

        $validatedData = DB::table('detail_good_receipt_note_details')->insert([
            'id_grn_detail' => $request->id,
            'lot_number' => $request->lot_number
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
    public function generateBarcode($lot_number)
    {
        $generator = new BarcodeGeneratorHTML();
        $barcode = $generator->getBarcode($lot_number, $generator::TYPE_CODE_128);

        $qtyGenerateBarcode = GoodReceiptNoteDetail::select('qty_generate_barcode')
        ->where('lot_number', $lot_number)
        ->first();

        return view('grn.barcode_grn', compact('barcode','lot_number','qtyGenerateBarcode'));
    }
    public function grn_qc()
    {
        // dd('test');
        // die;

        $receiptDetails = DB::table('good_receipt_notes as a')
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
                ->get();

        return view('grn.grn_qc', compact('receiptDetails'));
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
    public function external_no_lot ()
    {
        // dd('tes');
        // die;
        $details = DB::table('detail_good_receipt_note_details')->get();

        return view('grn.external_no_lot',compact('details'));
    }
    public function update_ext_lot_number(Request $request)
    {
        // dd($request->lot_number);
        // die;
        $validatedData = DB::update("UPDATE `detail_good_receipt_note_details` SET `ext_lot_number` = '$request->ext_lot_number',qty='$request->qty' WHERE `id` = '$request->id';");

        $id_grn_detail = DetailGoodReceiptNoteDetail::select('id_grn_detail')->where('id', $request->id)->first();

        $result = GoodReceiptNoteDetail::select('id_master_products')->where('id', $id_grn_detail->id_grn_detail)->first();
        $id_master_products=$result->id_master_products;

        $stockk = DB::table('master_raw_materials')
        ->select('stock')
        ->where('id', $id_master_products)
        ->first();

        $stok=$stockk->stock;
        // dd($stok);
        // die;
        $tmbhstok = $stok+$request->qty;

        $validatedData = DB::update("UPDATE `master_raw_materials` SET `stock` = '$tmbhstok' WHERE `id` = '$id_master_products'");
        
        $allStocks = DB::table('good_receipt_note_details')
        ->where('id', $id_grn_detail->id_grn_detail)
        ->get();
       

        $validatedData = DB::table('history_stocks')->insert([
            'id_good_receipt_notes_details' => $allStocks->id,
            'type_product' => $allStocks->type_product,
            'id_master_products' => $allStocks->id_master_products,
            'qty' => $tmbhstok,
            'type_stock' => 'IN',
            'date' => DB::raw('CURRENT_DATE()')
        ]);

        if ($validatedData) {
            return redirect('/external-no-lot')->with('pesan', 'Data berhasil ditambahkan');
        } else {
            // Penanganan jika $id tidak ditemukan
            return redirect()->back()->with('error', 'ID tidak ditemukan');
        }
        
    }
    
    
}
