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
            text-align: center;
            width: 40%;
        }

        .header-right {
            text-align: right;
            width: 30%;
            font-size: 10px;
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

        .header .title {
            font-size: 14px;
            font-weight: bold;
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
            width: 30%;
            text-align: left;
            font-size: 12px;
        }

        .kepada-yth p {
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <img src="https://production.olefinatifaplas.my.id/assets/images/icon-otp.png" alt="" height="60" width="60">
                <div>
                    <h1>PT OLEFINA TIFAPLAS POLIKEMINDO</h1>
                    <p>Jl. Raya Serang KM 16.8 Desa Telaga, Kec. Cikupa Tangerang, 15710<br>
                        Phone: 02159663657 Fax: 0
                    </p>
                </div>
            </div>
            <div class="header-center">
                <p class="title">SURAT PENGANTAR</p>
                <p><strong>{{ $prefix }}{{ str_replace('DN', '', $deliveryNote->dn_number) }}</strong></p>
            </div>
            <div class="header-right">
                <p>FM-SM-PPIC-09, Rev. 0, 01 September 2021</p>
                <p>Tanggal Cetak: {{ date('d/m/Y') }}</p>
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
                <td>Cust. Product Code</td>
                <td>: -</td>
                <td></td>
            </tr>
            <tr>
                <td>Shipping Address</td>
                <td>: {{ $shippingAddress->address }}</td>
                <td></td>
            </tr>
            <tr>
                <td>Invoice Address</td>
                <td>: {{ $invoiceAddress->address }}</td>
                <td></td>
            </tr>
        </table>

        <div class="kepada-yth">
            <p>Kepada Yth,</p>
            <p>{{ $deliveryNote->customer_name }}</p>
            <p>-</p>
            <p>-</p>
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <!-- <th>Perforasi</th> -->
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Weight</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($packingListDetails as $detail)
                <tr>
                    <td>{{ $detail->product_description }}</td>
                    <!-- <td>{{ $detail->perforasi }}</td> -->
                    <td>{{ $detail->qty }}</td>
                    <td>{{ $detail->unit_name }}</td>
                    <td>{{ $detail->weight }} KG</td>
                    <td>{{ $detail->remark }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p class="note">Total Weight: {{ $totalWeight }} KG</p>
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