<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Browser;

// Model
use App\Models\MstAccountCodes;
use App\Models\MstAccountTypes;

class MstAccountCodesController extends Controller
{
    use AuditLogsTrait;

    public function index(Request $request)
    {
        $account_code = $request->get('account_code');
        $account_name = $request->get('account_name');
        $id_master_account_types = $request->get('id_master_account_types');
        $status = $request->get('status');
        $searchDate = $request->get('searchDate');
        $startdate = $request->get('startdate');
        $enddate = $request->get('enddate');
        $flag = $request->get('flag');

        $datas = MstAccountCodes::select(
                DB::raw('ROW_NUMBER() OVER (ORDER BY id) as no'),
                'master_account_codes.*', 'master_account_types.account_type_code', 'master_account_types.account_type_name'
            )
            ->leftjoin('master_account_types', 'master_account_codes.id_master_account_types', 'master_account_types.id');

        if($account_code != null){
            $datas = $datas->where('account_code', 'like', '%'.$account_code.'%');
        }
        if($account_name != null){
            $datas = $datas->where('account_name', 'like', '%'.$account_name.'%');
        }
        if($account_name != null){
            $datas = $datas->where('id_master_account_types', 'like', '%'.$id_master_account_types.'%');
        }
        if($status != null){
            $datas = $datas->where('master_account_codes.is_active', $status);
        }
        if($startdate != null && $enddate != null){
            $datas = $datas->whereDate('created_at','>=',$startdate)->whereDate('created_at','<=',$enddate);
        }
        
        if($request->flag != null){
            $datas = $datas->get()->makeHidden(['id']);
            return $datas;
        }

        $datas = $datas->paginate(10);

        $acctypes = MstAccountTypes::where('is_active', 1)->get();
        
        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Mst Account Code';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('accountcode.index',compact('datas', 'acctypes',
            'account_code', 'account_name', 'id_master_account_types', 'status', 'searchDate', 'startdate', 'enddate', 'flag'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'account_code' => 'required',
            'account_name' => 'required',
            'id_master_account_types' => 'required'
        ]);

        DB::beginTransaction();
        try{
            $data = MstAccountCodes::create([
                'account_code' => $request->account_code,
                'account_name' => $request->account_name,
                'id_master_account_types' => $request->id_master_account_types,
                'is_active' => '1'
            ]);

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Create New Account Code ('. $request->account_name . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();

            return redirect()->back()->with(['success' => 'Success Create New Account Code']);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Create New Account Code!']);
        }
    }

    public function update(Request $request, $id){
        // dd($request->all());

        $id = decrypt($id);

        $request->validate([
            'account_code' => 'required',
            'account_name' => 'required',
            'id_master_account_types' => 'required'
        ]);

        $databefore = MstAccountCodes::where('id', $id)->first();
        $databefore->account_code = $request->account_code;
        $databefore->account_name = $request->account_name;
        $databefore->id_master_account_types = $request->id_master_account_types;

        if($databefore->isDirty()){
            DB::beginTransaction();
            try{
                $data = MstAccountCodes::where('id', $id)->update([
                    'account_code' => $request->account_code,
                    'account_name' => $request->account_name,
                    'id_master_account_types' => $request->id_master_account_types
                ]);

                //Audit Log
                $username= auth()->user()->email; 
                $ipAddress=$_SERVER['REMOTE_ADDR'];
                $location='0';
                $access_from=Browser::browserName();
                $activity='Update Account Code ('. $request->account_name . ')';
                $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                DB::commit();
                return redirect()->back()->with(['success' => 'Success Update Account Code']);
            } catch (\Exception $e) {
                dd($e);
                return redirect()->back()->with(['fail' => 'Failed to Update Account Code!']);
            }
        } else {
            return redirect()->back()->with(['info' => 'Nothing Change, The data entered is the same as the previous one!']);
        }
    }

    public function activate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstAccountCodes::where('id', $id)->update([
                'is_active' => 1
            ]);

            $name = MstAccountCodes::where('id', $id)->first();

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Activate Account Type ('. $name->account_name . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Activate Account Code ' . $name->account_name]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Activate Account Code ' . $name->account_name .'!']);
        }
    }

    public function deactivate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstAccountCodes::where('id', $id)->update([
                'is_active' => 0
            ]);

            $name = MstAccountCodes::where('id', $id)->first();
            
            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Deactivate Account Code ('. $name->account_name . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Deactivate Account Code ' . $name->account_name]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Deactivate Account Code ' . $name->account_name .'!']);
        }
    }
}
