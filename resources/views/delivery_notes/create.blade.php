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
                                    <label for="jenis-dn" class="form-label">Pilih Jenis Delivery Note</label>
                                    <select class="form-control" id="jenis-dn" name="jenis_dn" required
                                        onchange="this.form.submit()">
                                        <option value="Regular" {{ request('jenis_dn') == 'Regular' ? 'selected' : '' }}>
                                            Regular</option>
                                        <option value="Export" {{ request('jenis_dn') == 'Export' ? 'selected' : '' }}>
                                            Export</option>
                                        <option value="Sample" {{ request('jenis_dn') == 'Sample' ? 'selected' : '' }}>
                                            Sample</option>
                                        <option value="Return" {{ request('jenis_dn') == 'Return' ? 'selected' : '' }}>
                                            Return</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="dn_number" class="form-label">Nomor DN</label>
                                    <input type="text" class="form-control" id="dn_number" name="dn_number"
                                        value="{{ $dnNumber }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="date" class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>

                                <div class="mb-3">
                                    <label for="customer" class="form-label">Customer</label>
                                    <select class="form-control select2" id="customers" name="id_master_customer" required>
                                        <option value="" selected disabled>** Pilih Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="sales_order" class="form-label">No. So</label>
                                    <select class="form-control select2" id="soNo" name="so_number" required>
                                        <option value="" disabled selected>*</option>
                                    </select>
                                    <input type="hidden" name="id_master_salesman" value="">
                                </div>
                                <div class="mb-3">
                                    <label for="customer_address" class="form-label">Alamat Shipping</label>
                                    <select class="form-control" id="addressShipping"
                                        name="id_master_customer_address_shipping" required>
                                        <option value="" selected disabled>*</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="invoice_address" class="form-label">Alamat Invoice</label>
                                    <select class="form-control" id="addressInvoice"
                                        name="id_master_customer_address_invoice" required>
                                        <option value="" selected disabled>*</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="vehicle" class="form-label">Kendaraan</label>
                                    <select class="form-control" id="vehicle" name="id_master_vehicle" required>
                                        <option value="" selected disabled>** Pilih Kendaraan</option>
                                        @foreach ($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="note" class="form-label">Catatan</label>
                                    <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <button type="button" class="btn btn-secondary" id="kembali-btn"
                                    style="display: none;">Kembali</button>
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
                                    <select class="form-control select2" id="packing_list" name="packing_list_id"
                                        required>
                                        <option value="" selected disabled>** Pilih Packing List</option>
                                        <!-- Options will be loaded dynamically -->
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="po_number" class="form-label">Nomor PO</label>
                                    <input type="text" class="form-control" id="po_number" name="po_number" required
                                        readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="dn_type" class="form-label">Tipe DN</label>
                                    <input type="text" class="form-control" id="dn_type" name="dn_type" required
                                        readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="type_product" class="form-label">Tipe Produk</label>
                                    <input type="text" class="form-control" id="type_product" name="type_product"
                                        required readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="transaction_type" class="form-label">Tipe Transaksi</label>
                                    <input type="text" class="form-control" id="transaction_type"
                                        name="transaction_type" required readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="salesman_name" class="form-label">Nama Salesman</label>
                                    <input type="text" class="form-control" id="salesman_name" name="salesman_name"
                                        required readonly>
                                </div>
                                <button type="submit" class="btn btn-primary">Tambah Packing List</button>
                            </form>
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Nomor Packing List</th>
                                            <th>Nomor PO</th>
                                            <th>Tipe DN</th>
                                            <th>Tipe Transaksi</th>
                                            <th>Salesman</th>
                                            <th>Remark</th>
                                            <th>Aksi</th>
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
        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            $(document).ready(function() {
                $('#customers').val('').trigger('change');
                var today = new Date().toISOString().split('T')[0];
                $('#date').val(today);
                $('.select2').select2();


                $('#delivery-note-form').on('submit', function(e) {
                    e.preventDefault();
                    var formData = $(this).serialize();

                    $.ajax({
                        url: '{{ route('delivery_notes.store') }}',
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                $('#delivery_note_id').val(response.delivery_note_id);
                                $('#packing-list-card').show();
                                $('#delivery-note-form button[type="submit"]').hide();
                                $('#kembali-btn').show();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Gagal menyimpan data',
                                });
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal menyimpan data',
                            });
                        }
                    });
                });

                $('#kembali-btn').on('click', function() {
                    window.location.href = "{{ route('delivery_notes.list') }}";
                });

                $('#packing_list').change(function() {
                    var packingListId = $(this).val();

                    if (packingListId) {
                        loadPackingListDetails(packingListId);
                    }
                });

                function loadPackingListDetails(packingListId) {
                    $.ajax({
                        url: '{{ url('getPackingListDetails') }}/' + packingListId,
                        method: 'GET',
                        success: function(response) {
                            $('#po_number').val(response.po_number);
                            $('#dn_type').val(response.dn_type);
                            $('#transaction_type').val(response.transaction_type);
                            $('#salesman_name').val(response.salesman_name);
                            $('#type_product').val(response.type_product);
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal memuat detail Packing List',
                            });
                        }
                    });
                }

                $('#packing-list-form').on('submit', function(e) {
                    e.preventDefault();
                    var formData = $(this).serialize();

                    $.ajax({
                        url: '{{ url('delivery_notes') }}/' + $('#delivery_note_id').val() +
                            '/store_packing_list',
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                var packingListId = $('#packing_list').val();
                                var packingListNumber = $('#packing_list option:selected').text();
                                $('#packing-list-details').append(
                                    '<tr data-id="' + packingListId + '">' +
                                    '<td>' + packingListNumber + '</td>' +
                                    '<td>' + response.po_number + '</td>' +
                                    '<td>' + response.dn_type + '</td>' +
                                    '<td>' + response.transaction_type + '</td>' +
                                    '<td>' + response.salesman_name + '</td>' +
                                    '<td><input type="text" class="form-control packing-list-remark" data-id="' +
                                    packingListId + '" placeholder="Remark"></td>' +
                                    '<td><button type="button" class="btn btn-danger btn-sm remove-packing-list">Hapus</button></td>' +
                                    '</tr>'
                                );
                                $('#packing_list').val('').trigger('change');
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message ||
                                        'Gagal menambahkan packing list',
                                });
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal menambahkan packing list',
                            });
                        }
                    });
                });

                $(document).on('click', '.remove-packing-list', function() {
                    var row = $(this).closest('tr');
                    var packingListId = row.data('id');

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
                                url: '{{ url('delivery_notes') }}/' + packingListId +
                                    '/delete_packing_list',
                                method: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        row.remove();
                                        Swal.fire(
                                            'Terhapus!',
                                            'Packing List telah dihapus.',
                                            'success'
                                        );
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: response.message ||
                                                'Gagal menghapus packing list',
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    console.error(xhr.responseText);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Gagal menghapus packing list',
                                    });
                                }
                            });
                        }
                    });
                });

                $(document).on('blur', '.packing-list-remark', function() {
                    var packingListId = $(this).data('id');
                    var remark = $(this).val();

                    $.ajax({
                        url: '{{ url('delivery_notes') }}/' + packingListId + '/update_remark',
                        method: 'PUT',
                        data: {
                            _token: '{{ csrf_token() }}',
                            remark: remark
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Remark berhasil diperbarui',
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Gagal memperbarui remark',
                                });
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal memperbarui remark',
                            });
                        }
                    });
                });

                $('#customers').change(function() {
                    $('#addressShipping, #addressInvoice')
                        .empty()
                        .append('<option value="">-</option>');
                    var customerId = $(this).val();
                    // console.log(customerId);
                    if (customerId) {

                        loadSoNumbers(customerId);
                    }
                });
                $('#soNo').change(function() {
                    var soNo = $(this).val();
                    // console.log(soNo);
                    if (soNo) {
                        loadPackingLists(soNo);
                        loadCustomerAddresses(soNo);
                    }
                });



                //  SO Number
                function loadSoNumbers(customerId) {
                    $.ajax({
                        url: '{{ url('get-so-number-by-customer') }}/' + customerId,
                        method: 'GET',
                        beforeSend: function() {
                            $('#soNo')
                                .empty()
                                .append('<option value="">Loading...</option>');
                        },
                        success: function(response) {
                            $('#soNo').empty().append(
                                '<option value="" disabled selected>** Pilih No. SO</option>');
                            $.each(response, function(index, so) {
                                $('#soNo').append('<option value="' + so.id + '">' + so.so_number +
                                    '</option>');
                            });
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal mengambil data SO',
                            });
                        }
                    });
                }

                function loadPackingLists(soId) {
                    $.ajax({
                        url: '{{ url('getPackingListsBySo') }}/' + soId,
                        method: 'GET',
                        success: function(response) {
                            $('#packing_list').empty().append(
                                '<option value="" selected disabled>** Pilih Packing List</option>');
                            $.each(response, function(key, value) {
                                $('#packing_list').append('<option value="' + value.id + '">' +
                                    value.packing_number + '</option>');
                            });
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal memuat daftar Packing List',
                            });
                        }
                    });
                }
               


                function loadCustomerAddresses(soNo) {
                    $.ajax({
                        url: `{{ url('get-customer-addresses-by-so') }}/${soNo}`,
                        method: 'GET',
                        beforeSend: function() {
                            $('#addressShipping, #addressInvoice')
                                .empty()
                                .append('<option value="">Loading...</option>');
                        },
                        success: function(response) {
                            // console.log("Response Data:", response);
                            $('#addressShipping, #addressInvoice').empty();

                            // Cek jika response kosong
                            if (!response || (!response.shipping && !response.invoice)) {
                                $('#addressShipping').append(
                                    '<option value="">No Shipping Address</option>');
                                $('#addressInvoice').append('<option value="">No Invoice Address</option>');
                                return;
                            }

                            // Shipping address
                            if (response.shipping) {
                                $('#addressShipping')
                                    .append(
                                        `<option value="${response.shipping.id}">${response.shipping.address}</option>`
                                        )
                                    .val(response.shipping.id)
                                    .trigger('change');
                            } else {
                                $('#addressShipping').append(
                                    '<option value="">No Shipping Address</option>');
                            }

                            // Invoice address
                            if (response.invoice) {
                                $('#addressInvoice')
                                    .append(
                                        `<option value="${response.invoice.id}">${response.invoice.address}</option>`
                                        )
                                    .val(response.invoice.id)
                                    .trigger('change');
                            } else {
                                $('#addressInvoice').append('<option value="">No Invoice Address</option>');
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal memuat alamat customer',
                            });
                        }
                    });
                }



            });
        </script>
    @endpush
@endsection
