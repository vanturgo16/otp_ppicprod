@extends('layouts.master')

@section('konten')

<div class="page-content">
        <div class="container-fluid">
        @if (session('pesan'))
            <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-check-all label-icon"></i><strong>Success</strong> - {{ session('pesan') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @elseif (session('error'))
            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-check-all label-icon"></i><strong>Error</strong> - {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
         @endif
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> Good Receipt Note</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">PPIC</a></li>
                                <li class="breadcrumb-item active"> Good Receipt Note</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Good Lote Number</h5>
                                <div>
                                    <!-- Include modal content -->
                                    
                                @include('grn.modal')
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="so_grn_table" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr>
                                        <tr>
                                            <th>No</th>
                                            <th>Good Receipt Note</th>
                                            <th>Products</th>
                                            <th>Receipt Qty</th>
                                            <th>Units</th>
                                            <th>Qc Passed</th>
                                            <th>Lot Number</th>
                                            <th>Note</th>
                                            <th>Generate Lot </th>
                                           
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                   
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
            // alert('test')
            var i = 1;
            let dataTable = $('#so_grn_table').DataTable({
                dom: '<"top d-flex"<"position-absolute top-0 end-0 d-flex"fl>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>><"clear:both">',
                initComplete: function(settings, json) {
                    // Setelah DataTable selesai diinisialisasi
                    // Tambahkan elemen kustom ke dalam DOM
                    $('.top').prepend(
                        `<div class='pull-left col-sm-12 col-md-5'><div class="btn-group mb-4"></div></div>`
                    );
                },
                processing: true,
                serverSide: true,
                // scrollX: true,
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
                ], // start to sort data in second column 
                ajax: {
                    url: baseRoute + '/good-lote-number',
                    data: function(d) {
                        d.search = $('input[type="search"]').val(); // Kirim nilai pencarian
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                        // className: 'align-middle text-center',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'receipt_number',
                        name: 'receipt_number',
                        // className: 'align-middle text-center',
                        orderable: true,
                    },
                    {
                        data: 'description',
                        name: 'description',
                        // className: 'align-middle text-center',
                        orderable: true,
                    },
                    {
                        data: 'receipt_qty',
                        name: 'receipt_qty',
                        // className: 'align-middle text-center',
                        orderable: true,
                    },
                    {
                        data: 'unit_code',
                        name: 'unit_code',
                        // className: 'align-middle text-center',
                        orderable: true,
                    },
                    {
                        data: 'qc_passed',
                        name: 'qc_passed',
                        // className: 'align-middle',
                        orderable: true,
                    },
                    {
                        data: 'lot_number',
                        name: 'lot_number',
                        // className: 'align-middle',
                        orderable: true,
                    },
                    {
                        data: 'note',
                        name: 'note',
                        // className: 'align-middle',
                        orderable: true,
                    },
                    {
                        data: 'generate_lot',
                        name: 'generate_lot',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        // className: 'align-middle text-center',
                        orderable: false,
                        searchable: false
                    },
                ],
                createdRow: function(row, data, dataIndex) {
                    // Tambahkan class "table-success" ke tr jika statusnya "Posted"
                    if (data.statusLabel === 'Posted') {
                        $(row).addClass('table-success');
                    }
                },
                bAutoWidth: false,
                columnDefs: [{
                        width: "10%",
                        targets: [3]
                    }, {
                        width: '100px', // Menetapkan min-width ke 150px
                        targets: [6, 7], // Menggunakan class 'progress' pada kolom
                    },
                    {
                        width: '60px', // Menetapkan min-width ke 150px
                        targets: [4], // Menggunakan class 'progress' pada kolom
                    }, {
                        orderable: false,
                        targets: [0]
                    }
                ],
            });
        });
    </script>
@endpush