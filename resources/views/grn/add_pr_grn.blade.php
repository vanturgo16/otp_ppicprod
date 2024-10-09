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
    <form method="post" action="/simpan_pr_grn" class="form-material m-t-40" enctype="multipart/form-data">
    @csrf
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> Add Good Receipt Note</h4>
                   
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">PPIC</a></li>
                            <li class="breadcrumb-item active"> Add Good Receipt Note From PR</li>
                        </ol>
                    </div>
                </div>
                <a href="/good-receipt-note" class="btn btn-info waves-effect waves-light">Back To List Data Good Receipt Note</a>
                <div></div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Good Receipt Note</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                                
                    
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Receipt Number</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="receipt_number" class="form-control" value="{{ $formattedCode }}" readonly>
                                            <input type="hidden" name="non_invoiceable" class="form-control" value="N" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Reference Number (PR) </label>
                                        <div class="col-sm-9">
                                            <select class="form-select request_number data-select2" name="reference_number" id="choices-single-default" onchange="get_data_pr()">
                                                <option>Pilih Rsdsdeference Number (PR) </option>
                                                @foreach ($pr as $data)
                                                    <option value="{{ $data->id }}" data-id="{{ $data->id }}">{{ $data->request_number }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Date</label>
                                        <div class="col-sm-9">
                                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">External Doc Number</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="external_doc_number" class="form-control" value="">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Suppliers </label>
                                        <div class="col-sm-9">
                                            <select class="form-select data-select2" name="id_master_suppliers" id="id_master_suppliers">
                                                <option>Pilih Suppliers</option>
                                                
                                                    <option value=""></option>
                                                
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Note </label>
                                        <div class="col-sm-9">
                                            <textarea name="note" rows="4" cols="50" class="form-control"></textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">QC Check </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="qc_status" value="Y" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Status </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="status" value="Hold" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="type" value="" id='type'readonly>
                                        </div>
                                    </div>
                                    <div class="row left-content-end">
                                    <div class="col-sm-9">
                                        <div>
                                            <a href="/good-receipt-note" class="btn btn-info waves-effect waves-light">Back</a>
                                            <button type="submit" class="btn btn-primary w-md" name="save">Save</button>
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