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
use App\Models\ppic\workOrderDetails;
use Illuminate\Support\Facades\Crypt;

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
            $columns = ['', 'id', 'wo_number', 'so_number', 'product_code', 'process', 'work_center', 'qty', 'unit', 'pc_needed', 'qty_needed', 'unit_needed', 'note', 'status', ''];

            // Query dasar
            $query = DB::table('work_orders as a')
                // ->join('master_group_subs as i', 'a.id_master_process_productions', '=', 'i.id')
                // ->join('master_process_productions as b', 'i.group_sub_code', '=', 'b.process_code')
                ->join('master_process_productions as b', 'a.id_master_process_productions', '=', 'b.id')
                ->leftJoin('master_work_centers as c', 'a.id_master_work_centers', '=', 'c.id')
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
                ->select('a.id', 'a.wo_number', 'd.so_number', 'a.type_product', 'a.id_master_products', 'a.id_master_process_productions', 'b.process', 'c.work_center', 'a.qty', 'a.id_master_units', 'a.type_product_material', 'a.id_master_products_material', 'a.qty_needed', 'a.id_master_units_needed', 'a.note', 'a.status', 'e.product_code', 'e.description', 'f.unit_code as unit', 'g.unit_code as unit_needed', 'h.pc_needed', 'h.dsc')
                ->orderBy($columns[$orderColumn], $orderDirection);

            // Filter berdasarkan status
            if ($request->status) {
                $query->where('a.status', $request->status);
                // $query->where('a.status', 'like', '%' . $request->status . '%');
            }

            if ($request->id_sales_orders) {
                $query->where('a.id_sales_orders', $request->id_sales_orders);
            }

            if ($request->has('search') && $request->input('search')) {
                $searchValue = $request->input('search');
                $query->where(function ($query) use ($searchValue) {
                    $query->where('a.wo_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('d.so_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('e.product_code', 'like', '%' . $searchValue . '%')
                        ->orWhere('e.description', 'like', '%' . $searchValue . '%')
                        ->orWhere('b.process', 'like', '%' . $searchValue . '%')
                        ->orWhere('c.work_center', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.qty', 'like', '%' . $searchValue . '%')
                        ->orWhere('f.unit', 'like', '%' . $searchValue . '%')
                        ->orWhere('h.pc_needed', 'like', '%' . $searchValue . '%')
                        ->orWhere('h.dsc', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.qty_needed', 'like', '%' . $searchValue . '%')
                        // ->orWhere('g.unit_needed', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.note', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.status', 'like', '%' . $searchValue . '%');
                });
            }

            return DataTables::of($query)
                ->addColumn('action', function ($data) {
                    return view('ppic.work_order.action', compact('data'));
                })
                ->addColumn('action_list_wo', function ($data) {
                    return view('ppic.work_order.action_list_wo', compact('data'));
                })
                ->addColumn('bulk-action', function ($data) {
                    $checkBox = $data->status == 'Request' ? '<input type="checkbox" name="checkbox" data-wo-number="' . $data->wo_number . '" class="rowCheckbox" />' : '';
                    return $checkBox;
                })
                ->addColumn('description', function ($data) {
                    return $data->product_code . ' - ' . $data->description;
                })
                ->addColumn('description_needed', function ($data) {
                    return $data->pc_needed . ' - ' . $data->dsc;
                })
                ->addColumn('status', function ($data) {
                    $badgeColor = $data->status == 'Request' ? 'secondary' : ($data->status == 'Un Posted' ? 'warning' : ($data->status == 'Closed' ? 'info' : ($data->status == 'Finish' ? 'primary' : 'success')));
                    return '<span class="badge bg-' . $badgeColor . '" style="font-size: smaller;width: 100%">' . $data->status . '</span>';
                })
                ->addColumn('statusLabel', function ($data) {
                    return $data->status;
                })
                ->addColumn('wo_list', function ($data) {
                    return '<a href="' . route('ppic.workOrder.list', encrypt($data->so_number)) . '">' . $data->so_number . '</a>';
                })
                ->rawColumns(['bulk-action', 'status', 'wo_list'])
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
        $sales_order = DB::table('sales_orders as a')
            ->select('a.*')
            ->where('a.id', $request->id_sales_orders)
            ->first();

        $typeProduct = $request->type_product;
        $idProduct = $request->id_master_products;
        if ($typeProduct == 'WIP') {
            $product = DB::table('master_wips as a')
                ->select('a.*', 'b.group_sub_code', 'b.name')
                ->join('master_group_subs as b', 'a.id_master_group_subs', '=', 'b.id')
                ->where('a.id', $idProduct)
                ->first();

            $product_ref = DB::table('master_wip_ref_wips as a')
                ->select('a.*')
                ->where('a.id_master_wips', $idProduct)
                ->first();

            $product_ref_rm = DB::table('master_wip_refs as a')
                ->select('a.*', 'b.id_master_units')
                ->join('master_raw_materials as b', 'a.id_master_raw_materials', '=', 'b.id')
                ->where('a.id_master_wips', $idProduct)
                ->get();

            // $data['type_product_material'] = 'WIP';
            $data['type_product_material'] = $product_ref->id_master_wips_material == '0' ? null : 'WIP';
            // $data['id_master_products_material'] = $product_ref->id_master_wips_material;
            $data['id_master_products_material'] = $product_ref->id_master_wips_material == '0' ? null : $product_ref->id_master_wips_material;
            // $data['qty_needed'] = $product_ref->id_master_wips_material;
            $data['id_master_units_needed'] = $product_ref->master_units_id;
            $data['qty_results'] = $product_ref->qty_results;
        } else if ($typeProduct == 'FG') {
            $product = DB::table('master_product_fgs as a')
                ->select('a.*', 'b.group_sub_code', 'b.name')
                ->join('master_group_subs as b', 'a.id_master_group_subs', '=', 'b.id')
                ->where('a.id', $idProduct)
                ->first();

            $product_ref = DB::table('master_product_fg_refs as a')
                ->select('a.*')
                ->where('a.id_master_product_fgs', $idProduct)
                ->first();

            $data['type_product_material'] = $product_ref->type_ref;
            // $data['qty_needed'] = $product_ref->id_master_wips_material;
            $data['id_master_units_needed'] = $product_ref->master_units_id;
            if ($product_ref->type_ref == 'WIP') {
                $data['id_master_products_material'] = $product_ref->id_master_wips;
            } else if ($product_ref->type_ref == 'FG') {
                $data['id_master_products_material'] = $product_ref->id_master_fgs;
            } else {
                $data['id_master_products_material'] = null;
            }
            $data['qty_results'] = $product_ref->qty_results;
        }

        if ($data['qty_results'] <> null) {
            $qty_needed = $request->qty / $data['qty_results'];
        } else {
            $qty_needed = null;
        }

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
                'type_product_material' => $data['type_product_material'],
                'id_master_products_material' => $data['id_master_products_material'],
                'qty' => $request->qty,
                'id_master_units' => $request->id_master_units,
                // 'qty_results' => $request->qty_results,
                'qty_needed' => $qty_needed,
                'id_master_units_needed' => $data['id_master_units_needed'],
                'start_date' => $request->start_date,
                'finish_date' => $request->finish_date,
                'status' => 'Request',
                'note' => $request->note,
                // Sesuaikan dengan kolom-kolom lain yang ada pada tabel order_confirmation
            ]);

            if (isset($product_ref_rm) && count($product_ref_rm) > 0) {
                $id_work_order = workOrder::where('wo_number', $request->wo_number)->first();

                // Simpan data
                foreach ($product_ref_rm as $product_rm) {
                    workOrderDetails::create([
                        'id_work_orders' => $id_work_order->id,
                        'type_product' => 'RM',
                        'id_master_products' => $product_rm->id_master_raw_materials,
                        'qty' => $product_rm->weight,
                        'id_master_units' => $product_rm->id_master_units,
                    ]);
                }
            }

            $this->saveLogs('Adding New Work Order : ' . $request->wo_number);

            DB::commit();

            if ($request->has('save_add_more')) {
                return redirect()->back()->with(['success' => 'Success Create New Work Order ' . $request->wo_number]);
            } else if ($request->has('save_add_more_with_so')) {
                return redirect()->back()->with(['success' => 'Success Create New Work Order ' . $request->wo_number]);
            } else if ($request->has('save_with_so')) {
                return redirect()->route('ppic.workOrder.list', encrypt($sales_order->so_number))->with(['success' => 'Success Create New Work Order ' . $request->wo_number]);
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
    public function show(workOrder $workOrder, $encryptedWONumber)
    {
        $wo_number = Crypt::decrypt($encryptedWONumber);

        $work_order = workOrder::with('masterUnit', 'masterUnitNeeded', 'masterProcessProduction', 'masterWorkCenter')
            ->select('a.so_number', 'work_orders.*')
            ->join('sales_orders as a', 'a.id', '=', 'work_orders.id_sales_orders')
            ->where('wo_number', $wo_number)
            ->join(
                \DB::raw(
                    '(SELECT id as id_prod, product_code, description, id_master_units as unit, \'FG\' as t_product FROM master_product_fgs WHERE status = \'Active\' UNION ALL SELECT id as id_prod, wip_code as product_code, description, id_master_units as unit, \'WIP\' as t_product FROM master_wips WHERE status = \'Active\') b'
                ),
                function ($join) {
                    $join->on('work_orders.id_master_products', '=', 'b.id_prod');
                    $join->on('work_orders.type_product', '=', 'b.t_product');
                }
            )
            ->leftJoin(
                \DB::raw(
                    '(SELECT id as id_prod_needed, product_code as pc_needed, description as desc_needed, id_master_units as unit_needed, \'FG\' as t_product_needed FROM master_product_fgs WHERE status = \'Active\' UNION ALL SELECT id as id_prod_needed, wip_code as pc_needed, description as desc_needed, id_master_units as unit_needed, \'WIP\' as t_product_needed FROM master_wips WHERE status = \'Active\') c'
                ),
                function ($join) {
                    $join->on('work_orders.id_master_products_material', '=', 'c.id_prod_needed');
                    $join->on('work_orders.type_product_material', '=', 'c.t_product_needed');
                }
            )
            ->first();

        $typeProduct = $work_order->type_product;
        $idProduct = $work_order->id_master_products;
        if ($typeProduct == 'WIP') {
            $product = DB::table('master_wips as a')
                ->select('a.*', 'a.wip_code as product_code')
                ->where('a.id', $idProduct)
                ->first();
        } else if ($typeProduct == 'FG') {
            $product = DB::table('master_product_fgs as a')
                ->select('a.*')
                ->where('a.id', $idProduct)
                ->first();
        }

        $typeProductNeeded = $work_order->type_product_material;
        $idProductNeeded = $work_order->id_master_products_material;
        if ($typeProductNeeded == 'WIP') {
            $productNeeded = DB::table('master_wips as a')
                ->select('a.*', 'a.wip_code as product_code')
                ->where('a.id', $idProductNeeded)
                ->first();
        } else if ($typeProductNeeded == 'FG') {
            $productNeeded = DB::table('master_product_fgs as a')
                ->select('a.*')
                ->where('a.id', $idProductNeeded)
                ->first();
        } else {
            $productNeeded = null;
        }

        // echo json_encode($work_order);
        // exit;

        return view('ppic.work_order.show', compact('work_order', 'product', 'productNeeded'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(workOrder $workOrder, $encryptedWONumber)
    {
        $wo_number = Crypt::decrypt($encryptedWONumber);

        $workOrder = workOrder::join('sales_orders as b', 'work_orders.id_sales_orders', '=', 'b.id')
            ->select('work_orders.*', 'b.so_number')
            ->where('wo_number', $wo_number)
            ->first();
        $salesOrders = $this->getAllSalesOrders();
        $proccessProductions = $this->getAllProccessProductions();
        $workCenters = $this->getAllWorkCenters();
        $units = $this->getAllUnit();

        $typeProduct = $workOrder->type_product;
        if ($typeProduct == 'WIP') {
            $product = DB::table('master_wips as a')
                ->select('a.*', 'a.wip_code as product_code')
                ->get();
        } else if ($typeProduct == 'FG') {
            $product = DB::table('master_product_fgs as a')
                ->select('a.*')
                ->get();
        }

        $typeProductNeeded = $workOrder->type_product_material;
        if ($typeProductNeeded == 'WIP') {
            $productNeeded = DB::table('master_wips as a')
                ->select('a.*', 'a.wip_code as product_code')
                ->get();
        } else if ($typeProductNeeded == 'FG') {
            $productNeeded = DB::table('master_product_fgs as a')
                ->select('a.*')
                ->get();
        } else {
            $productNeeded = [];
        }

        // echo json_encode($product);
        // exit;

        return view('ppic.work_order.edit', compact('salesOrders', 'proccessProductions', 'workCenters', 'units', 'workOrder', 'product', 'productNeeded'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, workOrder $workOrder)
    {
        $sales_order = workOrder::where('work_orders.id', $request->id_wo)
            ->select('work_orders.*', 'b.so_number')
            ->join('sales_orders as b', 'work_orders.id_sales_orders', '=', 'b.id')
            ->first();

        $old_wo_number = $sales_order->wo_number;

        DB::beginTransaction();
        try {
            // Simpan data ke dalam tabel sales_orders
            $sales_order->update([
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
                // 'qty_results' => $request->id,
                'qty_needed' => $request->id,
                'id_master_units_needed' => $request->id_master_units_needed,
                'start_date' => $request->start_date,
                'finish_date' => $request->finish_date,
                // 'status' => 'Request',
                'note' => $request->note,
                // 'waste' => $salesOrder->id,
                // 'from_stock' => $salesOrder->id,
                // Sesuaikan dengan kolom-kolom lain yang ada pada tabel order_confirmation
            ]);

            if ($old_wo_number == $request->wo_number) {
                $this->saveLogs('Edit Work Order : ' . $old_wo_number);
            } else {
                $this->saveLogs('Edit Work Order : ' . $old_wo_number . ' to ' . $request->wo_number);
            }

            DB::commit();

            $routeName = $request->route_name == 'edit-from-list' ? 'ppic.workOrder.list' : 'ppic.workOrder.index';
            $routeParams = encrypt($sales_order->so_number);

            $message = 'Success Update Work Order ' . $old_wo_number;
            if ($old_wo_number != $request->wo_number) {
                $message .= ' to ' . $request->wo_number;
            }

            return redirect()->route($routeName, $routeParams)->with(['success' => $message]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => $e . 'Failed to Update Work Order! ' . $request->so_number]);
        }
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

    public function getAllRawMaterials()
    {
        $proccessProductions = DB::table('master_raw_materials')
            ->select('*')
            ->where('status', 'Active')
            ->orderBy('rm_code', 'asc')
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
        $units = MstUnits::select('*')
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
        $prefix = 'WO' . $proccessProduction; // Prefix yang diinginkan
        $currentMonthYear = now()->format('ymd'); // Format tahun dan bulan saat ini
        $suffixLength = 5; // Panjang angka di bagian belakang

        $latestCode = WorkOrder::orderBy(DB::raw("RIGHT(wo_number, 5)"), 'desc')
            ->value('wo_number');

        $lastNumber = $latestCode ? intval(substr($latestCode, -1 * $suffixLength)) : 0;

        $newNumber = $lastNumber + 1;

        $newCode = $prefix . str_pad($newNumber, $suffixLength, '0', STR_PAD_LEFT);

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

    public function workOrderList($encryptedSONumber)
    {
        // Mendekripsi nomor sales order
        $so_number = Crypt::decrypt($encryptedSONumber);
        $sales_order = DB::table('sales_orders as a')
            ->select('a.*')
            ->where('a.so_number', $so_number)
            ->first();

        // Menggunakan Query Builder untuk mengambil data work order yang sesuai dengan nomor sales order
        $list_wo = workOrder::with('masterUnit', 'masterUnitNeeded', 'masterProcessProduction', 'masterWorkCenter')
            ->where('id_sales_orders', function ($query) use ($so_number) {
                $query->select('id')
                    ->from('sales_orders')
                    ->where('so_number', $so_number);
            })
            ->join(
                \DB::raw(
                    '(SELECT id as id_prod, product_code, description, id_master_units as unit, \'FG\' as t_product FROM master_product_fgs WHERE status = \'Active\' UNION ALL SELECT id as id_prod, wip_code as product_code, description, id_master_units as unit, \'WIP\' as t_product FROM master_wips WHERE status = \'Active\') b'
                ),
                function ($join) {
                    $join->on('work_orders.id_master_products', '=', 'b.id_prod');
                    $join->on('work_orders.type_product', '=', 'b.t_product');
                }
            )
            ->leftJoin(
                \DB::raw(
                    '(SELECT id as id_prod_needed, product_code as pc_needed, description as desc_needed, id_master_units as unit_needed, \'FG\' as t_product_needed FROM master_product_fgs WHERE status = \'Active\' UNION ALL SELECT id as id_prod_needed, wip_code as pc_needed, description as desc_needed, id_master_units as unit_needed, \'WIP\' as t_product_needed FROM master_wips WHERE status = \'Active\') c'
                ),
                function ($join) {
                    $join->on('work_orders.id_master_products_material', '=', 'c.id_prod_needed');
                    $join->on('work_orders.type_product_material', '=', 'c.t_product_needed');
                }
            )
            ->get();

        // echo json_encode($list_wo);
        // exit;

        return view('ppic.work_order.list', compact('so_number', 'sales_order', 'list_wo'));
    }

    public function createWithSO($encryptedSONumber)
    {
        $so_number = Crypt::decrypt($encryptedSONumber);
        $salesOrders = $this->getAllSalesOrders();
        $proccessProductions = $this->getAllProccessProductions();
        $workCenters = $this->getAllWorkCenters();
        $units = $this->getAllUnit();

        return view('ppic.work_order.create', compact('salesOrders', 'proccessProductions', 'workCenters', 'units'));
    }

    public function bulkPosted(Request $request)
    {
        $wo_numbers = $request->input('wo_numbers');

        DB::beginTransaction();
        try {
            // Lakukan logika untuk melakukan bulk update status di sini

            // Contoh: Update status menjadi 'Posted'
            workOrder::whereIn('wo_number', $wo_numbers)
                ->update(['status' => 'Posted', 'updated_at' => now()]);

            DB::commit();
            $this->saveLogs('Changed Work Order ' . implode(', ', $wo_numbers) . ' to posted');

            return response()->json(['message' => 'Change to posted successful', 'type' => 'success'], 200);
        } catch (\Exception $e) {
            // Tangani kesalahan jika diperlukan
            return response()->json(['error' => 'Error updating to posted', 'type' => 'error'], 500);
        }
    }

    public function bulkUnPosted(Request $request)
    {
        $wo_numbers = $request->input('wo_numbers');

        DB::beginTransaction();
        try {
            // Lakukan logika untuk melakukan bulk update status di sini

            // Contoh: Update status menjadi 'Posted'
            workOrder::whereIn('wo_number', $wo_numbers)
                ->update(['status' => 'Un Posted', 'updated_at' => now()]);

            $this->saveLogs('Changed Work Order ' . implode(', ', $wo_numbers) . ' to unposted');

            DB::commit();
            return response()->json(['message' => 'Change to unposted successful', 'type' => 'success'], 200);
        } catch (\Exception $e) {
            // Tangani kesalahan jika diperlukan
            return response()->json(['error' => 'Error updating to unposted', 'type' => 'error'], 500);
        }
    }

    public function bulkDeleted(Request $request)
    {
        $wo_numbers = $request->input('wo_numbers');

        try {
            // Hapus data POCustomer sesuai wo_number
            workOrder::whereIn('wo_number', $wo_numbers)->delete();

            $this->saveLogs('Deleted Work Order ' . implode(', ', $wo_numbers));

            return response()->json(['message' => 'Successfully deleted data', 'type' => 'success'], 200);
        } catch (\Exception $e) {
            // Tangani kesalahan jika diperlukan
            return response()->json(['error' => 'Failed to delete data', 'type' => 'error'], 500);
        }
    }

    public function woDetails(workOrder $workOrder, $encryptedWONumber)
    {
        $wo_number = Crypt::decrypt($encryptedWONumber);
        return view('ppic.work_order.wo_details', compact('wo_number'));
    }

    public function ajaxWODetails(Request $request)
    {
        // $wo_number = $request->wo_number;

        // Lakukan sesuatu dengan $wo_number, misalnya, gunakan untuk mengambil data tertentu
        // Contoh:
        // $data = DB::table('work_order_details as a')
        //     ->select('a.*', 'c.id as id_raw_materials', 'c.rm_code', 'c.description', 'd.unit_code', 'd.unit')
        //     ->join('work_orders as b', 'b.id', '=', 'a.id_work_orders')
        //     ->join('master_raw_materials as c', 'c.id', '=', 'a.id_master_products')
        //     ->join('master_units as d', 'd.id', '=', 'a.id_master_units')
        //     ->where('b.wo_number', $wo_number)
        //     ->get();

        if (request()->ajax()) {
            $orderColumn = $request->input('order')[0]['column'];
            $orderDirection = $request->input('order')[0]['dir'];
            $columns = ['id', 'rm_code', 'qty', 'unit_code', ''];

            // Query dasar
            $query = DB::table('work_order_details as a')
                ->select('a.*', 'b.wo_number', 'c.id as id_raw_materials', 'c.rm_code', 'c.description', 'd.unit_code', 'd.unit')
                ->join('work_orders as b', 'b.id', '=', 'a.id_work_orders')
                ->join('master_raw_materials as c', 'c.id', '=', 'a.id_master_products')
                ->join('master_units as d', 'd.id', '=', 'a.id_master_units');

            // Mengatur kondisi berdasarkan nomor WO
            if ($request->filled('wo_number')) {
                $wo_number = $request->input('wo_number');
                $query->where('b.wo_number', $wo_number);
            }

            // Handle pencarian
            if ($request->filled('search')) {
                $searchValue = $request->input('search');
                $query->where(function ($query) use ($searchValue) {
                    $query->where('c.rm_code', 'like', '%' . $searchValue . '%')
                        ->orWhere('c.description', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.qty', 'like', '%' . $searchValue . '%')
                        ->orWhere('d.unit_code', 'like', '%' . $searchValue . '%');
                });
            }

            // Mengatur urutan
            $query->orderBy($columns[$orderColumn], $orderDirection);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('products', function ($data) {
                    return $data->rm_code . ' - ' . $data->description;
                })
                ->addColumn('action', function ($data) {
                    return view('ppic.work_order.action_wo_details', compact('data'));
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function createWODetails(workOrder $workOrder, $encryptedWONumber)
    {
        $wo_number = Crypt::decrypt($encryptedWONumber);
        $work_order = workOrder::where('wo_number', $wo_number)->first();
        $rawMaterials = $this->getAllRawMaterials();
        $units = $this->getAllUnit();

        return view('ppic.work_order.create_wo_details', compact('work_order', 'rawMaterials', 'units'));
    }

    public function getRawMaterial()
    {
        $id_raw_material = request()->get('id_raw_material');

        $dataRawMaterial = DB::table('master_raw_materials as a')
            ->select('a.*')
            // ->join('master_units as b', 'a.id_master_units', '=', 'b.id')
            ->where('a.id', $id_raw_material)
            ->first();

        return response()->json(['dataRawMaterial' => $dataRawMaterial]);
    }

    public function storeWODetail(Request $request)
    {
        // echo json_encode($request->all());
        // exit;
        // dd($request->all());

        DB::beginTransaction();
        try {
            // Simpan data ke dalam tabel work_order_details
            $work_order_details =  DB::table('work_order_details')->insert([
                'id_work_orders' =>  $request->id_work_orders,
                'type_product' =>  'RM',
                'id_master_products' =>  $request->id_master_raw_materials,
                'qty' =>  $request->qty,
                'id_master_units' =>  $request->id_master_units,
                // Sesuaikan dengan kolom-kolom lain yang ada pada tabel order_confirmation
            ]);

            $this->saveLogs('Adding New Work Order Details : ' . $request->wo_number);

            DB::commit();

            if ($request->has('save_add_more')) {
                return redirect()->back()->with(['success' => 'Success Create New Work Order Details']);
            } else {
                return redirect()->route('ppic.workOrder.woDetails', encrypt($request->wo_number))->with(['success' => 'Success Create New Work Order Details']);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => $e . 'Failed to Create New Work Order!']);
        }
    }

    public function editWODetail(Request $request, workOrder $workOrder, $encryptedWONumber, $encryptedIDRawMaterials)
    {
        $id_work_orders = Crypt::decrypt($encryptedWONumber);
        $id_raw_material = Crypt::decrypt($encryptedIDRawMaterials);
        $work_order_detail = DB::table('work_order_details as a')
            ->select('a.*', 'b.wo_number')
            ->join('work_orders as b', 'a.id_work_orders', '=', 'b.id')
            ->where('a.id_work_orders', $id_work_orders)
            ->where('a.id_master_products', $id_raw_material)
            ->first();
        $rawMaterials = $this->getAllRawMaterials();
        $units = $this->getAllUnit();
        // echo json_encode($work_order_detail);
        // exit;

        return view('ppic.work_order.edit_wo_details', compact('work_order_detail', 'rawMaterials', 'units'));
    }

    public function updateWODetail(Request $request, workOrder $workOrder)
    {
        // echo json_encode($request->all());
        // exit;

        DB::beginTransaction();
        try {
            // Simpan data ke dalam tabel work_order_details
            DB::table('work_order_details')
                ->where('id_work_orders', $request->id_work_orders)
                ->where('id_master_products', $request->id_master_products_old)
                ->update([
                    'id_master_products' => $request->id_master_raw_materials,
                    'qty' => $request->qty,
                    'id_master_units' => $request->id_master_units,
                    // Sesuaikan dengan kolom-kolom lain yang ada pada tabel work_order_details
                ]);

            $this->saveLogs('Edit Work Order Detail : ' . $request->wo_number);

            DB::commit();

            $routeName = 'ppic.workOrder.woDetails';
            $routeParams = encrypt($request->wo_number);
            $message = 'Success Update Work Order Detail ' . $request->wo_number;

            return redirect()->route($routeName, $routeParams)->with(['success' => $message]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fail' => $e . 'Failed to Update Work Order Detail! ' . $request->so_number]);
        }
    }

    public function showWODetail(Request $request, workOrder $workOrder, $encryptedWONumber, $encryptedIDRawMaterials)
    {
        $id_work_orders = Crypt::decrypt($encryptedWONumber);
        $id_raw_material = Crypt::decrypt($encryptedIDRawMaterials);
        $work_order_detail = DB::table('work_order_details as a')
            ->select('a.*', 'b.wo_number', 'c.rm_code', 'c.description', 'd.unit_code', 'd.unit')
            ->join('work_orders as b', 'a.id_work_orders', '=', 'b.id')
            ->join('master_raw_materials as c', 'a.id_master_products', '=', 'c.id')
            ->join('master_units as d', 'a.id_master_units', '=', 'd.id')
            ->where('a.id_work_orders', $id_work_orders)
            ->where('a.id_master_products', $id_raw_material)
            ->first();

        // echo json_encode($work_order_detail);
        // exit;

        return view('ppic.work_order.show_wo_details', compact('work_order_detail'));
    }

    public function deleteWODetail(Request $request)
    {
        $id_work_orders = $request->input('id_work_orders');
        $id_master_products = $request->input('id_master_products');

        try {
            DB::table('work_order_details')
                ->where('id_work_orders', $id_work_orders)
                ->where('id_master_products', $id_master_products)
                ->delete();

            $this->saveLogs('Deleted Work Order Detail');

            return response()->json(['message' => 'Successfully deleted data', 'type' => 'success'], 200);
        } catch (\Exception $e) {
            // Tangani kesalahan jika diperlukan
            return response()->json(['error' => 'Failed to delete data', 'type' => 'error'], 500);
        }
    }

    public function getDataSalesOrder()
    {
        $sales_orders = salesOrder::select('*')
            ->orderBy('id', 'desc')
            ->get();

        return $sales_orders;
    }

    public function print(Request $request)
    {
        $id_sales_orders = $request->id_sales_orders;
        $salesOrder = salesOrder::with('masterUnit', 'masterSalesman', 'masterCustomer')
            ->where('id', $id_sales_orders)
            ->first();

        $typeProduct = $salesOrder->type_product;
        $idProduct = $salesOrder->id_master_products;
        if ($typeProduct == 'WIP') {
            $product = DB::table('master_wips as a')
                ->select('a.id', 'a.wip_code as product_code', 'a.description', 'a.thickness', 'a.width', 'a.length as height', 'a.perforasi', 'a.id_master_units')
                ->where('a.id', $idProduct)
                ->first();
        } else if ($typeProduct == 'FG') {
            $product = DB::table('master_product_fgs as a')
                ->select('a.id', 'a.product_code', 'a.description', 'a.thickness', 'a.width', 'a.height', 'a.perforasi', 'a.id_master_units', 'a.sales_price as price')
                ->where('a.id', $idProduct)
                ->first();
        }

        $work_order_details = DB::table('work_orders as a')
            ->select('a.*', 'c.pc_needed', 'c.dsc', 'd.process_code', 'e.work_center_code', 'f.unit_code', 'g.unit_code as unit_needed')
            ->join(
                \DB::raw(
                    '(SELECT id, product_code, description, id_master_units, \'FG\' as type_product FROM master_product_fgs WHERE status = \'Active\' UNION ALL SELECT id, wip_code as product_code, description, id_master_units, \'WIP\' as type_product FROM master_wips WHERE status = \'Active\') b'
                ),
                function ($join) {
                    $join->on('a.id_master_products', '=', 'b.id');
                    $join->on('a.type_product', '=', 'b.type_product');
                }
            )
            ->leftJoin(
                \DB::raw(
                    '(SELECT id, product_code as pc_needed, description as dsc, id_master_units, \'FG\' as type_product FROM master_product_fgs WHERE status = \'Active\' UNION ALL SELECT id, wip_code as pc_needed, description as dsc, id_master_units, \'WIP\' as type_product FROM master_wips WHERE status = \'Active\') c'
                ),
                function ($join) {
                    $join->on('a.id_master_products_material', '=', 'c.id');
                    $join->on('a.type_product_material', '=', 'c.type_product');
                }
            )
            ->join('master_process_productions as d', 'a.id_master_process_productions', '=', 'd.id')
            ->leftJoin('master_work_centers as e', 'a.id_master_work_centers', '=', 'e.id')
            ->join('master_units as f', 'a.id_master_units', '=', 'f.id')
            ->leftJoin('master_units as g', 'a.id_master_units_needed', '=', 'g.id')
            ->where('a.id_sales_orders', $id_sales_orders)
            ->orderBy('a.id', 'asc')
            ->get();
        // echo json_encode($work_order_details);
        // exit;
        return view('ppic.work_order.print', compact('salesOrder', 'product', 'work_order_details'));
    }
}
