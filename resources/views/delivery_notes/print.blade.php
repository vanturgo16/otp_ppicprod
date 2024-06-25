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
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }

        h1,
        h2 {
            text-align: center;
        }

        p {
            margin: 0;
            padding: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .note {
            margin-top: 20px;
        }

        .signatures {
            width: 100%;
            margin-top: 50px;
            text-align: center;
        }

        .signatures td {
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>PT OLEFINA TIFAPLAS POLIKEMINDO</h1>
        <p>Jl. Raya Serang KM 16.8 Desa Telaga, Kec. Cikupa<br>
            Tangerang, 15710<br>
            Phone: 02159663657 Fax: 0</p>
        <h2>SURAT PENGANTAR</h2>
        <p>No. DN: {{ $deliveryNote->dn_number }}</p>
        <p style="text-align:right;">Tanggal Cetak: {{ date('d/m/Y') }}</p>

        <table>
            <tr>
                <td>Tanggal</td>
                <td>: {{ date('d/m/Y', strtotime($deliveryNote->date)) }}</td>
            </tr>
            <tr>
                <td>No. PO</td>
                <td>: {{ $deliveryNote->sales_order_po_number }}</td>
            </tr>
            <tr>
                <td>No. Pol. Kendaraan</td>
                <td>: {{ $deliveryNote->vehicle_number }}</td>
            </tr>
            <tr>
                <td>Cust. Product Code</td>
                <td>: -</td>
            </tr>
        </table>

        <p>Kepada Yth,</p>
        <p>{{ $deliveryNote->customer_name }}</p>

        <table>
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
                <tr>
                    <td>{{ $packingListDetails->product_description }}</td>
                    <td>{{ $packingListDetails->perforasi }}</td>
                    <td>{{ $packingListDetails->total_qty }}</td>
                    <td>{{ $packingListDetails->unit_name }}</td>
                    <td>{{ $packingListDetails->total_weight }} KG</td>
                    <td>{{ $packingListDetails->remark }}</td>
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