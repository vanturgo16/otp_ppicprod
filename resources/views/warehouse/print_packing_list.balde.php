@extends('layouts.print')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 text-center">
                <h4 class="font-size-18">PACKING LIST</h4>
                <div class="mt-4">
                    <p><strong>Nomor:</strong> {{ $packingList->packing_number }}</p>
                    <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($packingList->date)->format('d/m/Y') }}</p>
                    <p><strong>Customer:</strong> {{ $packingList->customer_name }}</p>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Cust. Product Code</th>
                            <th>No. Lot</th>
                            <th>No. SO</th>
                            <th>Isi Dus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($details as $index => $detail)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $detail->product_code }}</td>
                            <td>{{ $detail->description }}</td>
                            <td>{{ $detail->cust_product_code }}</td>
                            <td>{{ $detail->barcode_number }}</td>
                            <td>{{ $detail->so_number }}</td>
                            <td>1 {{ $detail->unit }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection