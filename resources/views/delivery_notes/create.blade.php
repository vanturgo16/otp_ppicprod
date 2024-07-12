@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Tambah Delivery Notes</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('delivery_notes.list') }}">Warehouse</a></li>
                            <li class="breadcrumb-item active">Tambah Delivery Notes</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form id="delivery-note-form">
                            @csrf
                            <div class="mb-3">
                                <label for="dn_number" class="form-label">Nomor DN</label>
                                <input type="text" class="form-control" id="dn_number" name="dn_number" value="{{ $dnNumber }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="date" class="form-label">Tanggal</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            <div class="mb-3">
                                <label for="customer" class="form-label">Customer</label>
                                <select class="form-control select2" id="customer" name="id_master_customer" required>
                                    <option value="" selected disabled>** Pilih Customer</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="vehicle" class="form-label">Kendaraan</label>
                                <select class="form-control select2" id="vehicle" name="id_master_vehicle" required>
                                    <option value="" selected disabled>** Pilih Kendaraan</option>
                                    @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="note" class="form-label">Catatan</label>
                                <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </form>
                    </div>
                </div>
                <div class="card" id="packing-list-card" style="display: none;">
                    <div class="card-body">
                        <h5 class="card-title">Tambah Packing List</h5>
                        <form id="packing-list-form">
                            @csrf
                            <input type="hidden" id="delivery_note_id" name="delivery_note_id">
                            <div class="mb-3">
                                <label for="packing_list" class="form-label">Packing List</label>
                                <select class="form-control select2" id="packing_list" name="packing_list_id" required>
                                    <option value="" selected disabled>** Pilih Packing List</option>
                                    <!-- Options will be loaded dynamically -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="po_number" class="form-label">PO Number</label>
                                <input type="text" class="form-control" id="po_number" name="po_number" required>
                            </div>
                            <div class="mb-3">
                                <label for="dn_type" class="form-label">DN Type</label>
                                <input type="text" class="form-control" id="dn_type" name="dn_type" required>
                            </div>
                            <div class="mb-3">
                                <label for="transaction_type" class="form-label">Transaction Type</label>
                                <input type="text" class="form-control" id="transaction_type" name="transaction_type" required>
                            </div>
                            <div class="mb-3">
                                <label for="salesman_name" class="form-label">Salesman Name</label>
                                <input type="text" class="form-control" id="salesman_name" name="salesman_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Tambah Packing List</button>
                        </form>
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Packing List Number</th>
                                        <th>PO Number</th>
                                        <th>DN Type</th>
                                        <th>Transaction Type</th>
                                        <th>Salesman</th>
                                    </tr>
                                </thead>
                                <tbody id="packing-list-details"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2();

        $('#delivery-note-form').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: '{{ route("delivery_notes.store") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#delivery_note_id').val(response.delivery_note_id);
                        $('#packing-list-card').show();
                        $('#delivery-note-form button[type="submit"]').text('Back').removeClass('btn-primary').addClass('btn-secondary'); // Ubah teks dan gaya tombol
                    } else {
                        alert(response.message || 'Gagal menyimpan data');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Gagal menyimpan data');
                }
            });
        });

        $('#packing-list-form').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: '{{ url("delivery_notes") }}/' + $('#delivery_note_id').val() + '/store_packing_list',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        var packingListId = $('#packing_list').val();
                        var packingListNumber = $('#packing_list option:selected').text();
                        $('#packing-list-details').append(
                            '<tr>' +
                            '<td>' + packingListNumber + '</td>' +
                            '<td>' + response.po_number + '</td>' +
                            '<td>' + response.dn_type + '</td>' +
                            '<td>' + response.transaction_type + '</td>' +
                            '<td>' + response.salesman_name + '</td>' +
                            '</tr>'
                        );
                        $('#packing_list').val('').trigger('change');
                        $('#delivery-note-form button[type="submit"]').text('Simpan').removeClass('btn-secondary').addClass('btn-primary'); // Kembalikan teks dan gaya tombol
                    } else {
                        alert(response.message || 'Gagal menambahkan packing list');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Gagal menambahkan packing list');
                }
            });

        });

        $('#customer').change(function() {
            var customerId = $(this).val();
            if (customerId) {
                loadPackingLists(customerId);
            }
        });

        function loadPackingLists(customerId) {
            $.ajax({
                url: '{{ url("getPackingListsByCustomer") }}/' + customerId,
                method: 'GET',
                success: function(response) {
                    $('#packing_list').empty().append('<option value="" selected disabled>** Pilih Packing List</option>');
                    $.each(response, function(key, value) {
                        $('#packing_list').append('<option value="' + value.id + '">' + value.packing_number + '</option>');
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Gagal memuat daftar Packing List');
                }
            });
        }

    });
</script>
@endpush
@endsection