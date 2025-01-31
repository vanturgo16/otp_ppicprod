@extends('layouts.master')
@section('konten')
@include('layouts.additional')

<div class="page-content">
    <div class="container-fluid">
        <div class="row custom-margin">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="page-title-left">
                        <a href="{{ route('grn.edit', encrypt($data->id_good_receipt_notes)) }}" class="btn btn-light waves-effect btn-label waves-light">
                            <i class="mdi mdi-arrow-left label-icon"></i> Back To Data Good Receipt Note
                        </a>
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Good Receipt Note</a></li>
                            <li class="breadcrumb-item active"> Edit Item GRN ({{ $data->type_product }})</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.alert') 
        {{-- ITEM PR --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Good Receipt Note Detail ({{ $data->type_product }})</h4>
            </div>
            <form method="POST" action="{{ route('grn.update', encrypt($data->id)) }}" class="form-material m-t-40 formLoad" enctype="multipart/form-data">
                @csrf
                <div class="card-body p-4">
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Type Product</label>
                        <div class="col-sm-9">
                            <input type="radio" name="type_product" value="{{ $data->type_product }}" checked>
                            <label for="html">{{ $data->type_product }}</label>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Product {{ $data->type_product }}</label>
                        <div class="col-sm-9">
                            <select class="form-select request_number input-select2" name="id_master_products" style="width: 100%" required>
                                <option value="">Pilih Product {{ $data->type_product }}</option>
                                @foreach ($products as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data->id_master_products ? 'selected' : '' }}>{{ $item->description }}
                                        @if($data->type_product == 'FG')
                                            @if(!empty($item->perforasi)) || {{ $item->perforasi }} @endif
                                            @if(!empty($item->group_sub_code)) || Group Sub: {{ $item->group_sub_code }} @endif
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Qty</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control custom-bg-gray" name="qty" value="{{ $data->qty }}" placeholder="Masukkan Qty.." required readonly>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Receipt Qty</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" name="receipt_qty" value="{{ $data->receipt_qty }}" placeholder="Masukkan Receipt Qty.." required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Outstanding Qty</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control custom-bg-gray" name="outstanding_qty" value="{{ $data->outstanding_qty }}" placeholder="Masukkan Outstanding Qty.." required readonly>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Units</label>
                        <div class="col-sm-9">
                            <select class="form-select input-select2" name="master_units_id" style="width: 100%" required>
                                <option value="">Pilih Units</option>
                                @foreach ($units as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data->master_units_id ? 'selected' : '' }}>{{ $item->unit_code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label class="col-sm-3 col-form-label">Notes</label>
                        <div class="col-sm-9">
                            <textarea name="note" rows="3" cols="50" class="form-control" placeholder="Note.. (Opsional)">{{ $data->note }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row text-end">
                        <div>
                            <button type="reset" class="btn btn-secondary waves-effect btn-label waves-light">
                                <i class="mdi mdi-reload label-icon"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-info waves-effect btn-label waves-light">
                                <i class="mdi mdi-update label-icon"></i>Update
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
