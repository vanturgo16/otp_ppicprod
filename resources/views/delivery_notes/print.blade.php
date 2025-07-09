<!DOCTYPE html>
<html>

<head>
    <title>Cetak Delivery Note</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 5px;
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
            font-size: 12px;
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
            font-size: 14px;
        }

        .header .cont-title {
            text-align: center;
        }

        .header .title {
            
            font-size: 24px;
            font-weight: bold;
            border-bottom: 2px solid;
            
            line-height: 1;
        }
        

        
           
        

        .info-table {
            min-width: 60%;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        .info-table td {
            padding: 5px 0;
            font-size: 14px;
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
            border: 0.8px solid black;
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
            font-size: 14px;
            border: solid 3px;
            padding: 0;
        }



        .kepada-yth p {
            margin: 0;

        }

        .kepada-yth p b {
            font-size: 16px;

        }

        .total {
            font-size: 14px;
        }

        @media print {

            @page {
                /* ruang putih tepi kertas */
            }

            body {
                margin: 5.1px !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background: none !important;
            }

            .container {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 0 !important;
                position: relative;
                page-break-inside: avoid;
               
            }

          
            .header {
                white-space: nowrap;
                font-size: clamp(14px, 1.8vw, 18px);
                /* ukuran adaptif: min 12 px, max 18 px */
                line-height: 1.8;
            }

            .header-left,
            .header-center,
            .header-right {
                flex: 0 0 auto;
            }

            .header-center {
                padding-top: 130px;

            }

            .header-right {
                margin-top: 0px;
                display: flex;
                justify-content: left;
                padding-left: 30px;
            }

            .kepada-yth {
                border: solid 3px black;
                padding: 2px;
                padding-right: 70px;
            }

            .main-table {
                width: 100% !important;
                border-collapse: collapse !important;
                page-break-inside: avoid;
            }

            .main-table th,
            .main-table td {
                border: 1px solid #000 !important;
                font-size: 10pt !important;
                padding: 4pt !important;
            }

            .signatures {
                width: 100%;
                page-break-inside: avoid;
                margin-top: 25mm !important;
            }
            
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
                    <p>{{ $prefix }}{{ str_replace('DN', '', $deliveryNote->dn_number) }}</p>
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
            <p><b>{{ $deliveryNote->customer_name }}</b></p>
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
                        <td>{{ $detail->description }}</td>
                        <td>{{ $detail->code }}</td>
                        <!-- <td>perforasi</td> -->
                        <td>{{ $detail->qty }}</td>
                        <td>{{ $detail->unit }}</td>
                        <td>{{ $detail->weight }} kg</td>
                        <td>{{ $detail->remark }}</td>

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
