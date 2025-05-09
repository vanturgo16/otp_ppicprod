@extends('layouts.master')

@section('konten')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.26.0/dist/ui/trumbowyg.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.26.0/dist/trumbowyg.min.js"></script>

    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Edit Delivery Notes</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('delivery_notes.list') }}">Warehouse</a></li>
                                <li class="breadcrumb-item active">Edit Delivery Notes</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Edit Delivery Note -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form id="edit-delivery-note-form"
                                action="{{ route('delivery_notes.update', $deliveryNote->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="dn_number" class="form-label">Nomor DN</label>
                                    <input type="text" class="form-control" id="dn_number" name="dn_number"
                                        value="{{ old('dn_number', $deliveryNote->dn_number) }}" required readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="date" class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" id="date" name="date"
                                        value="{{ old('date', $deliveryNote->date) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="customers" class="form-label">Customer</label>
                                    <select class="form-control select2" id="customers" name="id_master_customer" required>
                                        <option value="" disabled>Pilih Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ old('id_master_customers', $deliveryNote->customer_id) == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="sales_order" class="form-label">No. So</label>
                                    <select class="form-control select2" id="soNo" name="so_number" required>
                                        <option value="" ></option>
                                        @foreach ($soNo as $noSo)
                                            <option
                                                value="{{ $noSo->id }}"{{ old('id_sales_orders', $deliveryNote->so_id) == $noSo->id ? 'selected' : '' }}>
                                                {{ $noSo->so_number }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="customer_address" class="form-label">Alamat Shipping</label>
                                    <select class="form-control" id="addressShipping"
                                        name="id_master_customer_address_shipping" required>
                                        @foreach ($shipAddress as $address)
                                            <option value="{{ $address->id }}"
                                                {{ old('id_master_customer_address_invoice', $deliveryNote->id_master_customer_addresses_shipping ?? '') == $address->id ? 'selected' : '' }}>
                                                {{ $address->address }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="invoice_address" class="form-label">Alamat Invoice</label>
                                    <select class="form-control" id="addressInvoice"
                                        name="id_master_customer_address_invoice" required>
                                        @foreach ($invAddress as $address)
                                            <option value="{{ $address->id }}"
                                                {{ old('id_master_customer_address_invoice', $deliveryNote->id_master_customer_addresses_invoice ?? '') == $address->id ? 'selected' : '' }}>
                                                {{ $address->address }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="vehicle" class="form-label">Kendaraan</label>
                                    <select class="form-control select2" id="vehicle" name="id_master_vehicles" required>
                                        <option value="" selected disabled>** Pilih Kendaraan</option>
                                        @foreach ($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}"
                                                {{ old('id_master_vehicles', $deliveryNote->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>
                                                {{ $vehicle->vehicle_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="note" class="form-label">Catatan</label>
                                    <textarea class="form-control" id="note" name="note" rows="3">{{ old('note', $deliveryNote->note) }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="{{ route('delivery_notes.list') }}" class="btn btn-secondary">Kembali</a>
                            </form>
                        </div>
                    </div>

                    <!-- Form Tambah Packing List -->
                    <div class="card" id="packing-list-card" style="display: block;">
                        <div class="card-body">
                            <h5 class="card-title">Tambah Packing List</h5>
                            <form id="packing-list-form">
                                @csrf
                                <input type="hidden" id="delivery_note_id" name="delivery_note_id"
                                    value="{{ $deliveryNote->id }}">
                                <div class="mb-3">
                                    <label for="packing_list" class="form-label">Packing List</label>
                                    <select class="form-control select2" id="packing_list" name="packing_list_id" required>
                                        <option value="" selected disabled>** Pilih Packing List</option>
                                        @foreach ($packingLists as $packing_list)
                                            <option value="{{ $packing_list->id }}">{{ $packing_list->packing_number }}
                                            </option>
                                        @endforeach
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
                                    <tbody id="packing-list-details">
                                        @foreach ($deliveryNoteDetails as $detail)
                                            <tr data-id="{{ $detail->id_packing_lists }}">
                                                <td>{{ $detail->packing_number }}</td>
                                                <td>{{ $detail->po_number }}</td>
                                                <td>{{ $detail->dn_type }}</td>
                                                <td>{{ $detail->transaction_type }}</td>
                                                <td>{{ $detail->salesman_name }}</td>
                                                <td>
                                                    <input id="remark" type="text"
                                                        class="form-control packing-list-remark"
                                                        data-id="{{ $detail->id_packing_lists }} "
                                                        value="{{ $detail->remark }}">
                                                </td>
                                                <td><button type="button"
                                                        class="btn btn-danger btn-sm remove-packing-list">Hapus</button>
                                                </td>
                                        @endforeach
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
        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            $(document).ready(function() {
                $('#edit-delivery-note-form').on('submit', function(e) {
                    e.preventDefault();
                    var formData = $(this).serialize();

                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.message,
                                }).then(() => {
                                    window.location.href =
                                        "{{ route('delivery_notes.list') }}";
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Failed to update data',
                                });
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to update data',
                            });
                        }
                    });
                });
                $('.select2').select2();

                $('#packing_list').change(function() {
                    let packingListId = $(this).val();
                    if (packingListId) {
                        $.ajax({
                            url: '/getPackingListDetails/' + packingListId,
                            type: 'GET',
                            dataType: 'json',
                            success: function(data) {
                                $('#po_number').val(data.po_number);
                                $('#dn_type').val(data.dn_type);
                                $('#transaction_type').val(data.transaction_type);
                                $('#salesman_name').val(data.salesman_name);
                                $('#type_product').val(data.type_product);
                            }
                        });
                    }
                });

                $('#packing-list-form').on('submit', function(e) {
                    e.preventDefault();
                    let formData = $(this).serialize();
                    $.ajax({
                        url: '{{ url('delivery_notes') }}/' + $('#delivery_note_id').val() +
                            '/store_packing_list',
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                let packingListId = $('#packing_list').val();
                                let packingListNumber = $('#packing_list option:selected').text();
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
                
                $('#customers').change(function() {
                    $('#addressShipping, #addressInvoice').empty()
                    var customerId = $(this).val();
                    if (customerId) {
                        loadSoNumbers(customerId);
                    }
                });


                function loadPackingLists(packingListId) {
                    $.ajax({
                        url: '{{ url('getPackingListDetails') }}/' + packingListId,
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
                $('#soNo').change(function() {
                    var soNo = $(this).val();
                    // console.log(soNo);
                    if (soNo) {
                        loadCustomerAddresses(soNo);
                        loadPackingLists(soNo);
                    }
                });

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
            });
        </script>
    @endpush
@endsection
