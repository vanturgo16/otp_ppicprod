<?php

namespace App\Http\Controllers\barcode;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Marketing\salesOrder;
use Illuminate\Support\Facades\DB;
use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use App\Models\Barcode;
use Carbon\Carbon;

class BarcodeMesinController extends Controller
{
    use AuditLogsTrait;

    public function create()
    {
        $wo = DB::table('sales_orders as a')
        ->leftJoin('master_customers as f', 'a.id_master_customers', '=', 'f.id')
            ->leftJoin(
                DB::raw("
                    (SELECT id, rm_code as product_code, description, id_master_units, 'RM' as type_product, 'NULL' as perforasi, weight 
                    FROM master_raw_materials WHERE status = 'Active' 
                    UNION ALL 
                    SELECT id, code as product_code, description, id_master_units, 'AUX' as type_product, 'NULL' as perforasi, '' as weight 
                    FROM master_tool_auxiliaries) as c"
                ), 
                function ($join) {
                    $join->on('a.id_master_products', '=', 'c.id')
                        ->on('a.type_product', '=', 'c.type_product');
                }
            )
            ->where('a.status', 'Posted')
            ->whereNotIn('a.type_product', ['FG', 'WIP'])
            ->select('a.*','c.description as product_name_aux','c.description as product_name_rm','f.name as customer_name')
            ->get();

        // debugging purposes
        // dd($wo);

        $wc = DB::table('master_work_centers')->where('status', 'Active')->get();

        return view('barcode.barcode_mesian.create', compact('wo', 'wc'));
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'id_sales_orders' => 'required',
            'qty' => 'required|numeric',
            'id_master_customers' => 'required',
            'id_master_products' => 'required',
            'type_product' => 'required'
        ]);
    
        try {
            // Start a transaction
            DB::beginTransaction();
    
            // Create a barcode detail using the Barcode model
            $detailsoal = Barcode::create([
                'qty' => $validatedData['qty'],
                'id_sales_orders' => $validatedData['id_sales_orders'],
                'id_master_customers' => $validatedData['id_master_customers'],
                'id_master_products' => $validatedData['id_master_products'],
                'staff' => Auth::user()->name,
                'type_product' => $validatedData['type_product']
            ]);
    
            // Generate barcode numbers and save them
            if ($detailsoal) {
                $qty = $validatedData['qty'];
                $yearMonth = Carbon::now()->format('ym');
                $lastBarcode = DB::table('barcode_detail')
                    ->where('barcode_number', 'like', $yearMonth . '%')
                     ->orderBy('barcode_number', 'desc')
                     ->first();
    
                $lastNumber = $lastBarcode ? intval(substr($lastBarcode->barcode_number, 4, 5)) : 0;
    
                $typeSuffix = $validatedData['type_product'] === 'AUX' ? 'MC' : 'RM';
                $status = $validatedData['type_product'] === 'AUX' ? 'In Stock AUX' : 'In Stock RM';
                $barcodeDetails = [];
                for ($i = 1; $i <= $qty; $i++) {
                    $lastNumber++;
                    $barcodeNumber = $yearMonth . str_pad($lastNumber, 5, '0', STR_PAD_LEFT) . $typeSuffix;
                    $barcodeDetails[] = [
                        'id_barcode' => $detailsoal->id,
                        'barcode_number' => $barcodeNumber,
                        'status' => $status, // Add status here
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
    
                DB::table('barcode_detail')->insert($barcodeDetails);
            }
    
            // Commit the transaction
            DB::commit();
            return redirect('/barcode')->with('status', 'Data Ditambah');
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollback();
            return redirect('/barcode')->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
}
