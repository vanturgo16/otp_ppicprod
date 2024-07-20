@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">
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
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Edit Delivery Notes</h5>
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <form action="{{ route('delivery_notes.update', $deliveryNote->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="date" class="form-label">Tanggal</label>
                                <input type="date" class="form-control" id="date" name="date" value="{{ old('date', $deliveryNote->date) }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="customer_address" class="form-label">Alamat Customer</label>
                                <select class="form-select select2" id="customer_address" name="customer_address" required>
                                    <option value="" selected disabled>** Pilih Alamat Customer</option>
                                    @foreach($customerAddresses as $address)
                                    <option value="{{ $address->id }}" {{ old('customer_address', $deliveryNote->id_master_customer_addresses) == $address->id ? 'selected' : '' }}>{{ $address->address }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="id_master_vehicle" class="form-label">Kendaraan</label>
                                <select class="form-select select2" id="id_master_vehicle" name="id_master_vehicle" required>
                                    <option value="" selected disabled>** Pilih Kendaraan</option>
                                    @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ old('id_master_vehicle', $deliveryNote->id_master_vehicles) == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->vehicle_number }}</option>
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
                <div class="card" id="packing-list-card">
                    <div class="card-body">
                        <h5 class="card-title">Tambah Packing List</h5>
                        <form id="packing-list-form">
                            @csrf
                            <input type="hidden" id="delivery_note_id" name="delivery_note_id" value="{{ $deliveryNote->id }}">
                            <div class="mb-3">
                                <label for="packing_list" class="form-label">Packing List</label>
                                <select class="form-control select2" id="packing_list" name="packing_list_id" required>
                                    <option value="" selected disabled>** Pilih Packing List</option>
                                    @foreach($packingLists as $packing_list)
                                    <option value="{{ $packing_list->id }}">{{ $packing_list->packing_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="po_number" class="form-label">Nomor PO</label>
                                <input type="text" class="form-control" id="po_number" name="po_number" required>
                            </div>
                            <div class="mb-3">
                                <label for="dn_type" class="form-label">Tipe DN</label>
                                <input type="text" class="form-control" id="dn_type" name="dn_type" required>
                            </div>
                            <div class="mb-3">
                                <label for="transaction_type" class="form-label">Tipe Transaksi</label>
                                <input type="text" class="form-control" id="transaction_type" name="transaction_type" required>
                            </div>
                            <div class="mb-3">
                                <label for="salesman_name" class="form-label">Nama Salesman</label>
                                <input type="text" class="form-control" id="salesman_name" name="salesman_name" required>
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
                                    </tr>
                                </thead>
                                <tbody id="packing-list-details">
                                    @foreach($deliveryNoteDetails as $detail)
                                    <tr>
                                        <td>{{ $detail->packing_number }}</td>
                                        <td>{{ $detail->po_number }}</td>
                                        <td>{{ $detail->dn_type }}</td>
                                        <td>{{ $detail->transaction_type }}</td>
                                        <td>{{ $detail->salesman_name }}</td>
                                    </tr>
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
<script>
    $(document).ready(function() {
        $('.select2').select2();

        $('#packing_list').change(function() {
            let packingListId = $(this).val();
            if (packingListId) {
                $.ajax({
                    url: '/getPackingListDetails/' + packingListId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#po_number').val(data.sales_order_id); // Store sales_order_id in hidden input
                        $('#po_number_display').val(data.reference_number + ' - ' + data.so_number); // Display reference number
                        $('#id_master_customer').val(data.customer_id);
                        $('#customer_name').val(data.customer_name);
                        $('#dn_type').val(data.so_category);
                        $('#transaction_type').val(data.so_type);
                        $('#id_master_salesman').val(data.salesman_id);
                        $('#salesman_name').val(data.salesman_name);
                    }
                });
            }
        });
    });
</script>
@endpush
@endsection