@extends('layouts.master')

@section('konten')

<div class="page-content">
    <div class="container-fluid">
        <p></p>
        {{-- <button onclick="window.print()" class="btn btn-primary no-print">Print</button> --}}
        <div class="barcode-print">
          
            <div class="barcode-item">
              
<table class="barcode-table">
    <p></p>
 
        <td><strong>SO No.</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="value">{{ $barcode->so_number }}
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span style="float:">{{ \Carbon\Carbon::now()->format('d F Y') }}</span>
        </td>
    </b>
    </tr>
    <tr>
        <td colspan="3" class="company-name">PT Olefina Tifaplas Polikemindo </td>
    </tr>
    
    <tr>
        <td><strong>Customer</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="value">{{ $barcode->nm_cust ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>Description</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
       
            <td  class="description">
            <b>{{ $barcode->description ?? '-' }}</b></td>
    </tr>
    <tr>
        <td><strong>No KO/PO</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
       
            <td  class="value">
            <b>
            @if(is_null($barcode->id_order_confirmations))
                {{ $barcode->reference_number ?? '-' }}
            @else
                {{ '-' }}
            @endif
            </b></td>
    </tr>
    <tr>
        <td><strong>Size</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="value">{{ $barcode->width ?? '-' }} MM  &nbsp; X &nbsp;{{ $barcode->height ?? ($barcode->length ?? '') }}
            @if(substr($barcode->work_center_code, 0, 3) === 'BAG')
            MM
        @else
            M
        @endif
            
            &nbsp;&nbsp;&nbsp;&nbsp; <strong>P:</strong>{{ $barcode->perforasi ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>Thickness</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="value">{{ $barcode->thickness ?? '-' }} MIC </td>
    </tr>
    <tr>
        <td><strong>Group</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="value">{{ $barcode->shift }} &nbsp; <strong>Machine: {{ $barcode->work_center_code }}</strong> &nbsp; <strong>Joint:</strong> <span class="joint">1</span> <span class="joint">2</span> <span class="joint">3</span></td>
    </tr>
    <tr>
        <td><strong>Left</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="value">
            <table class="up-down-table">
                <tr>
                    @for ($i = 1; $i <= 10; $i++)
                    <td>{{ $i % 10 }}</td>
                    @endfor
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td><strong>Right</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="value">
            <table class="up-down-table">
                <tr>
                    @for ($i = 1; $i <= 10; $i++)
                    <td>{{ $i % 10 }}</td>
                    @endfor
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td><strong>Lot</strong></td>
        <td class="colon">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td>
        <td class="value">
            <dev class="barcode-number">{{ $barcode->barcode_number }}</dev>
            {{-- <img class="barcode-img" src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->barcode_number, 'C128') }}" alt="barcode" /> --}}
           
        </td>
       
    </tr>
</table>
<b>Made In Indonesia</b>
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
&nbsp; &nbsp; &nbsp; &nbsp; 

<img class="qr-code" src="data:image/png;base64,{{ DNS2D::getBarcodePNG($barcode->barcode_number, 'QRCODE', 4, 4) }}" alt="QR Code" />

            </div>
           
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
