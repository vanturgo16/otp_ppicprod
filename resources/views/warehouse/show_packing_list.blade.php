<!-- resources/views/warehouse/show_packing_list.blade.php -->
@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12">
                <a href="{{ route('packing-list') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back To List Data Packing List
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Detail Packing List</h4>
                        <div class="mb-3">
                            <label for="packing_number" class="form-label">Packing Number</label>
                            <p>{{ $packingList->packing_number }}</p>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <p>{{ \Carbon\Carbon::parse($packingList->date)->format('F d, Y') }}</p>
                        </div>
                        <div class="mb-3">
                            <label for="customer" class="form-label">Customers</label>
                            <p>{{ $packingList->customer_name }}</p>
                        </div>
                        <div class="mb-3">
                            <label for="all_barcodes" class="form-label">All Barcodes</label>
                            <p>{{ $packingList->all_barcodes }}</p>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <p>{{ $packingList->status }}</p>
                        </div>
                        <h5 class="card-title">Packing List Detail</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Change SO</th>
                                    <th>Barcode</th>
                                    <th>Product Name</th>
                                    <th>Waste</th>
                                    <th>Weight</th>
                                    <th>PCS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($details as $detail)
                                <tr>
                                    <td>{{ $detail->change_so }}</td>
                                    <td>{{ $detail->barcode }}</td>
                                    <td>{{ $detail->description }}</td>
                                    <td>{{ $detail->number_of_box }}</td>
                                    <td>{{ $detail->weight }}</td>
                                    <td>{{ $detail->pcs }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <a href="{{ route('packing-list') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection