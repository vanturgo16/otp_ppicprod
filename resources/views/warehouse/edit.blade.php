@extends('layouts.master')

@section('konten')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Edit Packing List</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('packing-list') }}">Packing List</a></li>
                                <li class="breadcrumb-item active">Edit Packing List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('packing_list.update', $packingList->id) }}" method="POST"
                                id="packing-list-edit-form">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label for="packing_number" class="form-label">Packing Number</label>
                                    <input type="text" class="form-control" id="packing_number" name="packing_number"
                                        value="{{ $packingList->packing_number }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" name="date"
                                        value="{{ $packingList->date }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="customer" class="form-label">Customer</label>
                                    <input type="text" class="form-control" id="customer" name="customer"
                                        value="{{ $packingList->customer_name }}" readonly>
                                    <input type="hidden" id="customer_id" name="customer_id"
                                        value="{{ $packingList->customer_id }}">
                                </div>
                                <div class="mb-3">
                                    <label for="soNo" class="form-label">SO Number</label>
                                    <input type="text" class="form-control" id="soNo" name="so_number"
                                        value="{{ $packingList->so_number }}" readonly>
                                    <input type="hidden" id="so_id" name="so_id"
                                        value="{{ $packingList->id_sales_orders }}">
                                </div>
                                <div class="mb-3">
                                    <label for="all_barcodes" class="form-label">All Barcodes</label>
                                    <input type="text" class="form-control" id="all_barcodes" name="all_barcodes"
                                        value="{{ $packingList->all_barcodes }}" readonly>
                                </div>
                                <button type="submit" class="btn btn-primary" id="save-button">Update</button>
                                <a href="{{ route('packing-list', ['page' => request()->query('page', 1)]) }}"
                                    class="btn btn-secondary">Kembali</a>

                            </form>
                        </div>
                    </div>
                    <div class="card" id="packing-list-detail-card">
                        <div class="card-body">
                            <h5 class="card-title">Packing List Detail</h5>
                            <form id="packing-list-detail-form">
                                <div class="mb-3" id="change_so_wrapper"
                                    style="{{ $packingList->all_barcodes == 'Y' ? 'display: block;' : 'display: none;' }}">
                                    <label for="change_so" class="form-label">Change SO</label>
                                    <input type="text" class="form-control" id="change_so" name="change_so">
                                </div>
                                <div class="mb-3">
                                    <label for="barcode" class="form-label">Barcode</label>
                                    <input type="text" class="form-control" id="barcode" name="barcode" required>
                                    <div id="barcode-error" class="text-danger" style="display: none;">Barcode tidak
                                        ditemukan</div>
                                </div>
                            </form>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="barcode-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Change SO</th>
                                            <th>Barcode</th>
                                            <th>Product Name</th>
                                            <th>Wrap</th>
                                            <th>Weight</th>
                                            <th>PCS</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($details && $details->isNotEmpty())
                                            @foreach ($details as $index => $detail)
                                                <tr data-id="{{ $detail->id }}">
                                                    <td class="row-number">{{ $index + 1 }}</td>
                                                    <td></td>
                                                    <td>{{ $detail->barcode }}</td>
                                                    <td>{{ $detail->product_description }}</td>
                                                    @if (stripos($detail->sts_start, 'bag'))
                                                        <td><input type="number" class="form-control wrap"
                                                                data-id="{{ $detail->id }}" name="wrap"
                                                                value="{{ $detail->total_wrap }}"></td>
                                                        <td><input type="number" class="form-control weight"
                                                                data-id="{{ $detail->id }}" name="weight"
                                                                value="{{ $detail->weight }}"></td>
                                                        <td><input type="number" class="form-control pcs"
                                                                data-id="{{ $detail->id }}" name="pcs"
                                                                value="{{ $detail->pcs }}"></td>
                                                    @else
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                    @endif
                                                    <td><button type="button"
                                                            class="btn btn-danger btn-sm remove-barcode">Remove</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr id="no-details">
                                                <td colspan="8" class="text-center">No details available</td>
                                            </tr>
                                        @endif
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="packing_list_id" value="{{ $packingList->id }}">
@endsection
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            var today = new Date().toISOString().split('T')[0];
            $('#date').val(today);

            $('#packing-list-edit-form').on('submit', function(e) {
                e.preventDefault();

                var formData = $(this).serialize();
                // console.log("Form Data:", formData);

                $.ajax({
                    url: $(this).attr('action'),
                    method: $(this).attr('method'),
                    data: formData,
                    success: function(response) {
                        // console.log("Response:", response);
                        if (response.success) {
                            Swal.fire('Success', 'Data berhasil diupdate', 'success');
                        } else {
                            Swal.fire('Error', response.error ||
                                'Gagal mengupdate data packing list', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("AJAX Error:", xhr.responseText);
                        Swal.fire('Error', 'Gagal mengupdate data packing list', 'error');
                    }
                });
            });

            $('#barcode').on('input', function() {
                const noDetailsRow = document.getElementById('no-details');
                if (noDetailsRow) {
                    noDetailsRow.remove();
                }
                if ($(this).val().length === 11) {
                    $.ajax({
                        url: '{{ route('check-barcode') }}',
                        method: 'POST',
                        data: {
                            barcode: $(this).val(),
                            customer_id: $('#customer_id').val(),
                            so_id: $('#so_id').val(),
                            change_so: $('#change_so').val(),
                            packing_list_id: $('#packing_list_id').val(),
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            // console.log("Response:", response.exists);


                            if (response.exists) {
                                var newRow = 
                                '<tr data-id="' + response.id + '">' +
                                    '<td class="row-number">' + ($('#barcode-table tbody tr')
                                        .length + 1) + '</td>' +
                                    '<td>' + ($('#change_so').val() || '') + '</td>' +
                                    '<td>' + $('#barcode').val() + '</td>' +
                                    '<td>' + (response.product_name || '') + '</td>' +
                                    (response.is_bag ?
                                        '<td><input type="number" class="form-contro wrap" data-id="' +
                                        response.id + '" name="wrap" value="' +
                                        response.wrap +
                                        '" readonly></td>' +
                                        '<td><input type="number" class="form-control weight" data-id="' +
                                        response.id + '" name="weight" value="' + response
                                        .weight + '" readonly></td>' +
                                        '<td><input type="number" class="form-control pcs" data-id="' +
                                        response.id + '" name="pcs" value="' + response.pcs +
                                        '" readonly></td>' :
                                        '<td></td>' +
                                        '<td></td>' +
                                        '<td></td>') +
                                    '<td><button type="button" class="btn btn-danger btn-sm remove-barcode">Remove</button></td>' +
                                    '</tr>';

                                $('#barcode-table tbody').append(newRow);
                                $('#barcode').val('');
                                $('#barcode').focus();
                                $('#change_so').val('');
                            } else if (response.duplicate) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Barcode sudah terdaftar di packing list',
                                    didClose: () => {
                                        // Kosongkan input barcode setelah pesan error ditutup
                                        $('#barcode').val('').focus();
                                    }
                                });
                            } else if (!response.exists && response.status === false) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                    didClose: () => {
                                        // Kosongkan input barcode setelah pesan error ditutup
                                        $('#barcode').val('').focus();
                                    }
                                });
                            } else {
                                $('#barcode-error').show();
                                setTimeout(function() {
                                    $('#barcode-error').hide();
                                }, 3000);
                            }
                        }
                    });
                }
            });

            $(document).on('change', '.wrap, .weight, .pcs', function() {
                var id = $(this).data('id');
                var field = $(this).attr('name');
                var value = $(this).val();
                var inputElement = $(this);

                $.ajax({
                    url: '{{ route('update-barcode-detail') }}',
                    method: 'POST',
                    data: {
                        id: id,
                        field: field,
                        value: value,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (!response.success) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.error || 'Gagal memperbarui data'
                            }).then(() => {
                                // Reset input pcs ke 0 jika stok tidak mencukupi
                                if (field === 'pcs') {
                                    inputElement.val(0);
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("AJAX Error:", xhr.responseText);
                        Swal.fire('Error', 'Gagal memperbarui data', 'error');
                    }
                });
            });

            $(document).on('click', '.remove-barcode', function() {
                var row = $(this).closest('tr');
                var id = row.data('id');
                var pcs = row.find('.pcs').val(); // Ambil nilai pcs sebelum menghapus
                // console.log(id);

                $.ajax({
                    url: '{{ route('packing_list.remove_barcode') }}',
                    method: 'POST',
                    data: {
                        id: id,
                        pcs: pcs,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            row.remove();
                            updateRowNumbers();
                        } else {
                            console.log(response.message);
                            Swal.fire('Error', 'Gagal menghapus data barcode', 'error');
                        }
                    }
                });
            });

            function updateRowNumbers() {
                $('#barcode-table tbody tr').each(function(index, row) {
                    $(row).find('.row-number').text(index + 1);
                });
            }


            function updateRowNumbers() {
                $('#barcode-table tbody tr').each(function(index, row) {
                    $(row).find('.row-number').text(index + 1);
                });
            }
        });
    </script>
@endpush
