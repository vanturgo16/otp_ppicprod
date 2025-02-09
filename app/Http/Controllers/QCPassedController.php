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

    // GRN DATA
    public function index(Request $request)
    {
        
    }
    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $request->validate([
            'decision' => 'required',
        ], [
            'decision.required' => 'Decision QC harus dipilih.',
        ]);
        $user = Auth::user()->id;

        DB::beginTransaction();
        try{
            // Update
            GoodReceiptNoteDetail::where('id', $id)->update([
                'qc_passed' => $request->decision,
                'qc_check_by' => $user,
                'qc_uncheck_by' => $user,
            ]);

            // Audit Log
            $this->auditLogsShort('Update QC GRN Detail ID ('. $id . ')');
            DB::commit();
            return redirect()->back()->with(['success' => 'Berhasil QC GRN Detail']);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => 'Gagal QC GRN Detail!']);
        }
    }
}
