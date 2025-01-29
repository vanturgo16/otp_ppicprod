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
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Sales Order*</label>
                                        <div class="col-sm-9">
                                            <select class="form-select request_number2 data-select2" name="id_sales_orders" id="id_work_orders">
                                                <option>Pilih Sales Order</option>
                                                @foreach ($wo as $data)
                                                <option value="{{ $data->id }}" 
                                                   
                                                    
                                                    data-id-master-customers="{{ $data->id_master_customers }}"
                                                    data-id-master-products="{{ $data->id_master_products }}"
                                                    
                                                 
                                                    data-type-product="{{ $data->type_product }}"> <!-- Tambahkan ini -->
                                                    {{ $data->so_number }} {{ $data->status }}
                                                </option>
                                                
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!-- Hidden input fields -->
                                    

                                            <input type="hidden" class="form-control" id="id_master_customers" name="id_master_customers" value="">
                                            <input type="hidden" class="form-control" id="id_master_products" name="id_master_products" value="">
                                            <input type="hidden" class="form-control" id="type_product" name="type_product" value="">
                                            <input type="hidden" class="form-control" id="qty" name="qty" value="1">


                                    
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

                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="width:100%">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Sales Orders</th>
                            <th>Customers </th>
                            <th>Type </th>
                            <th>Product </th>
                           
                            <th>Creted_at</th>
                           
                           
                        </tr>
                        </thead>
            
            
                        <tbody>
                            @foreach ($wo as $data)
                                
                            
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $data->so_number }}</td>
                            <td>{{ $data->customer_name ?? 'N/A'}}</td>
                            <td>{{ $data->type_product ?? 'N/A'}}</td>
                            <td>{{ $data->product_name_aux}} {{ $data->product_name_rm}}</td>

                            <td>{{ $data->created_at }}</td>
                        
                            
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
        $('#id_work_orders').change(function() {
            var selectedOption = $(this).find('option:selected');
            var idSalesOrders = selectedOption.data('id-sales-orders');
            var idMasterProductsMaterial = selectedOption.data('id-master-products-material');
            var idMasterCustomers = selectedOption.data('id-master-customers');
            var idMasterProducts = selectedOption.data('id-master-products');
            var typeProductCode = selectedOption.data('type-product-code');
            var groupSubCode = selectedOption.data('group-sub-code');
            var typeProduct = selectedOption.data('type-product'); //

            // Update hidden fields
            $('#id_sales_orders').val(idSalesOrders);
            $('#id_master_process_productions').val(idMasterProductsMaterial);
            $('#id_master_customers').val(idMasterCustomers);
            $('#id_master_products').val(idMasterProducts);
            $('#type_product_code').val(typeProductCode);
            $('#group_sub_code').val(groupSubCode);
            $('#type_product').val(typeProduct); // Tambahkan
        });
    });
</script>
@endsection
