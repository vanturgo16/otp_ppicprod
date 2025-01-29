@extends('layouts.print')

@section('content')
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 95%;
        margin: 0 auto;
        padding: 20px;
        position: relative;
    }

    .header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .header-left {
        display: flex;
        align-items: center;
        width: 30%;
    }

    .header-left img {
        margin-right: 10px;
    }

    .header-center {
        text-align: center;
        width: 40%;
    }

    .header-right {
        text-align: right;
        width: 30%;
        font-size: 10px;
    }

    .header-left h1 {
        margin: 0;
        font-size: 14px;
        font-weight: bold;
    }

    .header-left p {
        margin: 0;
        line-height: 1.5;
        font-size: 10px;
    }

    .header-center p {
        margin: 0;
        font-size: 14px;
        font-weight: bold;
    }

    .header-center p.title {
        font-size: 16px;
    }

    .info-table {
        width: 100%;
        margin-bottom: 20px;
        table-layout: fixed;
    }

    .info-table td {
        padding: 5px 0;
        font-size: 12px;
    }

    .main-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .main-table th,
    .main-table td {
        border: 1px solid black;
        padding: 8px;
        text-align: left;
        font-size: 12px;
    }

    .main-table th {
        background-color: #f2f2f2;
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

    .note {
        margin-top: 20px;
        font-size: 12px;
    }

    @media print {

        .main-table th,
        .main-table td {
            border: 1px solid black;
            -webkit-print-color-adjust: exact;
        }
    }
</style>

<div class="page-content">
    <div class="container-fluid">
        <div class="header-row">
            <div class="header-left">
                <img src="https://production.olefinatifaplas.my.id/assets/images/icon-otp.png" alt=""
                    height="60" width="60">
                <div>
                    <h1>PT OLEFINA TIFAPLAS POLIKEMINDO</h1>
                    <p>Jl. Raya Serang KM 16.8 Desa Telaga, Kec. Cikupa Tangerang, 15710<br>
                        Phone: 02159663657 Fax: 0
                    </p>
                </div>
            </div>
            <div class="header-center">
                <p class="title">PACKING LIST</p>
                <p><strong>{{ $packingList->packing_number }}</strong></p>
            </div>
            <div class="header-right">
                <p>FM-SM-PPIC-09, Rev. 0, 01 September 2021</p>
                <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <div class="info-table">
            <table>
                <tr>
                    <td>Nomor</td>
                    <td>: {{ $packingList->packing_number }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: {{ \Carbon\Carbon::parse($packingList->date)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>Customer</td>
                    <td>: {{ $packingList->customer_name }}</td>
                </tr>
            </table>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <table class="main-table">
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
                            <td>{{ $detail->pcs . ' PCS' }}</td>
                            <td>{{ stripos($detail->sts_start, 'bag') ? $detail->weight : $detail->production_weight }} KG</td>
                            @php
                            $totalRoll += $detail->pcs;
                            $totalWeight += stripos($detail->sts_start, 'bag')
                            ? $detail->weight
                            : $detail->production_weight;
                            @endphp
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
<<<<<<< HEAD
        </div>
        <div class="row mt-4">
            <div class="col-12" style="float: right; text-align: right;">
                <p><strong>Subtotal:</strong> {{ $totalRoll }} {{ $detail->unit }}</p>
                <p><strong>Total Berat:</strong> {{ number_format($totalWeight, 2) }} KG</p>
=======

            <div class="row mt-4">
                <div class="col-12">
                    <table class="main-table">
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
                                    <td>{{ $detail->pcs .' '. $detail->unit}}</td>
                                    <td>{{ stripos($detail->sts_start, 'bag') ? $detail->weight : $detail->production_weight }} KG</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
>>>>>>> 6e5a55b40421054756e8455cfe587964ccfc178c
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