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
        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Ada kesalahan input:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <form method="post" action="/store-barcode-mesin" class="form-material m-t-40" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Generate Barcode</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">PPIC</a></li>
                                <li class="breadcrumb-item active">Generate Barcode</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Generate Barcode (AUX,RM)</h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="col-sm-12">
                                <div class="mt-4 mt-lg-0">
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Work Orders / Sales Order*</label>
                                        <div class="col-sm-9">
                                            <select class="form-select request_number2 data-select2" name="id_sales_orders" id="id_work_orders" required>
                                                <option value="">Pilih Sales Order</option>
                                                @foreach ($wo as $data)
                                                <option value="{{ $data->id }}" 
                                                    data-id-master-customers="{{ $data->id_master_customers }}"
                                                    data-id-master-products="{{ $data->id_master_products }}"
                                                    data-type-product="{{ $data->type_product }}">
                                                    {{ $data->so_number }} {{ $data->status }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4 field-wrapper">
                                        <label for="id_master_warehouses" class="col-sm-3 col-form-label">Location*</label>
                                        <div class="col-sm-9">
                                            <select class="form-select data-select2" name="id_master_warehouses" id="id_master_warehouses" required>
                                                <option value="">Pilih Gudang</option>
                                                @foreach ($warehouses as $wh)
                                                <option value="{{ $wh->id }}">{{ $wh->warehouse }} ({{ $wh->warehouse_code }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4 field-wrapper">
                                        <label for="shift" class="col-sm-3 col-form-label">Shift*</label>
                                        <div class="col-sm-9">
                                            <select class="form-select data-select2" name="shift" id="shift" required>
                                                <option value="">Pilih Shift</option>
                                                <option value="1">Shift 1</option>
                                                <option value="2">Shift 2</option>
                                                <option value="3">Shift 3</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4 field-wrapper">
                                        <label for="qty" class="col-sm-3 col-form-label">QTY (Jumlah Barcode)*</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" id="qty" name="qty" min="1" value="1" required style="max-width: 400px;">
                                        </div>
                                    </div>

                                    <!-- Hidden input fields -->
                                    <input type="hidden" class="form-control" id="id_master_customers" name="id_master_customers" value="">
                                    <input type="hidden" class="form-control" id="id_master_products" name="id_master_products" value="">
                                    <input type="hidden" class="form-control" id="type_product" name="type_product" value="">
                                    
                                    <div class="row left-content-end">
                                        <div class="col-sm-9">
                                            <div>
                                                <a href="/barcode" class="btn btn-info waves-effect waves-light">Back</a>
                                                <button type="submit" class="btn btn-primary w-md" name="save">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-bordered dt-responsive w-100" style="font-size: small" id="datatable-barcode">

                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Sales Orders</th>
                            <th>Customers </th>
                            <th>Type </th>
                            <th>Product </th>
                            <th>Created_at</th>
                            <th>Jml</th>
                            <th>ID Barcode</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
            
            
                        <tbody>
                            @foreach ($woHasBarcode as $data)
                                
                            
                        <tr style="{{ session('new_barcode_id') == $data->id_barcode ? 'background-color: #d1e7dd; color: #0f5132; font-weight: bold;' : '' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $data->so_number }}
                                @if(session('new_barcode_id') == $data->id_barcode)
                                    <span class="badge bg-success" style="font-size: 10px; margin-left: 5px; vertical-align: middle;">Baru!</span>
                                @endif
                            </td>
                            <td>{{ $data->customer_name ?? 'N/A'}}</td>
                            <td>{{ $data->type_product_barcode ?? 'N/A'}}</td>
                            <td>{{ $data->product_name_aux}} {{ $data->product_name_rm}}</td>

                            <td>{{ $data->created_at }}</td>
                            <td>{{ $data->qty }}</td>
                            <td>{{ $data->id_barcode }}</td>
                         
                            <td>
                                <a href="{{ route('show_barcodemesin', $data->id_barcode) }}" class="btn btn-primary btn-sm">Detail Barcode</a>
                              

                            </td>
                        
                            
                        </tr>
                        @endforeach
                        
                        <!-- Tambahkan data lainnya di sini -->
                        
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
        <!-- end row -->
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi DataTable khusus dengan sorting ID Barcode (index 7) Descending
        $('#datatable-barcode').DataTable({
            order: [[7, 'desc']],
            responsive: false
        });

        $('#id_work_orders').change(function() {
            var selectedOption = $(this).find('option:selected');
            var idMasterCustomers = selectedOption.data('id-master-customers');
            var idMasterProducts = selectedOption.data('id-master-products');
            var typeProduct = selectedOption.data('type-product');

            // Update hidden fields
            $('#id_master_customers').val(idMasterCustomers);
            $('#id_master_products').val(idMasterProducts);
            $('#type_product').val(typeProduct);
        });
    });
</script>
@endsection
