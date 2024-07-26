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
                    <h4 class="mb-sm-0 font-size-18">Delivery Notes</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Warehouse</a></li>
                            <li class="breadcrumb-item active">Delivery Notes</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-12">
                <a href="{{ route('delivery_notes.create') }}" class="btn btn-primary">Tambah Data</a>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Delivery Notes</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="delivery_notes_table" class="table table-bordered dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>DN Number</th>
                                        <th>Packing Numbers</th>
                                        <th>PO Number</th>
                                        <th>Date</th>
                                        <th>DN Type</th>
                                        <th>Transaction Type</th>
                                        <th>Vehicles</th>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        let dataTable = $('#delivery_notes_table').DataTable({
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
            pageLength: 10,
            lengthMenu: [
                [5, 10, 20, 25, 50, 100],
                [5, 10, 20, 25, 50, 100]
            ],
            aaSorting: [],
            ajax: {
                url: '{{ route("delivery_notes.list") }}',
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
                    data: 'dn_number',
                    name: 'dn_number',
                    orderable: true
                },
                {
                    data: 'packing_numbers',
                    name: 'packing_numbers',
                    orderable: true
                },
                {
                    data: 'po_number',
                    name: 'po_number',
                    orderable: true,
                    render: function(data, type, row) {
                        return data; // Menampilkan reference_number yang sudah dijoin
                    }
                },
                {
                    data: 'date',
                    name: 'date',
                    orderable: true
                },
                {
                    data: 'dn_type',
                    name: 'dn_type',
                    orderable: true
                },
                {
                    data: 'transaction_type',
                    name: 'transaction_type',
                    orderable: true
                },
                {
                    data: 'vehicle',
                    name: 'vehicle',
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
                        return generateActionButtons(row);
                    }
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
                    targets: [5]
                },
                {
                    orderable: false,
                    targets: [0]
                }
            ],
        });

        function generateActionButtons(data) {
            let buttons = `<div class="btn-group" role="group" aria-label="Action Buttons">
    <a href="/delivery_notes/${data.id}/show" class="btn btn-sm btn-primary waves-effect waves-light">
        <i class="bx bx-show-alt"></i>
    </a>`;

            if (data.status == 'Request') {
                buttons += `<form action="/delivery_notes/${data.id}/post" method="post" class="d-inline" data-id="">
        @method('PUT')
        @csrf
        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Anda yakin mau Post item ini ?')">
            <i class="bx bx-check-circle" title="Posted"> Posted</i>
        </button>
    </form>`;
            } else if (data.status == 'Posted') {
                buttons += `<form action="/delivery_notes/${data.id}/unpost" method="post" class="d-inline" data-id="">
        @method('PUT')
        @csrf
        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Anda yakin mau Un Post item ini ?')">
            <i class="bx bx-undo" title="Un Posted"> Un Posted</i>
        </button>
    </form>`;
            }

            buttons += `<a href="/delivery_notes/${data.id}/print" class="btn btn-sm btn-secondary">
    <i class="bx bx-printer"></i> Print
</a>`;
            buttons += `<a href="/print_packing_list/${data.id}" class="btn btn-sm btn-info">
    <i class="bx bx-book-open"></i> Packing List
</a>`;

            if (data.status == 'Request') {
                buttons += `<a href="/delivery_notes/${data.id}/edit" class="btn btn-sm btn-warning">
        <i class="bx bx-edit"></i> Edit
    </a>
    <form action="/delivery_notes/${data.id}" method="post" class="d-inline delete-delivery-note" data-id="${data.id}">
        @method('DELETE')
        @csrf
        <button type="submit" class="btn btn-sm btn-danger">
            <i class="bx bx-trash"></i> Delete
        </button>
    </form>`;
            }

            buttons += `</div>`;

            return buttons;
        }


        $(document).on('submit', '.delete-delivery-note', function(e) {
            e.preventDefault();
            var form = $(this);
            var id = form.data('id');

            Swal.fire({
                title: 'Apakah anda yakin?',
                text: "Data ini akan dihapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                dataTable.ajax.reload();
                                Swal.fire(
                                    'Terhapus!',
                                    'Delivery Note telah dihapus.',
                                    'success'
                                );
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Gagal menghapus Delivery Note',
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal menghapus Delivery Note',
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection