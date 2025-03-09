@extends('layouts.master')
@section('konten')
@include('layouts.additional')

<div class="page-content">
    <div class="container-fluid">
        <div class="row custom-margin">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="page-title-left">
                        <form action="{{ route('grn.index') }}" method="GET" id="resetForm" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="idUpdated" value="{{ $data->id }}">
                            <button type="submit" class="btn btn-light waves-effect btn-label waves-light">
                                <i class="mdi mdi-arrow-left label-icon"></i> Back To List Good Receipt Note   
                            </button>
                        </form>
                        {{-- <a href="{{ route('grn.index') }}" class="btn btn-light waves-effect btn-label waves-light">
                            <i class="mdi mdi-arrow-left label-icon"></i> Back To List Good Receipt Note
                        </a> --}}
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Good Receipt Note</a></li>
                            <li class="breadcrumb-item active"> Edit GRN dari ({{ $source }})</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.alert')

        {{-- DATA GRN --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Good Receipt Note dari ({{ $source }})</h4>
            </div>
            <form method="POST" action="{{ route('grn.update', encrypt($data->id)) }}" class="form-material m-t-40 formLoad" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="source" value="{{ $source }}">
                <input type="hidden" name="id_purchase_orders_before" value="{{ $data->id_purchase_orders }}">
                <input type="hidden" name="reference_number_before" value="{{ $data->reference_number }}">
                <input type="hidden" name="non_invoiceable" value="N">
                
                <div class="card-body p-4">
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Receipt Number</label>
                        <div class="col-sm-9">
                            <input type="text" name="receipt_number" class="form-control custom-bg-gray" value="{{ $data->receipt_number }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Date</label>
                        <div class="col-sm-9">
                            <input type="date" name="date" class="form-control" value="{{ $data->date }}" required>
                        </div>
                    </div>
                    @if($source == 'PO')
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Purchase Orders (PO)</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" value="{{ $data->po_number }}" placeholder="Otomatis Terisi.." readonly required>
                        </div>
                    </div>
                    @endif
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Reference Number (PR)</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" value="{{ $data->request_number }}" placeholder="Otomatis Terisi.." readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Suppliers</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" value="{{ $data->supplier_name }}" placeholder="Otomatis Terisi.." readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Qc Check</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" name="qc_status" value="{{ $data->qc_status }}" placeholder="Otomatis Terisi.." readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Status </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" name="status" value="{{ $data->status }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Type </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control custom-bg-gray" name="type" value="{{ $data->type }}" placeholder="Otomatis Terisi.." readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">External Doc. Number</label>
                        <div class="col-sm-9">
                            <input type="text" name="external_doc_number" class="form-control" value="{{ $data->external_doc_number }}" placeholder="Masukkan External Doc. Number.." required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label class="col-sm-3 col-form-label">Note</label>
                        <div class="col-sm-9">
                            <textarea name="remarks" rows="3" cols="50" class="form-control" placeholder="Note.. (Opsional)">{{ $data->remarks }}</textarea>
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
                                <i class="mdi mdi-update label-icon"></i>Update
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- LIST ITEM --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">List Item Product <b>"{{ $data->type }}"</b></h4>
            </div>
            <div class="card-body p-4">
                <table class="table table-bordered dt-responsive w-100" style="font-size: small" id="tableItem">
                    <thead>
                        <tr>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">No.</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Product</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Qty</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Receipt Qty</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Outstanding Qty</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Units</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Status</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Note</th>
                            @if (in_array($data->status, ['Hold', 'Un Posted']))
                                <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itemDatas as $item)
                            <tr>
                                <td class="align-top text-center"><b>{{ $loop->iteration }}</b></td>
                                <td class="align-top">
                                    <b>{{ $item->type_product }}</b>
                                    <br>{!! implode('<br>', array_map(fn($chunk) => implode(' ', $chunk), array_chunk(explode(' ', $item->product_desc), 10))) !!}  {{-- max 10 word one line --}}
                                </td>
                                <td class="text-center">
                                    <b>
                                        {{ $item->qty 
                                            ? (strpos(strval($item->qty), '.') !== false 
                                                ? rtrim(rtrim(number_format($item->qty, 6, ',', '.'), '0'), ',') 
                                                : number_format($item->qty, 0, ',', '.')) 
                                            : '0' }}
                                    </b>
                                </td>
                                <td class="text-center">
                                    {{ $item->receipt_qty 
                                        ? (strpos(strval($item->receipt_qty), '.') !== false 
                                            ? rtrim(rtrim(number_format($item->receipt_qty, 6, ',', '.'), '0'), ',') 
                                            : number_format($item->receipt_qty, 0, ',', '.')) 
                                        : '0' }}
                                </td>
                                <td class="text-center">
                                    {{ $item->outstanding_qty 
                                        ? (strpos(strval($item->outstanding_qty), '.') !== false 
                                            ? rtrim(rtrim(number_format($item->outstanding_qty, 6, ',', '.'), '0'), ',') 
                                            : number_format($item->outstanding_qty, 0, ',', '.')) 
                                        : '0' }}
                                </td>
                                <td class="text-center">{{ $item->unit_code }}</td>
                                <td class="text-center">
                                    @if ($item->status)
                                        <span class="badge bg-{{ $item->status === 'Open' ? 'info' : 'success' }}">{{ ucfirst($item->status) }}</span>
                                    @endif
                                    @if ($item->qc_passed)
                                        <br>
                                        <span class="badge bg-{{ $item->qc_passed === 'Y' ? 'success' : 'danger' }}">
                                            <i class="fas fa-{{ $item->qc_passed === 'Y' ? 'check' : 'times' }}"></i>
                                            {{ $item->qc_passed === 'Y' ? 'QC Passed' : 'QC Not Passed' }}
                                        </span>
                                    @endif
                                    @if ($item->lot_number)
                                        <br>
                                        <span class="badge bg-info">{{ $item->lot_number }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span title="{{ strlen($item->note) > 70 ? $item->note : '' }}">
                                        {{ strlen($item->note) > 70 ? substr($item->note, 0, 70) . '...' : $item->note }}
                                    </span>
                                </td>
                                @if (in_array($data->status, ['Hold', 'Un Posted']))
                                    <td class="align-top text-center">
                                        @if($item->qc_passed == 'Y' || $item->lot_number)
                                            -
                                        @else
                                            <button type="button" class="btn btn-sm {{ $item->status ? 'btn-info' : 'btn-primary' }} my-half" 
                                                    data-bs-toggle="modal" data-bs-target="#update{{ $item->id }}"><i class="bx bx-edit-alt" title="Confirm Qty"></i> 
                                                    @if($item->status) Re - @endif Confirm Qty
                                            </button>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                            {{-- Modal Edit --}}
                            @if (in_array($data->status, ['Hold', 'Un Posted']))
                                <div class="modal fade" id="update{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-top modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="staticBackdropLabel">Confirm Qty</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('grn.updateItem', encrypt($item->id)) }}" method="POST" enctype="multipart/form-data" id="formUpdate{{ $item->id }}">
                                                @csrf
                                                <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                                                    <div class="container">
                                                        <div class="row mb-2 field-wrapper required-field">
                                                            <label class="col-sm-3 col-form-label">Product</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" class="form-control custom-bg-gray" placeholder="Product.." value="{{ $item->product_desc }}" readonly>
                                                            </div>
                                                        </div>
                                                        <br>
                                                        <div class="row mb-2 field-wrapper required-field">
                                                            <label class="col-sm-3 col-form-label">Qty</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" class="form-control custom-bg-gray" name="qty" id="qty" placeholder="Qty.." 
                                                                    value="{{ $item->qty 
                                                                    ? (strpos(strval($item->qty), '.') !== false 
                                                                        ? rtrim(rtrim(number_format($item->qty, 6, ',', '.'), '0'), ',') 
                                                                        : number_format($item->qty, 0, ',', '.')) 
                                                                    : '0' }}"
                                                                    readonly required>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2 field-wrapper required-field">
                                                            <label class="col-sm-3 col-form-label">Receipt Qty</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" class="form-control number-format" name="receipt_qty" id="receipt_qty" placeholder="Masukkan Receipt Qty.." 
                                                                    value="{{ $item->receipt_qty 
                                                                    ? (strpos(strval($item->receipt_qty), '.') !== false 
                                                                        ? rtrim(rtrim(number_format($item->receipt_qty, 6, ',', '.'), '0'), ',') 
                                                                        : number_format($item->receipt_qty, 0, ',', '.')) 
                                                                    : '0' }}"
                                                                    required>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2 field-wrapper required-field">
                                                            <label class="col-sm-3 col-form-label">Outstanding Qty</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" class="form-control custom-bg-gray" name="outstanding_qty" id="outstanding_qty" placeholder="Outstanding.. (Terisi Otomatis)" 
                                                                    value="{{ $item->outstanding_qty 
                                                                    ? (strpos(strval($item->outstanding_qty), '.') !== false 
                                                                        ? rtrim(rtrim(number_format($item->outstanding_qty, 6, ',', '.'), '0'), ',') 
                                                                        : number_format($item->outstanding_qty, 0, ',', '.')) 
                                                                    : '0' }}"
                                                                    readonly required>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2 field-wrapper required-field">
                                                            <label class="col-sm-3 col-form-label">Units</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" class="form-control custom-bg-gray" placeholder="Masukkan Units.." value="{{ $item->unit_code }}" readonly>
                                                            </div>
                                                        </div>
                                                        <br>
                                                        <div class="row mb-2 field-wrapper">
                                                            <label class="col-sm-3 col-form-label">Note</label>
                                                            <div class="col-sm-9">
                                                                <textarea name="note" rows="4" cols="50" class="form-control" placeholder="Note.. (Opsional)">{{ $item->note }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-info waves-effect btn-label waves-light" id="btnFormUpdate{{ $item->id }}">
                                                        <i class="mdi mdi-sync label-icon"></i>Update
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        <script>
                            $(document).ready(function () {
                                function formatPrice(value) {
                                    let num = parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
                                    return num;
                                }
                                function formatPriceDisplay(value) {
                                    let formatted = value.toFixed(6).replace('.', ','); // Convert decimal separator
                                    let parts = formatted.split(",");

                                    // Apply thousands separator only to the integer part
                                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                                    // Remove unnecessary trailing zeros after the comma
                                    if (parts[1]) {
                                        parts[1] = parts[1].replace(/0+$/, ""); // Remove trailing zeros
                                        if (parts[1] === "") return parts[0]; // If decimal part is empty, return only integer part
                                    }

                                    return parts.join(",");
                                }

                                $('input[name="receipt_qty"]').on('input', function () {
                                    let receiptInput = $(this);
                                    let modal = receiptInput.closest('.modal');
                                    let qty = formatPrice(modal.find('input[name="qty"]').val()) || 0;
                                    let receiptQty = formatPrice(receiptInput.val()) || 0;
                                    if (receiptQty > qty) {
                                        receiptInput.val(formatPriceDisplay(qty));
                                        receiptQty = qty;
                                    }
                                    let outstandingQty = qty - receiptQty;
                                    modal.find('input[name="outstanding_qty"]').val(formatPriceDisplay(outstandingQty));
                                });
                            });

                            $(document).on('submit', 'form[id^="formUpdate"]', function(event) {
                                event.preventDefault();
                                let formId = this.id;
                                let btnId = formId.replace("formUpdate", "btnFormUpdate");
                                let btn = $("#" + btnId);
                                if (typeof $.fn.valid === "function" && !$(this).valid()) {
                                    return false;
                                }
                                if (btn.length > 0) {
                                    btn.prop("disabled", true);
                                    btn.html('<i class="mdi mdi-loading mdi-spin label-icon"></i> Please Wait...');
                                }
                                this.submit();
                            });
                        </script>
                    </tbody>
                </table>
            </div>
            <div class="card-footer p-4"></div>
        </div>
    </div>
</div>

@endsection
