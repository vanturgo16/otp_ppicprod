@extends('layouts.print')

@section('content')
<style>
    .header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .signature-row {
        display: flex;
        justify-content: space-between;
        margin-top: 50px;
    }

    .signature {
        text-align: center;
        width: 40%;
    }

    .signature p {
        margin-bottom: 100px;
    }

    .signature-name {
        margin-top: 50px;
        font-weight: bold;
    }
</style>

<div class="page-content">
    <div class="container-fluid">
        <div class="header-row">
            <div>
                <h3>PT OLEFINA TIFAPLAS POLIKEMINDO</h3>
            </div>
            <div>
                <p>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-12 text-center">
                <h2>SURAT PENGANTAR</h2>
                <h3>{{ $deliveryNote->dn_number }}</h3>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <div class="float-left">
                    <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($deliveryNote->date)->translatedFormat('d F Y') }}</p>
                    <p><strong>No. PO:</strong> {{ $deliveryNote->po_number }}</p>
                    <p><strong>No. Pol. Kendaraan:</strong> {{ $deliveryNote->vehicle }}</p>
                </div>
                <div class="float-right">
                    <p><strong>Customer:</strong> {{ $deliveryNote->customer_name }}</p>
                    <p><strong>Cust. Product Code:</strong> -</p>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Perforasi</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Weight</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($details as $detail)
                        <tr>
                            <td>{{ $detail->description }}</td>
                            <td>{{ $detail->perforasi ?? '-' }}</td>
                            <td>{{ $detail->qty }}</td>
                            <td>{{ $detail->unit }}</td>
                            <td>{{ $detail->weight }} KG</td>
                            <td>{{ $detail->remark ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <p><strong>Note:</strong> {{ $deliveryNote->note }}</p>
            </div>
        </div>
        <div class="signature-row">
            <div class="signature">
                <p>Diterima</p>
                <p>(....................)</p>
            </div>
            <div class="signature">
                <p>Pengemudi,</p>
                <p>(....................)</p>
            </div>
            <div class="signature">
                <p>Security,</p>
                <p>(....................)</p>
            </div>
            <div class="signature">
                <p>Pembuat,</p>
                <p>(....................)</p>
            </div>
        </div>
    </div>
</div>
@endsection