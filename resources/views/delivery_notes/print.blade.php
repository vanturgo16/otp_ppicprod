<!DOCTYPE html>
<html>

<head>
    <title>Cetak Delivery Note</title>
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

        .header {
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
            display: flex;
            justify-content: center;
            align-items: center;
            width: 40%;
            
        }

        .header-right {
            display: flex;
            justify-content: end;
            width: 30%;
            font-size: 10px;
            margin-bottom: 0;
            margin-top: 60px;
            padding-right: 110px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .header p {
            margin: 0;
            line-height: 1.5;
            font-size: 12px;
        }
        .header .cont-title{
            text-align: center;
        }

        .header .title {
            font-size: 14px;
            font-weight: bold;
            border-bottom: 2px solid;
        }

        .info-table {
            min-width: 60%;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        .info-table td {
            padding: 5px 0;
            font-size: 12px;
        }

        .right-align {
            text-align: right;
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

        .note {
            margin-top: 20px;
            font-size: 12px;
        }

        .signatures {
            width: 100%;
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
        }

        .signatures td {
            padding: 20px;
        }

        .kepada-yth {
            position: absolute;
            top: 100px;
            right: 0;
            width: 20%;
            text-align: left;
            font-size: 12px;
            border: solid 2px;
            padding: 0;
        }



        .kepada-yth p {
            margin: 0;

        }

        .total {
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <img src="https://production.olefinatifaplas.my.id/assets/images/icon-otp.png" alt=""
                    height="60" width="60">
                <div>
                    <h1>PT OLEFINA TIFAPLAS POLIKEMINDO</h1>

                    <p>JL. Raya Serang KM 16.8 Cikupa - Tangerang<br>
                        Tlp. (021)59663567
                    </p>
                </div>
            </div>
            <div class="header-center">
                <div class="cont-title">
                    <p class="title">SURAT PENGANTAR</p>
                    <p><strong>{{ $prefix }}{{ str_replace('DN', '', $deliveryNote->dn_number) }}</strong></p>
                </div>
            </div>
            <div class="header-right">
                <p>FM-SM-PPIC-09, Rev. 0, 01 September 2021</p>

            </div>
        </div>

        <table class="info-table">
            <tr>
                <td>Tanggal</td>
                <td>: {{ date('d/m/Y', strtotime($deliveryNote->date)) }}</td>
                <td></td>
            </tr>
            <tr>
                <td>No. PO</td>
                <td>: {{ $deliveryNote->sales_order_po_number }}</td>
                <td></td>
            </tr>
            <tr>
                <td>No. Pol. Kendaraan</td>
                <td>: {{ $deliveryNote->vehicle_number }}</td>
                <td></td>
            </tr>
            <tr>
                <td>Shipping Address</td>
                <td>: {{ $shippingAddress->address }} {{ $shippingAddress->postal_code }}</td>
                <td></td>
            </tr>
        </table>

        <div class="kepada-yth">
            <p>Kepada Yth,</p>
            <p>{{ $deliveryNote->customer_name }}</p>
            {{-- invoice addres: --}}
            <p>{{ $invoiceAddress->address }}</p>
            <p>{{ $invoiceAddress->postal_code }}</p>
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Description</th>
                    <th>Cust. Product Code</th>
                    <!-- <th>Perforasi</th> -->
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Weight</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                @endphp
                @foreach ($packingListDetails as $detail)
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{$detail->description}}</td>
                        <td>{{$detail->code}}</td>
                        <!-- <td>perforasi</td> -->
                        <td>{{$detail->qty}}</td>
                        <td>{{$detail->unit}}</td>
                        <td>{{$detail->weight}} kg</td>
                        <td>{{$detail->remark}}</td>

                    </tr>
                @endforeach
                <tr>
                    <td style="text-align: center" colspan="3"><strong>TOTAL</strong></td>
                    <td>{{ $totalQty }} </td>
                    <td></td>
                    <td>{{ $totalWeight }} KG</td>
                    <td></td>
                </tr>

            </tbody>
        </table>
        <p class="note">Note: {{ $deliveryNote->note }}</p>

        <table class="signatures">
            <tr>
                <td>Diterima,</td>
                <td>Pengemudi,</td>
                <td>Security,</td>
                <td>Pembuat,</td>
            </tr>
            <tr>
                <td>(...................)</td>
                <td>(...................)</td>
                <td>(...................)</td>
                <td>(...................)</td>
            </tr>
        </table>
    </div>
</body>

</html>
