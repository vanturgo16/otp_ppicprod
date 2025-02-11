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

class GoodLotNumberController extends Controller
{
    use AuditLogsTrait;

    // QC PASSED DATA
    public function index(Request $request)
    {
        $datas = GoodReceiptNoteDetail::select('good_receipt_notes.receipt_number',
                DB::raw('
                    CASE 
                        WHEN good_receipt_note_details.type_product = "RM" THEN master_raw_materials.description 
                        WHEN good_receipt_note_details.type_product = "WIP" THEN master_wips.description 
                        WHEN good_receipt_note_details.type_product = "FG" THEN master_product_fgs.description 
                        WHEN good_receipt_note_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.description 
                    END as product_desc'),
                'master_units.unit',
                'master_units.unit_code',
                'good_receipt_note_details.*')
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
            ->leftjoin('good_receipt_notes', 'good_receipt_note_details.id_good_receipt_notes', 'good_receipt_notes.id')
            ->whereIn('good_receipt_note_details.status', ['Open', 'Closed'])
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('good_receipt_notes.qc_status', 'Y')
                        ->where('good_receipt_note_details.qc_passed', 'Y');
                })
                ->orWhere(function ($q) {
                    $q->where('good_receipt_notes.qc_status', 'N');
                });
            });
        if ($request->has('filterType') && $request->filterType != '' && $request->filterType != 'All') {
            $datas->where('good_receipt_note_details.type_product', $request->filterType);
        }
        if ($request->has('filterStatus') && $request->filterStatus != '' && $request->filterStatus != 'All') {
            $datas->where('good_receipt_note_details.status', $request->filterStatus);
        }
        $datas = $datas->orderBy('good_receipt_notes.created_at', 'desc')->get();

        // Datatables
        if ($request->ajax()) {
            $genLotNumber = $this->genLotNumber();
            
            return DataTables::of($datas)
                ->addColumn('action', function ($data) use ($genLotNumber){
                    return view('gl_number.action', compact('data', 'genLotNumber'));
                })->make(true);
        }

        // Audit Log
        $this->auditLogsShort('View List Good Lot Number Product GRN');
        return view('gl_number.index');
    }

    public function genLotNumber()
    {
        $currentYear = date('y');
        $currentMonth = date('m');
        $lastCode = GoodReceiptNoteDetail::whereNotNull('lot_number')
            ->whereRaw('LEFT(lot_number, 2) = ?', [$currentYear])  // Filter by year
            ->whereRaw('MID(lot_number, 3, 2) = ?', [$currentMonth]) // Filter by month
            ->orderByRaw('CAST(MID(lot_number, 5, 5) AS UNSIGNED) DESC') // Order by numeric sequence
            ->value(DB::raw('MID(lot_number, 5, 5)'));
        $lastCode = $lastCode ? $lastCode : 0;
        $nextCode = str_pad($lastCode + 1, 5, '0', STR_PAD_LEFT);
        $formattedCode = sprintf('%02d%s%05dM', $currentYear, $currentMonth, $nextCode);
        return $formattedCode;
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        
        // Validate request
        $request->validate([
            'lot_number' => 'required',
            'qty' => 'required',
        ], [
            'lot_number.required' => 'Lot Number Masih Kosong.',
            'qty.required' => 'Qty harus diisi.',
        ]);
        
        $data = GoodReceiptNoteDetail::where('id', $id)->first();

        $generateQty = (float) $data->qty_generate_barcode; 
        $requestQty = (float) str_replace(['.', ','], ['', '.'], $request->qty);

        $totalGenerateQty = $generateQty + $requestQty;
        $receiptQty = (float) $data->receipt_qty;
        if($totalGenerateQty > $receiptQty){
            $restQty = (float) $data->receipt_qty - (float) $data->qty_generate_barcode;
            $restQty = rtrim(rtrim(sprintf("%.3f", $restQty), '0'), '.');
            return redirect()->back()->with(['fail' => 'Qty tidak boleh melebihi sisa generated qty (' . $restQty . ')']);
        }
        $totalGenerateQty = rtrim(rtrim(sprintf("%.3f", $totalGenerateQty), '0'), '.');
        $lotNumber = $data->lot_number ?: $this->genLotNumber();

        DB::beginTransaction();
        try {
            // Update data
            GoodReceiptNoteDetail::where('id', $id)->update([
                'qty_generate_barcode' => rtrim(rtrim(sprintf("%.3f", $totalGenerateQty), '0'), '.'),
                'lot_number' => $lotNumber,
            ]);
            // Create new data
            DetailGoodReceiptNoteDetail::insert([
                'id_grn' => $data->id_good_receipt_notes,
                'id_grn_detail' => $id,
                'lot_number' => $lotNumber,
                'qty' => $requestQty,
                'qty_out' => 0,
            ]);

            // Audit Log
            $this->auditLogsShort('Generate Lot Number GRN Detail ID (' . $id . ')');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil Tambah Generate Lot Number Produk']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal Tambah Generate Lot Number Produk!']);
        }
    }

    // Detail Lot Number
    public function detailLot(Request $request, $id)
    {
        $id = decrypt($id);

        // Datatables
        if ($request->ajax()) {
            $itemDatas = DetailGoodReceiptNoteDetail::where('id_grn_detail', $id)->get();
            return DataTables::of($itemDatas)
                ->addColumn('action', function ($data){
                    return view('gl_number.detail.action', compact('data'));
                })
                ->addColumn('generate_barcode', function ($data){
                    return view('gl_number.detail.gen_barcode', compact('data'));
                })->make(true);
        }

        $data = GoodReceiptNoteDetail::select('good_receipt_notes.receipt_number',
                DB::raw('
                    CASE 
                        WHEN good_receipt_note_details.type_product = "RM" THEN master_raw_materials.description 
                        WHEN good_receipt_note_details.type_product = "WIP" THEN master_wips.description 
                        WHEN good_receipt_note_details.type_product = "FG" THEN master_product_fgs.description 
                        WHEN good_receipt_note_details.type_product IN ("TA", "Other") THEN master_tool_auxiliaries.description 
                    END as product_desc'),
                'master_units.unit',
                'master_units.unit_code',
                'good_receipt_note_details.*')
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
            ->leftjoin('good_receipt_notes', 'good_receipt_note_details.id_good_receipt_notes', 'good_receipt_notes.id')
            ->where('good_receipt_note_details.id', $id)
            ->first();


        //Audit Log
        $this->auditLogsShort('View List Detail Lot Number Product GRN Detail ID (' . $id . ')');
        return view('gl_number.detail.index', compact('id', 'data'));
    }
    public function updateDetailLot(Request $request, $id)
    {
        $id = decrypt($id);

    }
    public function deleteDetailLot($id)
    {
        $id = decrypt($id);
        
    }
    public function generateBarcode(Request $request, $lot_number)
    {
        $lotNumber = decrypt($lot_number);
        $generator = new BarcodeGeneratorHTML();
        $barcode = $generator->getBarcode($lotNumber, $generator::TYPE_CODE_128);
        $qty = $request->input('qty', 1);

        return view('gl_number.detail.barcode', compact('barcode','lotNumber','qty'));
    }
}
