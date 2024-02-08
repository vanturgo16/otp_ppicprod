<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Browser;

// Model
use App\Models\MstAccountTypes;

class MstAccountTypesController extends Controller
{
    use AuditLogsTrait;

    public function index(Request $request)
    {
        $account_type_code = $request->get('account_type_code');
        $account_type_name = $request->get('account_type_name');
        $status = $request->get('status');
        $searchDate = $request->get('searchDate');
        $startdate = $request->get('startdate');
        $enddate = $request->get('enddate');
        $flag = $request->get('flag');

        $datas = MstAccountTypes::select(
            DB::raw('ROW_NUMBER() OVER (ORDER BY id) as no'),
            'master_account_types.*'
        );

        if($account_type_code != null){
            $datas = $datas->where('account_type_code', 'like', '%'.$account_type_code.'%');
        }
        if($account_type_name != null){
            $datas = $datas->where('account_type_name', 'like', '%'.$account_type_name.'%');
        }
        if($status != null){
            $datas = $datas->where('is_active', $status);
        }
        if($startdate != null && $enddate != null){
            $datas = $datas->whereDate('created_at','>=',$startdate)->whereDate('created_at','<=',$enddate);
        }
        
        if($request->flag != null){
            $datas = $datas->get()->makeHidden(['id']);
            return $datas;
        }

        $datas = $datas->paginate(10);
        
        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Mst Account Type';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('accounttype.index',compact('datas',
            'account_type_code', 'account_type_name', 'status', 'searchDate', 'startdate', 'enddate', 'flag'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'account_type_code' => 'required',
            'account_type_name' => 'required'
        ]);

        DB::beginTransaction();
        try{
            $data = MstAccountTypes::create([
                'account_type_code' => $request->account_type_code,
                'account_type_name' => $request->account_type_name,
                'is_active' => '1'
            ]);

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Create New Account Type ('. $request->account_type_name . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();

            return redirect()->back()->with(['success' => 'Success Create New Account Type']);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Create New Account Type!']);
        }
    }

    public function update(Request $request, $id){
        // dd($request->all());

        $id = decrypt($id);

        $request->validate([
            'account_type_code' => 'required',
            'account_type_name' => 'required',
        ]);

        $databefore = MstAccountTypes::where('id', $id)->first();
        $databefore->account_type_code = $request->account_type_code;
        $databefore->account_type_name = $request->account_type_name;

        if($databefore->isDirty()){
            DB::beginTransaction();
            try{
                $data = MstAccountTypes::where('id', $id)->update([
                    'account_type_code' => $request->account_type_code,
                    'account_type_name' => $request->account_type_name,
                ]);

                //Audit Log
                $username= auth()->user()->email; 
                $ipAddress=$_SERVER['REMOTE_ADDR'];
                $location='0';
                $access_from=Browser::browserName();
                $activity='Update Account Type ('. $request->account_type_name . ')';
                $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                DB::commit();
                return redirect()->back()->with(['success' => 'Success Update Account Type']);
            } catch (\Exception $e) {
                dd($e);
                return redirect()->back()->with(['fail' => 'Failed to Update Account Type!']);
            }
        } else {
            return redirect()->back()->with(['info' => 'Nothing Change, The data entered is the same as the previous one!']);
        }
    }

    public function activate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstAccountTypes::where('id', $id)->update([
                'is_active' => 1
            ]);

            $name = MstAccountTypes::where('id', $id)->first();

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Activate Account Type ('. $name->account_type_name . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Activate Account Type ' . $name->account_type_name]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Activate Account Type ' . $name->account_type_name .'!']);
        }
    }

    public function deactivate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = MstAccountTypes::where('id', $id)->update([
                'is_active' => 0
            ]);

            $name = MstAccountTypes::where('id', $id)->first();
            
            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Deactivate Account Type ('. $name->account_type_name . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Deactivate Account Type ' . $name->account_type_name]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Deactivate Account Type ' . $name->account_type_name .'!']);
        }
    }
}
