<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Browser;

// Model
use App\Models\TransDataKas;
use App\Models\MstAccountCodes;
use App\Models\MstCurrencies;
use App\Models\MstDropdowns;

class TransDataKasController extends Controller
{
    use AuditLogsTrait;

    public function index(Request $request)
    {
        $date = $request->get('date');
        $trans_number = $request->get('trans_number');
        $id_master_account_codes = $request->get('id_master_account_codes');
        $description = $request->get('description');
        $type_transaction = $request->get('type_transaction');
        $searchDate = $request->get('searchDate');
        $startdate = $request->get('startdate');
        $enddate = $request->get('enddate');
        $flag = $request->get('flag');

        $datas = TransDataKas::select(
                DB::raw('ROW_NUMBER() OVER (ORDER BY id) as no'),
                'data_kas_transactions.*', 'master_account_codes.account_code'
            )
            ->leftjoin('master_account_codes', 'data_kas_transactions.id_master_account_codes', 'master_account_codes.id');

        if($date != null){
            $datas = $datas->where('date', $date);
        }
        if($trans_number != null){
            $datas = $datas->where('trans_number', 'like', '%'.$trans_number.'%');
        }
        if($id_master_account_codes != null){
            $datas = $datas->where('id_master_account_codes', $id_master_account_codes);
        }
        if($description != null){
            $datas = $datas->where('description', 'like', '%'.$description.'%');
        }
        if($type_transaction != null){
            $datas = $datas->where('type_transaction', $type_transaction);
        }
        if($startdate != null && $enddate != null){
            $datas = $datas->whereDate('created_at','>=',$startdate)->whereDate('created_at','<=',$enddate);
        }
        
        if($request->flag != null){
            $datas = $datas->get()->makeHidden(['id']);
            return $datas;
        }

        $datas = $datas->paginate(10);

        $currencies = MstCurrencies::where('is_active', 1)->get();
        $accountcodes = MstAccountCodes::where('is_active', 1)->get();
        $typetrans = MstDropdowns::where('category', 'Type Transaction')->get();
        
        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Trans Data Kas';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

        return view('transdatakas.index',compact('datas', 'currencies', 'accountcodes', 'typetrans',
            'date', 'trans_number', 'id_master_account_codes', 'description', 'type_transaction', 'searchDate', 'startdate', 'enddate', 'flag'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'currency' => 'required',
            'date' => 'required',
            'id_master_account_codes' => 'required',
            'description' => 'required',
            'type_transaction' => 'required',
            'amount' => 'required',
            'tax' => 'required',
        ]);

        $idrrate = MstCurrencies::where('currency_code', $request->currency)->first()->idr_rate;
        $amount_in_idr = ($request->amount*$idrrate);

        DB::beginTransaction();
        try{
            $data = TransDataKas::create([
                'currency' => $request->currency,
                'date' => $request->date,
                'trans_number' => 1,
                'id_master_account_codes' => $request->id_master_account_codes,
                'description' => $request->description,
                'type_transaction' => $request->type_transaction,
                'amount' => $request->amount,
                'amount_in_idr' => $amount_in_idr,
                'tax' => $request->tax
            ]);
            $number = $data->id;
            $trans_number = "TK" . str_pad($number, 8, '0', STR_PAD_LEFT);
            TransDataKas::where('id', $data->id)->update([
                'trans_number' => $trans_number
            ]);

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Create New Transaction Data Kas ('. $trans_number . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();

            return redirect()->back()->with(['success' => 'Success Create New Transaction Data Kas']);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Create New Transaction Data Kas!']);
        }
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());

        $id = decrypt($id);

        $request->validate([
            'currency' => 'required',
            'date' => 'required',
            'id_master_account_codes' => 'required',
            'description' => 'required',
            'type_transaction' => 'required',
            'amount' => 'required',
            'tax' => 'required',
        ]);

        $idrrate = MstCurrencies::where('currency_code', $request->currency)->first()->idr_rate;
        $amount_in_idr = ($request->amount*$idrrate);

        $databefore = TransDataKas::where('id', $id)->first();
        $databefore->currency = $request->currency;
        $databefore->date = $request->date;
        $databefore->id_master_account_codes = $request->id_master_account_codes;
        $databefore->description = $request->description;
        $databefore->type_transaction = $request->type_transaction;
        $databefore->amount = $request->amount;
        $databefore->amount_in_idr = $amount_in_idr;
        $databefore->tax = $request->tax;

        if($databefore->isDirty()){
            DB::beginTransaction();
            try{
                $data = TransDataKas::where('id', $id)->update([
                    'currency' => $request->currency,
                    'date' => $request->date,
                    'id_master_account_codes' => $request->id_master_account_codes,
                    'description' => $request->description,
                    'type_transaction' => $request->type_transaction,
                    'amount' => $request->amount,
                    'amount_in_idr' => $amount_in_idr,
                    'tax' => $request->tax
                ]);

                $trans_number = TransDataKas::where('id', $id)->first()->trans_number;

                //Audit Log
                $username= auth()->user()->email; 
                $ipAddress=$_SERVER['REMOTE_ADDR'];
                $location='0';
                $access_from=Browser::browserName();
                $activity='Update Transaction Data Kas ('. $trans_number . ')';
                $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                DB::commit();
                return redirect()->back()->with(['success' => 'Success Update Transaction Data Kas']);
            } catch (\Exception $e) {
                dd($e);
                return redirect()->back()->with(['fail' => 'Failed to Update Transaction Data Kas!']);
            }
        } else {
            return redirect()->back()->with(['info' => 'Nothing Change, The data entered is the same as the previous one!']);
        }
    }
    public function delete($id)
    {
        $id = decrypt($id);

        // dd($id);

        DB::beginTransaction();
        try{
            $trans_number = TransDataKas::where('id', $id)->first()->trans_number;
            TransDataKas::where('id', $id)->delete();

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Delete Transaction Data Kas ('. $trans_number . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Delete Transaction Data Kas ' . $trans_number]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Delete Transaction Data Kas ' . $trans_number .'!']);
        }
    }
}
