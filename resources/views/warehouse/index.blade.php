@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">
        @if (session('pesan'))
        <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-check-all label-icon"></i><strong>Success</strong> - {{ session('pesan') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Packing Lists</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Warehouse</a></li>
                            <li class="breadcrumb-item active">Packing Lists</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-12">
                <a href="{{ route('packing_list.create') }}" class="btn btn-primary">Tambah Data</a>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Packing Lists</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="packing_list_table" class="table table-bordered dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Packing Number</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data akan diisi oleh DataTables -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var i = 1;
        let dataTable = $('#packing_list_table').DataTable({
            dom: '<"top d-flex"<"position-absolute top-0 end-0 d-flex"fl>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>><"clear:both">',
            initComplete: function(settings, json) {
                $('.top').prepend(
                    `<div class='pull-left col-sm-12 col-md-5'><div class="btn-group mb-4"></div></div>`
                );
            },
            processing: true,
            serverSide: true,
            language: {
                lengthMenu: "_MENU_",
                search: "",
                searchPlaceholder: "Search",
            },
            pageLength: 5,
            lengthMenu: [
                [5, 10, 20, 25, 50, 100],
                [5, 10, 20, 25, 50, 100]
            ],
            aaSorting: [
                [1, 'desc']
            ],
            ajax: {
                url: '{{ route("packing-list") }}',
                data: function(d) {
                    d.search = $('input[type="search"]').val();
                }
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'packing_number',
                    name: 'packing_number',
                    orderable: true,
                },
                {
                    data: 'date',
                    name: 'date',
                    orderable: true,
                },
                {
                    data: 'customer',
                    name: 'customer',
                    orderable: true,
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: true,
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
            createdRow: function(row, data, dataIndex) {
                if (data.status === 'Posted') {
                    $(row).addClass('table-success');
                }
            },
            bAutoWidth: false,
            columnDefs: [{
                    width: '10%',
                    targets: [3]
                },
                {
                    width: '100px',
                    targets: [5],
                },
                {
                    orderable: false,
                    targets: [0]
                }
            ],
        });
    });
</script>
@endpush