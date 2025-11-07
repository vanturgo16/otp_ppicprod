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
    @if(in_array($data->status, ['Hold', 'Un Posted']))
        <div class="watermark">DRAFT</div>
    @endif
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-8 d-flex align-items-center gap-10">
                <img src="{{ asset('assets/images/icon-otp.png') }}" width="80" height="80">
                <small style="padding-left: 10px">
                    <b>{{ $dataCompany->company_name }}</b><br />
                    {{ $dataCompany->address }}<br />
                    {{ $dataCompany->city }}, {{ $dataCompany->province }} – {{ $dataCompany->postal_code }}.<br/>
                    Tlp. {{ $dataCompany->telephone ?? '-' }}, Fax. {{ $dataCompany->fax ?? '-' }}<br />
                </small>
            </div>
            <div class="col-4 d-flex justify-content-end">
                FM-SM-PPIC-01, Rev. 0, 01 September 2021
            </div>
        </div>

        <div class="row text-center">
            <h2 style="margin-top: 3rem;"><b>GOOD RECEIPT NOTE</b></h2>
            <h4>No. {{ $data->receipt_number }}</h4>
        </div>

        <table class="mb-3" style="font-size: 1rem;">
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
                <table class="table table-bordered table-sm mb-10" style="font-size: 1rem;">
                    <thead class="table-light">
                        <tr>
                            <td class="align-top fw-bold text-center p-2">#</td>
                            <td class="align-top fw-bold p-2">Item Code</td>
                            <td class="align-top fw-bold p-2">Description</td>
                            <td class="align-top fw-bold p-2">Qty</td>
                            <td class="align-top fw-bold p-2">Unit</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itemDatas as $item)
                            <tr>
                                <td class="align-top text-center p-2">{{ $loop->iteration }}</td>
                                <td class="p-2">{{ $item->code }}</td>
                                <td class="p-2">
                                    {{ $item->product_desc }} @if($item->type_product == 'FG') || {{ $item->perforasi }} @endif <br>
                                    {{ $item->remarks }}
                                </td>
                                <td class="p-2">
                                    {{ $item->receipt_qty 
                                        ? (strpos(strval($item->receipt_qty), '.') !== false 
                                            ? rtrim(rtrim(number_format($item->receipt_qty, 3, ',', '.'), '0'), ',') 
                                            : number_format($item->receipt_qty, 0, ',', '.')) 
                                        : '0' }}
                                </td>
                                <td class="p-2">{{ $item->unit_code }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row" style="font-size: 1rem;">
            <ul style="list-style-type: '- ';">
                Note : {{ $data->remarks; }}
            </ul>
        </div>
        <hr>
        <div class="row" style="font-size: 1rem;">
            <div class="col-4 text-center">
                <p class="mb-5">Diterima Oleh,</p>
                <br><br>
                <p>(.............)</p>
            </div>
            <div class="col-4 text-center"></div>
            <div class="col-4 text-center">
                <p class="mb-5">Diperiksa Oleh</p>
                <br><br>
                <p>(.............)</p>
            </div>
        </div>
    </div>
</body>
</html>