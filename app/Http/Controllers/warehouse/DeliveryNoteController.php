<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MstCustomers;
use App\Models\Warehouse\PackingList;
use App\Models\Marketing\salesOrder;
use App\Models\Warehouse\DeliveryNote;
use DataTables;

use function Laravel\Prompts\select;

class DeliveryNoteController extends Controller
{

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            $transaction_type = $request->input('transaction_type');

            $query = DB::table('delivery_notes')
                ->join('master_customers', 'delivery_notes.id_master_customers', '=', 'master_customers.id')
                ->leftJoin('delivery_note_details', 'delivery_notes.id', '=', 'delivery_note_details.id_delivery_notes')
                ->leftJoin('packing_lists', 'delivery_note_details.id_packing_lists', '=', 'packing_lists.id')
                ->join('master_vehicles', 'delivery_notes.id_master_vehicles', '=', 'master_vehicles.id')
                ->leftJoin('sales_orders', 'delivery_note_details.id_sales_orders', '=', 'sales_orders.id')
                ->select(
                    'delivery_notes.*',
                    'master_customers.name as customer',
                    'delivery_note_details.dn_type',
                    'delivery_note_details.transaction_type',
                    DB::raw('GROUP_CONCAT(DISTINCT packing_lists.packing_number SEPARATOR ", ") as packing_numbers'),
                    DB::raw("IF(sales_orders.id_order_confirmations IS NULL OR sales_orders.id_order_confirmations = '-', sales_orders.reference_number, sales_orders.id_order_confirmations) as ko_po_no"),
                    'master_vehicles.vehicle_number as vehicle',
                    'sales_orders.reference_number as po_number',
                    'sales_orders.reference_number as id_order_confirmation'
                )
                ->groupBy('delivery_notes.id')
                ->orderBy('delivery_notes.created_at', 'desc');

            if (!empty($transaction_type)) {
                $query->where('delivery_notes.jenis_dn', $transaction_type);
            }

            if ($start_date && $end_date) {
                $query->whereBetween('delivery_notes.date', [$start_date, $end_date]);
            }
            $data = $query->get();

            // dd($data);

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return $this->generateActionButtons($row);
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('delivery_notes.list');
    }

    private function generateActionButtons($data)
    {
        $buttons = '<div class="btn-group" role="group" aria-label="Action Buttons">';
        $buttons .= '<a href="/delivery_notes/' . $data->id . '" class="btn btn-sm btn-primary waves-effect waves-light"><i class="bx bx-show-alt"></i></a>';

        if ($data->status == 'Request') {
            $buttons .= '<form action="/delivery_notes/' . $data->id . '/post" method="post" class="d-inline" data-id="">' . csrf_field() . '<input type="hidden" name="_method" value="PUT"><button type="submit" class="btn btn-sm btn-success" onclick="return confirm(\'Anda yakin mau Post item ini ?\')"><i class="bx bx-check-circle" title="Posted"> Posted</i></button></form>';
        } else if ($data->status == 'Posted') {
            $buttons .= '<form action="/delivery_notes/' . $data->id . '/unpost" method="post" class="d-inline" data-id="">' . csrf_field() . '<input type="hidden" name="_method" value="PUT"><button type="submit" class="btn btn-sm btn-warning" onclick="return confirm(\'Anda yakin mau Un Post item ini ?\')"><i class="bx bx-undo" title="Un Posted"> Un Posted</i></button></form>';
        }

        $buttons .= '<a href="/print_packing_list/' . $data->id . '" class="btn btn-sm btn-secondary"><i class="bx bx-printer"></i> Print Packing List</a>';

        if ($data->status == 'Request') {
            $buttons .= '<a href="/delivery_notes/' . $data->id . '/edit" class="btn btn-sm btn-warning"><i class="bx bx-edit"></i> Edit</a>';
            $buttons .= '<form action="/delivery_notes/' . $data->id . '" method="post" class="d-inline" data-id="">' . csrf_field() . '<input type="hidden" name="_method" value="DELETE"><button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Anda yakin mau menghapus item ini ?\')"><i class="bx bx-trash"></i> Delete</button></form>';
        }

        $buttons .= '</div>';

        return $buttons;
    }

    public function create(Request $request)
    {
        $currentDate = now()->format('ym');

        // Ambil jenis DN dari request
        $jenisDn = $request->input('jenis_dn', 'Regular'); // Default ke Regular jika belum dipilih


        // Tentukan prefix sesuai jenis DN
        $prefix = match ($jenisDn) {
            'Export' => 'DE',
            'Sample' => 'DS',
            'Return' => 'DR',
            default => 'DN',
        };

        // Ambil last DN yang sesuai dengan prefix dan bulan saat ini
        $lastDn = DB::table('delivery_notes')
            ->where('dn_number', 'like', $prefix . $currentDate . '%')
            ->orderBy('dn_number', 'desc')
            ->first();

        if ($lastDn) {
            $lastSequence = intval(substr($lastDn->dn_number, -6));
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        $dnNumber = $prefix . $currentDate . str_pad($nextSequence, 6, '0', STR_PAD_LEFT);


        $packingLists = DB::table('packing_lists')->select('id', 'packing_number')->get();
        $vehicles = DB::table('master_vehicles')->select('id', 'vehicle_number')->get();
        $salesmen = DB::table('master_salesmen')->select('id', 'name')->get();
        $customers = DB::table('master_customers')->select('id', 'name')->get();

        return view('delivery_notes.create', compact('dnNumber', 'packingLists', 'vehicles', 'salesmen', 'customers', 'jenisDn'));
    }


    public function store(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();

        try {
            // Validasi input
            $validatedData = $request->validate([
                'dn_number' => 'required|unique:delivery_notes,dn_number',
                'jenis_dn' => 'required',
                'so_number' => 'required',
                'date' => 'required|date',
                'id_master_customer' => 'required',
                'id_master_vehicle' => 'required',
                'id_master_customer_address_shipping' => 'required',
                'id_master_customer_address_invoice' => 'required',
                'note' => 'nullable|string',
            ]);
            // dd($validatedData);



            // Insert data ke tabel delivery_notes
            $deliveryNoteId = DB::table('delivery_notes')->insertGetId([
                'dn_number' => $validatedData['dn_number'],
                'jenis_dn' => $validatedData['jenis_dn'],
                'id_sales_orders' => $validatedData['so_number'],
                'date' => $validatedData['date'],
                'id_master_customers' => $validatedData['id_master_customer'],
                'id_master_vehicles' => $validatedData['id_master_vehicle'],
                'id_master_customer_address_shipping' => $validatedData['id_master_customer_address_shipping'],
                'id_master_customer_address_invoice' => $validatedData['id_master_customer_address_invoice'],
                'note' => $validatedData['note'] ?? null,
                'status' => 'Request',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // dd($deliveryNoteId);



            DB::commit();

            return response()->json(['success' => true, 'delivery_note_id' => $deliveryNoteId]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    public function storePackingList(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validate([
                'packing_list_id' => 'required|exists:packing_lists,id',
                'type_product' => 'required',
            ]);

            // Cek apakah packing list sudah ada di dalam delivery_note_details
            $existingPackingList = DB::table('delivery_note_details')
                ->where('id_packing_lists', $validatedData['packing_list_id'])
                ->exists();

            if ($existingPackingList) {
                throw new \Exception('Packing list sudah ada di dalam delivery note detail.');
            }

            // Mengambil data quantity dan weight berdasarkan type_product
            $packingListDetails = DB::table('packing_list_details')
                ->join('barcode_detail', 'packing_list_details.barcode', '=', 'barcode_detail.barcode_number')
                ->join('barcodes', 'barcode_detail.id_barcode', '=', 'barcodes.id')
                ->join('packing_lists', 'packing_list_details.id_packing_lists', '=', 'packing_lists.id')
                ->join('sales_orders', 'packing_lists.id_sales_orders', '=', 'sales_orders.id')

                // Left join berdasarkan type_product
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

                // Menghubungkan unit produk secara dinamis
                ->join('master_units', function ($join) {
                    $join->on('sales_orders.id_master_units', '=', 'master_units.id')
                        ->orOn('master_wips.id_master_units', '=', 'master_units.id')
                        ->orOn('master_tool_auxiliaries.id_master_units', '=', 'master_units.id')
                        ->orOn('master_raw_materials.id_master_units', '=', 'master_units.id');
                })


                // Subquery untuk mendapatkan berat berdasarkan kondisi



                // Pilih kolom yang diperlukan
                ->select(
                    DB::raw('COUNT(packing_list_details.barcode) as total_qty'),
                    DB::raw('SUM(packing_list_details.weight) as total_weight'),
                    'master_units.id as id_master_unit',
                    'packing_lists.id_sales_orders as soId',
                    'sales_orders.id_master_salesmen as id_master_salesman',
                    DB::raw("IF(sales_orders.id_order_confirmations IS NULL OR sales_orders.id_order_confirmations = '-', sales_orders.reference_number, sales_orders.id_order_confirmations) as po_number")
                )
                ->where('packing_list_details.id_packing_lists', $validatedData['packing_list_id'])
                ->groupBy('master_units.id', 'sales_orders.id', 'sales_orders.id_master_salesmen')
                ->first();
            // dd($packingListDetails);



            if (!$packingListDetails) {
                throw new \Exception('Packing list details tidak ditemukan.');
            }

            // Simpan ke dalam tabel delivery_note_details
            DB::table('delivery_note_details')->insert([
                'id_delivery_notes' => $id,
                'id_sales_orders' =>  $packingListDetails->soId,
                'id_packing_lists' => $validatedData['packing_list_id'],
                'qty' => $packingListDetails->total_qty,
                'id_master_units' => $packingListDetails->id_master_unit,
                'weight' => $packingListDetails->total_weight,
                'po_number' => $request->po_number,
                'type_product' => $request->type_product,
                'dn_type' => $request->dn_type,
                'transaction_type' => $request->transaction_type,
                'id_master_salesman' => $packingListDetails->id_master_salesman,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'po_number' => $packingListDetails->po_number,
                'dn_type' => $request->dn_type,
                'transaction_type' => $request->transaction_type,
                'salesman_name' => $request->salesman_name,
                'id_sales_orders' =>  $packingListDetails->soId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }



    public function getPackingListDetails($id)
    {
        $details = DB::table('packing_list_details')
            ->join('barcode_detail', 'packing_list_details.barcode', '=', 'barcode_detail.barcode_number')
            ->join('barcodes', 'barcode_detail.id_barcode', '=', 'barcodes.id')
            ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
            ->join('master_salesmen', 'sales_orders.id_master_salesmen', '=', 'master_salesmen.id')
            ->join('master_customers', 'sales_orders.id_master_customers', '=', 'master_customers.id')
            ->select(
                'sales_orders.reference_number as po_number',
                'sales_orders.so_category as dn_type',
                'sales_orders.so_type as transaction_type',
                'master_salesmen.name as salesman_name',
                'barcodes.type_product as type_product'
            )
            ->where('packing_list_details.id_packing_lists', $id)
            ->first();

        return response()->json($details);
    }

    public function getCustomerAddressesBySo($soNo)
    {
        // Ambil type_address untuk cek apakah "same as"
        $sameAs = DB::table('sales_orders')
            ->join('master_customer_addresses', 'sales_orders.id_master_customer_addresses', '=', 'master_customer_addresses.id')
            ->where('sales_orders.id', $soNo)
            ->select('master_customer_addresses.type_address', 'master_customer_addresses.address', 'master_customer_addresses.id')
            ->first();

        if (!$sameAs) {
            return response()->json([
                'invoice' => null
            ]);
        }


        if (stripos($sameAs->type_address, 'same as') !== false) {
            // Jika alamat "same as"
            $invoice = [
                'id' => $sameAs->id,
                'address' => $sameAs->address
            ];
        } else {
            // Ambil alamat shipping & invoice
            $addresses = DB::table('master_customer_addresses')
                ->where('id_master_customers', function ($query) use ($soNo) {
                    $query->select('id_master_customers')
                        ->from('sales_orders')
                        ->where('id', $soNo)
                        ->limit(1);
                })
                ->whereIn('type_address', ['Shipping', 'Invoice'])
                ->get(['id', 'type_address', 'address']);

            $invoice = $addresses->firstWhere('type_address', 'Invoice');
            $invoice = $invoice ? ['id' => $invoice->id, 'address' => $invoice->address] : null;
        }
        $shipping = $sameAs->address;
        $shipping = $shipping ? ['id' => $sameAs->id, 'address' => $sameAs->address] : null;

        return response()->json([
            'shipping' => $shipping,
            'invoice' => $invoice
        ]);
    }



    public function show($id)
    {
        $deliveryNote = DB::table('delivery_notes')
            ->join('packing_lists', 'delivery_notes.id_packing_lists', '=', 'packing_lists.id')
            ->join('master_vehicles', 'delivery_notes.id_master_vehicles', '=', 'master_vehicles.id')
            ->join('sales_orders', 'delivery_notes.po_number', '=', 'sales_orders.id') // Join dengan sales_orders
            ->join('master_salesmen', 'delivery_notes.id_master_salesman', '=', 'master_salesmen.id')
            ->join('master_customers', 'delivery_notes.id_master_customers', '=', 'master_customers.id')
            ->select(
                'delivery_notes.*',
                'packing_lists.packing_number',
                'master_vehicles.vehicle_number as vehicle',
                'sales_orders.reference_number as po_number',
                'master_salesmen.name as salesman_name',
                'master_customers.name as customer_name'
            )
            ->where('delivery_notes.id', $id)
            ->first();

        return view('delivery_notes.show', compact('deliveryNote'));
    }

    public function edit($id)
    {
        // Ambil data delivery note berdasarkan ID yang diberikan
        $deliveryNote = DB::table('delivery_notes')
            ->leftJoin('delivery_note_details', 'delivery_notes.id', '=', 'delivery_note_details.id_delivery_notes')
            ->join('master_customers', 'delivery_notes.id_master_customers', '=', 'master_customers.id')
            ->join('master_vehicles', 'delivery_notes.id_master_vehicles', '=', 'master_vehicles.id')
            ->leftJoin('sales_orders as so', 'delivery_note_details.id_sales_orders', '=', 'so.id')
            ->select(
                'delivery_notes.id as id',
                'delivery_notes.dn_number',
                'delivery_notes.date',
                'delivery_notes.note',
                'delivery_notes.id_master_customers as id_customers',
                'delivery_notes.id_master_customer_address_shipping',
                'delivery_notes.id_master_customer_address_invoice',
                'master_customers.id as customer_id', // Tambahkan ID customer buat filter
                'master_customers.name as customer_name',
                'master_vehicles.id as vehicle_id',
                'master_vehicles.vehicle_number as vehicle_number',
                'so.so_number as so_number',
                'so.id as so_id'
            )
            ->where('delivery_notes.id', $id)
            ->first();

        // dd($id,$deliveryNote);

        // Ambil semua customer untuk dropdown
        $customers = DB::table('master_customers')->select('id', 'name')->get();
        //sono
        $soNo = DB::table('sales_orders')
            ->where('sales_orders.id_master_customers', $deliveryNote->id_customers)
            ->where('status', 'Posted')
            ->select('id', 'so_number')
            ->get();
        //address
        $shipAddress = DB::table('master_customer_addresses')
            ->where('master_customer_addresses.id', $deliveryNote->id_master_customer_address_shipping)
            ->select('id', 'address')
            ->get();
        $invAddress = DB::table('master_customer_addresses')
            ->where('master_customer_addresses.id', $deliveryNote->id_master_customer_address_invoice)
            ->select('id', 'address')
            ->get();
        // Ambil daftar packing list berdasarkan customer di delivery note
        $packingLists = DB::table('packing_lists')
            ->where('id_master_customers', $deliveryNote->id_customers)
            ->where('status', 'posted')
            ->select('id', 'packing_number')
            ->get();

        // Ambil semua kendaraan untuk dropdown
        $vehicles = DB::table('master_vehicles')->select('id', 'vehicle_number')->get();
        // Ambil detail delivery note
        $deliveryNoteDetails = DB::table('delivery_note_details')
            ->join('packing_lists', 'delivery_note_details.id_packing_lists', '=', 'packing_lists.id')
            ->join('sales_orders', 'delivery_note_details.id_sales_orders', '=', 'sales_orders.id')
            ->join('master_salesmen', 'delivery_note_details.id_master_salesman', '=', 'master_salesmen.id')
            ->select(
                'delivery_note_details.id_packing_lists',
                'packing_lists.packing_number',
                'delivery_note_details.dn_type',
                'delivery_note_details.remark',
                'delivery_note_details.transaction_type',
                DB::raw("IF(sales_orders.id_order_confirmations IS NULL OR sales_orders.id_order_confirmations = '-', sales_orders.reference_number, sales_orders.id_order_confirmations) as ko_po_no"),
                'master_salesmen.name as salesman_name'
            )
            ->where('delivery_note_details.id_delivery_notes', $id)
            ->get();

        return view('delivery_notes.edit', compact('deliveryNote', 'packingLists', 'vehicles',  'deliveryNoteDetails',  'customers', 'soNo', 'shipAddress', 'invAddress'));
    }

    public function update(Request $request, $id)
    {

        $validatedData = $request->validate([
            'dn_number' => 'required|unique:delivery_notes,dn_number,' . $id,
            'so_number' => 'required',
            'date' => 'required|date',
            'id_master_customer' => 'required',
            // 'id_master_salesman' => 'required',
            'id_master_vehicles' => 'required',
            'id_master_customer_address_shipping' => 'required',
            'id_master_customer_address_invoice' => 'required',
            'note' => 'nullable|string',

            // 'status' => 'required',
        ]);


        try {
            DB::beginTransaction();

            // Update data delivery_note
            DB::table('delivery_notes')
                ->where('id', $id)
                ->update([
                    'dn_number' => $validatedData['dn_number'],
                    'id_sales_orders' => $validatedData['so_number'],
                    'date' => $validatedData['date'],
                    'id_master_customers' => $validatedData['id_master_customer'],
                    // 'id_master_salesman' => $validatedData['id_master_salesman'],
                    'id_master_vehicles' => $validatedData['id_master_vehicles'],
                    'id_master_customer_address_shipping' => $validatedData['id_master_customer_address_shipping'],
                    'id_master_customer_address_invoice' => $validatedData['id_master_customer_address_invoice'],
                    'note' => $validatedData['note'] ?? null,

                    // 'status' => $validatedData['status'],
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Data berhasil diupdate']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function print($id)
    {
        $type = request()->query('type', 'DN');
        $prefix = match ($type) {
            'DS' => 'DS',
            'DR' => 'DR',
            default => 'DN',
        };

        // Mengambil data delivery note
        $deliveryNote = DB::table('delivery_notes')
            ->join('delivery_note_details', 'delivery_notes.id', '=', 'delivery_note_details.id_delivery_notes')
            ->join('sales_orders', 'delivery_notes.id_sales_orders', '=', 'sales_orders.id')
            ->join('master_customers', 'delivery_notes.id_master_customers', '=', 'master_customers.id')
            ->join('master_vehicles', 'delivery_notes.id_master_vehicles', '=', 'master_vehicles.id')
            ->join('master_salesmen', 'delivery_note_details.id_master_salesman', '=', 'master_salesmen.id')
            ->where('delivery_notes.id', $id)
            ->select(
                'delivery_notes.*',
                'delivery_note_details.type_product',
                DB::raw("IF(sales_orders.id_order_confirmations IS NULL OR sales_orders.id_order_confirmations = '-', sales_orders.reference_number, sales_orders.id_order_confirmations) as ko_po_no"),
                // 'sales_orders.so_category as dn_type',
                'master_customers.name as customer_name',
                'master_vehicles.vehicle_number as vehicle_number',
                'master_salesmen.name as salesman_name'
            )
            ->first();
        // dd($deliveryNote);
        if (!$deliveryNote) {
            // ID tidak ditemukan
            abort(404, 'Delivery note tidak ditemukan.');
        }



        $packingListDetails = DB::table('delivery_note_details')
            ->join('packing_lists', 'delivery_note_details.id_packing_lists', '=', 'packing_lists.id')
            ->join('packing_list_details', 'packing_lists.id', '=', 'packing_list_details.id_packing_lists')
            ->join('sales_orders', 'packing_lists.id_sales_orders', '=', 'sales_orders.id')
            ->leftJoin('master_product_fgs', function ($join) {
                $join->on('sales_orders.id_master_products', '=', 'master_product_fgs.id')
                    ->where('delivery_note_details.type_product', '=', 'FG');
            })
            ->leftJoin('master_wips', function ($join) {
                $join->on('sales_orders.id_master_products', '=', 'master_wips.id')
                    ->where('delivery_note_details.type_product', '=', 'WIP');
            })
            ->leftJoin('master_tool_auxiliaries', function ($join) {
                $join->on('sales_orders.id_master_products', '=', 'master_tool_auxiliaries.id')
                    ->where('delivery_note_details.type_product', '=', 'AUX');
            })
            ->leftJoin('master_raw_materials', function ($join) {
                $join->on('sales_orders.id_master_products', '=', 'master_raw_materials.id')
                    ->where('delivery_note_details.type_product', '=', 'RAW');
            })

            // Join ke master_units dari masing-masing master table
            ->leftJoin('master_units', function ($join) {
                $join->on('master_product_fgs.id_master_units', '=', 'master_units.id')
                    ->orOn('master_wips.id_master_units', '=', 'master_units.id')
                    ->orOn('master_tool_auxiliaries.id_master_units', '=', 'master_units.id')
                    ->orOn('master_raw_materials.id_master_units', '=', 'master_units.id');
            })

            ->select(
                DB::raw('SUM(packing_list_details.weight) as weight'),
                DB::raw('SUM(packing_list_details.pcs) as qty'),
                DB::raw('COALESCE(master_product_fgs.description, master_wips.description, master_tool_auxiliaries.description, master_raw_materials.description) as description'),
                DB::raw("COALESCE(master_product_fgs.perforasi, master_wips.perforasi, master_tool_auxiliaries.weight_stock, master_raw_materials.weight_stock,'p-') as perforasi"),
                DB::raw('COALESCE(master_product_fgs.product_code, master_wips.wip_code, master_tool_auxiliaries.description, master_raw_materials.description) as p_code'),
                DB::raw("IFNULL(sales_orders.cust_product_code,'-') as code"),
                'master_units.unit as unit',
                'sales_orders.id as soId',
                'sales_orders.so_category as dn_type',
                'sales_orders.id_master_products',
                'delivery_note_details.remark as remark'
            )
            ->where('delivery_note_details.id_delivery_notes', $id)
            ->groupBy('description', 'code', 'unit')
            ->get();

        //dd($packingListDetails);
        // dd($deliveryNote);
        // Menghitung total weight
        $totalWeight = $packingListDetails->sum('weight');
        //total qty
        $totalQty = $packingListDetails->sum('qty');
        // Mengambil alamat shipping dan invoice dari master_customer_addresses
        $idMs = DB::table('delivery_notes')
            ->where('delivery_notes.id', $id)
            ->select('id_master_customers')
            ->first();
        // $idMs);
        $Address = DB::table('master_customer_addresses')
            ->where('id_master_customers', $idMs->id_master_customers)
            ->select('type_address', 'address', 'postal_code')
            ->first();
        $sameAs = stripos($Address->type_address, 'Same As') !== false;
        if ($sameAs) {
            $invoiceAddress = $Address;
            $shippingAddress = $Address;
        } else {
            $invoiceAddress = DB::table('master_customer_addresses')
                ->where('id', $deliveryNote->id_master_customer_address_invoice)
                ->select('address', 'postal_code')
                ->first();
            $shippingAddress = DB::table('master_customer_addresses')
                ->where('id', $deliveryNote->id_master_customer_address_shipping)
                ->select('address', 'postal_code')
                ->first();
        }
        // Data untuk dicetak
        $data = [
            'deliveryNote' => $deliveryNote,
            'packingListDetails' => $packingListDetails,
            'totalWeight' => $totalWeight,
            'totalQty' => $totalQty,
            'shippingAddress' => $shippingAddress,
            'invoiceAddress' => $invoiceAddress,
            'prefix' => $prefix,
        ];

        // Menampilkan view cetak dengan data
        return view('delivery_notes.print', $data);
    }

    public function updateRemark(Request $request, $id)
    {
        $request->validate([
            'remark' => 'required|string|max:255',
        ]);

        DB::table('delivery_note_details')
            ->where('id_packing_lists', $id)
            ->update(['remark' => $request->remark, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }


    public function getSoNumberByCustomer($customerId, Request $request)
    {
        $search = $request->input('search'); // Ambil input search dari AJAX

        $soNo = DB::table('sales_orders')
            ->where('sales_orders.id_master_customers', $customerId)
            ->where('status', 'Posted')
            ->when($search, function ($query, $search) {
                $query->where('so_number', 'like', '%' . $search . '%');
            })
            ->select('id', 'so_number', 'id_order_confirmations', 'reference_number')
            ->get();

        return response()->json($soNo);
    }


    public function getPackingListsByCustomer($customerId)
    {
        $packingLists = DB::table('packing_lists')
            ->join('sales_orders', 'packing_lists.id_sales_orders', '=', 'sales_orders.id')
            ->join('master_customers', 'packing_lists.id_master_customers', '=', 'master_customers.id')
            ->where('master_customers.id', $customerId)
            ->where('packing_lists.status', 'Posted')
            ->where('sales_orders.status', 'Posted')
            ->select('packing_lists.id', 'packing_lists.packing_number', 'sales_orders.id_order_confirmations', 'sales_orders.reference_number')
            ->get();
        // dd($packingLists);


        return response()->json($packingLists);
    }
    public function deletePackingList($id)
    {
        DB::beginTransaction();

        try {
            // Hapus data dari tabel delivery_note_details
            DB::table('delivery_note_details')
                ->where('id_packing_lists', $id)
                ->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Packing list berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Hapus data di tabel delivery_note_details
            DB::table('delivery_note_details')->where('id_delivery_notes', $id)->delete();

            // Hapus data di tabel delivery_notes
            DB::table('delivery_notes')->where('id', $id)->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Delivery Note berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus Delivery Note: ' . $e->getMessage()]);
        }
    }
    public function printPackingList($id)
    {
        // Mengambil data delivery note
        $deliveryNote = DB::table('delivery_notes')
            ->join('master_customers', 'delivery_notes.id_master_customers', '=', 'master_customers.id')
            ->join('master_vehicles', 'delivery_notes.id_master_vehicles', '=', 'master_vehicles.id')
            ->select(
                'delivery_notes.*',
                'master_customers.name as customer_name',
                'master_vehicles.vehicle_number as vehicle_number'
            )
            ->where('delivery_notes.id', $id)
            ->first();

        // Mengambil data packing list dan detailnya
        $packingLists = DB::table('packing_lists')
            ->join('delivery_note_details', 'packing_lists.id', '=', 'delivery_note_details.id_packing_lists')
            ->select(
                'packing_lists.*'
            )
            ->where('delivery_note_details.id_delivery_notes', $id)
            ->groupBy('packing_lists.id')
            ->get();

        return view('delivery_notes.print_packing_list', compact('deliveryNote', 'packingLists'));
    }
    public function post($id)
    {
        // ----- 1. Ambil DN; 404 kalau tak ada -----
        $dn = DeliveryNote::findOrFail($id);


        // ----- 2. Rekap qty per SO -----
        $rekap = DB::table('delivery_note_details as dnd')
            ->join('sales_orders as so', 'dnd.id_sales_orders', '=', 'so.id')
            ->join('packing_list_details as pld', 'dnd.id_packing_lists', '=', 'pld.id_packing_lists')
            ->join('packing_lists as pl', 'pld.id_packing_lists', '=', 'pl.id')
            ->select(
                'dnd.id_sales_orders as so_id',
                'so.qty as so_qty',
                'pl.packing_number as pl_no',
                DB::raw('SUM(pld.pcs) AS pl_qty')
            )
            ->where('dnd.id_delivery_notes', $dn->id)
            ->groupBy('so_id', 'so_qty')
            ->get();
        // dd($rekap);

        // ----- 3. Pisahkan match & mismatch (abaikan tipe) -----
        [$match, $mismatch] = $rekap->partition(
            fn($r) => (int) $r->pl_qty === (int) $r->so_qty
        );

        // ----- 4. Jika ADA mismatch → alert & tidak ubah status apa pun -----
        if ($mismatch->isNotEmpty()) {
            $plNo = $mismatch->pluck('pl_no')->implode(', ');

            return back()->with([
                'alert' => "packing list dengan no : {$plNo} memiliki selisih qty sales_orders",
            ]);
        }


        // ----- 5. Semua match → jalankan perubahan di satu transaction -----
        DB::transaction(function () use ($dn, $match) {

            // 5a. Update status SO menjadi Closed
            SalesOrder::whereIn('id', $match->pluck('so_id'))
                ->update(['status' => 'Closed']);

            // 5b. Update status DN menjadi Posted
            $dn->status = 'Posted';
            $dn->save();
        });

        // ----- 6. Redirect dengan pesan sukses -----
        return redirect()
            ->route('delivery_notes.list')
            ->with('pesan', "Delivery Note #{$dn->id} berhasil di‑POST dan semua SO ditutup.");
    }

    public function unpost($id)
    {
        /* 1. Pastikan DN memang sudah Posted */
        $dn = DeliveryNote::where('status', 'Posted')->findOrFail($id);

        /* 2. Dapatkan semua SO yang terkait DN ini */
        $soIds = DB::table('delivery_note_details')
            ->where('id_delivery_notes', $dn->id)
            ->pluck('id_sales_orders')
            ->unique();               // contoh: [12, 15]

        /* 3. Jalankan perubahan dalam satu transaksi */
        DB::transaction(function () use ($dn, $soIds) {

            /* 3a. Turunkan status DN kembali ke “Request” */
            $dn->status = 'Request';          // atau label lain jika ingin
            $dn->save();

            /* 3b. “Buka” kembali semua SO tersebut */
            if ($soIds->isNotEmpty()) {
                SalesOrder::whereIn('id', $soIds)
                    ->update(['status' => 'Posted']);   // ← status setelah di‑un‑post
            }
        });

        /* 4. Redirect dengan pesan sukses */
        return redirect()
            ->route('delivery_notes.list')
            ->with('pesan', "Delivery Note #{$dn->id} berhasil di‑UN‑POST dan status SO dibuka kembali.");
    }
}
