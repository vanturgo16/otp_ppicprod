@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Detail Delivery Notes</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Warehouse</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('delivery_notes.list') }}">Delivery Notes</a></li>
                            <li class="breadcrumb-item active">Detail Delivery Notes</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Detail Delivery Notes</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th>DN Number</th>
                                <td>{{ $deliveryNote->dn_number }}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{{ \Carbon\Carbon::parse($deliveryNote->date)->locale('id')->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <th>Packing Number</th>
                                <td>{{ $deliveryNote->packing_number }}</td>
                            </tr>
                            <tr>
                                <th>PO Number</th>
                                <td>{{ $deliveryNote->po_number }}</td>
                            </tr>
                            <tr>
                                <th>Nama Customer</th>
                                <td>{{ $deliveryNote->customer_name }}</td>
                            </tr>
                            <tr>
                                <th>DN Type</th>
                                <td>{{ $deliveryNote->dn_type }}</td>
                            </tr>
                            <tr>
                                <th>Transaction Type</th>
                                <td>{{ $deliveryNote->transaction_type }}</td>
                            </tr>
                            <tr>
                                <th>Salesman</th>
                                <td>{{ $deliveryNote->salesman_name }}</td>
                            </tr>
                            <tr>
                                <th>Vehicles</th>
                                <td>{{ $deliveryNote->vehicle }}</td>
                            </tr>
                            <tr>
                                <th>Note</th>
                                <td>{{ $deliveryNote->note }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{{ $deliveryNote->status }}</td>
                            </tr>
                        </table>
                        <a href="{{ route('delivery_notes.list') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection