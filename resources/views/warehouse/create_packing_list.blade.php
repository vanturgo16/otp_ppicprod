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
                        <form action="{{ route('packing_list.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="packing_number" class="form-label">Packing Number</label>
                                <input type="text" class="form-control" id="packing_number" name="packing_number" required>
                            </div>
                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            <div class="mb-3">
                                <label for="customer" class="form-label">Customer</label>
                                <select class="form-control" id="customer" name="customer" required></select>
                            </div>
                            <div class="mb-3">
                                <label for="all_barcodes" class="form-label">All Barcodes</label>
                                <select class="form-control" id="all_barcodes" name="all_barcodes" required>
                                    <option value="" disabled selected>Please select All Barcodes</option>
                                    <option value="Y">Y</option>
                                    <option value="N">N</option>
                                </select>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex flex-wrap align-items-center mb-4">
                                        <h5 class="card-title me-2">Packing List Detail</h5>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-3" id="change_so_wrapper">
                                                <label for="change_so" class="form-label">Change SO</label>
                                                <input type="text" class="form-control" id="change_so" name="change_so">
                                            </div>
                                            <div class="mb-3">
                                                <label for="barcode" class="form-label">Barcode</label>
                                                <input type="text" class="form-control" id="barcode" name="barcode" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="number_of_box" class="form-label">Number Of Box</label>
                                                <input type="number" class="form-control" id="number_of_box" name="number_of_box">
                                            </div>
                                            <div class="mb-3">
                                                <label for="weight" class="form-label">Weight</label>
                                                <input type="text" class="form-control" id="weight" name="weight">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('packing-list') }}" class="btn btn-secondary">Kembali</a>
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
        });

        // Menyembunyikan atau menampilkan input Change SO berdasarkan pilihan All Barcodes
        $('#all_barcodes').on('change', function() {
            if ($(this).val() === 'N') {
                $('#change_so_wrapper').hide();
                $('#barcode').focus();
            } else if ($(this).val() === 'Y') {
                $('#change_so_wrapper').show();
                $('#change_so').focus();
            }
        });

        // Panggil event change saat pertama kali halaman dimuat
        $('#all_barcodes').trigger('change');
    });
</script>
@endpush