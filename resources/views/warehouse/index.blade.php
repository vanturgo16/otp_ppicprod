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
        <div class="row mb-3">
            <div class="col-md-3">
                <input type="date" id="start_date" class="form-control" placeholder="Start Date">
            </div>
            <div class="col-md-3">
                <input type="date" id="end_date" class="form-control" placeholder="End Date">
            </div>
            <div class="col-md-3">
                <button id="filter" class="btn btn-secondary">Filter</button>
                <button id="reset" class="btn btn-outline-secondary">Reset</button>
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
                                    <!-- Data will be populated by DataTables -->
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
        // Ambil halaman awal dari query string
        let initialPage = new URLSearchParams(window.location.search).get('page') || 1;
        initialPage = parseInt(initialPage) - 1; // Karena DataTables menghitung halaman dari 0

        let dataTable = $('#packing_list_table').DataTable({
            dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
                '<"row"<"col-sm-12"l>>' +
                'rt' +
                '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                'copy',
                {
                    extend: 'csv',
                    exportOptions: {
                        columns: ':visible:not(:last-child)' // Mengecualikan kolom aksi (tombol)
                    }
                },
                {
                    extend: 'excel',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    }
                },
                {
                    extend: 'pdf',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    }
                },
                {
                    extend: 'print',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    },
                    customize: function(win) {
                        $(win.document.body).find('th:last-child, td:last-child').hide();
                    }
                }
            ],
            processing: true,
            serverSide: true,
            pageLength: 10,
            displayStart: initialPage * 10, // Menyesuaikan dengan halaman awal
            ajax: {
                url: '{{ route("packing-list") }}',
                data: function(d) {
                    d.search = $('input[type="search"]').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
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
                    orderable: true
                },
                {
                    data: 'date',
                    name: 'date',
                    orderable: true
                },
                {
                    data: 'customer',
                    name: 'customer',
                    orderable: true
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: true
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return generateActionButtons(row, dataTable.page.info().page + 1);
                    }
                }
            ],
            order: [
                [1, 'desc']
            ], // Urutkan berdasarkan kolom 'packing_number' secara descending
            lengthMenu: [
                [5, 10, 20, 25, 50, 100, -1],
                [5, 10, 20, 25, 50, 100, "All"]
            ],
            language: {
                lengthMenu: "Display _MENU_ records per page",
                search: "Search:",
                searchPlaceholder: "Search records",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    previous: "Previous",
                    next: "Next"
                }
            },
            createdRow: function(row, data, dataIndex) {
                if (data.status === 'Posted') {
                    $(row).addClass('table-success'); // Menambahkan class pada baris dengan status 'Posted'
                }
            },
            columnDefs: [{
                    width: '10%',
                    targets: [3]
                }, // Atur lebar kolom ke 10% untuk kolom 'customer'
                {
                    width: '100px',
                    targets: [5]
                }, // Atur lebar kolom aksi
                {
                    orderable: false,
                    targets: [0]
                } // Nonaktifkan pengurutan pada kolom pertama (nomor urut)
            ]
        });

        // Filter button
        $('#filter').click(function() {
            dataTable.draw();
        });

        // Reset button
        $('#reset').click(function() {
            $('#start_date').val('');
            $('#end_date').val('');
            dataTable.draw();
        });

        function generateActionButtons(data, currentPage) {
            let buttons = `<div class="btn-group" role="group" aria-label="Action Buttons">
            <a href="/packing-list/${data.id}?page=${currentPage}" class="btn btn-sm btn-primary waves-effect waves-light">
                <i class="bx bx-show-alt"></i>
            </a>`;

            if (data.status == 'Request') {
                buttons += `<form action="/packing-list/${data.id}/post" method="post" class="d-inline" data-id="">
            @method('PUT')
            @csrf
            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Anda yakin mau Post item ini ?')">
                <i class="bx bx-check-circle" title="Posted"> Posted</i>
            </button>
        </form>`;
            } else if (data.status == 'Posted') {
                buttons += `<form action="/packing-list/${data.id}/unpost" method="post" class="d-inline" data-id="">
            @method('PUT')
            @csrf
            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Anda yakin mau Un Post item ini ?')">
                <i class="bx bx-undo" title="Un Posted"> Un Posted</i>
            </button>
        </form>`;
            }

            // Tombol Print dengan target _blank untuk membuka di tab baru
            buttons += `<a href="/print/${data.id}" target="_blank" class="btn btn-sm btn-secondary">
            <i class="bx bx-printer"></i> Print
        </a>`;

            if (data.status == 'Request') {
                buttons += `<a href="/packing-list/${data.id}/edit?page=${currentPage}" class="btn btn-sm btn-warning">Edit</a>

            <form action="/packing-list/${data.id}" method="post" class="d-inline" data-id="">
                @method('DELETE')
                @csrf
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                    <i class="bx bx-trash"></i> Delete
                </button>
            </form>`;
            }

            buttons += `</div>`;

            return buttons;
        }
    });
</script>
@endpush