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
                ->join('sales_orders as so', 'pl.id_sales_orders', '=', 'so.id')
                ->join('master_customer_addresses as shipping', 'so.id_master_customer_addresses', '=', 'shipping.id')
                ->leftJoin('master_customers as mc', 'pl.id_master_customers', '=', 'mc.id')
                ->select(
                    'pl.id',
                    'pl.packing_number',
                    'pl.date',
                    'mc.name as customer',
                    'pl.status',
                    'so.so_number',
                    'shipping.address as address',
                    DB::raw('"" as action') // Menambahkan kolom action sebagai kolom kosong
                )
                ->orderBy($columns[$orderColumn], $orderDirection);


            // Handle search
            if ($request->has('search') && $request->input('search')) {
                $searchValue = $request->input('search');
                $query->where(function ($query) use ($searchValue) {
                    $query->where('pl.packing_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('mc.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('pl.status', 'like', '%' . $searchValue . '%')
                        ->orWhere('so.so_number', 'like', '%' . $searchValue . '%');
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
                'so_id' => 'required|exists:sales_orders,id',
                'date' => 'required|date',
                'customer' => 'required|exists:master_customers,id',
                'all_barcodes' => 'required|in:Y,N',
            ]);

            // Simpan data ke database
            $packingList = new PackingList();
            $packingList->packing_number = $request->packing_number;
            $packingList->id_sales_orders = $request->so_id;
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


    public function getSoNumberByCustomer($customerId)
    {
        //cek apakah status dn posted

        $soNo = DB::table('sales_orders')
            ->where('sales_orders.id_master_customers', $customerId)
            ->where('status', 'Posted')
            ->select('id', 'so_number')
            ->get();
        // dd($customerId);
        // dd($soNo);
        return response()->json($soNo);
    }
    // Method untuk memeriksa barcode


    public function checkBarcode(Request $request)
    {
        $barcode = $request->input('barcode');
        $customerId = $request->input('customer_id');
        $changeSo = $request->input('change_so');
        $packingListId = $request->input('packing_list_id');
        $soId =  $request->input('so_id');

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

        $barcodeRecord = DB::table('barcodes')
            ->join('barcode_detail', 'barcodes.id', '=', 'barcode_detail.id_barcode')
            ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
            ->when($changeSo, function ($query) use ($barcode, $changeSo) {
                return $query
                    ->where('barcode_detail.barcode_number', $barcode)
                    ->where('sales_orders.so_number', $changeSo);
            }, function ($query) use ($barcode, $soId) {
                return $query
                    ->where('barcode_detail.barcode_number', $barcode)
                    ->where('barcodes.id_sales_orders', $soId);
            })
            ->select(
                'barcode_detail.barcode_number',
                'sales_orders.so_number as soNo',
                'sales_orders.id_master_products as id_master_products',
                'barcode_detail.status',
                'barcodes.type_product',
                'barcodes.id_sales_orders as sales_order_id',
                'barcodes.qty',
                DB::raw('COALESCE(master_product_fgs.description, master_wips.description, master_tool_auxiliaries.description, master_raw_materials.description) as description'),
                DB::raw('COALESCE(master_product_fgs.id, master_wips.id, master_tool_auxiliaries.id, master_raw_materials.id) as product_id'),
                DB::raw('COALESCE(master_product_fgs.stock, master_wips.stock, master_tool_auxiliaries.stock, master_raw_materials.stock) as stock'),
                DB::raw('report_sf_production_results.weight as sf_weight'),
                DB::raw('report_blow_production_results.weight as blow_weight'),
                DB::raw('master_raw_materials.weight as raw_weight'),
                DB::raw('COALESCE(rbp.total_amount_result, 1) as total_amount_result'),
                DB::raw('COALESCE(rbp.total_wrap, 0) as total_wrap'),
                DB::raw('COALESCE(rbp.total_weight_starting, 0) as total_weight_starting')
            )
            ->leftJoin('report_blow_production_results', 'barcode_detail.barcode_number', '=', 'report_blow_production_results.barcode')
            ->leftJoin('report_sf_production_results', 'barcode_detail.barcode_number', '=', 'report_sf_production_results.barcode')
            ->leftJoin('master_product_fgs', function ($join) {
                $join->on('sales_orders.id_master_products', '=', 'master_product_fgs.id')
                    ->where('barcodes.type_product', 'FG');
            })
            ->leftJoin('master_wips', function ($join) {
                $join->on('barcodes.id_master_products', '=', 'master_wips.id')
                    ->where('barcodes.type_product', 'WIP');
            })
            ->leftJoin('master_tool_auxiliaries', function ($join) {
                $join->on('sales_orders.id_master_products', '=', 'master_tool_auxiliaries.id')
                    ->where('barcodes.type_product', 'AUX');
            })
            ->leftJoin('master_raw_materials', function ($join) {
                $join->on('sales_orders.id_master_products', '=', 'master_raw_materials.id')
                    ->where('barcodes.type_product', 'RAW');
            })
            ->leftJoin(DB::raw('(SELECT barcode, SUM(amount_result) as total_amount_result, SUM(weight_starting) as total_weight_starting, SUM(wrap) as total_wrap FROM report_bag_production_results GROUP BY barcode) as rbp'), 'barcode_detail.barcode_number', '=', 'rbp.barcode')
            ->first();
        // dd($barcodeRecord);


        if ($barcodeRecord && strpos($barcodeRecord->status, 'In Stock') !== false) {
            $exists = true;
            $productName = $barcodeRecord->description;
            $isBag = stripos($barcodeRecord->status, 'bag') !== false;
            $pcs = $barcodeRecord->total_amount_result;

            $newStock = $barcodeRecord->stock - ($isBag ? $pcs : 1);
            // dd($newStock);
            if ($newStock < 0) {
                return response()->json(['exists' => false, 'status' => false, 'message' => 'Stok tidak mencukupi']);
            }

            // Tentukan weight berdasarkan status
            $finalWeight = 0;
            if (stripos($barcodeRecord->status, 'SLT') !== false || stripos($barcodeRecord->status, 'FLD') !== false) {
                $finalWeight = $barcodeRecord->sf_weight;
            } elseif (stripos($barcodeRecord->status, 'BLW') !== false) {
                $finalWeight = $barcodeRecord->blow_weight;
            } elseif (stripos($barcodeRecord->status, 'RAW') !== false) {
                $finalWeight = $barcodeRecord->raw_weight;
            } elseif (stripos($barcodeRecord->status, 'AUX') !== false) {
                $finalWeight = 0;
            }

            //alert non numeric weight
            if (!is_numeric($finalWeight)) {
                $message = "(Weight :$finalWeight) A non-numeric value encountered";
                return response()->json(['weight' => false, 'status' => false, 'message' => $message]);
            }
            $weightValue = $isBag ? $barcodeRecord->total_weight_starting : $finalWeight;

            $insertedId = DB::table('packing_list_details')->insertGetId([
                'barcode' => $barcode,
                'change_so' => $changeSo === null ? null : $barcodeRecord->soNo,
                'total_wrap' => $barcodeRecord->total_wrap,
                'id_packing_lists' => $packingListId,
                'weight' => $weightValue,
                'pcs' => ($barcodeRecord->type_product === 'AUX' || $barcodeRecord->type_product === 'RAW') ? $barcodeRecord->qty : ($isBag ? $pcs : 1),
                'sts_start' => $barcodeRecord->status,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $packing = DB::table('packing_lists')
                ->select('packing_number')
                ->where('id', $packingListId)
                ->first();

            if ($packing) {
                $existingHistory = DB::table('history_stocks')
                    ->where('id_good_receipt_notes_details', $packing->packing_number)
                    ->where('type_product', $barcodeRecord->type_product)
                    ->where('id_master_products', $barcodeRecord->product_id)
                    ->first();

                $qtyToInsert = ($barcodeRecord->type_product === 'AUX' || $barcodeRecord->type_product === 'RAW') ? $barcodeRecord->qty : ($isBag ? $pcs : 1);
                $weightToInsert = $weightValue;

                if ($existingHistory) {
                    $newQty = $existingHistory->qty + $qtyToInsert;
                    $newBarcodes = $existingHistory->barcode ? $existingHistory->barcode . ', ' . $barcode : $barcode;

                    DB::table('history_stocks')
                        ->where('id', $existingHistory->id)
                        ->update([
                            'qty' => $newQty,
                            'weight' => ($existingHistory->weight ?? 0) + $weightToInsert,
                            'barcode' => $newBarcodes,
                            'updated_at' => now()
                        ]);
                } else {
                    DB::table('history_stocks')->insert([
                        'id_good_receipt_notes_details' => $packing->packing_number,
                        'type_product' => $barcodeRecord->type_product,
                        'id_master_products' => $barcodeRecord->product_id,
                        'qty' => $qtyToInsert,
                        'weight' => $weightToInsert,
                        'type_stock' => 'OUT',
                        'barcode' => $barcode,
                        'date' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                        'remarks' => null
                    ]);
                }
            }

            // ambil data weight dari tabel packing_list_details
            $weightDetail = $weightValue;

            $stockUpdateQty = ($barcodeRecord->type_product === 'AUX' || $barcodeRecord->type_product === 'RAW')
                ? $barcodeRecord->qty
                : ($isBag ? $pcs : 1);

            switch ($barcodeRecord->type_product) {
                case 'FG':
                    DB::table('master_product_fgs')
                        ->where('id', $barcodeRecord->product_id)
                        ->update([
                            'stock' => DB::raw("stock - $stockUpdateQty"),
                            'weight_stock' => DB::raw("weight_stock - $weightDetail")
                        ]);
                    break;

                case 'WIP':
                    DB::table('master_wips')
                        ->where('id', $barcodeRecord->product_id)
                        ->update([
                            'stock' => DB::raw("stock - $stockUpdateQty"),
                            'weight_stock' => DB::raw("weight_stock - $weightDetail")
                        ]);
                    break;

                case 'AUX':
                    DB::table('master_tool_auxiliaries')
                        ->where('id', $barcodeRecord->product_id)
                        ->update([
                            'stock' => DB::raw("stock - $stockUpdateQty"),
                            'weight_stock' => DB::raw("weight_stock - $weightDetail")
                        ]);
                    break;

                case 'RAW':
                    DB::table('master_raw_materials')
                        ->where('id', $barcodeRecord->product_id)
                        ->update([
                            'stock' => DB::raw("stock - $stockUpdateQty"),
                            'weight_stock' => DB::raw("weight_stock - $weightDetail")
                        ]);
                    break;
            }

            // Jika ada change_so, kurangi outstanding_delivery_qty dari SO yang dituju (soId), bukan dari SO asal
            if ($changeSo) {
                $unitCode = DB::table('sales_orders as so')
                    ->join('master_units as mu', 'so.id_master_units', '=', 'mu.id')
                    ->where('so.id', $soId)
                    ->value('mu.unit_code');
                $decrementValue = ($unitCode === 'KG') ? $weightDetail : $stockUpdateQty;
                DB::table('sales_orders')->where('id', $soId)->decrement('outstanding_delivery_qty', $decrementValue);
            } else {
                $unitCode = DB::table('sales_orders as so')
                    ->join('master_units as mu', 'so.id_master_units', '=', 'mu.id')
                    ->where('so.id', $barcodeRecord->sales_order_id)
                    ->value('mu.unit_code');
                $decrementValue = ($unitCode === 'KG') ? $weightDetail : $stockUpdateQty;
                DB::table('sales_orders')->where('id', $barcodeRecord->sales_order_id)->decrement('outstanding_delivery_qty', $decrementValue);
            }

            DB::table('barcode_detail')->where('barcode_number', $barcode)->update(['status' => 'Packing List']);
        } else {
            $message = $changeSo ? 'Barcode tidak sesuai dengan SO yang diberikan' : 'Barcode tidak sesuai dengan customer/SO yang diberikan';
            return response()->json(['exists' => false, 'status' => false, 'message' => $message]);
        }

        return response()->json([
            'exists' => $exists,
            'duplicate' => false,
            'id' => $insertedId,
            'product_name' => $productName,
            'is_bag' => $isBag,
            'sales_order_id' => $barcodeRecord->sales_order_id,
            'pcs' => $isBag ? $pcs : 1,
            'changeSo' => $changeSo,
            'soId' => $soId,
            'wrap' =>  $barcodeRecord->total_wrap,
            'weight' => $isBag ? $barcodeRecord->total_weight_starting : $finalWeight
        ]);
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
        $soId =  $request->input('so_id');


        // Ambil informasi barcode dari tabel packing_list_details
        $barcodeDetail = DB::table('packing_list_details as pls')
            ->join('packing_lists as pl', 'pls.id_packing_lists', '=', 'pl.id')->where('pls.id', $id)->first();

        if ($barcodeDetail) {
            // Ambil informasi produk terkait dari tabel barcodes, master_product_fgs dan sales_orders
            $barcodeRecord = DB::table('barcodes')
                ->join('barcode_detail', 'barcodes.id', '=', 'barcode_detail.id_barcode')
                ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
                ->leftJoin('master_product_fgs', function ($join) {
                    $join->on('sales_orders.id_master_products', '=', 'master_product_fgs.id')
                        ->where('barcodes.type_product', 'FG');
                })
                ->leftJoin('master_wips', function ($join) {
                    $join->on('barcodes.id_master_products', '=', 'master_wips.id')
                        ->where('barcodes.type_product', 'WIP');
                })
                ->leftJoin('master_tool_auxiliaries', function ($join) {
                    $join->on('sales_orders.id_master_products', '=', 'master_tool_auxiliaries.id')
                        ->where('barcodes.type_product', 'AUX');
                })
                ->leftJoin('master_raw_materials', function ($join) {
                    $join->on('sales_orders.id_master_products', '=', 'master_raw_materials.id')
                        ->where('barcodes.type_product', 'RAW');
                })
                ->where('barcode_detail.barcode_number', $barcodeDetail->barcode)
                ->select(
                    'barcodes.id_sales_orders as sales_order_id',
                    'barcodes.id_master_products as id_master_products',
                    'barcodes.type_product as type_product',
                    'barcode_detail.status as status',
                    DB::raw('COALESCE(master_product_fgs.id, master_wips.id, master_tool_auxiliaries.id, master_raw_materials.id) as product_id'),
                    'barcodes.qty'
                )
                ->first();

            $soId = $barcodeDetail->id_sales_orders;



            if ($barcodeRecord) {
                $barcode = $barcodeDetail->barcode;
                $isBag = stripos($barcodeDetail->sts_start, 'bag') !== false;

                // Ambil SO yang dituju dari packing_lists jika ada change_so
                $targetSoId = $barcodeRecord->sales_order_id;
                if ($barcodeDetail->change_so) {
                    $packingList = DB::table('packing_lists')
                        ->where('id', $barcodeDetail->id_packing_lists)
                        ->first();
                    if ($packingList) {
                        $targetSoId = $packingList->id_sales_orders;
                    }
                }

                // ambil data weight dari tabel packing_list_details
                $weightDetail = DB::table('packing_list_details')
                    ->where('barcode', $barcode)
                    ->value('weight');
                $weightToRemove = $weightDetail;

                $stockUpdateQty = ($barcodeRecord->type_product === 'AUX' || $barcodeRecord->type_product === 'RAW')
                    ? $barcodeRecord->qty
                    : ($isBag ? $pcs : 1);

                switch ($barcodeRecord->type_product) {
                    case 'FG':
                        DB::table('master_product_fgs')
                            ->where('id', $barcodeRecord->product_id)
                            ->update([
                                'stock' => DB::raw("stock + $stockUpdateQty"),
                                'weight_stock' => DB::raw("weight_stock + $weightDetail")
                            ]);
                        break;

                    case 'WIP':
                        DB::table('master_wips')
                            ->where('id', $barcodeRecord->product_id)
                            ->update([
                                'stock' => DB::raw("stock + $stockUpdateQty"),
                                'weight_stock' => DB::raw("weight_stock + $weightDetail")
                            ]);
                        break;

                    case 'AUX':
                        DB::table('master_tool_auxiliaries')
                            ->where('id', $barcodeRecord->product_id)
                            ->update([
                                'stock' => DB::raw("stock + $stockUpdateQty"),
                                'weight_stock' => DB::raw("weight_stock + $weightDetail")
                            ]);
                        break;

                    case 'RAW':
                        DB::table('master_raw_materials')
                            ->where('id', $barcodeRecord->product_id)
                            ->update([
                                'stock' => DB::raw("stock + $stockUpdateQty"),
                                'weight_stock' => DB::raw("weight_stock + $weightDetail")
                            ]);
                        break;
                }

                // Kembalikan outstanding_delivery_qty ke SO yang dituju
                $unitCode = DB::table('sales_orders as so')
                    ->join('master_units as mu', 'so.id_master_units', '=', 'mu.id')
                    ->where('so.id', $targetSoId)
                    ->value('mu.unit_code');

                $incrementValue = ($unitCode === 'KG') ? $weightDetail : $stockUpdateQty;

                DB::table('sales_orders')
                    ->where('id', $targetSoId)
                    ->increment('outstanding_delivery_qty', $incrementValue);
                // Tambahan: Proses remove dari history_stocks
                $packing = DB::table('packing_lists')
                    ->select('packing_number')
                    ->where('id', $barcodeDetail->id_packing_lists)
                    ->first();

                $packingNumber = $packing ? $packing->packing_number : null;

                if ($packingNumber) {
                    $history = DB::table('history_stocks')
                        ->where('id_good_receipt_notes_details', $packingNumber)
                        ->where('type_product', $barcodeRecord->type_product)
                        ->where('id_master_products', $barcodeRecord->id_master_products)
                        ->first();

                    if ($history) {
                        $qtyToRemove = $barcodeDetail->pcs;
                        $weightFromHistory = $history->weight ?? 0;

                        // Hapus barcode dari string
                        $barcodeList = explode(', ', $history->barcode);
                        $barcodeList = array_filter($barcodeList, fn($b) => $b !== $barcode);
                        $newBarcodeString = implode(', ', $barcodeList);
                        $newQty = $history->qty - $qtyToRemove;
                        $newWeight = $weightFromHistory - $weightToRemove;

                        if ($newQty > 0) {
                            DB::table('history_stocks')
                                ->where('id', $history->id)
                                ->update([
                                    'qty' => $newQty,
                                    'weight' => max(0, $newWeight),
                                    'barcode' => $newBarcodeString,
                                    'updated_at' => now()
                                ]);
                        } else {
                            DB::table('history_stocks')
                                ->where('id', $history->id)
                                ->delete();
                        }
                    }
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
        // Ambil semua data details sekaligus berdasarkan id packing list
        $details = DB::table('packing_list_details')
            ->join('barcode_detail', 'packing_list_details.barcode', '=', 'barcode_detail.barcode_number')
            ->join('barcodes', 'barcodes.id', '=', 'barcode_detail.id_barcode')
            ->leftJoin('master_wips', function ($join) {
                $join->on('barcodes.id_master_products', '=', 'master_wips.id')
                    ->where('barcodes.type_product', '=', 'WIP');
            })
            ->leftJoin('master_product_fgs', function ($join) {
                $join->on('barcodes.id_master_products', '=', 'master_product_fgs.id')
                    ->where('barcodes.type_product', '=', 'FG');
            })
            ->leftJoin('master_tool_auxiliaries', function ($join) {
                $join->on('barcodes.id_master_products', '=', 'master_tool_auxiliaries.id')
                    ->where('barcodes.type_product', '=', 'AUX');
            })
            ->leftJoin('master_raw_materials', function ($join) {
                $join->on('barcodes.id_master_products', '=', 'master_raw_materials.id')
                    ->where('barcodes.type_product', '=', 'RAW');
            })
            ->where('packing_list_details.id_packing_lists', $id)
            ->select(
                'packing_list_details.*',
                'barcodes.type_product',
                DB::raw('COALESCE(master_wips.description, master_product_fgs.description, master_tool_auxiliaries.description, master_raw_materials.description) as product_description')
            )
            ->get();
        // dd($details);


        // Ambil data packing list dan customer dalam satu query
        $packingList = DB::table('packing_lists')
            ->join('master_customers', 'packing_lists.id_master_customers', '=', 'master_customers.id')
            ->join('sales_orders', 'packing_lists.id_sales_orders', '=', 'sales_orders.id')
            ->where('packing_lists.id', $id)
            ->select(
                'packing_lists.*',
                'sales_orders.so_number',
                'master_customers.name as customer_name',
                'master_customers.id as customer_id'
            )
            ->first();

        // Return view dengan data yang sudah diproses
        return view('warehouse.edit', compact('packingList', 'details'));
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
                        ->select(
                            'barcode_detail.*',
                            'master_product_fgs.description',
                            'master_product_fgs.id as product_id',
                            'sales_orders.id as sales_order_id',
                            'master_product_fgs.stock',
                            'sales_orders.outstanding_delivery_qty' //,barcode_detail.status
                        )
                        ->first();

                    if ($barcodeRecord) {
                        $exists = true;
                        $productName = $barcodeRecord->description;
                        $isBag = stripos($barcodeRecord->status, 'bag') !== false;
                        // $isBag = (substr($barcode, -1) === 'B');
                    }

                    if ($exists) {
                        $oldBarcodeRecord = DB::table('barcodes')
                            ->join('barcode_detail', 'barcodes.id', '=', 'barcode_detail.id_barcode')
                            ->join('master_product_fgs', 'barcodes.id_master_products', '=', 'master_product_fgs.id')
                            ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
                            ->where('barcode_detail.barcode_number', $oldDetail->barcode)
                            ->select('master_product_fgs.id as product_id', 'barcodes.id_sales_orders as old_sales_order_id', 'master_product_fgs.stock', 'sales_orders.outstanding_delivery_qty')
                            ->first();

                        if ($oldBarcodeRecord && stripos($oldDetail->status, 'bag') !== false) {
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
                    // if ($field == 'pcs' && $oldDetail && substr($oldDetail->barcode, -1) === 'B')
                    if ($field == 'pcs' && $oldDetail && stripos($$oldDetail->status, 'bag') !== false) {
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
            ->join('sales_orders', 'packing_lists.id_sales_orders', '=', 'sales_orders.id')
            ->select('packing_lists.packing_number', 'packing_lists.date', 'master_customers.name as customer_name', 'sales_orders.so_number')
            ->where('packing_lists.id', $id)
            ->first();

        $details = DB::table('packing_list_details')
            ->join('barcode_detail', 'packing_list_details.barcode', '=', 'barcode_detail.barcode_number')
            ->join('barcodes', 'barcode_detail.id_barcode', '=', 'barcodes.id')
            ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')

            // Subquery untuk mendapatkan berat berdasarkan kondisi
            // ->leftJoinSub(
            //     DB::table('report_blow_production_results')
            //         ->select('barcode', 'weight as blow_weight'),
            //     'blow_results',
            //     function ($join) {
            //         $join->on('barcode_detail.barcode_number', '=', 'blow_results.barcode')
            //             ->where('packing_list_details.sts_start', 'like', '%BLW');
            //     }
            // )
            // ->leftJoinSub(
            //     DB::table('report_sf_production_results')
            //         ->select('barcode', 'weight as sf_weight'),
            //     'sf_results',
            //     function ($join) {
            //         $join->on('barcode_detail.barcode_number', '=', 'sf_results.barcode')
            //             ->where(function ($query) {
            //                 $query->where('packing_list_details.sts_start', 'like', '%FLD')
            //                     ->orWhere('packing_list_details.sts_start', 'like', '%SLT');
            //             });
            //     }
            // )

            // Select kolom yang diambil
            ->select(
                'barcodes.type_product',
                DB::raw('COALESCE(master_product_fgs.product_code, master_wips.wip_code, master_tool_auxiliaries.code, master_raw_materials.rm_code) as product_code'),
                'packing_list_details.sts_start',
                DB::raw("COALESCE(master_product_fgs.perforasi, master_wips.perforasi, master_tool_auxiliaries.weight_stock, master_raw_materials.weight_stock,'p-') as perforasi"),
                DB::raw('COALESCE(master_product_fgs.description, master_wips.description, master_tool_auxiliaries.description, master_raw_materials.description) as description'),
                'barcode_detail.barcode_number',
                'sales_orders.so_number',
                'master_units.unit',
                'packing_list_details.pcs',
                'packing_list_details.weight',
                'packing_list_details.total_wrap'
                // DB::raw('COALESCE(blow_results.blow_weight, sf_results.sf_weight, master_raw_materials.weight ) as production_weight')
            )
            ->leftJoin('master_product_fgs', function ($join) {
                $join->on('sales_orders.id_master_products', '=', 'master_product_fgs.id')
                    ->where('barcodes.type_product', '=', 'FG');
            })
            ->leftJoin('master_wips', function ($join) {
                $join->on('sales_orders.id_master_products', '=', 'master_wips.id')
                    ->where('barcodes.type_product', '=', 'WIP');
            })
            ->leftJoin('master_tool_auxiliaries', function ($join) {
                $join->on('sales_orders.id_master_products', '=', 'master_tool_auxiliaries.id')
                    ->where('barcodes.type_product', '=', 'AUX');
            })
            ->leftJoin('master_raw_materials', function ($join) {
                $join->on('sales_orders.id_master_products', '=', 'master_raw_materials.id')
                    ->where('barcodes.type_product', '=', 'RAW');
            })
            ->join('master_units', function ($join) {
                $join->on('master_product_fgs.id_master_units', '=', 'master_units.id')
                    ->orOn('master_wips.id_master_units', '=', 'master_units.id')
                    ->orOn('master_raw_materials.id_master_units', '=', 'master_units.id')
                    ->orOn('master_tool_auxiliaries.id_master_units', '=', 'master_units.id');
            })
            ->where('packing_list_details.id_packing_lists', $id)
            ->get();
        // dd($packingList, $details);

        return view('warehouse.print_packing_list', compact('packingList', 'details'));
    }


    public function show($id)
    {
        // Ambil data packing list dengan informasi customer
        $packingList = DB::table('packing_lists as pl')
            ->join('master_customers as mc', 'pl.id_master_customers', '=', 'mc.id')
            ->join('sales_orders as so', 'pl.id_sales_orders', '=', 'so.id')
            ->select('pl.*', 'mc.name as customer_name', 'so.so_number')
            ->where('pl.id', $id)
            ->first();

        // Deklarasi kondisi tipe produk
        $typeProductConditions = [
            'FG' => 'master_product_fgs as fg',
            'WIP' => 'master_wips as wip',
            'AUX' => 'master_tool_auxiliaries as aux',
            'RAW' => 'master_raw_materials as raw',
        ];

        // Ambil detail packing list dengan deskripsi berdasarkan tipe produk
        $detailsQuery = DB::table('packing_list_details as pld')
            ->join('barcode_detail as bd', 'pld.barcode', '=', 'bd.barcode_number')
            ->join('packing_lists as pl', 'pld.id_packing_lists', '=', 'pl.id')
            ->join('barcodes as b', 'bd.id_barcode', '=', 'b.id')
            ->join('sales_orders as so', 'b.id_sales_orders', '=', 'so.id');

        // Iterasi kondisi tipe produk untuk left join
        foreach ($typeProductConditions as $type => $table) {
            // Pisahkan nama tabel dan alias
            list($tableName, $alias) = explode(' as ', $table);

            // Tambahkan left join dengan nama tabel dan alias yang diparsing
            $detailsQuery->leftJoin(DB::raw($tableName . ' as ' . $alias), function ($join) use ($type, $alias) {
                $join->on('b.id_master_products', '=', DB::raw($alias . '.id'))
                    ->where('b.type_product', '=', $type);
            });
        }

        $details = $detailsQuery
            ->select(
                'pld.*',
                'b.type_product',
                'so.so_number',
                'pld.change_so as change_so',
                DB::raw('COALESCE(fg.description, wip.description, aux.description, raw.description) as description')
            )
            ->where('pld.id_packing_lists', $id)
            ->get();

        // Return view dengan data packing list dan details
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

            // Ambil packing number
            $packing = DB::table('packing_lists')
                ->select('packing_number')
                ->where('id', $id)
                ->first();

            $packingNumber = $packing ? $packing->packing_number : null;

            foreach ($details as $detail) {
                // Ambil informasi barcode + unit
                $barcodeRecord = DB::table('barcodes')
                    ->join('barcode_detail', 'barcodes.id', '=', 'barcode_detail.id_barcode')
                    ->join('packing_list_details', 'barcode_detail.barcode_number', '=', 'packing_list_details.barcode')
                    ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
                    ->join('master_units', 'sales_orders.id_master_units', '=', 'master_units.id')
                    ->where('barcode_detail.barcode_number', $detail->barcode)
                    ->select(
                        'barcodes.type_product',
                        'packing_list_details.pcs',
                        'packing_list_details.weight',
                        'packing_list_details.sts_start',
                        'sales_orders.id as sales_order_id',
                        'barcodes.id_master_products as product_id',
                        'master_units.unit_code'
                    )
                    ->first();

                if ($barcodeRecord) {
                    // Ambil SO yang dituju dari packing_lists jika ada change_so
                    $targetSoId = $barcodeRecord->sales_order_id;
                    if ($detail->change_so) {
                        $packingList = DB::table('packing_lists')
                            ->where('id', $id)
                            ->first();
                        if ($packingList) {
                            $targetSoId = $packingList->id_sales_orders;
                        }
                    }

                    // Tentukan nilai increment outstanding (weight kalau KG, pcs kalau bukan)
                    $incrementValue = ($barcodeRecord->unit_code === 'KG')
                        ? $barcodeRecord->weight
                        : $barcodeRecord->pcs;

                    // Mapping type_product ke tabel master
                    $productTables = [
                        'FG'  => 'master_product_fgs',
                        'WIP' => 'master_wips',
                        'AUX' => 'master_tool_auxiliaries',
                        'RAW' => 'master_raw_materials',
                    ];

                    // Jika type_product ada di mapping, update stok dan weight_stock
                    if (isset($productTables[$barcodeRecord->type_product])) {
                        DB::table($productTables[$barcodeRecord->type_product])
                            ->where('id', $barcodeRecord->product_id)
                            ->update([
                                'stock' => DB::raw("stock + {$barcodeRecord->pcs}"),
                                'weight_stock' => DB::raw("weight_stock + {$barcodeRecord->weight}")
                            ]);
                    }

                    // Increment outstanding_delivery_qty dari SO yang dituju
                    DB::table('sales_orders')
                        ->where('id', $targetSoId)
                        ->increment('outstanding_delivery_qty', $incrementValue);

                    // Update status barcode di tabel barcode_detail
                    DB::table('barcode_detail')
                        ->where('barcode_number', $detail->barcode)
                        ->update(['status' => $detail->sts_start]);
                }
            }

            // Hapus detail packing list
            DB::table('packing_list_details')->where('id_packing_lists', $id)->delete();

            // Hapus entri history_stocks berdasarkan packing_number
            if ($packingNumber) {
                DB::table('history_stocks')
                    ->where('id_good_receipt_notes_details', $packingNumber)
                    ->delete();
            }

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
