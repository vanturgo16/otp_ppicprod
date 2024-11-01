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
    
    <form method="post" action="/update_grn_item_smt_po/{{ $goodReceiptNote->id; }}" class="form-material m-t-40" enctype="multipart/form-data">
        @csrf
        @method('PUT')
    <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Good Receipt Note Detail</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                                
                               
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type Product</label>
                                        <div class="col-sm-9">
                                            <input type="radio" id="html" name="type_product" value="{{ $goodReceiptNote->type_product }}" checked>
                                              <label for="html">{{ $goodReceiptNote->type_product }}</label>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Pilih Produk </label>
                                        <div class="col-sm-9">
                                           @if($goodReceiptNote->type_product=='RM')
                                            <select class="form-select data-select2" name="id_master_products">
                                                <option>Pilih Produk RM</option>
                                                @foreach ($rawMaterials as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                        @if($goodReceiptNote->id_master_products == $data->id) selected @endif>
                                                        {{ $data->description }}
                                                    </option>
                                                @endforeach
                                                  
                                            </select>
                                            @elseif($goodReceiptNote->type_product=='WIP')
                                            <select class="form-select data-select2" name="id_master_products">
                                                <option>Pilih Produk WIP</option>
                                                @foreach ($wip as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                        @if($goodReceiptNote->id_master_products == $data->id) selected @endif>
                                                        {{ $data->description }}
                                                    </option>
                                                @endforeach
                                                  
                                            </select>
                                            @elseif($goodReceiptNote->type_product=='TA')
                                            <select class="form-select data-select2" name="id_master_products">
                                                <option>Pilih Produk TA</option>
                                                @foreach ($ta as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                        @if($goodReceiptNote->id_master_products == $data->id) selected @endif>
                                                        {{ $data->description }}
                                                    </option>
                                                @endforeach
                                                  
                                            </select>
                                            @elseif($goodReceiptNote->type_product=='FG')
                                            <select class="form-select data-select2" name="id_master_products">
                                                <option>Pilih Produk FG</option>
                                                @foreach ($fg as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                        @if($goodReceiptNote->id_master_products == $data->id) selected @endif>
                                                        {{ $data->description }}
                                                    </option>
                                                @endforeach
                                                  
                                            </select>
                                            @elseif($goodReceiptNote->type_product=='Other')
                                            <select class="form-select data-select2" name="id_master_products">
                                                <option>Pilih Produk Other</option>
                                                @foreach ($other as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}" 
                                                        @if($goodReceiptNote->id_master_products == $data->id) selected @endif>
                                                        {{ $data->description }}
                                                    </option>
                                                @endforeach
                                                  
                                            </select>
                                            @endif
                                            
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Receipt Qty</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" id="receipt_qty" name="receipt_qty" value="{{ $goodReceiptNote->receipt_qty }}">
                                            <input type="hidden" class="form-control" name="id_good_receipt_notes" value="{{ $goodReceiptNote->id_good_receipt_notes }}">
                                        </div>
                                    </div>

                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Outstanding Qty </label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" id="outstanding_qty" name="outstanding_qty" value="{{ $goodReceiptNote->outstanding_qty }}" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Units </label>
                                        <div class="col-sm-9">
                                            <select class="form-select data-select2" name="master_units_id" id="unit_code">
                                                <option>Pilih Unit</option>
                                                @foreach ($units as $data)
                                                    <option value="{{ $data->id }}" @if($goodReceiptNote->master_units_id == $data->id) selected @endif>
                                                        {{ $data->unit_code }}
                                                    </option>
                                                @endforeach 
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Note </label>
                                        <div class="col-sm-9">
                                            <textarea name="note" rows="4" cols="50" class="form-control">{{ $goodReceiptNote->note }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Status</label>
                                        <div class="col-sm-9">
                                            <select class="form-select data-select2" name="status" id="status">
                                                <option>Pilih Status</option>
                                                <option value="Open" {{ $goodReceiptNote->status == 'Open' ? 'selected' : '' }}>Open</option>
                                                <option value="Close" {{ $goodReceiptNote->status == 'Close' ? 'selected' : '' }}>Close</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row justify-content-end">
                                        <div class="col-sm-9">
                                            <div>
                                                <button type="reset" class="btn btn-info w-md">Reset</button>
                                                <button type="submit" class="btn btn-primary w-md">Update</button>
                                            </div>
                                        </div>
                                    </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <form>
       
                    <!-- end row -->
    </div>
</div>
<script>
    document.getElementById('receipt_qty').addEventListener('input', function() {
        var receiptQty = parseFloat(this.value) || 0;
        var originalOutstandingQty = parseFloat({{ $goodReceiptNote->outstanding_qty }}) || 0;
        
        var newOutstandingQty = originalOutstandingQty - receiptQty;
        
        // Update nilai di field outstanding_qty
        document.getElementById('outstanding_qty').value = newOutstandingQty >= 0 ? newOutstandingQty : 0; // Pastikan tidak negatif
    });
</script>
@endsection