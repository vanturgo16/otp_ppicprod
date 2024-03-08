<?php

namespace App\Http\Controllers\ppic;

use Browser;
use DataTables;
use App\Models\MstUnits;
use Illuminate\Http\Request;
use App\Models\MstWorkCenters;
use App\Models\ppic\workOrder;
use App\Traits\AuditLogsTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Marketing\salesOrder;
use App\Models\MstProcessProductions;

class workOrderController extends Controller
{
    use AuditLogsTrait;
    public function saveLogs($activityLog = null)
    {
        //Audit Log
        $username = auth()->user()->email;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $location = '0';
        $access_from = Browser::browserName();
        $activity = $activityLog;
        $this->auditLogs($username, $ipAddress, $location, $access_from, $activity);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            $orderColumn = $request->input('order')[0]['column'];
            $orderDirection = $request->input('order')[0]['dir'];
            $columns = ['', '', 'wo_number', 'so_number', 'product_code', 'id_master_products', 'process', 'work_center', 'status', ''];

            // Query dasar
            $query = DB::table('work_orders as a')
                ->join('master_process_productions as b', 'a.id_master_process_productions', '=', 'b.id')
                ->join('master_work_centers as c', 'a.id_master_work_centers', '=', 'c.id')
                ->join('sales_orders as d', 'a.id_sales_orders', '=', 'd.id')
                ->join(
                    \DB::raw(
                        '(SELECT id, product_code, description, id_master_units, \'FG\' as type_product FROM master_product_fgs WHERE status = \'Active\' UNION ALL SELECT id, wip_code as product_code, description, id_master_units, \'WIP\' as type_product FROM master_wips WHERE status = \'Active\') e'
                    ),
                    function ($join) {
                        $join->on('a.id_master_products', '=', 'e.id');
                        $join->on('a.type_product', '=', 'e.type_product');
                    }
                )
                ->join('master_units as f', 'a.id_master_units', '=', 'f.id')
                ->leftJoin('master_units as g', 'a.id_master_units_needed', '=', 'g.id')
                ->leftJoin(
                    \DB::raw(
                        '(SELECT id, product_code as pc_needed, description as dsc, id_master_units, \'FG\' as type_product FROM master_product_fgs WHERE status = \'Active\' UNION ALL SELECT id, wip_code as pc_needed, description as dsc, id_master_units, \'WIP\' as type_product FROM master_wips WHERE status = \'Active\') h'
                    ),
                    function ($join) {
                        $join->on('a.id_master_products_material', '=', 'h.id');
                        $join->on('a.type_product_material', '=', 'h.type_product');
                    }
                )
                ->select('a.wo_number', 'd.so_number', 'a.type_product', 'a.id_master_products', 'b.process', 'c.work_center', 'a.qty', 'a.id_master_units', 'a.type_product_material', 'a.id_master_products_material', 'a.qty_needed', 'a.id_master_units_needed', 'a.note', 'a.status', 'e.product_code', 'e.description', 'f.unit', 'g.unit as unit_needed', 'h.pc_needed', 'h.dsc')
                ->orderBy($columns[$orderColumn], $orderDirection);

            // Handle pencarian
            if ($request->has('search') && $request->input('search')) {
                $searchValue = $request->input('search');
                $query->where(function ($query) use ($searchValue) {
                    $query->where('a.wo_number', 'like', '%' . $searchValue . '%')
                        // ->orWhere('a.so_number', 'like', '%' . $searchValue . '%')
                        // ->orWhere('a.date', 'like', '%' . $searchValue . '%')
                        // ->orWhere('a.so_type', 'like', '%' . $searchValue . '%')
                        // ->orWhere('b.name', 'like', '%' . $searchValue . '%')
                        // ->orWhere('c.name', 'like', '%' . $searchValue . '%')
                        // ->orWhere('a.reference_number', 'like', '%' . $searchValue . '%')
                        // ->orWhere('a.due_date', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.status', 'like', '%' . $searchValue . '%');
                });
            }

            return DataTables::of($query)
                ->addColumn('action', function ($data) {
                    return view('ppic.work_order.action', compact('data'));
                })
                ->addColumn('bulk-action', function ($data) {
                    $checkBox = $data->status == 'Request' ? '<input type="checkbox" name="checkbox" data-so-number="' . $data->so_number . '" class="rowCheckbox" />' : '';
                    return $checkBox;
                })
                ->addColumn('description', function ($data) {
                    return $data->product_code . ' - ' . $data->description;
                })
                ->addColumn('description_needed', function ($data) {
                    return $data->pc_needed . ' - ' . $data->dsc;
                })
                ->addColumn('status', function ($data) {
                    $badgeColor = $data->status == 'Request' ? 'info' : 'success';
                    return '<span class="badge bg-' . $badgeColor . '" style="font-size: smaller;width: 100%">' . $data->status . '</span>';
                })
                ->rawColumns(['bulk-action', 'status'])
                ->make(true);
        }
        return view('ppic.work_order.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $kodeOtomatis = $this->generateCode();
        $salesOrders = $this->getAllSalesOrders();
        $proccessProductions = $this->getAllProccessProductions();
        $workCenters = $this->getAllWorkCenters();
        $units = $this->getAllUnit();

        return view('ppic.work_order.create', compact('salesOrders', 'proccessProductions', 'workCenters', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // echo json_encode($request->all());exit;
        // dd($request->all());

        DB::beginTransaction();
        try {
            // Simpan data ke dalam tabel work_orders
            $work_order = workOrder::create([
                'id_sales_orders' => $request->id_sales_orders,
                'wo_number' => $request->wo_number,
                'id_master_process_productions' => $request->id_master_process_productions,
                'id_master_work_centers' => $request->id_master_work_centers,
                'type_product' => $request->type_product,
                'id_master_products' => $request->id_master_products,
                'type_product_material' => $request->type_product_material,
                'id_master_products_material' => $request->id_master_products_material,
                'qty' => $request->qty,
                'id_master_units' => $request->id_master_units,
                'qty_neede' => $request->qty_neede,
                'id_master_units_needed' => $request->id_master_units_needed,
                'start_date' => $request->start_date,
                'finish_date' => $request->finish_date,
                'status' => 'Request',
                'note' => $request->note,
                // Sesuaikan dengan kolom-kolom lain yang ada pada tabel order_confirmation
            ]);

            $this->saveLogs('Adding New Work Order : ' . $request->wo_number);

            DB::commit();

            if ($request->has('save_add_more')) {
                return redirect()->back()->with(['success' => 'Success Create New Work Order ' . $request->wo_number]);
            } else {
                return redirect()->route('ppic.workOrder.index')->with(['success' => 'Success Create New Work Order ' . $request->wo_number]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => $e . 'Failed to Create New Work Order! ' . $request->wo_number]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getAllSalesOrders()
    {
        $salesOrders = salesOrder::select('*')
            ->where('status', 'Posted')
            ->get();
        return $salesOrders;
    }

    public function getAllProccessProductions()
    {
        $proccessProductions = MstProcessProductions::select('*')
            ->where('status', 'Active')
            ->orderBy('process_code', 'asc')
            ->get();
        return $proccessProductions;
    }

    public function getAllWorkCenters()
    {
        $workCenters = MstWorkCenters::select('*')
            ->where('status', 'Active')
            ->orderBy('work_center', 'asc')
            ->get();
        return $workCenters;
    }

    public function getAllUnit()
    {
        $units = MstUnits::select('id', 'unit')
            ->where('is_active', 1)
            ->orderBy('unit', 'asc')
            ->get();

        return $units;
    }

    public function getDataProduct()
    {
        $typeProduct = request()->get('typeProduct');
        if ($typeProduct == 'WIP') {
            $products = DB::table('master_wips as a')
                ->where('a.status', 'Active')
                ->select('a.id', 'a.wip_code', 'a.description')
                ->get();
        } else if ($typeProduct == 'FG') {
            $products = DB::table('master_product_fgs as a')
                ->where('a.status', 'Active')
                ->select('a.id', 'a.product_code', 'a.description')
                ->get();
        }
        return response()->json(['products' => $products]);
    }

    public function getProductDetail()
    {
        $typeProduct = request()->get('typeProduct');
        $idProduct = request()->get('idProduct');
        if ($typeProduct == 'WIP') {
            $product = DB::table('master_wips as a')
                ->select('a.id', 'a.description', 'a.id_master_units')
                // ->join('master_units as b', 'a.id_master_units', '=', 'b.id')
                ->where('a.id', $idProduct)
                ->first();
        } else if ($typeProduct == 'FG') {
            $product = DB::table('master_product_fgs as a')
                ->select('a.id', 'a.description', 'a.id_master_units', 'a.sales_price as price')
                // ->join('master_units as b', 'a.id_master_units', '=', 'b.id')
                ->where('a.id', $idProduct)
                ->first();
        }
        return response()->json(['product' => $product]);
    }

    public function generateWONumber()
    {
        $proccessProduction = request()->get('proccessProduction');
        $prefix = 'WO'; // Prefix yang diinginkan
        $currentMonthYear = now()->format('ymd'); // Format tahun dan bulan saat ini
        $suffixLength = 4; // Panjang angka di bagian belakang

        $latestCode = workOrder::where('wo_number', 'like', "%{$currentMonthYear}%")
            ->orderBy('wo_number', 'desc')
            ->value('wo_number');

        $lastNumber = $latestCode ? intval(substr($latestCode, -1 * $suffixLength)) : 0;

        $newNumber = $lastNumber + 1;

        $newCode = $prefix . $proccessProduction . $currentMonthYear . str_pad($newNumber, $suffixLength, '0', STR_PAD_LEFT);

        // Gunakan $newCode sesuai kebutuhan Anda
        return response()->json(['code' => $newCode]);
    }

    public function getOrderDetail()
    {
        $so_number = request()->get('so_number');

        $sales_order = salesOrder::with('salesOrderDetails', 'salesOrderDetails.masterUnit')
            ->where('so_number', $so_number)
            ->first();

        $combinedDataProducts = DB::table('master_product_fgs')
            ->select('id', 'product_code', 'description', 'id_master_units', DB::raw("'FG' as type_product"))
            ->where('status', 'Active')
            ->unionAll(
                DB::table('master_wips')
                    ->select('id', 'wip_code as product_code', 'description', 'id_master_units', DB::raw("'WIP' as type_product"))
                    ->where('status', 'Active')
            )
            ->get();

        return response()->json(['sales_order' => $sales_order, 'products' => $combinedDataProducts]);
    }
}
