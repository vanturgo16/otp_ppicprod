<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use RealRashid\SweetAlert\Facades\Alert;
use Browser;
use Illuminate\Support\Facades\Crypt;

// Model
use App\Models\ProductionReqSparepartAuxiliaries;
use App\Models\ProductionEntryMaterialUse;
use App\Models\ProductionEntryReportBlow;

use App\Models\MstRequester;//dummy


class ProductionController extends Controller
{
    use AuditLogsTrait;
	
    public function production_req_sparepart_auxiliaries()
    {
         $datas = ProductionReqSparepartAuxiliaries::leftJoin('master_departements AS b', 'request_tool_auxiliaries.id_master_departements', '=', 'b.id')
                ->select('request_tool_auxiliaries.*', 'b.name')
                ->where('request_tool_auxiliaries.status', 'Approve')
                ->orderBy('request_tool_auxiliaries.created_at', 'desc')
                ->get();
        $data_requester = MstRequester::get();

        //Audit Log		
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Request Sparepart Auxiliaries';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);		

        return view('production.req_sparepart_auxiliaries',compact('datas','data_requester'));
    }
	public function production_entry_material_use()
    {
         $datas = ProductionEntryMaterialUse::leftJoin('master_work_centers AS b', 'work_orders.id_master_work_centers', '=', 'b.id')
				//->leftJoin('master_departements AS c', 'request_tool_auxiliaries.id_master_departements', '=', 'b.id')
                //->where('work_orders.status', '<>', 'Request')
                ->select('work_orders.*', 'b.work_center')
                ->orderBy('work_orders.created_at', 'desc')
                ->get();
        $data_requester = MstRequester::get();

        //Audit Log		
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Entry Material Use';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);		

        return view('production.entry_material_use',compact('datas','data_requester'));
    }
	public function production_entry_report_blow()
    {
         $datas = ProductionEntryReportBlow::leftJoin('work_orders AS b', 'report_blows.id_work_orders', '=', 'b.id')
				//->leftJoin('master_departements AS c', 'request_tool_auxiliaries.id_master_departements', '=', 'b.id')
                //->where('work_orders.status', '<>', 'Request')
                ->select('report_blows.*', 'b.wo_number')
                ->orderBy('report_blows.created_at', 'desc')
                ->get();
        $data_requester = MstRequester::get();

        //Audit Log		
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Entry Report Blow';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);		

        return view('production.entry_report_blow',compact('datas','data_requester'));
    }

}
