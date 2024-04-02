@extends('layouts.master')

@section('konten')

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
                                    
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Good Receipt Notes</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="" id="" value="{{ $id_good_receipt_notes }}">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Receipt Qty </label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="receipt_qty" id="" value="{{ $receipt_qty }}">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Units </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="unit_code" id="" value="{{ $unit_code }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Qc Passed </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="qc_passed" id="" value="{{ $qc_passed }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Outstanding Qty </label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="outstanding_qty" id="" value="{{ $outstanding_qty }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Note </label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="note" id="" value="{{ $note }}">
                                        </div>
                                    </div>

                                    <div class="row justify-content-end">
                                        <div class="col-sm-9">
                                            <div>
                                                <a href="/good-lote-number" class="btn btn-info w-md">back</a>
                                            </div>
                                        </div>
                                    </div>   
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection