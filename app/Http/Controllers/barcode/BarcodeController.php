<?php

namespace App\Http\Controllers\barcode;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Barcode;
use Carbon\Carbon;

class BarcodeController extends Controller
{

    public function index(Request $request)
    {
        // Ambil nilai start_date, end_date, dan work_center dari request
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $work_center = $request->input('work_center');

        // Buat query dengan filter tanggal dan work_center, tanpa nomor di belakang
        $results = DB::table('barcodes as a')
            ->leftJoin('sales_orders as b', 'a.id_sales_orders', '=', 'b.id')
            ->join('work_orders as c', 'a.id_work_orders', '=', 'c.id')
            ->join('master_process_productions as d', 'a.id_master_process_productions', '=', 'd.id')
            ->join('master_work_centers as e', 'a.id_work_centers', '=', 'e.id')
            ->leftJoin('master_customers as f', 'a.id_master_customers', '=', 'f.id')
            ->leftJoin('barcode_detail as g', 'a.id', '=', 'g.id_barcode')
            ->select(
                'a.*',
                'b.so_number',
                'c.wo_number',
                'e.work_center_code',
                DB::raw("SUBSTRING_INDEX(e.work_center, ' ', 2) as work_center"),
                'f.name as name_cust',
                DB::raw("COUNT(g.id) as barcode_count")
            )
            ->when($start_date, function ($query, $start_date) {
                return $query->whereDate('a.created_at', '>=', $start_date);
            })
            ->when($end_date, function ($query, $end_date) {
                return $query->whereDate('a.created_at', '<=', $end_date);
            })
            ->groupBy(
                'a.id',
                'b.so_number',
                'c.wo_number',
                'e.work_center_code',
                'f.name',
                DB::raw("SUBSTRING_INDEX(e.work_center, ' ', 2)")
            )
            ->get();

        // Mengirim hasil query ke view barcode.index
        return view('barcode.index', compact('results'));
    }

    public function create()
    {

        $wo = DB::table('work_orders as a')
            ->join('sales_orders as b', 'a.id_sales_orders', '=', 'b.id')
            ->leftjoin('master_product_fgs as c', 'a.id_master_products', '=', 'c.id')
            ->where('a.status', 'Posted')
            ->select('a.*', 'b.id as id_sal', 'b.id_master_customers', 'c.type_product_code', 'c.group_sub_code')
            ->get();

        // dd($wo);

        $wc = DB::table('master_work_centers')->where('status', 'Active')->get();

        return view('barcode.create', compact('wo', 'wc'));
    }


    public function cange($id)
    {
        $results = DB::table('barcodes as a')
            ->leftJoin('sales_orders as b', 'a.id_sales_orders', '=', 'b.id')
            ->join('work_orders as c', 'a.id_work_orders', '=', 'c.id')
            ->join('master_process_productions as d', 'a.id_master_process_productions', '=', 'd.id')
            ->join('master_work_centers as e', 'a.id_work_centers', '=', 'e.id')
            ->leftJoin('master_customers as f', 'a.id_master_customers', '=', 'f.id')
            ->leftJoin('barcode_detail as g', 'a.id', '=', 'g.id_barcode')
            ->select(
                'a.*',
                'b.so_number',
                'c.wo_number',
                'e.work_center_code',
                DB::raw("SUBSTRING_INDEX(e.work_center, ' ', 2) as work_center"),
                'f.name as name_cust',
                DB::raw("COUNT(g.id) as barcode_count")
            )
            ->where('a.id', $id)  // Pastikan ID difilter dari tabel 'barcodes'
            ->groupBy(
                'a.id',
                'b.so_number',
                'c.wo_number',
                'e.work_center_code',
                'f.name',
                DB::raw("SUBSTRING_INDEX(e.work_center, ' ', 2)")
            )
            ->get();

        // Menampilkan hasil dengan dd (dump and die)


        $so = DB::table('sales_orders')
            ->select('so_number', 'so_category', 'status', 'id')
            ->get();
        // dd($results);
        return view('barcode.cange', compact('so', 'results'));
    }


    public function store(Request $request)
    {
        // Validate the request data
        $this->validate($request, [
            'id_work_orders'    => 'required',
            'id_work_centers'   => 'required',
            'shift'             => 'required',
            'qty'               => 'required',
        ]);

        $id_work_orders = $request->input('id_work_orders');

        // Temukan data tambahan terkait dengan id_work_orders yang dipilih
        $workOrder = DB::table('work_orders')->where('id', $id_work_orders)->first();

        if ($workOrder) {
            $id_sales_orders = $workOrder->id_sales_orders;
            $id_master_process_productions = $workOrder->id_master_process_productions;

            // Retrieve additional fields from the request
            $type_product_code = $request->input('type_product_code');
            $group_sub_code = $request->input('group_sub_code');

            // Gunakan transaksi database untuk memastikan atomisitas
            DB::transaction(function () use ($request, $id_work_orders, $type_product_code, $group_sub_code) {
                // Simpan detail soal menggunakan model Barcode
                $detailsoal = Barcode::create([
                    'id_work_orders'                => $id_work_orders,
                    'id_work_centers'               => $request->input('id_work_centers'),
                    'shift'                         => $request->input('shift'),
                    'qty'                           => $request->input('qty'),
                    'id_sales_orders'               => $request->input('id_sales_orders'),
                    'id_master_process_productions' => $request->input('id_master_process_productions'),
                    'id_master_customers'           => $request->input('id_master_customers'),
                    'id_master_products'            => $request->input('id_master_products'),
                    'staff'                         => Auth::user()->name
                ]);

                // Generate barcode numbers and save them
                if ($detailsoal) {
                    $qty = $request->input('qty');
                    $yearMonth = Carbon::now()->format('ym');
                    $lastBarcode = DB::table('barcode_detail')
                        ->where('barcode_number', 'like', $yearMonth . '%')
                        ->orderBy('barcode_number', 'desc')
                        ->first();

                    $lastNumber = $lastBarcode ? intval(substr($lastBarcode->barcode_number, 4, 5)) : 0;

                    $barcodeDetails = [];
                    for ($i = 1; $i <= $qty; $i++) {
                        $lastNumber++;
                        $barcodeNumber = $yearMonth . str_pad($lastNumber, 5, '0', STR_PAD_LEFT) . $type_product_code . $group_sub_code;
                        $barcodeDetails[] = [
                            'id_barcode' => $detailsoal->id,
                            'barcode_number' => $barcodeNumber,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    DB::table('barcode_detail')->insert($barcodeDetails);
                }
            });

            return redirect('/barcode')->with('status', 'Data Ditambah');
        } else {
            return redirect('/barcode')->with(['error' => 'Data Work Order tidak ditemukan!']);
        }
    }

    public function show($id)
    {
        $barcodeDetails = DB::table('barcode_detail as bd')
            ->join('barcodes as b', 'bd.id_barcode', '=', 'b.id')
            ->leftJoin('sales_orders as so', 'b.id_sales_orders', '=', 'so.id')

            ->join('work_orders as wo', 'b.id_work_orders', '=', 'wo.id')
            ->join(
                \DB::raw(
                    '(SELECT id, product_code, description, id_master_units, \'FG\' as type_product FROM master_product_fgs WHERE status = \'Active\' UNION ALL SELECT id, wip_code as product_code, description, id_master_units, \'WIP\' as type_product FROM master_wips WHERE status = \'Active\') e'
                ),
                function ($join) {
                    // $join->on('d.id_master_products', '=', 'e.id');
                    // $join->on('d.type_product', '=', 'e.type_product');
                    $join->on('so.id_master_products', '=', 'e.id');
                    $join->on('so.type_product', '=', 'e.type_product');
                }
            )
            // ->join('master_product_fgs  as mp', 'b.id_master_products', '=', 'mp.id')
            ->join('master_work_centers as mwc', 'b.id_work_centers', '=', 'mwc.id')
            ->leftJoin('master_customers as mc', 'b.id_master_customers', '=', 'mc.id')
            ->select(
                'bd.barcode_number',
                'bd.created_at as tgl_buat',
                'b.shift',
                'so.so_number',
                'wo.wo_number',
                'mwc.work_center_code',
                'mwc.work_center',
                'mc.name as nm_cust',
                'e.description',
                // 'e.perforasi',
                'e.product_code',
                'e.type_product'
            )->where('bd.id_barcode', $id)
            ->get();
            // dd($barcodeDetails);
        return view('barcode.show_barcode', compact('barcodeDetails'));
    }

    public function print_satuan_standar($id)
    {
        $barcode = DB::table('barcode_detail as bd')
            ->join('barcodes as b', 'bd.id_barcode', '=', 'b.id')
            ->leftJoin('sales_orders as so', 'b.id_sales_orders', '=', 'so.id')
            ->join('work_orders as wo', 'b.id_work_orders', '=', 'wo.id')
            ->join('master_product_fgs  as mp', 'b.id_master_products', '=', 'mp.id')
            ->join('master_work_centers as mwc', 'b.id_work_centers', '=', 'mwc.id')
            ->leftJoin('master_customers as mc', 'b.id_master_customers', '=', 'mc.id')
            ->select(
                'bd.barcode_number',
                'bd.created_at as tgl_buat',
                'b.shift',
                'so.so_number',
                'wo.wo_number',
                'mwc.work_center_code',
                'mwc.work_center',
                'mc.name as nm_cust',
                'mp.description',
                'mp.thickness',
                'mp.perforasi',
                'mp.width',
                'mp.height',
            )->where('bd.barcode_number', $id)
            ->first();
        //  dd($barcode);
        return view('barcode.print_satuan', compact('barcode'));
    }

    public function print_standar($id)
    {
        $barcodeDetails = DB::table('barcode_detail as bd')
            ->join('barcodes as b', 'bd.id_barcode', '=', 'b.id')
            ->leftJoin('sales_orders as so', 'b.id_sales_orders', '=', 'so.id')
            ->join('work_orders as wo', 'b.id_work_orders', '=', 'wo.id')
            ->join('master_product_fgs  as mp', 'b.id_master_products', '=', 'mp.id')
            ->join('master_work_centers as mwc', 'b.id_work_centers', '=', 'mwc.id')
            ->leftJoin('master_customers as mc', 'b.id_master_customers', '=', 'mc.id')
            ->select(
                'bd.barcode_number',
                'bd.created_at as tgl_buat',
                'b.shift',
                'so.so_number',
                'wo.wo_number',
                'mwc.work_center_code',
                'mwc.work_center',
                'mc.name as nm_cust',
                'mp.description',
                'mp.thickness',
                'mp.perforasi',
                'mp.width',
                'mp.height',
                //  'mp.type_product_code',
                //  'mp.group_sub_code',
            )->where('bd.id_barcode', $id)
            ->get();
        return view('barcode.print', compact('barcodeDetails'));
    }

    public function print_broker($id)
    {
        $barcodeDetails = DB::table('barcode_detail as bd')
            ->join('barcodes as b', 'bd.id_barcode', '=', 'b.id')
            ->leftJoin('sales_orders as so', 'b.id_sales_orders', '=', 'so.id')
            ->join('work_orders as wo', 'b.id_work_orders', '=', 'wo.id')
            ->join('master_product_fgs  as mp', 'b.id_master_products', '=', 'mp.id')
            ->join('master_work_centers as mwc', 'b.id_work_centers', '=', 'mwc.id')
            ->leftJoin('master_customers as mc', 'b.id_master_customers', '=', 'mc.id')
            ->select(
                'bd.barcode_number',
                'bd.created_at as tgl_buat',
                'b.shift',
                'so.so_number',
                'wo.wo_number',
                'mwc.work_center_code',
                'mwc.work_center',
                'mc.name as nm_cust',
                'mp.description',
                'mp.thickness',
                'mp.perforasi',
                'mp.width',
                'mp.height',
            )->where('bd.id_barcode', $id)
            ->get();
        return view('barcode.print_broker', compact('barcodeDetails'));
    }

    public function print_cbc($id)
    {
        $barcodeDetails = DB::table('barcode_detail as bd')
            ->join('barcodes as b', 'bd.id_barcode', '=', 'b.id')
            ->leftJoin('sales_orders as so', 'b.id_sales_orders', '=', 'so.id')
            ->join('work_orders as wo', 'b.id_work_orders', '=', 'wo.id')
            ->join('master_product_fgs  as mp', 'b.id_master_products', '=', 'mp.id')
            ->join('master_work_centers as mwc', 'b.id_work_centers', '=', 'mwc.id')
            ->leftJoin('master_customers as mc', 'b.id_master_customers', '=', 'mc.id')
            ->select(
                'bd.barcode_number',
                'bd.created_at as tgl_buat',
                'b.shift',
                'so.so_number',
                'wo.wo_number',
                'mwc.work_center_code',
                'mwc.work_center',
                'mc.name as nm_cust',
                'mp.description',
                'mp.thickness',
                'mp.perforasi',
                'mp.width',
            )->where('bd.id_barcode', $id)
            ->get();
        return view('barcode.print_cbc', compact('barcodeDetails'));
    }

    // public function table_print()
    // {

    //     return view('barcode.table');
    // }
}
