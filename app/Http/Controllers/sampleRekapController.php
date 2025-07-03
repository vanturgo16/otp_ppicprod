<?php

namespace App\Http\Controllers;


use Browser;
use DataTables;
use Carbon\Carbon;
use App\Models\MstUnits;
use App\Models\MstCustomers;
use App\Models\MstSalesmans;
use Illuminate\Http\Request;
use App\Models\ppic\workOrder;
use App\Traits\AuditLogsTrait;
use App\Models\MstTermPayments;
use App\Exports\ExportSalesOrder;
use Illuminate\Support\Facades\DB;
use App\Models\MstCustomersAddress;
use App\Http\Controllers\Controller;
use App\Models\Marketing\salesOrder;
use App\Models\ppic\workOrderDetail;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Marketing\InputPOCust;
use Illuminate\Support\Facades\Crypt;
use App\Models\Marketing\salesOrderDetail;
use App\Models\Marketing\orderConfirmation;
use Illuminate\Database\Eloquent\JsonEncodingException;

class sampleRekapController extends Controller
{
    public function index(Request $request)
    {
        // if (request()->ajax()) {
        //     $orderColumn = $request->input('order')[0]['column'];
        //     $orderDirection = $request->input('order')[0]['dir'];
        //     $columns = ['', 'so_number', 'description', 'outstanding_delivery_qty', '', 'unit_code', 'so_category'];

        //     // Query dasar
        //     $query = DB::table('sales_orders as a')
        //         ->leftJoin('master_customers as b', 'a.id_master_customers', '=', 'b.id')
        //         // ->leftJoin('master_salesmen as c', 'a.id_master_salesmen', '=', 'c.id')
        //         // ->join('sales_order_details as d', 'a.so_number', '=', 'd.id_sales_orders')
        //         ->join(
        //             \DB::raw(
        //                 '(SELECT id, product_code, description, id_master_units, \'FG\' as type_product, perforasi, weight FROM master_product_fgs WHERE status = \'Active\' UNION ALL SELECT id, wip_code as product_code, description, id_master_units, \'WIP\' as type_product, perforasi, weight FROM master_wips WHERE status = \'Active\' UNION ALL SELECT id, rm_code as product_code, description, id_master_units, \'RM\' as type_product, \'NULL\'as perforasi, weight FROM master_raw_materials WHERE status = \'Active\' UNION ALL SELECT id, code as product_code, description, id_master_units, \'AUX\' as type_product, \'NULL\' as perforasi, \'\' as weight FROM master_tool_auxiliaries) e'
        //             ),
        //             function ($join) {
        //                 // $join->on('d.id_master_products', '=', 'e.id');
        //                 // $join->on('d.type_product', '=', 'e.type_product');
        //                 $join->on('a.id_master_products', '=', 'e.id');
        //                 $join->on('a.type_product', '=', 'e.type_product');
        //             }
        //         )
        //         // ->join('master_units as f', 'd.id_master_units', '=', 'f.id')
        //         ->join('master_units as f', 'a.id_master_units', '=', 'f.id')
        //         // ->select('a.id', 'a.id_order_confirmations', 'a.so_number', 'a.date', 'a.so_type', 'b.name as customer', 'c.name as salesman', 'a.reference_number', 'a.due_date', 'a.status', 'd.qty', 'd.outstanding_delivery_qty', 'e.product_code', 'e.description', 'f.unit_code')
        //         ->select('a.id', 'a.so_number', 'a.so_category', 'b.name as customer', 'a.outstanding_delivery_qty', 'e.product_code', 'e.description', 'f.unit_code', 'e.perforasi', 'e.weight', 'a.status')
        //         ->orderBy($columns[$orderColumn], $orderDirection);

        //     if ($request->has('type')) {
        //         if ($request->input('type') <> '') {
        //             $query->where('a.so_type', '=', $request->input('type'));
        //         } else {
        //             $query->where('a.so_type', '<>', $request->input('type'));
        //         }
        //     }

        //     // Handle pencarian
        //     if ($request->has('search') && $request->input('search')) {
        //         $searchValue = $request->input('search');
        //         $query->where(function ($query) use ($searchValue) {
        //             $query->where('a.so_number', 'like', '%' . $searchValue . '%')
        //                 ->orWhere('e.description', 'like', '%' . $searchValue . '%')
        //                 ->orWhere('b.name', 'like', '%' . $searchValue . '%')
        //                 ->orWhere('a.outstanding_delivery_qty', 'like', '%' . $searchValue . '%')
        //                 ->orWhere('f.unit_code', 'like', '%' . $searchValue . '%')
        //                 ->orWhere('a.so_category', 'like', '%' . $searchValue . '%');
        //         });
        //     }
        //     $query->where('a.status', '<>', 'Closed');

        //     return DataTables::of($query)
        //         ->addColumn('description', function ($data) {
        //             $perforasi = $data->perforasi === 'NULL' || $data->perforasi === null ? '-' : $data->perforasi;
        //             return $data->product_code . ' - ' . $data->description . ' | Perforasi: ' . $perforasi;
        //         })
        //         ->addColumn('kg', function ($data) {
        //             $kg = $data->outstanding_delivery_qty * $data->weight;
        //             return $kg;
        //         })
        //         ->addColumn('product', function ($data) {
        //             $product = $data->so_category == 'S/W' ? 'SINGLE' : ($data->so_category == 'CF' ? 'FOLDING' : ($data->so_category == 'Bag' ? 'BAG MAKING' : ''));
        //             return $product;
        //         })
        //         ->rawColumns(['description', 'kg', 'product'])
        //         ->make(true);
        // }

        // Query dasar
        $sales_order = DB::table('sales_orders as a')
            ->leftJoin('master_customers as b', 'a.id_master_customers', '=', 'b.id')
            // ->leftJoin('master_salesmen as c', 'a.id_master_salesmen', '=', 'c.id')
            // ->join('sales_order_details as d', 'a.so_number', '=', 'd.id_sales_orders')
            ->join(
                \DB::raw(
                    '(SELECT id, product_code, description, id_master_units, \'FG\' as type_product, perforasi, weight FROM master_product_fgs WHERE status = \'Active\' UNION ALL SELECT id, wip_code as product_code, description, id_master_units, \'WIP\' as type_product, perforasi, weight FROM master_wips WHERE status = \'Active\' UNION ALL SELECT id, rm_code as product_code, description, id_master_units, \'RM\' as type_product, \'NULL\'as perforasi, weight FROM master_raw_materials WHERE status = \'Active\' UNION ALL SELECT id, code as product_code, description, id_master_units, \'AUX\' as type_product, \'NULL\' as perforasi, \'\' as weight FROM master_tool_auxiliaries) e'
                ),
                function ($join) {
                    // $join->on('d.id_master_products', '=', 'e.id');
                    // $join->on('d.type_product', '=', 'e.type_product');
                    $join->on('a.id_master_products', '=', 'e.id');
                    $join->on('a.type_product', '=', 'e.type_product');
                }
            )
            // ->join('master_units as f', 'd.id_master_units', '=', 'f.id')
            ->join('master_units as f', 'a.id_master_units', '=', 'f.id')
            // ->select('a.id', 'a.id_order_confirmations', 'a.so_number', 'a.date', 'a.so_type', 'b.name as customer', 'c.name as salesman', 'a.reference_number', 'a.due_date', 'a.status', 'd.qty', 'd.outstanding_delivery_qty', 'e.product_code', 'e.description', 'f.unit_code')
            ->select('a.id', 'a.so_number', 'a.so_category', 'b.name as customer', 'a.outstanding_delivery_qty', 'e.product_code', 'e.description', 'f.unit_code', 'e.perforasi', 'e.weight', 'a.status')
            ->where('a.status', '<>', 'Closed')
            ->orderBy('b.name', 'asc')->get();

        $categories = [
            'S/W' => 'total_single',
            'CF' => 'total_folding',
            'Bag' => 'total_bag_making',
        ];

        $results = [];

        foreach ($categories as $category => $label) {
            $total = DB::table('sales_orders as a')
                ->join(
                    \DB::raw(
                        '(SELECT id, product_code, description, id_master_units, \'FG\' as type_product, perforasi, weight FROM master_product_fgs WHERE status = \'Active\' UNION ALL SELECT id, wip_code as product_code, description, id_master_units, \'WIP\' as type_product, perforasi, weight FROM master_wips WHERE status = \'Active\' UNION ALL SELECT id, rm_code as product_code, description, id_master_units, \'RM\' as type_product, \'NULL\' as perforasi, weight FROM master_raw_materials WHERE status = \'Active\' UNION ALL SELECT id, code as product_code, description, id_master_units, \'AUX\' as type_product, \'NULL\' as perforasi, \'\' as weight FROM master_tool_auxiliaries) b'
                    ),
                    function ($join) {
                        $join->on('a.id_master_products', '=', 'b.id');
                        $join->on('a.type_product', '=', 'b.type_product');
                    }
                )
                ->where('a.status', '<>', 'Closed')
                ->where('a.so_category', '=', $category)
                ->selectRaw('SUM(a.outstanding_delivery_qty * CAST(NULLIF(b.weight, \'\') AS DECIMAL(10,6))) as total_weight')
                ->value('total_weight');

            $raw = (string) $total;
            if (strpos($raw, '.') !== false) {
                [$int, $decimal] = explode('.', $raw);
                $int_formatted = number_format($int, 0, ',', '.');
                $formatted_full = $int_formatted . ',' . rtrim($decimal, '0');
            } else {
                $formatted_full = number_format($raw, 0, ',', '.');
            }

            $results[$label] = [
                'raw' => (float) $total,
                'formatted' => $formatted_full
            ];
        }

        // echo json_encode($results['total_single']);
        // exit;

        return view('rekapitulasiOrder.index', compact('sales_order', 'results'));
    }

    public function index_new(Request $request)
    {
        if (request()->ajax()) {
            $orderColumn = $request->input('order')[0]['column'];
            $orderDirection = $request->input('order')[0]['dir'];
            $columns = ['', 'so_number', 'description', 'outstanding_delivery_qty', '', 'unit_code', 'so_category'];

            // Query dasar
            $query = DB::table('sales_orders as a')
                ->leftJoin('master_customers as b', 'a.id_master_customers', '=', 'b.id')
                ->join(
                    \DB::raw(
                        '(SELECT id, product_code, description, id_master_units, \'FG\' as type_product, perforasi, weight FROM master_product_fgs WHERE status = \'Active\' UNION ALL SELECT id, wip_code as product_code, description, id_master_units, \'WIP\' as type_product, perforasi, weight FROM master_wips WHERE status = \'Active\' UNION ALL SELECT id, rm_code as product_code, description, id_master_units, \'RM\' as type_product, \'NULL\'as perforasi, weight FROM master_raw_materials WHERE status = \'Active\' UNION ALL SELECT id, code as product_code, description, id_master_units, \'AUX\' as type_product, \'NULL\' as perforasi, \'\' as weight FROM master_tool_auxiliaries) e'
                    ),
                    function ($join) {
                        $join->on('a.id_master_products', '=', 'e.id');
                        $join->on('a.type_product', '=', 'e.type_product');
                    }
                )
                ->join('master_units as f', 'a.id_master_units', '=', 'f.id')
                ->select('a.id', 'a.so_number', 'a.so_category', 'b.name as customer', 'a.outstanding_delivery_qty', 'e.product_code', 'e.description', 'f.unit_code', 'e.perforasi', 'e.weight', 'a.status')
                ->orderBy('b.name')  // Sort by customer name
                ->orderBy($columns[$orderColumn], $orderDirection); // Other ordering

            if ($request->has('type')) {
                if ($request->input('type') <> '') {
                    $query->where('a.so_type', '=', $request->input('type'));
                } else {
                    $query->where('a.so_type', '<>', $request->input('type'));
                }
            }

            // Handle search
            if ($request->has('search') && $request->input('search')) {
                $searchValue = $request->input('search');
                $query->where(function ($query) use ($searchValue) {
                    $query->where('a.so_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('e.description', 'like', '%' . $searchValue . '%')
                        ->orWhere('b.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.outstanding_delivery_qty', 'like', '%' . $searchValue . '%')
                        ->orWhere('f.unit_code', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.so_category', 'like', '%' . $searchValue . '%');
                });
            }
            $query->where('a.status', '<>', 'Closed');

            return DataTables::of($query)
                ->addColumn('description', function ($data) {
                    $perforasi = $data->perforasi === 'NULL' || $data->perforasi === null ? '-' : $data->perforasi;
                    return $data->product_code . ' - ' . $data->description . ' | Perforasi: ' . $perforasi;
                })
                ->addColumn('kg', function ($data) {
                    $kg = $data->outstanding_delivery_qty * $data->weight;
                    return $kg;
                })
                ->addColumn('product', function ($data) {
                    $product = $data->so_category == 'S/W' ? 'SINGLE' : ($data->so_category == 'CF' ? 'FOLDING' : ($data->so_category == 'Bag' ? 'BAG MAKING' : ''));
                    return $product;
                })
                ->rawColumns(['description', 'kg', 'product'])
                ->make(true);
        }
        return view('rekapitulasiOrder.index');
    }
}
