@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Print Packing List</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('delivery_notes.list') }}">Warehouse</a></li>
                            <li class="breadcrumb-item active">Print Packing List</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Delivery Note: {{ $deliveryNote->dn_number }}</h4>
                        <p>Date: {{ $deliveryNote->date }}</p>
                        <p>Customer: {{ $deliveryNote->customer_name }}</p>
                        <p>Vehicle: {{ $deliveryNote->vehicle_number }}</p>

                        @foreach($packingLists as $packingList)
                        <div class="mt-4">
                            <h5>Packing List: {{ $packingList->packing_number }}</h5>
                            <p>Date: {{ $packingList->date }}</p>
                        </div>
                        @endforeach

                        <div class="mt-4">
                            <button class="btn btn-primary" onclick="window.print()">Print</button>
                            <a href="{{ route('delivery_notes.list') }}" class="btn btn-secondary">Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection