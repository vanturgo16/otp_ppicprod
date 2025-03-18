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

class QCPassedController extends Controller
{
    use AuditLogsTrait;

    // QC PASSED DATA
    public function index(Request $request)
    {
        $idUpdated = $request->get('idUpdated');

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
                        ->whereIn('good_receipt_note_details.type_product', ['TA', 'Other']);
            })
            ->leftJoin('master_units', 'good_receipt_note_details.master_units_id', '=', 'master_units.id')
            ->leftjoin('good_receipt_notes', 'good_receipt_note_details.id_good_receipt_notes', 'good_receipt_notes.id')
            ->whereIn('good_receipt_note_details.status', ['Open', 'Close'])
            ->where('good_receipt_notes.qc_status', 'Y');
        if ($request->has('filterType') && $request->filterType != '' && $request->filterType != 'All') {
            $datas->where('good_receipt_note_details.type_product', $request->filterType);
        }
        if ($request->has('filterStatus') && $request->filterStatus != '' && $request->filterStatus != 'All') {
            $datas->where('good_receipt_note_details.status', $request->filterStatus);
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
                    return view('qc_passed.action', compact('data'));
                })->make(true);
        }

        //Audit Log
        $this->auditLogsShort('View List Good Receipt Note Product Need QC Passed');
        return view('qc_passed.index', compact('idUpdated', 'page_number'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        
        // Validate request
        $request->validate([
            'decision' => 'required',
        ], [
            'decision.required' => 'Decision QC harus dipilih.',
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
            return redirect()->route('grn_qc.index', ['idUpdated' => $id])->with(['success' => 'Berhasil QC GRN Detail']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->route('grn_qc.index', ['idUpdated' => $id])->with(['fail' => 'Gagal QC GRN Detail!']);
        }
    }

}
