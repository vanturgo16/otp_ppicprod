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
    <form method="post" action="/update_detail_ext_nolot" class="form-material m-t-40" enctype="multipart/form-data">
    @method('PUT')
    @csrf
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> Edit Good Receipt Note</h4>
                   
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">PPIC</a></li>
                            <li class="breadcrumb-item active"> Edit Good Receipt Note From PO</li>
                        </ol>
                    </div>
                </div>
                
                <div></div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Good Receipt Note</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                                
                    
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Id GRN Detail</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="id_grn_detail" class="form-control" value="{{ $detail_ext_nolot->id_grn_detail }}" readonly>
                                            <input type="hidden" name="id" class="form-control" value="{{ $detail_ext_nolot->id }}">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Lot Number</label>
                                        <div class="col-sm-9">
                                        <input type="text" name="lot_number" class="form-control" value="{{ $detail_ext_nolot->lot_number }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">External Lot Number</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="ext_lot_number" class="form-control" value="{{ $detail_ext_nolot->ext_lot_number }}">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">QTY </label>
                                        <div class="col-sm-9">
                                        <input type="text" class="form-control" name="qty" value="{{ $detail_ext_nolot->qty }}">
                                        </div>
                                    </div>
                                    
                                    <div class="row left-content-end">
                                    <div class="col-sm-9">
                                        <div>
                                            <a href="/good-receipt-note" class="btn btn-info waves-effect waves-light">Back</a>
                                            <button type="submit" class="btn btn-primary w-md" name="save">Update</button>
                                        </div>
                                    </div>
                                </div>
                                    
                        
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </form>
    

       
                    <!-- end row -->
    </div>
</div>
@endsection