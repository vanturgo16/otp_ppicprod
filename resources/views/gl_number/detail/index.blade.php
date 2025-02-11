@extends('layouts.master')
@section('konten')
@include('layouts.additional')

<div class="page-content">
    <div class="container-fluid">
        <div class="row custom-margin">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="page-title-left">
                        <a href="{{ route('grn_gln.index') }}" class="btn btn-light waves-effect btn-label waves-light">
                            <i class="mdi mdi-arrow-left label-icon"></i> Back To List Lot Number Product GRN
                        </a>
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
                            <td class="align-middle"><b>Receipt Number</b></td>
                            <td class="align-middle">: {{ $data->receipt_number }}</td>
                        </tr>
                        <tr>
                            <td class="align-middle"><b>Product</b></td>
                            <td class="align-middle">: {{ $data->product_desc}} <b>({{ $data->type_product }})</b></td>
                        </tr>
                        <tr>
                            <td class="align-middle"><b>Receipt Qty</b></td>
                            <td class="align-middle">: <u>{{ $data->receipt_qty }}</u> - (Generated : <b>{{ $data->qty_generate_barcode }}</b>)</td>
                        </tr>
                        {{-- <tr>
                            <td class="align-middle"><b>Generated Qty</b></td>
                            <td class="align-middle">: {{ $data->qty_generate_barcode }}</td>
                        </tr> --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- LIST ITEM --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">List Generated Lot Number Product</h4>
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
                            <th class="align-middle text-center">Action</th>
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
        var dataTable = $('#server-side-table').DataTable({
            scrollX: true,
            responsive: false,
            fixedColumns: {
                leftColumns: 2,
                rightColumns: 2
            },
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
            columns: [
                {
                data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    orderable: false,
                    searchable: false,
                    className: 'text-center fw-bold freeze-column',
                },
                {
                    data: 'lot_number',
                    name: 'lot_number',
                    orderable: true,
                    searchable: true,
                    className: 'align-top fw-bold freeze-column',
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
                    orderable: true,
                    searchable: true,
                    className: 'align-top'
                },
                {
                    data: 'generate_barcode',
                    name: 'generate_barcode',
                    orderable: false,
                    searchable: false,
                    className: 'align-top text-center freeze-column',
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'align-top text-center freeze-column',
                },
            ],
            createdRow: function(row, data, dataIndex) {
                let darkColor = '#FAFAFA';
                $(row).find('.freeze-column').css('background-color', darkColor);
            },
        });
        $('.dataTables_scrollHeadInner thead th').each(function(index) {
            let $this = $(this);
            let isFrozenColumn = index < 2 || index === $('.dataTables_scrollHeadInner thead th').length - 2 || index === $('.dataTables_scrollHeadInner thead th').length - 1;
            if (isFrozenColumn) {
                $this.css({
                    'background-color': '#FAFAFA',
                    'position': 'sticky',
                    'z-index': '3',
                    'left': index < 2 ? ($this.outerWidth() * index) + 'px' : 'auto',
                    'right': index === $('.dataTables_scrollHeadInner thead th').length - 2 ? '0px' : 'auto'
                });
            }
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
