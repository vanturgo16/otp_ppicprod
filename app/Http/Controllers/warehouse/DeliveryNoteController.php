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
                ->join('packing_lists', 'delivery_notes.id_packing_lists', '=', 'packing_lists.id')
                ->join('master_vehicles', 'delivery_notes.id_master_vehicles', '=', 'master_vehicles.id')
                ->select('delivery_notes.*', 'packing_lists.packing_number', 'master_vehicles.vehicle_number as vehicle')
                ->orderBy('delivery_notes.created_at', 'desc') // Mengatur urutan berdasarkan created_at secara descending
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

        $buttons .= '<a href="/print/' . $data->id . '" class="btn btn-sm btn-secondary"><i class="bx bx-printer"></i> Print</a>';

        if ($data->status == 'Request') {
            $buttons .= '<a href="/delivery_notes/' . $data->id . '/edit" class="btn btn-sm btn-warning"><i class="bx bx-edit"></i> Edit</a>';
            $buttons .= '<form action="/delivery_notes/' . $data->id . '" method="post" class="d-inline" data-id="">' . csrf_field() . '<input type="hidden" name="_method" value="DELETE"><button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Anda yakin mau menghapus item ini ?\')"><i class="bx bx-trash"></i> Delete</button></form>';
        }

        $buttons .= '</div>';

        return $buttons;
    }
    public function create()
    {
        // Mendapatkan tahun dan bulan saat ini dalam format 'ym'
        $currentDate = now()->format('ym');

        // Mendapatkan DN terakhir yang dibuat untuk tahun dan bulan saat ini
        $lastDn = DB::table('delivery_notes')
            ->where('dn_number', 'like', 'DN' . $currentDate . '%')
            ->orderBy('dn_number', 'desc')
            ->first();

        // Debugging
        if ($lastDn) {
            $lastDnNumber = $lastDn->dn_number;
            $lastSequence = intval(substr($lastDnNumber, -6));;
            $nextSequence = $lastSequence + 1;;
        } else {
            $nextSequence = 1;
        }

        // Membentuk DN Number baru
        $dnNumber = 'DN' . $currentDate . str_pad($nextSequence, 6, '0', STR_PAD_LEFT);

        // Mendapatkan data packing lists, vehicles, dan salesmen
        $packingLists = DB::table('packing_lists')->select('id', 'packing_number')->get();
        $vehicles = DB::table('master_vehicles')->select('id', 'vehicle_number')->get();
        $salesmen = DB::table('master_salesmen')->select('id', 'name')->get();
        return view('delivery_notes.create', compact('dnNumber', 'packingLists', 'vehicles', 'salesmen'));
    }
    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'dn_number' => 'required|unique:delivery_notes,dn_number',
            'id_packing_lists' => 'required|unique:delivery_notes,id_packing_lists',
            'date' => 'required|date',
            'id_sales_order' => 'required', // id_sales_order diubah dari po_number
            'id_master_customer' => 'required',
            'customer_name' => 'required',
            'dn_type' => 'required',
            'transaction_type' => 'required',
            'id_master_salesman' => 'required',
            'salesman_name' => 'required',
            'id_master_vehicle' => 'required',
            'status' => 'required',
        ], [
            'dn_number.unique' => 'Nomor DN sudah ada dan tidak bisa diinput lagi.',
            'id_packing_lists.unique' => 'Nomor Packing sudah ada dan tidak bisa diinput lagi.'
        ]);

        // Menyimpan data baru ke dalam database
        DB::table('delivery_notes')->insert([
            'dn_number' => $validatedData['dn_number'],
            'date' => $validatedData['date'],
            'id_packing_lists' => $validatedData['id_packing_lists'],
            'po_number' => $validatedData['id_sales_order'], // simpan id_sales_order
            'id_master_customers' => $validatedData['id_master_customer'],
            'dn_type' => $validatedData['dn_type'],
            'transaction_type' => $validatedData['transaction_type'],
            'id_master_salesman' => $validatedData['id_master_salesman'],
            'id_master_vehicles' => $validatedData['id_master_vehicle'],
            'note' => $validatedData['note'] ?? null,
            'status' => $validatedData['status'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('delivery_notes.list')->with('pesan', 'Delivery Note berhasil ditambahkan.');
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
                'sales_orders.id as sales_order_id',
                'sales_orders.reference_number',
                'sales_orders.so_number',
                'sales_orders.so_category',
                'sales_orders.so_type',
                'master_salesmen.id as salesman_id',
                'master_salesmen.name as salesman_name',
                'master_customers.id as customer_id',
                'master_customers.name as customer_name'
            )
            ->where('packing_list_details.id_packing_lists', $id)
            ->first();

        return response()->json($details);
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
            ->join('packing_lists', 'delivery_notes.id_packing_lists', '=', 'packing_lists.id')
            ->join('sales_orders', 'delivery_notes.po_number', '=', 'sales_orders.id') // Assuming po_number in delivery_notes stores sales_order_id
            ->join('master_customers', 'sales_orders.id_master_customers', '=', 'master_customers.id')
            ->join('master_salesmen', 'sales_orders.id_master_salesmen', '=', 'master_salesmen.id')
            ->join('master_vehicles', 'delivery_notes.id_master_vehicles', '=', 'master_vehicles.id')
            ->select(
                'delivery_notes.*',
                'packing_lists.packing_number',
                'sales_orders.reference_number',
                'sales_orders.so_number',
                'master_customers.name as customer_name',
                'master_salesmen.name as salesman_name',
                'master_vehicles.vehicle_number'
            )
            ->where('delivery_notes.id', $id)
            ->first();

        $packingLists = DB::table('packing_lists')->select('id', 'packing_number')->get();
        $vehicles = DB::table('master_vehicles')->select('id', 'vehicle_number')->get();

        return view('delivery_notes.edit', compact('deliveryNote', 'packingLists', 'vehicles'));
    }


    // Fungsi untuk memperbarui data
    public function update(Request $request, $id)
    {
        // Validasi input
        $validatedData = $request->validate([
            'dn_number' => 'required|unique:delivery_notes,dn_number,' . $id,
            'id_packing_lists' => 'required|unique:delivery_notes,id_packing_lists,' . $id,
            'date' => 'required|date',
            'po_number' => 'required',
            'id_master_customer' => 'required',
            'customer_name' => 'required',
            'dn_type' => 'required',
            'transaction_type' => 'required',
            'id_master_salesman' => 'required',
            'salesman_name' => 'required',
            'id_master_vehicle' => 'required',
            'status' => 'required',
        ], [
            'dn_number.unique' => 'Nomor DN sudah ada dan tidak bisa diinput lagi.',
            'id_packing_lists.unique' => 'Nomor Packing sudah ada dan tidak bisa diinput lagi.'
        ]);

        // Menyimpan data baru ke dalam database
        $deliveryNote = DeliveryNote::findOrFail($id);
        $deliveryNote->update([
            'dn_number' => $validatedData['dn_number'],
            'date' => $validatedData['date'],
            'id_packing_lists' => $validatedData['id_packing_lists'],
            'po_number' => $validatedData['po_number'],
            'id_master_customer' => $validatedData['id_master_customer'],
            'dn_type' => $validatedData['dn_type'],
            'transaction_type' => $validatedData['transaction_type'],
            'id_master_salesman' => $validatedData['id_master_salesman'],
            'id_master_vehicle' => $validatedData['id_master_vehicle'],
            'note' => $validatedData['note'] ?? null,
            'status' => $validatedData['status'],
            'updated_at' => now(),
        ]);

        return redirect()->route('delivery_notes.list')->with('pesan', 'Delivery Note berhasil diperbarui.');
    }
}
