@extends('layouts.master')
@section('konten')
@include('layouts.additional')

<div class="page-content">
    <div class="container-fluid">
        <div class="row custom-margin">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="page-title-left">
                        <form action="{{ route('grn_gln.index') }}" method="GET" id="resetForm" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="idUpdated" value="{{ $data->id }}">
                            <button type="submit" class="btn btn-light waves-effect btn-label waves-light">
                                <i class="mdi mdi-arrow-left label-icon"></i> Back To List Lot Number Product GRN 
                            </button>
                        </form>
                        {{-- <a href="{{ route('grn_gln.index') }}" class="btn btn-light waves-effect btn-label waves-light">
                            <i class="mdi mdi-arrow-left label-icon"></i> Back To List Lot Number Product GRN
                        </a> --}}
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Lot Number Product GRN</a></li>
                            <li class="breadcrumb-item active"> Detail</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.alert')

        {{-- DATA PRODUCT GRN --}}
        <div class="row">
            <div class="col-12">
                <table class="table table-bordered dt-responsive nowrap w-100">
                    <tbody>
                        <tr>
                            <td class="align-top"><b>Receipt Number</b></td>
                            <td class="align-top text-end" style="width: 1%;">:</td>
                            <td class="align-top">{{ $data->receipt_number }}</td>
                        </tr>
                        <tr>
                            <td class="align-top"><b>Product</b></td>
                            <td class="align-top text-end" style="width: 1%;">:</td>
                            <td class="align-top">{{ $data->product_desc}} <b>({{ $data->type_product }})</b></td>
                        </tr>
                        <tr>
                            <td class="align-top"><b>Receipt Qty</b></td>
                            <td class="align-top text-end" style="width: 1%;">:</td>
                            <td class="align-top">
                                <b>
                                    {{ $data->receipt_qty 
                                        ? (strpos(strval($data->receipt_qty ), '.') !== false 
                                            ? rtrim(rtrim(number_format($data->receipt_qty , 6, ',', '.'), '0'), ',') 
                                            : number_format($data->receipt_qty , 0, ',', '.')) 
                                        : '0' }}
                                </b>
                                - ( Generated :
                                <b>
                                    {{ $data->qty_generate_barcode 
                                        ? (strpos(strval($data->qty_generate_barcode ), '.') !== false 
                                            ? rtrim(rtrim(number_format($data->qty_generate_barcode , 6, ',', '.'), '0'), ',') 
                                            : number_format($data->qty_generate_barcode , 0, ',', '.'))
                                        : '0' }}
                                </b>)
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- LIST ITEM --}}
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title">List Generated Lot Number Product</h4>
                    @if(!in_array($statusGRN, ['Posted', 'Closed']))
                    <div>
                        @if($data->lot_number && $data->receipt_qty != $data->qty_generate_barcode)
                            <button class="btn btn-sm btn-primary waves-effect btn-label waves-light" title="Tambah Data" data-bs-toggle="modal" data-bs-target="#add">
                                <i class="mdi mdi-plus label-icon"></i> Tambah Data</b>
                            </button>
                            <div class="modal fade" id="add" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-top" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">Tambah Data</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('grn_gln.detail.add', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="decision" value="reset">
                                            <div class="modal-body p-4">
                                                <div class="row mb-4 field-wrapper required-field">
                                                    <label class="col-sm-5 col-form-label">Lot Number</label>
                                                    <div class="col-sm-7">
                                                        <input type="text" name="lot_number" class="form-control custom-bg-gray" value="{{ $data->lot_number }}" readonly required>
                                                    </div>
                                                </div>
                                                <div class="row mb-4 field-wrapper">
                                                    <label class="col-sm-5 col-form-label">Ext. Lot Number</label>
                                                    <div class="col-sm-7">
                                                        <input type="text" name="ext_lot_number" class="form-control" placeholder="Input Ext. Lot Number..(Optional)" value="">
                                                    </div>
                                                </div>
                                                <div class="row mb-4 field-wrapper required-field">
                                                    <label class="col-sm-5 col-form-label">Qty</label>
                                                    <div class="col-sm-7">
                                                        <input type="text" name="qty" class="form-control number-format" value="" placeholder="Input Qty.." required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary waves-effect btn-label waves-light">
                                                    <i class="mdi mdi-update label-icon"></i>Update
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <button class="btn btn-sm btn-primary waves-effect btn-label waves-light" title="Receipt Qty Sudah Tergenerate Semua" disabled>
                                <i class="mdi mdi-plus label-icon"></i> Tambah Data</b>
                            </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body p-4">
                <table class="table table-bordered dt-responsive w-100" id="server-side-table" style="font-size: small">
                    <thead>
                        <tr>
                            <th class="align-middle text-center">No.</th>
                            <th class="align-middle text-center">Lot Number</th>
                            <th class="align-middle text-center">Ext Lot Number</th>
                            <th class="align-middle text-center">Qty</th>
                            <th class="align-middle text-center">Generate Barcode</th>
                            @if(!in_array($statusGRN, ['Posted', 'Closed']))
                                <th class="align-middle text-center">Action</th>
                            @endif
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="card-footer p-4"></div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        var url = '{!! route('grn_gln.detail', encrypt($id)) !!}';
        var statusGRN = {!! json_encode($statusGRN) !!};
        var columns = [
            {
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                orderable: false,
                searchable: false,
                className: 'text-center fw-bold',
            },
            {
                data: 'lot_number',
                name: 'lot_number',
                orderable: true,
                searchable: true,
                className: 'align-top fw-bold',
            },
            {
                data: 'ext_lot_number',
                name: 'ext_lot_number',
                orderable: true,
                searchable: true,
                className: 'align-top',
                render: function(data, type, row) {
                    return data ? data : '<span class="badge bg-secondary">Not Set</span>';
                }
            },
            {
                data: 'qty',
                name: 'qty',
                searchable: true,
                orderable: true,
                className: 'align-top',
                render: function(data, type, row) {
                    if (data) {
                        let number = parseFloat(data).toString(); // Convert to string without rounding
                        let parts = number.split('.'); // Split integer and decimal parts
                        let integerPart = parts[0];
                        let decimalPart = parts[1] || '';

                        // Add dots as thousands separator
                        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                        return decimalPart ? `${integerPart},${decimalPart}` : integerPart;
                    }
                    return '';
                }
            },
            {
                data: 'generate_barcode',
                name: 'generate_barcode',
                orderable: false,
                searchable: false,
                className: 'align-top text-center',
            }
        ];
        // Conditionally add the "Action" column
        if (!['Posted', 'Closed'].includes(statusGRN)) {
            columns.push({
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'align-top text-center',
            });
        }

        var dataTable = $('#server-side-table').DataTable({
            processing: true,
            serverSide: true,
            aaSorting: [],
            ajax: {
                url: url,
                type: 'GET',
                data: function(d) {
                    d.filterType = $('#filterType').val();
                    d.filterStatus = $('#filterStatus').val();
                }
            },
            columns: columns
        });

        $('#vertical-menu-btn').on('click', function() {
            setTimeout(function() {
                dataTable.columns.adjust().draw();
                window.dispatchEvent(new Event('resize'));
            }, 10);
        });
    });
</script>

@endsection
