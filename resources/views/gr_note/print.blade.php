<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PRINT GOOD RECEIPT NOTE</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/customPrint.css') }}" rel="stylesheet" type="text/css" />
</head>

<body>
    @if(($data->status != 'Posted') && ($data->status != 'Closed'))
        <div class="watermark">DRAFT</div>
    @endif
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-8 d-flex align-items-center gap-10">
                <img src="{{ asset('assets/images/icon-otp.png') }}" width="80" height="80">
                <small style="padding-left: 10px">
                    <b>PT OLEFINA TIFAPLAS POLIKEMINDO</b><br />
                    Jl. Raya Serang KM 16.8 Desa Telaga, Kec. Cikupa<br />
                    Tangerang-Banten 15710<br />
                    Tlp. +62 21 595663567, Fax. +62 21 5960776<br />
                </small>
            </div>
            <div class="col-4 d-flex justify-content-end">
                FM-SM-PPIC-01, Rev. 0, 01 September 2021
            </div>
        </div>

        <div class="row text-center">
            <h1 style="margin-top: 3rem;">GOOD RECEIPT NOTE</h4>
            <h4>No. {{ $data->receipt_number }}</h4>
        </div>

        <table class="mb-3">
            <tbody>
                <tr>
                    <td class="align-top">Receipt Date</td>
                    <td class="align-top" style="padding-left: 15px;">:</td>
                    <td class="align-top">{{ $data->date }}</td>
                </tr>
                <tr>
                    <td class="align-top">Supplier</td>
                    <td class="align-top" style="padding-left: 15px;">:</td>
                    <td class="align-top">{{ $data->name }}</td>
                </tr>
                <tr>
                    <td class="align-top">Ext. Doc No</td>
                    <td class="align-top" style="padding-left: 15px;">:</td>
                    <td class="align-top">{{ $data->external_doc_number }}</td>
                </tr>
                <tr>
                    <td class="align-top">PO Number</td>
                    <td class="align-top" style="padding-left: 15px;">:</td>
                    <td class="align-top">{{ $data->po_number ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
        
        <div class="row">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-10">
                    <thead class="table-light">
                        <tr>
                            <td class="align-top text-center">#</td>
                            <td class="align-top">Item Code</td>
                            <td class="align-top">Description</td>
                            <td class="align-top">Qty</td>
                            <td class="align-top">Unit</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itemDatas as $item)
                            <tr>
                                <td class="align-top text-center">{{ $loop->iteration }}</td>
                                <td>{{ $item->code }}</td>
                                <td>
                                    {{ $item->product_desc }} @if($item->type_product == 'FG') || {{ $item->perforasi }} @endif <br>
                                    {{ $item->remarks }}
                                </td>
                                <td>
                                    {{ $item->receipt_qty 
                                        ? (strpos(strval($item->receipt_qty), '.') !== false 
                                            ? rtrim(rtrim(number_format($item->receipt_qty, 3, ',', '.'), '0'), ',') 
                                            : number_format($item->receipt_qty, 0, ',', '.')) 
                                        : '0' }}
                                </td>
                                <td>{{ $item->unit_code }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <ul style="list-style-type: '- ';">
                Note : {{ $data->remarks; }}
            </ul>
        </div>
        <hr>
        <div class="row">
            <div class="col-4 text-center">
                <p class="mb-5">Diterima Oleh,</p>
                <p>(.............)</p>
            </div>
            <div class="col-4 text-center"></div>
            <div class="col-4 text-center">
                <p class="mb-5">Diperiksa Oleh</p>
                <p>(.............)</p>
            </div>
        </div>
    </div>
</body>
</html>