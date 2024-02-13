@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">
    <form method="post" action="/simpan_pr_rm" class="form-material m-t-40" enctype="multipart/form-data">
    @csrf
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> Add Purchase</h4>
                   
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Purchase</a></li>
                            <li class="breadcrumb-item active"> Add Purchase RM</li>
                        </ol>
                    </div>
                </div>
                <a href="/purchase" class="btn btn-info waves-effect waves-light">Back To List Data Purchase Requisition</a>
                <div></div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Purchase Requisition</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                                
                    
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Request Number</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="request_number" class="form-control" value="{{ $formattedCode }}" readonly>
                                            <input type="hidden" id="html" name="type" value="RM">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-email-input" class="col-sm-3 col-form-label">Date</label>
                                        <div class="col-sm-9">
                                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Suppliers </label>
                                        <div class="col-sm-9">
                                            <select class="form-select" name="id_master_suppliers" id="">
                                                <option>Pilih Suppliers</option>
                                                @foreach ($supplier as $data)
                                                    <option value="{{ $data->id }}">{{ $data->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Requester </label>
                                        <div class="col-sm-9">
                                        <select class="form-select" name="requester" id="">
                                        <option>Pilih Requester</option>
                                        @foreach ($datas as $data)
                                            <option value="{{ $data->id }}">{{ $data->nm_requester }}</option>
                                        @endforeach
                                        </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Qc Check </label>
                                        <div class="col-sm-9">
                                            <input type="radio" id="html" name="qc_check" value="Y" checked>
                                              <label for="html">Y</label>
                                              <input type="radio" id="css" name="qc_check" value="N">
                                              <label for="css">N</label>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Note </label>
                                        <div class="col-sm-9">
                                            <textarea name="note" rows="4" cols="50" class="form-control"></textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Status </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="status" value="Request" readonly>
                                        </div>
                                    </div>
                                    <div class="row left-content-end">
                                    <div class="col-sm-9">
                                        <div>
                                            <a href="/purchase" class="btn btn-info waves-effect waves-light">Back</a>
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