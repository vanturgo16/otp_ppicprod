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
                            <li class="breadcrumb-item active"> Edit GRN dari ({{ $type }})</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.alert')

        {{-- DATA GRN --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Good Receipt Note dari ({{ $type }})</h4>
            </div>
            <form method="POST" action="{{ route('grn.update', encrypt($data->id)) }}" class="form-material m-t-40 formLoad" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
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
                    @if($type == 'PO')
                        <div class="row mb-4 field-wrapper required-field">
                            <label class="col-sm-3 col-form-label">
                                Purchase Orders (PO)
                                <i class="mdi mdi-information-outline info-change-po" data-bs-toggle="tooltip" data-bs-placement="top" title="Mengubah Purchase Order akan memperbarui item produk sesuai Purchase Order."></i>
                            </label>
                            <div class="col-sm-9">
                                <select class="form-select input-select2" name="id_purchase_orders" id="" style="width: 100%" required>
                                    <option value="">Pilih Purchase Orders</option>
                                    @foreach ($postedPO as $item)
                                        <option value="{{ $item->id }}" {{ $item->id == $data->id_purchase_orders ? 'selected' : '' }}>{{ $item->po_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">
                            Reference Number (PR)
                            <i class="mdi mdi-information-outline info-change-pr" data-bs-toggle="tooltip" data-bs-placement="top" title="Mengubah Nomor Referensi akan memperbarui item produk sesuai Purchase Request."></i>
                        </label>
                        <div class="col-sm-9">
                            <select class="form-select input-select2" name="reference_number" id="" style="width: 100%" required>
                                <option value="">Pilih Reference Number</option>
                                @foreach ($postedPRs as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data->reference_number ? 'selected' : '' }}>{{ $item->request_number }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-3 col-form-label">Suppliers</label>
                        <div class="col-sm-9">
                            <select class="form-select input-select2" name="id_master_suppliers" style="width: 100%" required>
                                <option value="">Pilih Suppliers</option>
                                @foreach ($suppliers as $item)
                                    <option value="{{ $item->id }}" {{ $item->id == $data->id_master_suppliers ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
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
                            <textarea name="note" rows="3" cols="50" class="form-control" placeholder="Note.. (Opsional)">{{ $data->note }}</textarea>
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
                <a href="" class="btn btn-info waves-effect btn-label waves-light mb-2" data-bs-toggle="modal" data-bs-target="#addProduct">
                    <i class="mdi mdi-plus label-icon"></i> Tambah Product <b>"{{ $data->type }}"</b>
                </a>
                <table class="table table-bordered dt-responsive w-100" style="font-size: small" id="tableItem">
                    <thead>
                        <tr>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">No.</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Product</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Qty</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Receipt Qty</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Outstanding Qty</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Units</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Note</th>
                            <th class="align-middle text-center" style="background-color: #6C7AE0; color:#ffff; border-bottom: 4px solid #e2e2e2;">Aksi</th>
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
                                <td class="text-center"><b>{{ $item->qty }}</b></td>
                                <td class="text-center">{{ $item->receipt_qty }}</td>
                                <td class="text-center">{{ $item->outstanding_qty }}</td>
                                <td class="text-center">{{ $item->unit_code }}</td>
                                <td>
                                    <span title="{{ strlen($item->note) > 70 ? $item->note : '' }}">
                                        {{ strlen($item->note) > 70 ? substr($item->note, 0, 70) . '...' : $item->note }}
                                    </span>
                                </td>
                                <td class="align-top text-center">
                                    <a href="{{ route('grn.editItem', encrypt($item->id)) }}">
                                        <button type="button" class="btn btn-sm btn-info my-half"><i class="bx bx-edit-alt" title="Edit Data"></i></button>
                                    </a>
                                    <button type="submit" class="btn btn-sm btn-danger my-half" data-bs-toggle="modal" data-bs-target="#delete{{ $item->id }}">
                                        <i class="bx bx-trash-alt" title="Hapus Data"></i>
                                    </button>
                                </td>
                            </tr>
                            {{-- Modal Delete --}}
                            <div class="modal fade" id="delete{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-top" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('grn.deleteItem', encrypt($item->id)) }}" method="POST" enctype="multipart/form-data" id="formDelete{{ $item->id }}">
                                            @csrf
                                            <input type="hidden" name="id_good_receipt_notes" value="{{ $item->id_good_receipt_notes }}">
                                            <div class="modal-body p-4">
                                                <div class="text-center">
                                                    Apakah Anda Yakin Untuk <b>Menghapus</b> Data?
                                                    <br><b>"{{ $item->product_desc }}"</b>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-danger waves-effect btn-label waves-light" id="btnFormDelete{{ $item->id }}">
                                                    <i class="mdi mdi-delete-alert label-icon"></i>Delete
                                                </button>
                                            </div>
                                        </form>
                                        <script>
                                            var idList = "{{ $item->id }}";
                                            $('#formDelete' + idList).submit(function() {
                                                if (!$('#formDelete' + idList).valid()) return false;
                                                $('#btnFormDelete' + idList).attr("disabled", "disabled");
                                                $('#btnFormDelete' + idList).html('<i class="mdi mdi-loading mdi-spin label-icon"></i>Please Wait...');
                                                return true;
                                            });
                                        </script>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{-- Modal Add --}}
            <div class="modal fade" id="addProduct" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-top modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">Tambah Product <b>"{{ $data->type }}"</b></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="formLoad" action="{{ route('grn.storeItem', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                                <div class="container">
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type Product</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control custom-bg-gray" placeholder="Masukkan Type Product.." name="type_product" value="{{ $data->type }}" readonly required>
                                        </div>
                                    </div>
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label class="col-sm-3 col-form-label">Product {{ $data->type }}</label>
                                        <div class="col-sm-9">
                                            <select class="form-select request_number input-select2" name="id_master_products" style="width: 100%" required>
                                                <option value="">Pilih Product {{ $data->type }}</option>
                                                @foreach ($products as $item)
                                                    <option value="{{ $item->id }}">{{ $item->description }}
                                                        @if($data->type == 'FG')  || {{ $item->perforasi }} || Group Sub :{{ $item->group_sub_code }} @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Qty</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" placeholder="Masukkan Qty.." name="qty" id="qty" value="" required>
                                        </div>
                                    </div>
                                    <div class="row mb-2 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Units </label>
                                        <div class="col-sm-9">
                                            <select class="form-select input-select2" name="master_units_id" id="unit_code" style="width: 100%" required>
                                                <option>Pilih Units</option>
                                                @foreach ($units as $item)
                                                    <option value="{{ $item->id }}">
                                                        {{ $item->unit_code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-2 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Note</label>
                                        <div class="col-sm-9">
                                            <textarea name="note" rows="4" cols="50" class="form-control" placeholder="Note.. (Opsional)"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary waves-effect btn-label waves-light">
                                    <i class="mdi mdi-plus label-icon"></i>Tambah Ke Tabel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-footer p-4"></div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();

        $('select[name="id_purchase_orders"]').change(function() {

            $('.info-change-po').tooltip('show');
            setTimeout(function () {
                $('.info-change-po').tooltip('hide');
            }, 3000);

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

            $('.info-change-pr').tooltip('show');
            setTimeout(function () {
                $('.info-change-pr').tooltip('hide');
            }, 3000);

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
