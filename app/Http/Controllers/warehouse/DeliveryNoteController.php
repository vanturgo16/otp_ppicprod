<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MstCustomers;
use App\Models\Warehouse\PackingList;
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

    public function post($id)
    {
        DB::table('delivery_notes')->where('id', $id)->update(['status' => 'Posted']);

        return redirect()->route('delivery_notes.list')->with('pesan', 'Delivery Note berhasil diposting');
    }

    public function unpost($id)
    {
        DB::table('delivery_notes')->where('id', $id)->update(['status' => 'Request']);

        return redirect()->route('delivery_notes.list')->with('pesan', 'Delivery Note berhasil di-unpost');
    }

    public function destroy($id)
    {
        DB::table('delivery_notes')->where('id', $id)->delete();

        return redirect()->route('delivery_notes.list')->with('pesan', 'Delivery Note berhasil dihapus');
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
}
