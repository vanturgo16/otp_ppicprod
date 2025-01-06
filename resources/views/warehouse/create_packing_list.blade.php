@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Tambah Packing List</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('packing-list') }}">Packing List</a></li>
                            <li class="breadcrumb-item active">Tambah Packing List</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="/packing-list-store" method="POST" id="packing-list-form">
                            @csrf
                            <div class="mb-3">
                                <label for="packing_number" class="form-label">Packing Number</label>
                                <input type="text" class="form-control" id="packing_number" name="packing_number" value="{{ $nextPackingNumber }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            <div class="mb-3">
                                <label for="customer" class="form-label">Customer</label>
                                <select class="form-control" id="customer" name="customer" required></select>
                                <input type="hidden" id="customer_id" name="customer_id">
                            </div>
                            <div class="mb-3">
                                <label for="all_barcodes" class="form-label">All Barcodes</label>
                                <select class="form-control" id="all_barcodes" name="all_barcodes" required>
                                    <option value="" disabled selected>Please select All Barcodes</option>
                                    <option value="Y">Y</option>
                                    <option value="N">N</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" id="save-button">Simpan</button>
                            <a href="{{ route('packing-list') }}" class="btn btn-secondary">Kembali</a>
                        </form>
                    </div>
                </div>
                <div class="card" id="packing-list-detail-card" style="display: none;">
                    <div class="card-body">
                        <h5 class="card-title">Packing List Detail</h5>
                        <form id="packing-list-detail-form">
                            <input type="hidden" id="packing_list_id" name="packing_list_id">
                            <div class="mb-3" id="change_so_wrapper">
                                <label for="change_so" class="form-label">Change SO</label>
                                <input type="text" class="form-control" id="change_so" name="change_so">
                            </div>
                            <div class="mb-3">
                                <label for="barcode" class="form-label">Barcode</label>
                                <input type="text" class="form-control" id="barcode" name="barcode" required>
                                <div id="barcode-error" class="text-danger" style="display: none;">Barcode tidak ditemukan</div>
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
                                        <th>Number Of Box</th>
                                        <th>Weight</th>
                                        <th>PCS</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data barcode akan dimuat di sini -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        var today = new Date().toISOString().split('T')[0];
        $('#date').val(today);
        $('#customer').select2({
            placeholder: 'Pilih Customer',
            ajax: {
                url: '{{ route("get-customers") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term // Mengambil parameter pencarian
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        }).on('select2:select', function(e) {
            var data = e.params.data;
            $('#customer_id').val(data.id);
        });

        $('#packing-list-form').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();
            console.log("Form Data:", formData);

            $.ajax({
                url: $(this).attr('action'),
                method: $(this).attr('method'),
                data: formData,
                success: function(response) {
                    console.log("Response:", response);
                    if (response.success) {
                        $('#packing-list-detail-card').show();
                        $('#packing-list-form').find('input, select').attr('disabled', true);
                        $('#packing_list_id').val(response.packing_list_id);
                        $('#save-button').hide(); // Menyembunyikan tombol simpan
                        $('#barcode').focus(); // Memfokuskan pada input barcode
                    } else {
                        Swal.fire('Error', response.error || 'Gagal menyimpan data packing list', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.log("AJAX Error:", xhr.responseText);
                    Swal.fire('Error', 'Gagal menyimpan data packing list', 'error');
                }
            });
        });

        $('#all_barcodes').change(function() {
            if ($(this).val() === 'Y') {
                $('#change_so_wrapper').show();
            } else {
                $('#change_so_wrapper').hide();
                $('#change_so').val('');
            }
        }).trigger('change'); // Trigger change event on page load to set the initial state

        $('#barcode').on('input', function() {
            if ($(this).val().length === 11) {
                $.ajax({
                    url: '{{ route("check-barcode") }}',
                    method: 'POST',
                    data: {
                        barcode: $(this).val(),
                        customer_id: $('#customer_id').val(),
                        change_so: $('#change_so').val(),
                        packing_list_id: $('#packing_list_id').val(),
                        pcs: $('#pcs').val(), // Kirim nilai pcs jika ada
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log("Response:", response.exists);

                        if (response.exists) {
                             newRow = '<tr data-id="' + response.id + '">' +
                                '<td class="row-number">' + ($('#barcode-table tbody tr').length + 1) + '</td>' +
                                '<td>' + ($('#change_so').val() || '') + '</td>' +
                                '<td>' + $('#barcode').val() + '</td>' +
                                '<td>' + (response.product_name || '') + '</td>' +
                                (response.is_bag ?
                                    '<td><input type="number" class="form-control number_of_box" data-id="' + response.id + '" name="number_of_box" value="' + ($('#barcode-table tbody tr').length + 1) +'" readonly></td>' +
                                    '<td><input type="number" class="form-control weight" data-id="' + response.id + '" name="weight" value="' + response.pcs + '" readonly></td>' +
                                    '<td><input type="number" class="form-control pcs" data-id="' + response.id + '" name="pcs" value="' + response.pcs + '" readonly></td>' :
                                    '<td></td>' +
                                    '<td></td>' +
                                    '<td></td>') +
                                '<td><button type="button" class="btn btn-danger btn-sm remove-barcode">Remove</button></td>' +
                                '</tr>';

                            $('#barcode-table tbody').append(newRow);
                            $('#barcode').val('');
                            // $('#change_so').val('');
                            $('#barcode').focus();
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

        $(document).on('change', '.number_of_box, .weight, .pcs', function() {
            var id = $(this).data('id');
            var field = $(this).attr('name');
            var value = $(this).val();
            var inputElement = $(this);

            $.ajax({
                url: '{{ route("update-barcode-detail") }}',
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

            $.ajax({
                url: '{{ route("packing_list.remove_barcode") }}',
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
    });
</script>
@endsection