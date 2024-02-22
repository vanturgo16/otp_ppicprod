<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use RealRashid\SweetAlert\Facades\Alert;
use Browser;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\Datatables;

// Model
//START REQUEST SPAREPART AND AUXILIARIES
use App\Models\ProductionReqSparepartAuxiliaries;
use App\Models\ProductionReqSparepartAuxiliariesDetail;
use App\Models\ProductionEntryMaterialUse;
use App\Models\ProductionEntryReportBlow;
//END REQUEST SPAREPART AND AUXILIARIES




class ProductionController extends Controller
{
    use AuditLogsTrait;
	
	//START REQUEST SPAREPART AND AUXILIARIES
    public function production_req_sparepart_auxiliaries()
    {
        $data = ProductionReqSparepartAuxiliaries::leftJoin('master_departements AS b', 'request_tool_auxiliaries.id_master_departements', '=', 'b.id')
                ->select('request_tool_auxiliaries.*', 'b.name')
                ->orderBy('request_tool_auxiliaries.created_at', 'desc')
                ->get();

        //Audit Log		
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Request Sparepart Auxiliaries';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);		

        return view('production.req_sparepart_auxiliaries',compact('data'));
    }
	public function production_req_sparepart_auxiliaries_add(){
        $ms_departements = DB::table('master_departements')
                        ->select('name','id')
                        ->get();
        $ms_tool_auxiliaries = DB::table('master_tool_auxiliaries')
                        ->select('description','id')
                        ->get();
        
        $formattedCode = $this->production_req_sparepart_auxiliaries_create_code();
		
        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Add Request Sparepart Auxiliaries';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('production.req_sparepart_auxiliaries_add',compact('ms_departements','ms_tool_auxiliaries','formattedCode'));
    }
	private function production_req_sparepart_auxiliaries_create_code(){
		$lastCode = ProductionReqSparepartAuxiliaries::orderBy('created_at', 'desc')
        ->value(DB::raw('RIGHT(request_number, 6)'));
    
        // Jika tidak ada nomor urut sebelumnya, atur ke 0
        $lastCode = $lastCode ? $lastCode : 0;

        // Tingkatkan nomor urut
        $nextCode = $lastCode + 1;

        // Format kode dengan panjang 7 karakter
        $formattedCode = 'TA'. date('y') . date('m') . str_pad($nextCode, 6, '0', STR_PAD_LEFT);
		
		return $formattedCode;
	}	
	public function production_req_sparepart_auxiliaries_save(Request $request){
        if ($request->has('savemore')) {
            return "Tombol Save & Add More diklik.";
        } elseif ($request->has('save')) {
            $pesan = [
                'request_number.required' => 'Cannot Be Empty',
                'date.required' => 'Cannot Be Empty',
                'id_master_departements.required' => 'Cannot Be Empty',
                'status.required' => 'Cannot Be Empty',                
            ];

            $validatedData = $request->validate([
                'date' => 'required',
                'id_master_departements' => 'required',
                'status' => 'required',

            ], $pesan);
			$validatedData['request_number'] = $this->production_req_sparepart_auxiliaries_create_code();
			$validatedData['status'] = 'Request';
			
            $request_number = $validatedData['request_number'];
			
            ProductionReqSparepartAuxiliaries::create($validatedData);
			
			//Audit Log		
			$username= auth()->user()->email; 
			$ipAddress=$_SERVER['REMOTE_ADDR'];
			$location='0';
			$access_from=Browser::browserName();
			$activity='Save Request Sparepart Auxiliaries';
			$this->auditLogs($username,$ipAddress,$location,$access_from,$activity);
			
            return Redirect::to('/production-req-sparepart-auxiliaries-detail/'.sha1($request_number))->with('pesan', 'Add Successfuly.');      
        }        
    }
	public function production_req_sparepart_auxiliaries_hold(Request $request){
		$id_hold = $request->input('hold');
		$validatedData['status'] = 'Hold';			
			
		ProductionReqSparepartAuxiliaries::whereRaw( "sha1(id) = '$id_hold'" )
			->update($validatedData);
		
		//Audit Log		
		$username= auth()->user()->email; 
		$ipAddress=$_SERVER['REMOTE_ADDR'];
		$location='0';
		$access_from=Browser::browserName();
		$activity='Hold Request Sparepart Auxiliaries '.$request->input('request_number');
		$this->auditLogs($username,$ipAddress,$location,$access_from,$activity);
		
		return Redirect::to('/production-req-sparepart-auxiliaries')->with('pesan', 'Hold Successfuly.');
	}
	public function production_req_sparepart_auxiliaries_approve(Request $request){
		$id_approve = $request->input('approve');
		$validatedData['status'] = 'Approve';			
			
		ProductionReqSparepartAuxiliaries::whereRaw( "sha1(id) = '$id_approve'" )
			->update($validatedData);
		
		//Audit Log		
		$username= auth()->user()->email; 
		$ipAddress=$_SERVER['REMOTE_ADDR'];
		$location='0';
		$access_from=Browser::browserName();
		$activity='Approve Request Sparepart Auxiliaries '.$request->input('request_number');
		$this->auditLogs($username,$ipAddress,$location,$access_from,$activity);
		
		return Redirect::to('/production-req-sparepart-auxiliaries')->with('pesan', 'Hold Successfuly.');
	}
	public function production_req_sparepart_auxiliaries_delete(Request $request){
		$id_delete = $request->input('hapus');
		
		ProductionReqSparepartAuxiliaries::whereRaw( "sha1(id) = '$id_delete'" )->delete();
		ProductionReqSparepartAuxiliariesDetail::whereRaw( "sha1(id_request_tool_auxiliaries) = '$id_delete'" )->delete();
		
		//Audit Log		
		$username= auth()->user()->email; 
		$ipAddress=$_SERVER['REMOTE_ADDR'];
		$location='0';
		$access_from=Browser::browserName();
		$activity='Delete Request Sparepart Auxiliaries '.$request->input('request_number');
		$this->auditLogs($username,$ipAddress,$location,$access_from,$activity);
		
		return Redirect::to('/production-req-sparepart-auxiliaries')->with('pesan', 'Delete Successfuly.');
	}
	public function production_req_sparepart_auxiliaries_detail($request_number){
		$data = ProductionReqSparepartAuxiliaries::leftJoin('master_departements AS b', 'request_tool_auxiliaries.id_master_departements', '=', 'b.id')
                ->select('request_tool_auxiliaries.*', 'b.name')
				->whereRaw( "sha1(request_tool_auxiliaries.request_number) = '$request_number'")
                ->orderBy('request_tool_auxiliaries.created_at', 'desc')
                ->get();
		if(!empty($data[0])){
			$ms_departements = DB::table('master_departements')
							->select('name','id')
							->get();
			$ms_tool_auxiliaries = DB::table('master_tool_auxiliaries')
							->select('description','id')
							->get();			
					
			$data_detail = DB::table('request_tool_auxiliaries_details as a')
					->leftJoin('request_tool_auxiliaries as b', 'a.id_request_tool_auxiliaries', '=', 'b.id')
					->leftJoin('master_tool_auxiliaries as c', 'a.id_master_tool_auxiliaries', '=', 'c.id')
					->select('a.*', 'c.description')
					->whereRaw( "sha1(b.request_number) = '$request_number'")
					->get();            
				
			//Audit Log
			$username= auth()->user()->email; 
			$ipAddress=$_SERVER['REMOTE_ADDR'];
			$location='0';
			$access_from=Browser::browserName();
			$activity='Detail Request Sparepart Auxiliaries '.$data[0]->request_number;
			$this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

			return view('production.req_sparepart_auxiliaries_detail',compact('ms_departements','ms_tool_auxiliaries','data','data_detail'));
		}else{
			return Redirect::to('/production-req-sparepart-auxiliaries');
		}
    }   
	public function production_req_sparepart_auxiliaries_detail_update(Request $request){
		if ($request->has('savemore')) {
            return "Tombol Save & Add More diklik.";
        } elseif ($request->has('update')) {
			
			$request_number = $_POST['request_number'];		
			$data = ProductionReqSparepartAuxiliaries::whereRaw( "sha1(request_tool_auxiliaries.request_number) = '$request_number'")
				->select('request_number')
				->get();
			
            $pesan = [
                'id_master_departements.required' => 'Cannot Be Empty',
                'date.required' => 'Cannot Be Empty',         
            ];

            $validatedData = $request->validate([
                'id_master_departements' => 'required',
                'date' => 'required',
            ], $pesan);
			$validatedData['status'] = 'Request';			
			
            ProductionReqSparepartAuxiliaries::where('request_number', $data[0]->request_number)
				->update($validatedData);
			
			//Audit Log		
			$username= auth()->user()->email; 
			$ipAddress=$_SERVER['REMOTE_ADDR'];
			$location='0';
			$access_from=Browser::browserName();
			$activity='Update Request Sparepart Auxiliaries '.$data[0]->request_number;
			$this->auditLogs($username,$ipAddress,$location,$access_from,$activity);
			
            return Redirect::to('/production-req-sparepart-auxiliaries-detail/'.$request_number)->with('pesan', 'Update Successfuly.');
        } 
			
    }
	public function production_req_sparepart_auxiliaries_detail_add(Request $request){
		if ($request->has('savemore')) {
            return "Tombol Save & Add More diklik.";
        } elseif ($request->has('save')) {
			
			$request_number = $_POST['request_number'];		
			$data = ProductionReqSparepartAuxiliaries::whereRaw( "sha1(request_tool_auxiliaries.request_number) = '$request_number'")
				->select('id','request_number')
				->get();
			
            $pesan = [
                'id_master_tool_auxiliaries.required' => 'Cannot Be Empty',
                'qty.required' => 'Cannot Be Empty',         
            ];

            $validatedData = $request->validate([
                'id_master_tool_auxiliaries' => 'required',
                'qty' => 'required',

            ], $pesan);
			$validatedData['remarks'] = !empty($request->input('remarks'))?$request->input('remarks'):'';			
			$validatedData['id_request_tool_auxiliaries'] = $data[0]->id;			
			
            ProductionReqSparepartAuxiliariesDetail::create($validatedData);
			
			//Audit Log		
			$username= auth()->user()->email; 
			$ipAddress=$_SERVER['REMOTE_ADDR'];
			$location='0';
			$access_from=Browser::browserName();
			$activity='Add Detail Request Sparepart Auxiliaries '.$data[0]->request_number;
			$this->auditLogs($username,$ipAddress,$location,$access_from,$activity); 
			 
            return Redirect::to('/production-req-sparepart-auxiliaries-detail/'.$request_number)->with('pesan', 'Add Successfuly.');      
        } 
			
    }
	public function production_req_sparepart_auxiliaries_detail_edit_get(Request $request, $id)
    {
		$data['find'] = ProductionReqSparepartAuxiliariesDetail::find($id);
        $data['ms_tool_auxiliaries'] = DB::select("SELECT master_tool_auxiliaries.description, master_tool_auxiliaries.id FROM master_tool_auxiliaries");
		
		//Audit Log		
		$username= auth()->user()->email; 
		$ipAddress=$_SERVER['REMOTE_ADDR'];
		$location='0';
		$access_from=Browser::browserName();
		$activity='Get Edit Detail Request Sparepart Auxiliaries '.$id;
		$this->auditLogs($username,$ipAddress,$location,$access_from,$activity); 
			
        return response()->json(['data' => $data]);
    }
	public function production_req_sparepart_auxiliaries_detail_edit_save(Request $request, $id){
		$pesan = [
            'id_master_tool_auxiliaries.required' => 'Cannot Be Empty',
            'qty.required' => 'Cannot Be Empty',
            'remarks.required' => 'Cannot Be Empty',
            
        ];

        $validatedData = $request->validate([
            'id_master_tool_auxiliaries' => 'required',
            'qty' => 'required',
            'remarks' => 'required',

        ], $pesan);

        ProductionReqSparepartAuxiliariesDetail::where('id', $id)
			->update($validatedData);

        $request_number = $request->input('request_number');
		
		//Audit Log		
		$username= auth()->user()->email; 
		$ipAddress=$_SERVER['REMOTE_ADDR'];
		$location='0';
		$access_from=Browser::browserName();
		$activity='Save Edit Detail Request Sparepart Auxiliaries '.$id;
		$this->auditLogs($username,$ipAddress,$location,$access_from,$activity);
		
		return Redirect::to('/production-req-sparepart-auxiliaries-detail/'.$request_number)->with('pesan', 'Edit Successfuly.');  
    }
	public function production_req_sparepart_auxiliaries_detail_delete(Request $request){
		$id_delete = $request->input('hapus_detail');
		$request_number = $request->input('request_number');
		
		ProductionReqSparepartAuxiliariesDetail::whereRaw( "sha1(id) = '$id_delete'" )->delete();
		
		//Audit Log		
		$username= auth()->user()->email; 
		$ipAddress=$_SERVER['REMOTE_ADDR'];
		$location='0';
		$access_from=Browser::browserName();
		$activity='Delete Request Sparepart Auxiliaries Detail';
		$this->auditLogs($username,$ipAddress,$location,$access_from,$activity);
		
		return Redirect::to('/production-req-sparepart-auxiliaries-detail/'.$request_number)->with('pesan', 'Delete Successfuly.');
	}
	//END REQUEST SPAREPART AND AUXILIARIES
	
	//START ENTRY MATERIAL USE
	public function production_entry_material_use()
    {        
        //Audit Log		
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Entry Report Material Use';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);		

        return view('production.entry_material_use');
    }
	public function production_entry_material_use_json()
    {
        $datas = ProductionEntryMaterialUse::leftJoin('work_orders AS b', 'report_material_uses.id_work_orders', '=', 'b.id')
				->leftJoin('master_regus AS c', 'report_material_uses.id_master_regus', '=', 'c.id')
				->leftJoin('master_work_centers AS d', 'report_material_uses.id_master_work_centers', '=', 'd.id')
                ->select('report_material_uses.*', 'b.wo_number', 'c.regu', 'd.work_center')
                ->orderBy('report_material_uses.created_at', 'desc')
                ->get();

		return DataTables::of($datas)
			->addColumn('action', function ($data) {
				if($data->status=='Hold'){
					$tombol = '
						<center>
							<a href="#" class="btn btn-primary waves-effect waves-light">
								<i class="bx bx-check" title="Hold"></i> APPROVE
							</a>
							<a href="#" class="btn btn-info waves-effect waves-light">
								<i class="bx bx-edit-alt" title="Edit"></i> EDIT
							</a>
							<a href="#" class="btn btn-danger waves-effect waves-light" onclick="return confirm('."'Anda yakin mau menghapus item inix ?'".')">
								<i class="bx bx-trash-alt" title="Delete" ></i> DELETE
							</a>
							<a href="#" class="btn btn-dark waves-effect waves-light">
								<i class="bx bx-printer" title="Print"></i> PRINT
							</a>
						</center>
					';
				}elseif($data->status=='Approve'){
					$tombol = '
						<center>
							<a href="#" class="btn btn-warning waves-effect waves-light">
								<i class="bx bx-block" title="Hold"></i> HOLD
							</a>
							<a href="#" class="btn btn-dark waves-effect waves-light">
								<i class="bx bx-printer" title="Print"></i> PRINT
							</a>
						</center>						
					';
				}
				return $tombol;
			})
		->make(true);
    }	
	public function production_entry_material_use_add(){
        $ms_work_orders = DB::table('work_orders')
                        ->select('id_master_process_productions','wo_number','id')
                        ->get();
		/*
        $ms_work_centers = DB::table('master_work_centers')
                        ->select('work_center_code','work_center','id')
                        ->get();
        $ms_regus = DB::table('master_regus')
                        ->select('regu','id')
                        ->get();
        */
		
        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='Add Entry Report Material Use';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('production.entry_material_use_add',compact('ms_work_orders'));
    }
	public function jsonGetWorkCenter()
    {
        $id_master_process_productions = request()->get('id_master_process_productions');
        
		$datas = DB::table('master_work_centers')
			->where('id_master_process_productions', $id_master_process_productions)
			->select('work_center_code','work_center','id')
			->get();
			
		$lists = "<option value='' disabled='' selected=''>** Please Select A Work Centers</option>";		
		foreach($datas as $data){
			$lists .= "<option value='".$data->id."'>".$data->work_center_code.' - '.$data->work_center."</option>";
		}
		
		$callback = array('list_work_center'=>$lists);
		echo json_encode($callback);			
    }
	public function jsonGetRegu()
    {
        $id_master_work_centers = request()->get('id_master_work_centers');
        
		$datas = DB::table('master_regus')
			->where('id_master_work_centers', $id_master_work_centers)
			->select('id', 'regu')
			->get();
			
		$lists = "<option value='' disabled='' selected=''>** Please Select A Regu</option>";		
		foreach($datas as $data){
			$lists .= "<option value='".$data->id."'>".$data->regu."</option>";
		}
		
		$callback = array('list_regu'=>$lists);
		echo json_encode($callback);			
    }
	public function production_entry_material_use_save(Request $request){
        if ($request->has('savemore')) {
            return "Tombol Save & Add More diklik.";
        } elseif ($request->has('save')) {
            $pesan = [
                'id_work_orders.required' => 'Cannot Be Empty',
                'date.required' => 'Cannot Be Empty',
                'id_master_work_centers.required' => 'Cannot Be Empty',
                'id_master_regus.required' => 'Cannot Be Empty',                
                'shift.required' => 'Cannot Be Empty',                
            ];

            $validatedData = $request->validate([
                'id_work_orders' => 'required',
                'date' => 'required',
                'id_master_work_centers' => 'required',
                'id_master_regus' => 'required',
                'shift' => 'required',

            ], $pesan);			
            $validatedData['status'] = 'Hold';
			
            $response = ProductionEntryMaterialUse::create($validatedData);
			
			//Audit Log		
			$username= auth()->user()->email; 
			$ipAddress=$_SERVER['REMOTE_ADDR'];
			$location='0';
			$access_from=Browser::browserName();
			$activity='Save Entry Report Material Use ID="'.$response->id.'"';
			$this->auditLogs($username,$ipAddress,$location,$access_from,$activity);
			
            return Redirect::to('/production-ent-material-use')->with('pesan', 'Add Successfuly.');
            //return Redirect::to('/production-ent-material-use-detail/'.sha1($response->id))->with('pesan', 'Add Successfuly.');
        }        
    }
	//END ENTRY MATERIAL USE
	
	/*
	public function production_entry_report_blow()
    {
        $datas = ProductionEntryReportBlow::leftJoin('work_orders AS b', 'report_blows.id_work_orders', '=', 'b.id')
                ->select('report_blows.*', 'b.wo_number')
                ->orderBy('report_blows.created_at', 'desc')
                ->get();

        //Audit Log		
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Entry Report Blow';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);		

        return view('production.entry_report_blow', compact('datas'));
    }
	*/
}
