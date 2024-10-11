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
    <form method="post" action="/simpan_po_grn" class="form-material m-t-40" enctype="multipart/form-data">
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
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Receipt Number</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="receipt_number" class="form-control" value="{{ $goodReceiptNote->receipt_number }}" readonly>
                                            <input type="hidden" name="non_invoiceable" class="form-control" value="N" readonly>
                                        </div>
                                    </div>
                                    @if ($goodReceiptNote->id_purchase_orders != '')
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Purchase Orders</label>
                                        <div class="col-sm-9">
                                            <select class="form-select request_number data-select2" name="id_purchase_orders" id="" onchange="get_data()">
                                                <option>Pilih Purchase Orders</option>
                                                @foreach ($po as $data)
                                                    <option value="{{ $data->id }}" {{ $data->id == $goodReceiptNote->id_purchase_orders ? 'selected' : '' }}>{{ $data->po_number }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Reference Number (PR) </label>
                                        <div class="col-sm-9">
                                            <select class="form-select data-select2" name="reference_number" id="request_number">
                                                <option>Pilih Reference Number</option>
                                                
                                                    @foreach ($pr as $data)
                                                        <option value="{{ $data->id }}" {{ $data->id == $goodReceiptNote->reference_number ? 'selected' : '' }}>{{ $data->request_number }}</option>
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
                                            <input type="text" name="external_doc_number" class="form-control" value="{{ $goodReceiptNote->external_doc_number }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Suppliers </label>
                                        <div class="col-sm-9">
                                            <select class="form-select data-select2" name="id_master_suppliers" id="id_master_suppliers">
                                                <option>Pilih Suppliers</option>
                                                
                                                    @foreach ($mst_supplier as $data)
                                                        <option value="{{ $data->id }}" {{ $data->id == $goodReceiptNote->id_master_suppliers ? 'selected' : '' }}>{{ $data->name }}</option>
                                                    @endforeach
                                                
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
                                            <input type="text" class="form-control" name="qc_status" value="{{ $goodReceiptNote->qc_status }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Status </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="status" value="{{ $goodReceiptNote->status }}" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Type </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="type" value="{{ $goodReceiptNote->type }}" id='type' readonly>
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
                                            <input type="radio" id="html" name="type_product" value="{{ $goodReceiptNote->type }}" checked>
                                              <label for="html">{{ $goodReceiptNote->type }}</label>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Pilih Produk </label>
                                        <div class="col-sm-9">
                                            @if($goodReceiptNote->type == 'RM')
                                            <select class="form-select data-select2" name="id_master_products">
                                                <option>Pilih Produk</option>
                                                    @foreach ($rm as $data)
                                                    <option value="{{ $data->id }}">{{ $data->description }}</option>
                                                    @endforeach
                                            </select>
                                            @elseif($goodReceiptNote->type == 'TA')
                                            <select class="form-select data-select2" name="id_master_products">
                                                <option>Pilih Produk sparepart & auxiliaries</option>
                                                @foreach ($ta as $data)
                                                <option value="{{ $data->id }}">{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                            @elseif($goodReceiptNote=='WIP')
                                            <select class="form-select data-select2" name="id_master_products">
                                                <option>Pilih Produk</option>
                                                @foreach ($wip as $data)
                                                <option value="{{ $data->id }}">{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                            @elseif($goodReceiptNote=='FG')
                                            <select class="form-select data-select2" name="id_master_products">
                                                <option>Pilih Produk</option>
                                                @foreach ($fg as $data)
                                                <option value="{{ $data->id }}">{{ $data->description }}</option>
                                                @endforeach
                                            </select>
                                            @endif
                                            
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-password-input" class="col-sm-3 col-form-label">Receipt Qty</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="receipt_qty" id="qty">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Outstanding Qty </label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" name="outstanding_qty" id="price">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Units </label>
                                        <div class="col-sm-9">
                                            <select class="form-select data-select2" name="master_units_id" id="unit_code">
                                                <option>Pilih Unit</option>
                                                    @foreach ($unit as $data)
                                                    <option value="{{ $data->id }}">{{ $data->unit_code }}</option>
                                                    @endforeach 
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-4 field-wrapper">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Note </label>
                                        <div class="col-sm-9">
                                            <textarea name="note" rows="4" cols="50" class="form-control"></textarea>
                                        </div>
                                    </div>

                                    <div class="row justify-content-end">
                                        <div class="col-sm-9">
                                            <div>
                                                <button type="reset" class="btn btn-info w-md">Reset</button>
                                                <button type="submit" class="btn btn-primary w-md">Add To Table</button>
                                            </div>
                                        </div>
                                    </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Table Detail</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                        @include('grn.modal')
                    </div>
                    <div class="card-body p-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Type Product</th>
                                        <th>Product</th>
                                        <th>Receipt Qty</th>
                                        <th>Outstanding Qty</th>
                                        <th>Units</th>
                                        <th>Note</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if($goodReceiptNote->type=='RM')
                                    @foreach ($data_detail_rm as $data)
                                        <tr>
                                            <td>{{ $data->type_product }}</td>
                                            <td>{{ $data->description }}</td>
                                            <td>{{ $data->receipt_qty }}</td>
                                            <td>{{ $data->outstanding_qty }}</td>
                                            <td>{{ $data->unit }}</td>
                                            <td>{{ $data->note }}</td>
                                            <td>
                                    
                                                    <form action="/hapus_grn_detail/" method="post"
                                                        class="d-inline">
                                                        @method('delete')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                    </form>
                                                    <a href="/edit-grn-item/{{ $data->id; }}">
                                                        <button type="button" class="btn btn-sm btn-info"><i class="bx bx-edit-alt" title="edit data"></i></button>
                                                    </a>
                                            </td>
                                                   
                                            
                                        </tr>
                                    <!-- Add more rows as needed -->
                                    @endforeach
                            @elseif($goodReceiptNote->type=='TA')
                                        @foreach ($data_detail_ta as $data)
                                        <tr>
                                            <td>{{ $data->type_product }}</td>
                                            <td>{{ $data->description }}</td>
                                            <td>{{ $data->receipt_qty }}</td>
                                            <td>{{ $data->outstanding_qty }}</td>
                                            <td>{{ $data->unit }}</td>
                                            <td>{{ $data->note }}</td>
                                            <td>
                                    
                                                    <form action="/hapus_grn_detail/" method="post"
                                                        class="d-inline">
                                                        @method('delete')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-info " id=""
                                                        data-bs-toggle="modal"
                                                        onclick="edit_grn_detail('{{ $data->id }}')"
                                                        data-bs-target="#edit-pr-detail" data-id="">
                                                        <i class="bx bx-edit-alt" title="edit data"></i>
                                                    </button></center></td>
                                            
                                            
                                        </tr>
                                    <!-- Add more rows as needed -->
                            @endforeach
                        @elseif($goodReceiptNote->type=='WIP')
                            @foreach ($data_detail_wip as $data)
                                        <tr>
                                            <td>{{ $data->type_product }}</td>
                                            <td>{{ $data->description }}</td>
                                            <td>{{ $data->receipt_qty }}</td>
                                            <td>{{ $data->outstanding_qty }}</td>
                                            <td>{{ $data->unit }}</td>
                                            <td>{{ $data->note }}</td>
                                            <td>
                                    
                                                    <form action="/hapus_grn_detail/" method="post"
                                                        class="d-inline">
                                                        @method('delete')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-info " id=""
                                                        data-bs-toggle="modal"
                                                        onclick="edit_pr_smt('{{ $data->id }}')"
                                                        data-bs-target="#edit-pr-smt" data-id="">
                                                        <i class="bx bx-edit-alt" title="edit data"></i>
                                                    </button></center></td>
                                                 
                                            
                                        </tr>
                                    <!-- Add more rows as needed -->
                                @endforeach
                        @elseif($goodReceiptNote->type=='FG')
                                @foreach ($data_detail_fg as $data)
                                        <tr>
                                            <td>{{ $data->type_product }}</td>
                                            <td>{{ $data->description }}</td>
                                            <td>{{ $data->receipt_qty }}</td>
                                            <td>{{ $data->outstanding_qty }}</td>
                                            <td>{{ $data->unit }}</td>
                                            <td>{{ $data->note }}</td>
                                            <td>
                                    
                                                    <form action="/hapus_grn_detail/" method="post"
                                                        class="d-inline">
                                                        @method('delete')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-info " id=""
                                                        data-bs-toggle="modal"
                                                        onclick="edit_pr_smt('{{ $data->id }}')"
                                                        data-bs-target="#edit-pr-smt" data-id="">
                                                        <i class="bx bx-edit-alt" title="edit data"></i>
                                                    </button></center></td>
                                                   
                                            
                                        </tr>
                                    <!-- Add more rows as needed -->
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row left-content-end">
                        <div class="col-sm-9">
                            <div>
                                <a href="/good-receipt-note" class="btn btn-info w-md">Back</a>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                    <!-- end row -->
    </div>
</div>
@endsection