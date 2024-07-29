<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MstCustomers;
use App\Models\Warehouse\PackingList;
use App\Models\Warehouse\DeliveryNote;
use DataTables;

class DeliveryNoteController extends Controller
{

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('delivery_notes')
                ->leftJoin('delivery_note_details', 'delivery_notes.id', '=', 'delivery_note_details.id_delivery_notes')
                ->leftJoin('packing_lists', 'delivery_note_details.id_packing_lists', '=', 'packing_lists.id')
                ->join('master_vehicles', 'delivery_notes.id_master_vehicles', '=', 'master_vehicles.id')
                ->leftJoin('sales_orders', 'delivery_note_details.id_sales_orders', '=', 'sales_orders.id')
                ->select(
                    'delivery_notes.*',
                    'delivery_note_details.dn_type', // Pastikan kolom ini disertakan
                    'delivery_note_details.transaction_type', // Pastikan kolom ini disertakan
                    DB::raw('GROUP_CONCAT(DISTINCT packing_lists.packing_number SEPARATOR ", ") as packing_numbers'),
                    'master_vehicles.vehicle_number as vehicle',
                    'sales_orders.reference_number as po_number'
                )
                ->groupBy('delivery_notes.id')
                ->orderBy('delivery_notes.created_at', 'desc')
                ->get();

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

    public function create()
    {
        $currentDate = now()->format('ym');
        $lastDn = DB::table('delivery_notes')
            ->where('dn_number', 'like', 'DN' . $currentDate . '%')
            ->orderBy('dn_number', 'desc')
            ->first();

        if ($lastDn) {
            $lastDnNumber = $lastDn->dn_number;
            $lastSequence = intval(substr($lastDnNumber, -6));
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        $dnNumber = 'DN' . $currentDate . str_pad($nextSequence, 6, '0', STR_PAD_LEFT);
        $packingLists = DB::table('packing_lists')->select('id', 'packing_number')->get();
        $vehicles = DB::table('master_vehicles')->select('id', 'vehicle_number')->get();
        $salesmen = DB::table('master_salesmen')->select('id', 'name')->get();
        $customers = DB::table('master_customers')->select('id', 'name')->get();

        return view('delivery_notes.create', compact('dnNumber', 'packingLists', 'vehicles', 'salesmen', 'customers'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validasi input
            $validatedData = $request->validate([
                'dn_number' => 'required|unique:delivery_notes,dn_number',
                'date' => 'required|date',
                'id_master_customer' => 'required',
                'id_master_vehicle' => 'required',
                'id_master_customer_address_shipping' => 'required',
                'id_master_customer_address_invoice' => 'required',
                'note' => 'nullable|string',
            ]);

            // Insert data ke tabel delivery_notes
            $deliveryNoteId = DB::table('delivery_notes')->insertGetId([
                'dn_number' => $validatedData['dn_number'],
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
            ]);

            // Cek apakah packing list sudah ada di dalam delivery_note_details
            $existingPackingList = DB::table('delivery_note_details')
                // ->where('id_delivery_notes', $id)
                ->where('id_packing_lists', $validatedData['packing_list_id'])
                ->exists();

            if ($existingPackingList) {
                throw new \Exception('Packing list sudah ada di dalam delivery note detail.');
            }

            // Mengambil data quantity dan weight dari packing list details dan produksi
            $packingListDetails = DB::table('packing_list_details')
                ->join('barcode_detail', 'packing_list_details.barcode', '=', 'barcode_detail.barcode_number')
                ->join('barcodes', 'barcode_detail.id_barcode', '=', 'barcodes.id')
                ->join('sales_orders', 'barcodes.id_sales_orders', '=', 'sales_orders.id')
                ->join('master_product_fgs', 'sales_orders.id_master_products', '=', 'master_product_fgs.id')
                ->join('master_units', 'master_product_fgs.id_master_units', '=', 'master_units.id')
                ->leftJoin('report_sf_production_results', 'packing_list_details.barcode', '=', 'report_sf_production_results.barcode')
                ->leftJoin('report_blow_production_results', 'packing_list_details.barcode', '=', 'report_blow_production_results.barcode')
                ->select(
                    DB::raw('COUNT(packing_list_details.barcode) as total_qty'),
                    DB::raw('SUM(CASE 
                        WHEN packing_list_details.barcode LIKE "%B" THEN packing_list_details.weight
                        ELSE COALESCE(report_sf_production_results.weight, report_blow_production_results.weight, 0)
                    END) as total_weight'),
                    'master_units.id as id_master_unit',
                    'sales_orders.id as id_sales_order',
                    'sales_orders.id_master_salesmen as id_master_salesman'
                )
                ->where('packing_list_details.id_packing_lists', $validatedData['packing_list_id'])
                ->groupBy('master_units.id', 'sales_orders.id', 'sales_orders.id_master_salesmen')
                ->first();

            if (!$packingListDetails) {
                throw new \Exception('Packing list details not found');
            }

            // Menyimpan data ke dalam tabel delivery_note_details
            DB::table('delivery_note_details')->insert([
                'id_delivery_notes' => $id,
                'id_sales_orders' => $packingListDetails->id_sales_order,
                'id_packing_lists' => $validatedData['packing_list_id'],
                'qty' => $packingListDetails->total_qty,
                'id_master_units' => $packingListDetails->id_master_unit,
                'weight' => $packingListDetails->total_weight,
                'po_number' => $request->po_number,
                'dn_type' => $request->dn_type,
                'transaction_type' => $request->transaction_type,
                'id_master_salesman' => $packingListDetails->id_master_salesman,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'po_number' => $request->po_number,
                'dn_type' => $request->dn_type,
                'transaction_type' => $request->transaction_type,
                'salesman_name' => $request->salesman_name,
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
                'master_salesmen.name as salesman_name'
            )
            ->where('packing_list_details.id_packing_lists', $id)
            ->first();

        return response()->json($details);
    }

    public function getCustomerAddresses($customerId, $type)
    {
        $addresses = DB::table('master_customer_addresses')
            ->where('id_master_customers', $customerId)
            ->where(function ($query) use ($type) {
                $query->where('type_address', $type)
                    ->orWhere('type_address', 'Same As (Invoice, Shipping)');
            })
            ->get();
        return response()->json($addresses);
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

        $packingLists = DB::table('packing_lists')
            ->where('id_master_customers', $deliveryNote->id_master_customers)
            ->select('id', 'packing_number')
            ->get();

        $vehicles = DB::table('master_vehicles')->select('id', 'vehicle_number')->get();

        $customerAddresses = DB::table('master_customer_addresses')
            ->where('id_master_customers', $deliveryNote->id_master_customers)
            ->select('id', 'address')
            ->get();

        $deliveryNoteDetails = DB::table('delivery_note_details')
            ->join('packing_lists', 'delivery_note_details.id_packing_lists', '=', 'packing_lists.id')
            ->join('sales_orders', 'delivery_note_details.id_sales_orders', '=', 'sales_orders.id')
            ->join('master_salesmen', 'delivery_note_details.id_master_salesman', '=', 'master_salesmen.id')
            ->select(
                'delivery_note_details.id_packing_lists', // Pastikan id disertakan di sini
                'packing_lists.packing_number',
                'sales_orders.reference_number as po_number',
                'delivery_note_details.dn_type',
                'delivery_note_details.remark',
                'delivery_note_details.transaction_type',
                'master_salesmen.name as salesman_name'
            )
            ->where('delivery_note_details.id_delivery_notes', $id)
            ->get();

        return view('delivery_notes.edit', compact('deliveryNote', 'packingLists', 'vehicles', 'customerAddresses', 'deliveryNoteDetails'));
    }




    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'dn_number' => 'required|unique:delivery_notes,dn_number,' . $id,
            'date' => 'required|date',
            // 'id_master_customer' => 'required',
            // 'id_master_salesman' => 'required',
            'id_master_vehicle' => 'required',
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
                    'date' => $validatedData['date'],
                    // 'id_master_customers' => $validatedData['id_master_customer'],
                    // 'id_master_salesman' => $validatedData['id_master_salesman'],
                    'id_master_vehicles' => $validatedData['id_master_vehicle'],
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
        $prefix = '';
        switch ($type) {
            case 'DS':
                $prefix = 'DS';
                break;
            case 'DR':
                $prefix = 'DR';
                break;
            default:
                $prefix = 'DN';
                break;
        }

        // Mengambil data delivery note
        $deliveryNote = DB::table('delivery_notes')
            ->join('delivery_note_details', 'delivery_notes.id', '=', 'delivery_note_details.id_delivery_notes')
            ->join('sales_orders', 'delivery_note_details.id_sales_orders', '=', 'sales_orders.id')
            ->join('master_customers', 'delivery_notes.id_master_customers', '=', 'master_customers.id')
            ->join('master_vehicles', 'delivery_notes.id_master_vehicles', '=', 'master_vehicles.id')
            ->join('master_salesmen', 'delivery_note_details.id_master_salesman', '=', 'master_salesmen.id')
            ->where('delivery_notes.id', $id)
            ->select(
                'delivery_notes.*',
                'sales_orders.reference_number as sales_order_po_number',
                'sales_orders.so_category as dn_type',
                'master_customers.name as customer_name',
                'master_vehicles.vehicle_number as vehicle_number',
                'master_salesmen.name as salesman_name'
            )
            ->first();

        // Mengambil data quantity dan weight dari delivery_note_details
        $packingListDetails = DB::table('delivery_note_details')
            ->join('sales_orders', 'delivery_note_details.id_sales_orders', '=', 'sales_orders.id')
            ->join('master_product_fgs', 'sales_orders.id_master_products', '=', 'master_product_fgs.id')
            ->join('master_units', 'delivery_note_details.id_master_units', '=', 'master_units.id')
            ->where('delivery_note_details.id_delivery_notes', $id)
            ->select(
                'delivery_note_details.qty as qty',
                'delivery_note_details.weight as weight',
                'master_units.unit_code as unit_name',
                'master_product_fgs.product_code as product_name',
                'master_product_fgs.description as product_description',
                'delivery_note_details.perforasi as perforasi',
                'delivery_note_details.remark as remark'
            )
            ->get();

        // Menghitung total weight
        $totalWeight = $packingListDetails->sum('weight');

        // Mengambil alamat shipping dan invoice dari master_customer_addresses
        $shippingAddress = DB::table('master_customer_addresses')
            ->where('id', $deliveryNote->id_master_customer_address_shipping)
            ->select('address')
            ->first();

        $invoiceAddress = DB::table('master_customer_addresses')
            ->where('id', $deliveryNote->id_master_customer_address_invoice)
            ->select('address')
            ->first();

        // Data untuk dicetak
        $data = [
            'deliveryNote' => $deliveryNote,
            'packingListDetails' => $packingListDetails,
            'totalWeight' => $totalWeight,
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
    public function getPackingListsByCustomer($customerId)
    {
        $packingLists = DB::table('packing_lists')
            ->join('sales_orders', 'packing_lists.id_master_customers', '=', 'sales_orders.id_master_customers')
            ->where('sales_orders.id_master_customers', $customerId)
            ->where('packing_lists.status', 'Posted')
            ->select('packing_lists.id', 'packing_lists.packing_number')
            ->get();

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
}
