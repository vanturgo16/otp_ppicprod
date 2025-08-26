<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse\PackingList;
use App\Models\Warehouse\DeliveryNote;
class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:PPIC']);
       
    }
    public function index(){
        // --- Packing List ---
        $packingRequest = PackingList::where('status', 'Request')->count() ?? 0;
        $packingPosted  = PackingList::where('status', 'Posted')->count() ?? 0;
        $packingClosed  = PackingList::where('status', 'Closed')->count() ?? 0;
        $packingTotal   = PackingList::count() ?? 0;

        // --- Delivery Note ---
        $deliveryRequest = DeliveryNote::where('status', 'Request')->count() ?? 0;
        $deliveryPosted  = DeliveryNote::where('status', 'Posted')->count() ?? 0;
        $deliveryClosed  = DeliveryNote::where('status', 'Closed')->count() ?? 0;
        $deliveryTotal   = DeliveryNote::count() ?? 0;

        // kirim ke view
        return view('dashboard.index', compact(
            'packingRequest',
            'packingPosted',
            'packingClosed',
            'packingTotal',
            'deliveryRequest',
            'deliveryPosted',
            'deliveryClosed',
            'deliveryTotal'
        ));
    }
}
