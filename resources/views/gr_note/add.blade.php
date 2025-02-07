@extends('layouts.master')
@section('konten')
@include('layouts.additional')

<div class="page-content">
    <div class="container-fluid">
        <div class="row custom-margin">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="page-title-left">
                        <a href="{{ route('grn.index') }}" class="btn btn-light waves-effect btn-label waves-light">
                            <i class="mdi mdi-arrow-left label-icon"></i> Back To List Good Receipt Note
                        </a>
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Good Receipt Note</a></li>
                            <li class="breadcrumb-item active"> Tambah GRN dari ({{ $source }})</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.alert')

        {{-- FORM GRN --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Tambah Good Receipt Note dari ({{ $source }})</h4>
            </div>
            <form method="POST" action="{{ route('grn.store') }}" class="form-material m-t-40 formLoad" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="source" value="{{ $source }}">
                <input type="hidden" name="non_invoiceable" value="N">
                <div class="card-body p-4">
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Receipt Number</label>
                        <div class="col-sm-9">
                            <input type="text" name="receipt_number" class="form-control custom-bg-gray" value="{{ $formattedCode }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Date</label>
                        <div class="col-sm-9">
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    @if($source == 'PO')
                        <div class="row mb-4 field-wrapper required-field">
                            <label class="col-sm-3 col-form-label">Purchase Orders (PO) </label>
                            <div class="col-sm-9">
                                <select class="form-select input-select2" name="id_purchase_orders" id="" style="width: 100%" required>
                                    <option value="">Pilih Purchase Orders</option>
                                    @foreach ($postedPO as $item)
                                        <option value="{{ $item->id }}">{{ $item->po_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Reference Number (PR) </label>
                        <div class="col-sm-9">
                            <select class="form-select input-select2 @if($source == 'PO') readonly-select2 @endif" name="reference_number" id="" style="width: 100%" required>
                                <option value="">@if($source == 'PO') Otomatis Terisi.. @else Pilih Reference Number @endif</option>
                                @foreach ($postedPRs as $item)
                                    <option value="{{ $item->id }}">{{ $item->request_number }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Suppliers</label>
                        <div class="col-sm-9">
                            <select class="form-select input-select2 readonly-select2" name="id_master_suppliers" style="width: 100%" required>
                                <option value="">Otomatis Terisi..</option>
                                @foreach ($suppliers as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Qc Check</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" name="qc_status" value="" placeholder="Otomatis Terisi.." readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Status </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" name="status" value="Hold" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Type </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" name="type" value="" placeholder="Otomatis Terisi.." readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">External Doc. Number</label>
                        <div class="col-sm-9">
                            <input type="text" name="external_doc_number" class="form-control" value="" placeholder="Masukkan External Doc. Number.." required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label class="col-sm-3 col-form-label">Note</label>
                        <div class="col-sm-9">
                            <textarea name="remarks" rows="3" cols="50" class="form-control" placeholder="Note.. (Opsional)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row text-end">
                        <div>
                            <a href="javascript:location.reload();" type="button" class="btn btn-secondary waves-effect btn-label waves-light">
                                <i class="mdi mdi-reload label-icon"></i>Reset
                            </a>
                            <button type="submit" class="btn btn-primary waves-effect btn-label waves-light">
                                <i class="mdi mdi-plus label-icon"></i>Save
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('select[name="id_purchase_orders"]').change(function() {
            var idPO = $(this).val();
            if (idPO) {
                $.ajax({
                    url: "{{ route('grn.getPODetails') }}",
                    method: 'GET',
                    data: { idPO: idPO },
                    success: function(response) {
                        if (response.success) {
                            $('select[name="reference_number"]').val(response.data.reference_number).trigger('change');
                            $('select[name="id_master_suppliers"]').val(response.data.id_master_suppliers).trigger('change');
                            $('input[name="qc_status"]').val(response.data.qc_check);
                            $('input[name="type"]').val(response.data.type);
                        } else {
                            alert('No data found for this data po.');
                        }
                    },
                    error: function() {
                        alert('Error fetching data. Please try again.');
                    }
                });
            } else {
                $('select[name="reference_number"]').val('').trigger('change');
                $('select[name="id_master_suppliers"]').val('').trigger('change');
                $('input[name="qc_status"]').val('');
                $('input[name="type"]').val('');
            }
        });

        $('select[name="reference_number"]').change(function() {
            var referenceId = $(this).val();
            if (referenceId) {
                $.ajax({
                    url: "{{ route('grn.getPRDetails') }}",
                    method: 'GET',
                    data: { reference_id: referenceId },
                    success: function(response) {
                        if (response.success) {
                            $('select[name="id_master_suppliers"]').val(response.data.id_master_suppliers).trigger('change');
                            $('input[name="qc_status"]').val(response.data.qc_check);
                            $('input[name="type"]').val(response.data.type);
                        } else {
                            alert('No data found for this reference number.');
                        }
                    },
                    error: function() {
                        alert('Error fetching data. Please try again.');
                    }
                });
            } else {
                $('select[name="id_master_suppliers"]').val('').trigger('change');
                $('input[name="qc_status"]').val('');
                $('input[name="type"]').val('');
            }
        });
    });
</script>


@endsection
