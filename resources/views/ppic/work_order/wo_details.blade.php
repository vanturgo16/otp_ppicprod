@extends('layouts.master')

@section('konten')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Work Order Details</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">PPIC</a></li>
                                <li class="breadcrumb-item active">Work Order Details</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show d-none" role="alert"
                id="alertSuccess">
                <i class="mdi mdi-check-all label-icon"></i><strong>Success</strong> - <span
                    class="alertMessage">{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show d-none" role="alert"
                id="alertFail">
                <i class="mdi mdi-block-helper label-icon"></i><strong>Failed</strong> - <span
                    class="alertMessage">{{ session('fail') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>Success</strong> - {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('fail'))
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>Failed</strong> - {{ session('fail') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-outline label-icon"></i><strong>Warning</strong> - {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('info'))
                <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-circle-outline label-icon"></i><strong>Info</strong> - {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row pb-3">
                <div class="col-12">
                    <a href="{{ url()->previous() }}" class="btn btn-primary waves-effect btn-label waves-light">
                        <i class="mdi mdi-arrow-left label-icon"></i> Back To List Data Work Order
                    </a>
                    <a href="{{ route('ppic.workOrder.createWODetails', encrypt($wo_number)) }}"
                        class="btn btn-success waves-effect btn-label waves-light">
                        <i class="mdi mdi-plus-box label-icon"></i> Add Data
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="mdi mdi-file-multiple-outline label-icon"></i> List Work Order Details
                        </div>

                        <div class="card-body">
                            <div class="mt-4 mt-lg-0">
                                <div class="row">
                                    <dt class="col-sm-2 mb-2"><strong><label>WO Number</label></strong></dt>
                                    <dd class="col-sm-10 mb-2"><span id="wo_number">{{ $wo_number }}</span></dd>
                                </div>
                                <hr>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="table-responsive table-bordered">
                                        <table class="table table-striped table-hover" id="wo_details_table">
                                            <thead>
                                                <tr class="text-center">
                                                    <th>No</th>
                                                    <th>Products</th>
                                                    <th>Weight</th>
                                                    <th>Unit</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                                <!-- end col -->
                            </div>
                            <!-- end row -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Static Backdrop Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Confirm to Posted</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to posted this data?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary waves-effect btn-label waves-light"
                        onclick="bulkPosted()"><i class="mdi mdi-arrow-right-top-bold label-icon"></i>Posted</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var i = 1;
            var wo_number = $('#wo_number').text(); // Misalnya, Anda memiliki variabel wo_number
            let dataTable = $('#wo_details_table').DataTable({
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
                pageLength: 20,
                lengthMenu: [
                    [5, 10, 20, 25, 50, 100, 200],
                    [5, 10, 20, 25, 50, 100, 200]
                ],
                aaSorting: [
                    [0, 'asc']
                ], // start to sort data in second column 
                ajax: {
                    url: "{{ route('ppic.workOrder.ajaxWODetails') }}",
                    data: function(d) {
                        d.search = $('input[type="search"]').val(); // Kirim nilai pencarian
                        d.wo_number = wo_number;
                    }
                },
                columns: [{
                        data: 'id',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                        // className: 'align-middle text-center',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'products',
                        name: 'products',
                        // className: 'align-middle text-center',
                        orderable: true,
                    },
                    {
                        data: 'qty',
                        name: 'qty',
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
                        data: 'action',
                        name: 'action',
                        // className: 'align-middle text-center',
                        orderable: true,
                    }
                ],
                bAutoWidth: false,
                columnDefs: [{
                    'orderable': false,
                    'targets': 0
                }], // hide sort icon on header of first column],
            });
        });
    </script>
@endpush
