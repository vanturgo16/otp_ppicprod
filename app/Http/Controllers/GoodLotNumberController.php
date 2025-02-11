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

        //Audit Log
        $this->auditLogsShort('View List Good Lot Number Product GRN');
        return view('gl_number.index');
    }

    public function genLotNumber()
    {
        $year = date('y');
        // Ambil nomor urut terakhir dari database
        $lastCode = GoodReceiptNoteDetail::whereNotNull('lot_number')->orderBy('lot_number', 'desc')->value(DB::raw('MID(lot_number, 5, 5)'));
        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;
        // Tingkatkan nomor urut
        $nextCode = str_pad($lastCode + 1, 5, '0', STR_PAD_LEFT);
        // Ambil bulan saat ini dalam format dua digit
        $currentMonth = date('m');
        // Format kode dengan urutan tahun, bulan, nomor urut, dan karakter konstan
        $formattedCode = sprintf('%02d%s%05dM', $year, $currentMonth, $nextCode);

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
        // Ensure the decision is one of the allowed values
        if (!in_array($request->decision, ['reset', 'Y', 'N'])) {
            return redirect()->back()->with(['fail' => 'Gagal QC GRN Detail!']);
        }

        $user = auth()->user()->id;
        // Determine update values based on decision
        $updateData = [
            'qc_passed' => $request->decision === 'reset' ? null : $request->decision,
            'qc_check_by' => $request->decision === 'Y' ? $user : null,
            'qc_uncheck_by' => $request->decision === 'N' ? $user : null,
        ];

        DB::beginTransaction();
        try {
            // Update the record
            GoodReceiptNoteDetail::where('id', $id)->update($updateData);

            // Audit Log
            $this->auditLogsShort('Update QC GRN Detail ID (' . $id . ')');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil QC GRN Detail']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal QC GRN Detail!']);
        }
    }

}
