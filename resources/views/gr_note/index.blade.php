@extends('layouts.master')
@section('konten')
@include('layouts.additional')

<div class="page-content">
    <div class="container-fluid">
        @include('layouts.alert')

        <div class="row custom-margin">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">List Good Receipt Note (GRN)</h5>
                            <div>
                                <a href="{{ route('grn.add', 'PR') }}" class="btn btn-sm btn-primary waves-effect btn-label waves-light" title="Tambah GRN dari PR">
                                    <i class="mdi mdi-plus label-icon"></i> Tambah GRN dari <b>(PR With Price)</b>
                                </a>
                                <a href="{{ route('grn.add', 'PO') }}" class="btn btn-sm btn-success waves-effect btn-label waves-light" title="Tambah GRN dari PO">
                                    <i class="mdi mdi-plus label-icon"></i> Tambah GRN dari <b>(PO)</b>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive w-100" id="server-side-table" style="font-size: small">
                            <thead>
                                <tr>
                                    <th class="align-middle text-center">No.</th>
                                    <th class="align-middle text-center">Receipt Number</th>
                                    <th class="align-middle text-center">Reference Number</th>
                                    <th class="align-middle text-center">Purchase Order</th>
                                    <th class="align-middle text-center">Date</th>
                                    <th class="align-middle text-center">External Doc Number</th>
                                    <th class="align-middle text-center">Suppliers</th>
                                    <th class="align-middle text-center">QC Check</th>
                                    <th class="align-middle text-center">Type</th>
                                    <th class="align-middle text-center">Total Product</th>
                                    <th class="align-middle text-center">Status</th>
                                    <th class="align-middle text-center">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var url = '{!! route('grn.index') !!}';
        var dataTable = $('#server-side-table').DataTable({
            scrollX: true,
            responsive: false,
            fixedColumns: {
                leftColumns: 2,
                rightColumns: 1
            },
            processing: true,
            serverSide: true,
            pageLength: 5,
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
                    data: 'receipt_number',
                    name: 'receipt_number',
                    orderable: true,
                    searchable: true,
                    className: 'align-top freeze-column',
                    render: function(data, type, row) {
                        let source = row.id_purchase_orders === null ? 'PR' : 'PO';
                        return '<b>' + data + '</b><br><small>Sumber: ' + source + '</small>';
                    },
                },
                {
                    data: 'request_number',
                    name: 'request_number',
                    orderable: true,
                    searchable: true,
                    className: 'align-top'
                },
                {
                    data: 'po_number',
                    name: 'po_number',
                    orderable: true,
                    searchable: true,
                    className: 'align-top'
                },
                {
                    data: 'date',
                    searchable: true,
                    orderable: true,
                    className: 'align-top text-center',
                },
                {
                    data: 'external_doc_number',
                    name: 'external_doc_number',
                    orderable: true,
                    searchable: true,
                    className: 'align-top'
                },
                {
                    data: 'supplier_name',
                    name: 'supplier_name',
                    orderable: true,
                    searchable: true,
                    className: 'align-top'
                },
                {
                    data: 'qc_status',
                    name: 'qc_status',
                    orderable: true,
                    searchable: true,
                    className: 'align-top text-center'
                },
                {
                    data: 'type',
                    name: 'type',
                    orderable: true,
                    searchable: true,
                    className: 'align-top text-center fw-bold'
                },
                {
                    data: 'count',
                    name: 'count',
                    orderable: true,
                    searchable: true,
                    className: 'align-top text-center'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: true,
                    searchable: true,
                    className: 'align-top text-center',
                    render: function(data, type, row) {
                        let badgeColor = data === 'Hold' ? 'secondary' : 
                                        data === 'Un Posted' ? 'warning' : 'success';
                        return `<span class="badge bg-${badgeColor}" style="font-size: smaller; width: 100%">${data}</span>`;
                    },
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
                let bgColor = '';
                let darkColor = '#FAFAFA';
                if (data.status === 'Posted') {
                    bgColor = 'table-success';
                    darkColor = '#CFEBE0';
                }
                if (data.status === 'Hold') {
                    bgColor = 'table-secondary';
                    darkColor = '#DFE0E3';
                }
                if (bgColor) {
                    $(row).addClass(bgColor);
                }
                $(row).find('.freeze-column').css('background-color', darkColor);
            },
        });
        $('.dataTables_scrollHeadInner thead th').each(function(index) {
            let $this = $(this);
            let isFrozenColumn = index < 2 || index === $('.dataTables_scrollHeadInner thead th').length - 1;
            if (isFrozenColumn) {
                $this.css({
                    'background-color': '#FAFAFA',
                    'position': 'sticky',
                    'z-index': '3',
                    'left': index < 2 ? ($this.outerWidth() * index) + 'px' : 'auto',
                    'right': index === $('.dataTables_scrollHeadInner thead th').length - 1 ? '0px' : 'auto'
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

<script>
    $(function() {
        // Hide Length Datatable
        $('.dataTables_wrapper .dataTables_length').hide();

        // Length
        var lengthDropdown = `
            <label>
                <select id="lengthDT">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </label>
        `;
        $('.dataTables_length').before(lengthDropdown);
        $('#lengthDT').select2({ minimumResultsForSearch: Infinity, width: '60px' });
        $('#lengthDT').on('change', function() {
            var newLength = $(this).val();
            var table = $("#server-side-table").DataTable();
            table.page.len(newLength).draw();
        });

        // Filter Type
        var filterType = `
            <label>
                <select id="filterType">
                    <option value="All">-- Semua Type --</option>
                    <option value="RM">RM</option>
                    <option value="WIP">WIP</option>
                    <option value="FG">FG</option>
                    <option value="TA">TA</option>
                    <option value="Other">Other</option>
                </select>
            </label>
        `;
        $('.dataTables_length').before(filterType);
        $('#filterType').select2({width: '150px' });
        $('#filterType').on('change', function() { $("#server-side-table").DataTable().ajax.reload(); });

        // Filter Status
        var filterStatus = `
            <label>
                <select id="filterStatus">
                    <option value="All">-- Semua Status --</option>
                    <option value="Hold">Hold</option>
                    <option value="Posted">Posted</option>
                    <option value="Un Posted">Un Posted</option>
                </select>
            </label>
        `;
        $('.dataTables_length').before(filterStatus);
        $('#filterStatus').select2({width: '200px' });
        $('#filterStatus').on('change', function() { $("#server-side-table").DataTable().ajax.reload(); });
    });
</script>

@endsection