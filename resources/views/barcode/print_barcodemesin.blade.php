@extends('layouts.master')

@section('konten')

<div class="page-content">
    <div class="container-fluid">
        <p></p>
        {{-- <button onclick="window.print()" class="btn btn-primary no-print">Print</button> --}}
        <div class="barcode-print">
            @foreach ($barcodeDetails as $barcode)
            <div class="barcode-item">
              
<table class="barcode-table">
    <tr>
        <td colspan="3" class="company-name">PT Olefina Tifaplas Polikemindo </td>
    </tr>
    <tr>
        <td><strong>SO No</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="value">{{ $barcode->so_number ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>Tanggal</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="value">{{ \Carbon\Carbon::parse($barcode->tgl_buat ?? now())->format('d F Y') }}</td>
    </tr>
    <tr>
        <td><strong>Customer</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="value">{{ $barcode->nm_cust ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>Description</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="description">
            <b>{{ $barcode->description ?? '-' }}</b>
        </td>
    </tr>
    <tr>
        <td><strong>No KO/PO</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="value">
            <b>
            @if(is_null($barcode->id_order_confirmations))
                {{ $barcode->reference_number ?? '-' }}
            @else
                {{ '-' }}
            @endif
            </b>
        </td>
    </tr>
    <tr>
        <td><strong>Cust Product Code</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="value">{{ $barcode->cust_product_code ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>Lot</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="value">
            <div class="barcode-number">{{ $barcode->barcode_number }}</div>
        </td>
    </tr>
</table>
<b>Made In Indonesia</b>
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
&nbsp; &nbsp; &nbsp; &nbsp; 

<img class="qr-code" src="data:image/png;base64,{{ DNS2D::getBarcodePNG($barcode->barcode_number, 'QRCODE', 4, 4) }}" alt="QR Code" />

            </div>
            @endforeach
        </div>
    </div>
</div>


<style>
    .page-content {
        max-width: 500px;
        padding: 10px;
    }
    
    .barcode-item {
        margin-bottom: 5px;
        margin-top: 5px;
        page-break-inside: avoid;
    }
    
    .barcode-table {
        width: 10%;
        font-size: 12px;
        border-collapse: collapse;
    }
    
    .barcode-table td {
        padding: 2px;
        vertical-align: top;
    }
    
    .label, .colon {
        padding-right: 0px;
    }

    .label, .value {
        white-space: nowrap;
    }

    .label, .value, .colon {
        white-space: nowrap;
    }

    .value {
    word-wrap: break-word; /* Buat memecah kata panjang */
    font-weight: bold; /* Menjadikan teks tebal */
}
    
    .company-name {
    text-align: left;
    font-weight: bold;
    font-size: 15px;
    padding-bottom: 5px;
}
    
    .up-down-table td {
        width: 15px;
        height: 15px;
        /* padding: 1; */
        text-align: center;
        border: 1px solid #000;
        font-size: 12px;
        line-height: 15px;
        margin-left: 1px;
    }
    
    .barcode-number, .joint {
        font-size: 12px;
    }
    
    .barcode-img {
        width: 80px; /* Ukuran barcode lebih kecil */
        height: 30px;
    }

    .joint {
        display: inline-block;
        width: 15px;
        height: 15px;
        border: 1px solid #000;
        text-align: center;
        line-height: 15px;
        margin-left: 1px;
    }
 
}
    @media print {
        .no-print {
            display: none;
        }
    }
</style>
@endsection
