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
    $detailSub = DB::table('barcode_detail')
        ->select('id_barcode', DB::raw('COUNT(*) as barcode_count'))
        ->groupBy('id_barcode');

    // Base query (dipakai 2x)
    $baseQuery = DB::table('sales_orders as a')
        ->leftJoin('barcodes as b', 'a.id', '=', 'b.id_sales_orders')
        ->leftJoin('master_customers as f', 'a.id_master_customers', '=', 'f.id')
        ->leftJoin(
            DB::raw("
                (SELECT id, rm_code as product_code, description, id_master_units, 'RM' as type_product, 'NULL' as perforasi, weight
                 FROM master_raw_materials WHERE status = 'Active'
                 UNION ALL
                 SELECT id, code as product_code, description, id_master_units, 'AUX' as type_product, 'NULL' as perforasi, '' as weight
                 FROM master_tool_auxiliaries) as c
            "),
            function ($join) {
                $join->on('a.id_master_products', '=', 'c.id')
                     ->on('a.type_product', '=', 'c.type_product');
            }
        )
        ->leftJoinSub($detailSub, 'g', function ($join) {
            $join->on('b.id', '=', 'g.id_barcode');
        })
        ->where('a.status', 'Posted')
        ->whereNotIn('a.type_product', ['FG', 'WIP'])
        ->select(
            'a.*',
            'b.*',
            'b.type_product as type_product_barcode',
            'b.id as id_barcode',
            'c.product_code',
            'c.description as product_name_aux',
            'c.description as product_name_rm',
            'f.name as customer_name',
            'g.barcode_count',
            'a.id as id',
            'a.id_master_customers as id_master_customers',
            'a.id_master_products as id_master_products',
            'a.type_product as type_product'
        );

    // 1) Semua data (barcode ada / belum ada)
    $wo = (clone $baseQuery)->get();

    // 2) Hanya yang barcode-nya SUDAH ADA, diurutkan dari yang terbaru (descending)
    $woHasBarcode = (clone $baseQuery)
        ->whereNotNull('b.id')
        ->orderByDesc('b.id')
        ->get();

    $wc = DB::table('master_work_centers')->where('status', 'Active')->get();
    $warehouses = DB::table('master_warehouses')->where('is_active', '1')->get();

    // Kirim dua variabel ke view
    return view('barcode.barcode_mesian.create', compact('wo', 'woHasBarcode', 'wc', 'warehouses'));
}


    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'id_sales_orders' => 'required',
            'qty' => 'required|integer|min:1',
            'id_master_customers' => 'required',
            'id_master_products' => 'required',
            'type_product' => 'required',
            'id_master_warehouses' => 'required',
            'shift' => 'required'
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
                'type_product' => $validatedData['type_product'],
                'id_master_warehouses' => $validatedData['id_master_warehouses'],
                'shift' => $validatedData['shift']
            ]);
    
            // Generate barcode numbers and save them
            if ($detailsoal) {
                $qty = $validatedData['qty'];
                $todayPrefix = Carbon::now()->format('ymd');
                $lastBarcode = DB::table('barcode_detail')
                    ->join('barcodes', 'barcode_detail.id_barcode', '=', 'barcodes.id')
                    ->where('barcode_detail.barcode_number', 'like', $todayPrefix . '%')
                    ->where('barcodes.id_work_orders', 0)
                    ->orderBy('barcode_detail.barcode_number', 'desc')
                    ->select('barcode_detail.*')
                    ->first();
    
                $lastNumber = $lastBarcode ? intval(substr($lastBarcode->barcode_number, 6, 3)) : 0;
    
                $typeSuffix = $validatedData['type_product'] === 'RM' ? 'RM' : 'MC';
                $status = 'In Stock ' . $validatedData['type_product'];
                $barcodeDetails = [];
                for ($i = 1; $i <= $qty; $i++) {
                    $lastNumber++;
                    $barcodeNumber = $todayPrefix . str_pad($lastNumber, 3, '0', STR_PAD_LEFT) . $typeSuffix;
                    $barcodeDetails[] = [
                        'id_barcode' => $detailsoal->id,
                        'barcode_number' => $barcodeNumber,
                        'status' => $status,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
    
                DB::table('barcode_detail')->insert($barcodeDetails);
            }
    
            // Commit the transaction
            DB::commit();
            return redirect('/create-barcode-mesin')
                ->with('status', 'Data Ditambah')
                ->with('new_barcode_id', $detailsoal->id);
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollback();
            return redirect('/create-barcode-mesin')->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
public function show($id)
{
    $barcodeDetails = DB::table('barcode_detail as bd')
        ->join('barcodes as b', 'bd.id_barcode', '=', 'b.id')
        ->leftJoin('sales_orders as so', 'b.id_sales_orders', '=', 'so.id')
        ->leftJoin(DB::raw("
            (
                SELECT 
                    id, 
                    rm_code as product_code, 
                    description, 
                    id_master_units, 
                    'RM' as type_product, 
                    'NULL' as perforasi, 
                    weight 
                FROM master_raw_materials 
                WHERE status = 'Active'
                
                UNION ALL 
                
                SELECT 
                    id, 
                    code as product_code, 
                    description, 
                    id_master_units, 
                    'AUX' as type_product, 
                    'NULL' as perforasi, 
                    '' as weight 
                FROM master_tool_auxiliaries
            ) as mp
        "), function ($join) {
            $join->on('b.id_master_products', '=', 'mp.id')
                 ->on('b.type_product', '=', 'mp.type_product');
        })
        ->leftJoin('master_units as mu', 'mp.id_master_units', '=', 'mu.id')
        ->leftJoin('master_customers as mc', 'b.id_master_customers', '=', 'mc.id')
        ->select(
            'bd.barcode_number',
            'bd.created_at as tgl_buat',
            'b.shift',

            'so.so_number',
            'so.so_type',
            'so.type_product',
            'so.qty as so_qty',
            'so.id_order_confirmations',
            'so.reference_number',
            'so.cust_product_code',

            'mc.name as nm_cust',

            'mp.product_code',
            'mp.description',
            'mp.perforasi',
            'mp.weight',

            'mu.unit_code as unit_code',
            
        )
        ->where('bd.id_barcode', $id)
        ->orderBy('bd.id')
        ->get();


    return view('barcode.print_barcodemesin', compact('barcodeDetails'));
}


}
