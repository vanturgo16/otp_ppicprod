<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MstCustomers;
use App\Models\Warehouse\PackingList;
use DataTables;


class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $orderColumn = $request->input('order')[0]['column'];
            $orderDirection = $request->input('order')[0]['dir'];
            $columns = ['id', 'packing_number', 'date', 'customer', 'status'];

            $query = DB::table('packing_lists as pl')
                ->leftJoin('master_customers as mc', 'pl.id_master_customers', '=', 'mc.id')
                ->select(
                    'pl.id',
                    'pl.packing_number',
                    'pl.date',
                    'mc.name as customer',
                    'pl.status',
                    DB::raw('"" as action') // Menambahkan kolom action sebagai kolom kosong
                )
                ->orderBy($columns[$orderColumn], $orderDirection);

            // Handle search
            if ($request->has('search') && $request->input('search')) {
                $searchValue = $request->input('search');
                $query->where(function ($query) use ($searchValue) {
                    $query->where('pl.packing_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('mc.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('pl.status', 'like', '%' . $searchValue . '%');
                });
            }

            // Handle date range filtering
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');
                if ($startDate && $endDate) {
                    $query->whereBetween('pl.date', [$startDate, $endDate]);
                }
            }

            return DataTables::of($query)
                ->addColumn('action', function ($data) {
                    // Generate action buttons here
                    $buttons = view('warehouse.action_buttons', compact('data'))->render();
                    return $buttons;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('warehouse.index');
    }


    public function getCustomers(Request $request)
    {
        $search = $request->search;

        if ($search == '') {
            $customers = MstCustomers::orderby('name', 'asc')
                ->select('id', 'name')
                ->limit(10)
                ->get();
        } else {
            $customers = MstCustomers::orderby('name', 'asc')
                ->select('id', 'name')
                ->where('name', 'like', '%' . $search . '%')
                ->limit(10)
                ->get();
        }

        $response = array();
        foreach ($customers as $customer) {
            $response[] = array(
                "id" => $customer->id,
                "text" => $customer->name
            );
        }

        return response()->json($response);
    }
    public function create()
    {
        $nextPackingNumber = $this->generatePackingNumber();
        return view('warehouse.create_packing_list', compact('nextPackingNumber'));
    }

    public function store(Request $request)
    {
        try {
            // Validasi data
            $request->validate([
                'packing_number' => 'required|unique:packing_lists,packing_number',
                'date' => 'required|date',
                'customer' => 'required|exists:master_customers,id',
                'all_barcodes' => 'required|in:Y,N',
            ]);

            // Simpan data ke database
            $packingList = new PackingList();
            $packingList->packing_number = $request->packing_number;
            $packingList->date = $request->date;
            $packingList->id_master_customers = $request->customer;
            $packingList->status = 'Request';
            $packingList->all_barcodes = $request->all_barcodes;
            $packingList->save();

            // Kembalikan respons sukses
            return response()->json(['success' => true, 'packing_list_id' => $packingList->id]);
        } catch (\Exception $e) {
            // Kembalikan respons gagal
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }


    private function generatePackingNumber()
    {
        $yearMonth = date('ym');
        $lastPackingList = PackingList::where('packing_number', 'like', $yearMonth . '%')->lockForUpdate()->orderBy('packing_number', 'desc')->first();
        $nextNumber = $lastPackingList ? intval(substr($lastPackingList->packing_number, 4)) + 1 : 1;
        return $yearMonth . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    // Method untuk memeriksa barcode
    public function checkBarcode(Request $request)
    {
        $barcode = $request->input('barcode');
        $customerId = $request->input('customer_id');
        $changeSo = $request->input('change_so');
        $packingListId = $request->input('packing_list_id');
        $pcs = $request->input('pcs', 0);

        // Cek apakah barcode sudah ada di tabel packing_list_details
        $duplicate = DB::table('packing_list_details')
            ->where('barcode', $barcode)
            ->exists();

        if ($duplicate) {
            return response()->json(['exists' => false, 'duplicate' => true]);
        }

        $exists = false;
        $insertedId = null;
        $productName = null;
        $isBag = false;

        if ($changeSo) {
            // Validasi berdasarkan sales order
            $barcodeRecord = DB::table('barcodes')
                ->join('barcode_detail', 'barcodes.id', '=', 'barcode_detail.id_barcode')
                ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
                ->join('master_product_fgs', 'sales_orders.id_master_products', '=', 'master_product_fgs.id') // Join untuk mendapatkan nama produk
                ->where('barcode_detail.barcode_number', $barcode)
                ->where('sales_orders.so_number', $changeSo)
                ->select('barcode_detail.*', 'master_product_fgs.description', 'master_product_fgs.id as product_id', 'sales_orders.id as sales_order_id', 'master_product_fgs.stock')
                ->first();

            if ($barcodeRecord && strpos($barcodeRecord->status, 'In Stock') !== false) {

                $exists = true;
                $productName = $barcodeRecord->description; // Ambil nama produk
                $isBag = (substr($barcode, -1) === 'B');

                // Periksa apakah stok akan minus setelah pengurangan
                $newStock = $barcodeRecord->stock - ($isBag ? $pcs : 1);
                if ($newStock < 0) {
                    return response()->json(['exists' => false, 'status' => false, 'message' => 'Stok tidak mencukupi']);
                }

                $insertedId = DB::table('packing_list_details')->insertGetId([
                    'barcode' => $barcode,
                    'id_sales_orders' => $barcodeRecord->sales_order_id,
                    'id_packing_lists' => $packingListId,
                    'pcs' => $isBag ? $pcs : 1, // Simpan jumlah pcs jika itu bag, 1 jika bukan
                    'sts_start' => $barcodeRecord->status,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                if ($isBag) {
                    DB::table('master_product_fgs')
                        ->where('id', $barcodeRecord->product_id)
                        ->decrement('stock', $pcs);
                    DB::table('sales_orders')
                        ->where('id', $barcodeRecord->sales_order_id)
                        ->decrement('outstanding_delivery_qty', $pcs);
                } else {
                    DB::table('master_product_fgs')
                        ->where('id', $barcodeRecord->product_id)
                        ->decrement('stock', 1);
                    DB::table('sales_orders')
                        ->where('id', $barcodeRecord->sales_order_id)
                        ->decrement('outstanding_delivery_qty', 1);
                }

                // Update status di tabel barcode_detail
                DB::table('barcode_detail')
                    ->where('barcode_number', $barcode)
                    ->update(['status' => 'Packing List']);
            } else {
                return response()->json(['exists' => false, 'status' => false, 'message' => 'Barcode tidak sesuai dengan SO yang diberikan']);
            }
        } else {
            // Validasi berdasarkan customer
            $barcodeRecord = DB::table('barcodes')
                ->join('barcode_detail', 'barcodes.id', '=', 'barcode_detail.id_barcode')
                ->join('master_product_fgs', 'barcodes.id_master_products', '=', 'master_product_fgs.id') // Join untuk mendapatkan nama produk
                ->where('barcode_detail.barcode_number', $barcode)
                ->where('barcodes.id_master_customers', $customerId)
                ->select('barcode_detail.*', 'master_product_fgs.description', 'master_product_fgs.id as product_id', 'barcodes.id_sales_orders as sales_order_id', 'master_product_fgs.stock')
                ->first();

            if ($barcodeRecord && strpos($barcodeRecord->status, 'In Stock') !== false) {
                $exists = true;
                $productName = $barcodeRecord->description; // Ambil nama produk
                $isBag = (substr($barcode, -1) === 'B');

                // Periksa apakah stok akan minus setelah pengurangan
                $newStock = $barcodeRecord->stock - ($isBag ? $pcs : 1);
                if ($newStock < 0) {
                    return response()->json(['exists' => false, 'status' => false, 'message' => 'Stok tidak mencukupi']);
                }

                $insertedId = DB::table('packing_list_details')->insertGetId([
                    'barcode' => $barcode,
                    'id_packing_lists' => $packingListId,
                    'pcs' => $isBag ? $pcs : 1, // Simpan jumlah pcs jika itu bag, 1 jika bukan
                    'sts_start' => $barcodeRecord->status,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                if ($isBag) {
                    DB::table('master_product_fgs')
                        ->where('id', $barcodeRecord->product_id)
                        ->decrement('stock', $pcs);
                    DB::table('sales_orders')
                        ->where('id', $barcodeRecord->sales_order_id)
                        ->decrement('outstanding_delivery_qty', $pcs);
                } else {
                    DB::table('master_product_fgs')
                        ->where('id', $barcodeRecord->product_id)
                        ->decrement('stock', 1);
                    DB::table('sales_orders')
                        ->where('id', $barcodeRecord->sales_order_id)
                        ->decrement('outstanding_delivery_qty', 1);
                }

                // Update status di tabel barcode_detail
                DB::table('barcode_detail')
                    ->where('barcode_number', $barcode)
                    ->update(['status' => 'Packing List']);
            } else {
                return response()->json(['exists' => false, 'status' => false, 'message' => 'Barcode tidak sesuai dengan customer yang diberikan']);
            }
        }

        return response()->json(['exists' => $exists, 'duplicate' => false, 'id' => $insertedId, 'product_name' => $productName, 'is_bag' => $isBag, 'sales_order_id' => $barcodeRecord->sales_order_id]);
    }


    // // Method untuk menyesuaikan stok
    // public function adjustStock(Request $request)
    // {
    //     $barcode = $request->input('barcode');
    //     $pcs = $request->input('pcs');

    //     // Dapatkan data produk dan sales order terkait
    //     $barcodeRecord = DB::table('barcodes')
    //         ->join('barcode_detail', 'barcodes.id', '=', 'barcode_detail.id_barcode')
    //         ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
    //         ->join('master_product_fgs', 'barcodes.id_master_products', '=', 'master_product_fgs.id')
    //         ->where('barcode_detail.barcode_number', $barcode)
    //         ->select('master_product_fgs.id as product_id', 'sales_orders.id as sales_order_id', 'master_product_fgs.stock')
    //         ->first();

    //     if ($barcodeRecord) {
    //         try {
    //             // Periksa apakah stok akan minus setelah pengurangan
    //             $newStock = $barcodeRecord->stock + $pcs;
    //             if ($newStock < 0) {
    //                 return response()->json(['success' => false, 'error' => 'Stok tidak mencukupi']);
    //             }

    //             // Tambahkan stok pada tabel master_product_fgs
    //             DB::table('master_product_fgs')
    //                 ->where('id', $barcodeRecord->product_id)
    //                 ->increment('stock', $pcs);

    //             // Tambahkan outstanding_delivery_qty pada tabel sales_orders
    //             DB::table('sales_orders')
    //                 ->where('id', $barcodeRecord->sales_order_id)
    //                 ->increment('outstanding_delivery_qty', $pcs);

    //             return response()->json(['success' => true]);
    //         } catch (\Exception $e) {
    //             return response()->json(['success' => false, 'error' => $e->getMessage()]);
    //         }
    //     } else {
    //         return response()->json(['success' => false, 'error' => 'Product not found']);
    //     }
    // }

    public function removeBarcode(Request $request)
    {
        $id = $request->input('id');
        $pcs = $request->input('pcs');

        // Ambil informasi barcode dari tabel packing_list_details
        $barcodeDetail = DB::table('packing_list_details')->where('id', $id)->first();

        if ($barcodeDetail) {
            // Ambil informasi produk terkait dari tabel barcodes, master_product_fgs dan sales_orders
            $barcodeRecord = DB::table('barcodes')
                ->join('barcode_detail', 'barcodes.id', '=', 'barcode_detail.id_barcode')
                ->join('master_product_fgs', 'barcodes.id_master_products', '=', 'master_product_fgs.id')
                ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
                ->where('barcode_detail.barcode_number', $barcodeDetail->barcode)
                ->select('master_product_fgs.id as product_id', 'barcodes.id_sales_orders as sales_order_id')
                ->first();

            if ($barcodeRecord) {
                $barcode = $barcodeDetail->barcode;

                // Kembalikan stok ke jumlah sebelumnya
                if (substr($barcode, -1) === 'B') {
                    // Jika barcode adalah bag, kembalikan stok berdasarkan pcs
                    DB::table('master_product_fgs')
                        ->where('id', $barcodeRecord->product_id)
                        ->increment('stock', $pcs);
                    DB::table('sales_orders')
                        ->where('id', $barcodeRecord->sales_order_id)
                        ->increment('outstanding_delivery_qty', $pcs);
                } else {
                    // Jika bukan bag, tambahkan stok sebesar 1 (atau sesuaikan jika diperlukan jumlah yang berbeda)
                    DB::table('master_product_fgs')
                        ->where('id', $barcodeRecord->product_id)
                        ->increment('stock', 1);
                    DB::table('sales_orders')
                        ->where('id', $barcodeRecord->sales_order_id)
                        ->increment('outstanding_delivery_qty', 1);
                }

                // Update status barcode di tabel barcode_detail
                DB::table('barcode_detail')
                    ->where('barcode_number', $barcode)
                    ->update(['status' => $barcodeDetail->sts_start]);

                // Hapus entri barcode dari tabel packing_list_details
                DB::table('packing_list_details')->where('id', $id)->delete();

                return response()->json(['success' => true]);
            }
        }

        return response()->json(['success' => false, 'message' => 'Barcode not found']);
    }


    public function edit($id)
    {
        $packingList = DB::table('packing_lists')->where('id', $id)->first();

        $details = DB::table('packing_list_details')
            ->join('barcode_detail', 'packing_list_details.barcode', '=', 'barcode_detail.barcode_number')
            ->join('barcodes', 'barcodes.id', '=', 'barcode_detail.id_barcode')
            ->join('master_product_fgs', 'barcodes.id_master_products', '=', 'master_product_fgs.id')
            ->where('packing_list_details.id_packing_lists', $id)
            ->select(
                'packing_list_details.*',
                'master_product_fgs.description as product_description'
            )
            ->get();
        $customer = DB::table('master_customers')->where('id', $packingList->id_master_customers)->first();

        return view('warehouse.edit', compact('packingList', 'details', 'customer'));
    }


    public function updateBarcodeDetail(Request $request)
    {
        $id = $request->input('id');
        $field = $request->input('field');
        $value = $request->input('value');

        try {
            $oldDetail = DB::table('packing_list_details')->where('id', $id)->first();

            if ($oldDetail) {
                if ($field == 'barcode') {
                    $barcode = $value;

                    $duplicate = DB::table('packing_list_details')
                        ->where('barcode', $barcode)
                        ->where('id', '!=', $id)
                        ->exists();

                    if ($duplicate) {
                        return response()->json(['exists' => false, 'duplicate' => true]);
                    }

                    $exists = false;
                    $productName = null;
                    $isBag = false;

                    $barcodeRecord = DB::table('barcodes')
                        ->join('barcode_detail', 'barcodes.id', '=', 'barcode_detail.id_barcode')
                        ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
                        ->join('master_product_fgs', 'barcodes.id_master_products', '=', 'master_product_fgs.id')
                        ->where('barcode_detail.barcode_number', $barcode)
                        // ->where('barcode_detail.status', 'In Stock')
                        ->where(DB::raw('strpos(barcode_detail.status, "In Stock")'), '!==', 'false')
                        ->select('barcode_detail.*', 'master_product_fgs.description', 'master_product_fgs.id as product_id', 'sales_orders.id as sales_order_id', 'master_product_fgs.stock', 'sales_orders.outstanding_delivery_qty')
                        ->first();

                    if ($barcodeRecord) {
                        $exists = true;
                        $productName = $barcodeRecord->description;
                        $isBag = (substr($barcode, -1) === 'B');
                    }

                    if ($exists) {
                        $oldBarcodeRecord = DB::table('barcodes')
                            ->join('barcode_detail', 'barcodes.id', '=', 'barcode_detail.id_barcode')
                            ->join('master_product_fgs', 'barcodes.id_master_products', '=', 'master_product_fgs.id')
                            ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
                            ->where('barcode_detail.barcode_number', $oldDetail->barcode)
                            ->select('master_product_fgs.id as product_id', 'barcodes.id_sales_orders as old_sales_order_id', 'master_product_fgs.stock', 'sales_orders.outstanding_delivery_qty')
                            ->first();

                        if ($oldBarcodeRecord && substr($oldDetail->barcode, -1) === 'B') {
                            DB::table('master_product_fgs')
                                ->where('id', $oldBarcodeRecord->product_id)
                                ->increment('stock', $oldDetail->pcs);
                            DB::table('sales_orders')
                                ->where('id', $oldBarcodeRecord->old_sales_order_id)
                                ->increment('outstanding_delivery_qty', $oldDetail->pcs);
                        } else {
                            DB::table('master_product_fgs')
                                ->where('id', $oldBarcodeRecord->product_id)
                                ->increment('stock', 1);
                            DB::table('sales_orders')
                                ->where('id', $oldBarcodeRecord->old_sales_order_id)
                                ->increment('outstanding_delivery_qty', 1);
                        }

                        $newStock = $barcodeRecord->stock - ($isBag ? $oldDetail->pcs : 1);
                        $newOutstandingQty = $barcodeRecord->outstanding_delivery_qty - ($isBag ? $oldDetail->pcs : 1);
                        if ($newStock < 0 || $newOutstandingQty < 0) {
                            return response()->json(['success' => false, 'error' => 'Stok atau Outstanding Delivery Qty tidak mencukupi']);
                        }

                        DB::table('barcode_detail')
                            ->where('barcode_number', $oldDetail->barcode)
                            ->update(['status' => $oldDetail->sts_start]);

                        DB::table('barcode_detail')
                            ->where('barcode_number', $barcode)
                            ->update(['status' => 'Packing List']);

                        DB::table('packing_list_details')->where('id', $id)->update([$field => $value]);

                        return response()->json(['success' => true, 'product_name' => $productName, 'is_bag' => $isBag]);
                    } else {
                        return response()->json(['success' => false, 'error' => 'Barcode not found or not valid for the given conditions.']);
                    }
                } else {
                    if ($field == 'pcs' && $oldDetail && substr($oldDetail->barcode, -1) === 'B') {
                        $barcodeRecord = DB::table('barcodes')
                            ->join('barcode_detail', 'barcodes.id', '=', 'barcode_detail.id_barcode')
                            ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
                            ->join('master_product_fgs', 'barcodes.id_master_products', '=', 'master_product_fgs.id')
                            ->where('barcode_detail.barcode_number', $oldDetail->barcode)
                            ->select('master_product_fgs.id as product_id', 'sales_orders.id as sales_order_id', 'master_product_fgs.stock', 'sales_orders.outstanding_delivery_qty')
                            ->first();

                        if ($barcodeRecord) {
                            $oldPcs = $oldDetail->pcs;
                            $difference = $value - $oldPcs;

                            $newStock = $barcodeRecord->stock - $difference;
                            $newOutstandingQty = $barcodeRecord->outstanding_delivery_qty - $difference;
                            if ($newStock < 0 || $newOutstandingQty < 0) {
                                return response()->json(['success' => false, 'error' => 'Stok atau Outstanding Delivery Qty tidak mencukupi']);
                            }

                            DB::table('master_product_fgs')
                                ->where('id', $barcodeRecord->product_id)
                                ->decrement('stock', $difference);

                            DB::table('sales_orders')
                                ->where('id', $barcodeRecord->sales_order_id)
                                ->decrement('outstanding_delivery_qty', $difference);
                        }
                    }

                    DB::table('packing_list_details')->where('id', $id)->update([$field => $value]);
                    return response()->json(['success' => true]);
                }
            } else {
                return response()->json(['success' => false, 'error' => 'Detail not found']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }


    public function printPackingList($id)
    {
        $packingList = DB::table('packing_lists')
            ->join('master_customers', 'packing_lists.id_master_customers', '=', 'master_customers.id')
            ->select('packing_lists.packing_number', 'packing_lists.date', 'master_customers.name as customer_name')
            ->where('packing_lists.id', $id)
            ->first();

        $details = DB::table('packing_list_details')
            ->join('barcode_detail', 'packing_list_details.barcode', '=', 'barcode_detail.barcode_number')
            ->join('barcodes', 'barcode_detail.id_barcode', '=', 'barcodes.id')
            ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
            ->join('master_product_fgs', 'sales_orders.id_master_products', '=', 'master_product_fgs.id')
            ->join('master_units', 'master_product_fgs.id_master_units', '=', 'master_units.id')
            ->leftJoin('report_blow_production_results', function ($join) {
                $join->on('barcode_detail.barcode_number', '=', 'report_blow_production_results.barcode')
                    ->where('barcode_detail.barcode_number', 'like', '%B');
            })
            ->leftJoin('report_sf_production_results', function ($join) {
                $join->on('barcode_detail.barcode_number', '=', 'report_sf_production_results.barcode')
                    ->where(function ($query) {
                        $query->where('barcode_detail.barcode_number', 'like', '%F')
                            ->orWhere('barcode_detail.barcode_number', 'like', '%S')
                            ->orWhere('barcode_detail.barcode_number', 'like', '%B');
                    });
            })
            ->select(
                'master_product_fgs.product_code',
                'master_product_fgs.description',
                'barcode_detail.barcode_number',
                'sales_orders.so_number',
                'master_units.unit',
                'packing_list_details.pcs',
                'packing_list_details.weight',
                DB::raw('COALESCE(report_blow_production_results.weight, report_sf_production_results.weight) as production_weight')
            )
            ->where('packing_list_details.id_packing_lists', $id)
            ->get();

        return view('warehouse.print_packing_list', compact('packingList', 'details'));
    }


    public function show($id)
    {
        $packingList = DB::table('packing_lists')
            ->join('master_customers', 'packing_lists.id_master_customers', '=', 'master_customers.id')
            ->select('packing_lists.*', 'master_customers.name as customer_name')
            ->where('packing_lists.id', $id)
            ->first();

        $details = DB::table('packing_list_details')
            ->join('barcode_detail', 'packing_list_details.barcode', '=', 'barcode_detail.barcode_number')
            ->join('barcodes', 'barcode_detail.id_barcode', '=', 'barcodes.id')
            ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
            ->join('master_product_fgs', 'sales_orders.id_master_products', '=', 'master_product_fgs.id')
            ->join('master_units', 'master_product_fgs.id_master_units', '=', 'master_units.id')
            ->select(
                'packing_list_details.*',
                'packing_list_details.id_sales_orders as change_so',
                'master_product_fgs.description'
            )
            ->where('packing_list_details.id_packing_lists', $id)
            ->get();

        return view('warehouse.show_packing_list', compact('packingList', 'details'));
    }


    public function post($id)
    {
        $packingList = PackingList::find($id);
        $packingList->status = 'Posted';
        $packingList->save();

        return redirect()->route('packing-list')->with('pesan', 'Status berhasil diubah menjadi Posted.');
    }

    public function unpost($id)
    {
        $packingList = PackingList::find($id);
        $packingList->status = 'Request';
        $packingList->save();

        return redirect()->route('packing-list')->with('pesan', 'Status berhasil diubah menjadi Request.');
    }
    // Method untuk menghapus packing list
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            // Ambil semua detail packing list
            $details = DB::table('packing_list_details')->where('id_packing_lists', $id)->get();

            foreach ($details as $detail) {
                // Ambil informasi barcode dari tabel barcodes dan master_product_fgs
                $barcodeRecord = DB::table('barcodes')
                    ->join('barcode_detail', 'barcodes.id', '=', 'barcode_detail.id_barcode')
                    ->join('master_product_fgs', 'barcodes.id_master_products', '=', 'master_product_fgs.id')
                    ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
                    ->where('barcode_detail.barcode_number', $detail->barcode)
                    ->select('master_product_fgs.id as product_id', 'sales_orders.id as sales_order_id')
                    ->first();

                if ($barcodeRecord) {
                    // Kembalikan stok sesuai kondisi barcode
                    if (substr($detail->barcode, -1) === 'B') {
                        // Jika barcode berakhiran 'B', kembalikan stok berdasarkan pcs
                        DB::table('master_product_fgs')
                            ->where('id', $barcodeRecord->product_id)
                            ->increment('stock', $detail->pcs);
                        DB::table('sales_orders')
                            ->where('id', $barcodeRecord->sales_order_id)
                            ->increment('outstanding_delivery_qty', $detail->pcs);
                    } else {
                        // Jika barcode bukan berakhiran 'B', kembalikan stok berdasarkan jumlah barcode
                        DB::table('master_product_fgs')
                            ->where('id', $barcodeRecord->product_id)
                            ->increment('stock', 1);
                        DB::table('sales_orders')
                            ->where('id', $barcodeRecord->sales_order_id)
                            ->increment('outstanding_delivery_qty', 1);
                    }

                    // Update status barcode di tabel barcode_detail
                    DB::table('barcode_detail')
                        ->where('barcode_number', $detail->barcode)
                        ->update(['status' => $detail->sts_start]);
                }
            }

            // Hapus detail packing list
            DB::table('packing_list_details')->where('id_packing_lists', $id)->delete();

            // Hapus packing list
            DB::table('packing_lists')->where('id', $id)->delete();
        });

        return redirect()->route('packing-list')->with('pesan', 'Data berhasil dihapus.');
    }

    public function update(Request $request, $id)
    {
        // Validasi input date
        $request->validate([
            'date' => 'required|date',
        ]);

        // Cari packing list berdasarkan id
        $packingList = DB::table('packing_lists')->where('id', $id)->first();

        if ($packingList) {
            // Update tanggal pada tabel packing_lists
            DB::table('packing_lists')->where('id', $id)->update([
                'date' => $request->date,
                'updated_at' => now()
            ]);

            // Redirect kembali ke halaman packing list dengan pesan sukses
            return response()->json(['success' => true, 'message' => 'Packing List updated successfully']);
        } else {
            // Redirect kembali ke halaman packing list dengan pesan error jika tidak ditemukan
            return response()->json(['success' => false, 'message' => 'Packing List not found']);
        }
    }
}
