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
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Warehouse</a></li>
                            <li class="breadcrumb-item active">Tambah Delivery Notes</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tambah Delivery Notes</h5>
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
                        <form action="{{ route('delivery_notes.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="dn_number" class="form-label">Nomor DN</label>
                                <input type="text" class="form-control" id="dn_number" name="dn_number" value="{{ old('dn_number', $dnNumber) }}" required readonly>
                            </div>
                            <div class="mb-3">
                                <label for="date" class="form-label">Tanggal</label>
                                <input type="date" class="form-control" id="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="id_packing_lists" class="form-label">Nomor Packing</label>
                                <select class="form-select select2" id="id_packing_lists" name="id_packing_lists" required>
                                    <option value="" selected disabled>** Pilih Nomor Packing</option>
                                    @foreach($packingLists as $packing_list)
                                    <option value="{{ $packing_list->id }}" {{ old('id_packing_lists') == $packing_list->id ? 'selected' : '' }}>{{ $packing_list->packing_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="po_number" class="form-label">Nomor PO</label>
                                <input type="hidden" id="id_sales_order" name="id_sales_order" value="{{ old('id_sales_order') }}" required>
                                <input type="text" class="form-control" id="po_number" name="po_number" value="{{ old('po_number') }}" readonly required>
                            </div>
                            <div class="mb-3">
                                <label for="id_master_customer" class="form-label">Nama Customer</label>
                                <input type="hidden" id="id_master_customer" name="id_master_customer" value="{{ old('id_master_customer') }}" required>
                                <input type="text" class="form-control" name="customer_name" id="customer_name" value="{{ old('customer_name') }}" readonly required>
                            </div>
                            <div class="mb-3">
                                <label for="dn_type" class="form-label">Tipe DN</label>
                                <input type="text" class="form-control" id="dn_type" name="dn_type" value="{{ old('dn_type') }}" readonly required>
                            </div>
                            <div class="mb-3">
                                <label for="transaction_type" class="form-label">Tipe Transaksi</label>
                                <input type="text" class="form-control" id="transaction_type" name="transaction_type" value="{{ old('transaction_type') }}" readonly required>
                            </div>
                            <div class="mb-3">
                                <label for="id_master_salesman" class="form-label">Salesman</label>
                                <input type="hidden" id="id_master_salesman" name="id_master_salesman" value="{{ old('id_master_salesman') }}" required>
                                <input type="text" class="form-control" name="salesman_name" id="salesman_name" value="{{ old('salesman_name') }}" readonly required>
                            </div>
                            <div class="mb-3">
                                <label for="id_master_vehicle" class="form-label">Kendaraan</label>
                                <select class="form-select select2" id="id_master_vehicle" name="id_master_vehicle" required>
                                    <option value="" selected disabled>** Pilih Kendaraan</option>
                                    @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ old('id_master_vehicle') == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->vehicle_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="note" class="form-label">Catatan</label>
                                <textarea class="form-control" id="note" name="note" rows="3">{{ old('note') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <!-- <label for="status" class="form-label">Status</label> -->
                                <input type="text" class="form-control" id="status" name="status" value="Request" readonly>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('delivery_notes.list') }}" class="btn btn-secondary">Kembali</a>
                        </form>
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
        $('.select2').select2();

        $('#id_packing_lists').change(function() {
            let packingListId = $(this).val();
            if (packingListId) {
                $.ajax({
                    url: '/getPackingListDetails/' + packingListId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#id_sales_order').val(data.sales_order_id);
                        $('#po_number').val(data.reference_number);
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