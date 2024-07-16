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
                <h2>PACKING LIST</h2>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <div class="float-left">
                    <p><strong>Nomor:</strong> {{ $packingList->packing_number }}</p>
                    <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($packingList->date)->format('d/m/Y') }}</p>
                    <p><strong>Customer:</strong> {{ $packingList->customer_name }}</p>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Barcode</th>
                            <th>No. SO</th>
                            <th>Isi Dus</th>
                            <th>Berat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $totalRoll = 0;
                        $totalWeight = 0;
                        @endphp
                        @foreach ($details as $index => $detail)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $detail->product_code }}</td>
                            <td>{{ $detail->description }}</td>
                            <td>{{ $detail->barcode_number }}</td>
                            <td>{{ $detail->so_number }}</td>
                            <td>{{ substr($detail->barcode_number, -1) === 'B' ? $detail->pcs . ' PCS' : '0 ' . $detail->unit }}</td>
                            <td>{{ substr($detail->barcode_number, -1) === 'B' ? ($detail->weight ?? 'N/A') : ($detail->production_weight ?? 'N/A') }} KG</td>
                            @php
                            $totalRoll += substr($detail->barcode_number, -1) === 'B' ? $detail->pcs : 0;
                            $totalWeight += substr($detail->barcode_number, -1) === 'B' ? $detail->weight : $detail->production_weight;
                            @endphp
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12" style="float: right; text-align: right;">
                <p><strong>Subtotal:</strong> {{ $totalRoll }} {{$detail->unit}}</p>
                <p><strong>Total Berat:</strong> {{ number_format($totalWeight, 2) }} KG</p>
            </div>
        </div>
        <div class="signature-row">
            <div class="signature">
                <p>Dibuat Oleh,</p>
                <p class="signature-name">({{ auth()->user()->name }})</p>
            </div>
            <div class="signature">
                <p>Diterima Oleh,</p>
                <p class="signature-name">..........................</p>
            </div>
        </div>
    </div>
</div>
@endsection