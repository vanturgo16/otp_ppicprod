@extends('layouts.master')

@section('konten')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Edit Sales Order</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Marketing</a></li>
                                <li class="breadcrumb-item active">Edit Sales Order</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>Success</strong> - {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('fail'))
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>Failed</strong> - {{ session('fail') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-outline label-icon"></i><strong>Warning</strong> - {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('info'))
                <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-circle-outline label-icon"></i><strong>Info</strong> - {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row pb-3">
                <div class="col-12">
                    <a href="{{ route('marketing.inputPOCust.index') }}"
                        class="btn btn-primary waves-effect btn-label waves-light">
                        <i class="mdi mdi-arrow-left label-icon"></i> Back To List Data Sales Order
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <form action="{{ route('marketing.inputPOCust.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-header">
                                <i class="mdi mdi-file-multiple-outline label-icon"></i> Edit Sales Order
                            </div>

                            <div class="card-body">
                                <div class="mt-4 mt-lg-0">
                                    <div class="row mb-4 field-wrapper">
                                        <label for="orderSelect" class="col-sm-3 col-form-label">Order Confirmation</label>
                                        <div class="col-sm-9">
                                            <select class="form-control data-select2" name="id_order_confirmations"
                                                id="orderSelect" style="width: 100%" disabled>
                                                <option value="{{ $salesOrder->id_order_confirmations }}">
                                                    {{ $salesOrder->id_order_confirmations }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="soTypeSelect" class="col-sm-3 col-form-label">SO Type</label>
                                        <div class="col-sm-9">
                                            <select class="form-control data-select2" name="so_type" id="soTypeSelect"
                                                style="width: 100%" required>
                                                <option value="">**
                                                    Please select a SO Type</option>
                                                <option value="Reguler"
                                                    {{ $salesOrder->so_type == 'Reguler' ? 'selected' : '' }}>Reguler
                                                </option>
                                                <option value="Sample"
                                                    {{ $salesOrder->so_type == 'Sample' ? 'selected' : '' }}>Sample
                                                </option>
                                                <option value="Raw Material"
                                                    {{ $salesOrder->so_type == 'Raw Material' ? 'selected' : '' }}>Raw
                                                    Material</option>
                                                <option value="Machine"
                                                    {{ $salesOrder->so_type == 'Machine' ? 'selected' : '' }}>Machine
                                                </option>
                                                <option value="Stock"
                                                    {{ $salesOrder->so_type == 'Stock' ? 'selected' : '' }}>Stock</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="so_number" class="col-sm-3 col-form-label">SO Number</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="so_number" id="so_number"
                                                value="{{ $salesOrder->so_number }}" required readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="soCategorySelect" class="col-sm-3 col-form-label">SO Category</label>
                                        <div class="col-sm-9">
                                            <select class="form-control data-select2" name="so_category"
                                                id="soCategorySelect" style="width: 100%" required>
                                                <option value="">** Please select
                                                    a SO Category</option>
                                                <option value="Stock"
                                                    {{ $salesOrder->so_category == 'Stock' ? 'selected' : '' }}>Stock
                                                </option>
                                                <option value="S/W"
                                                    {{ $salesOrder->so_category == 'S/W' ? 'selected' : '' }}>S/W</option>
                                                <option value="CF"
                                                    {{ $salesOrder->so_category == 'CF' ? 'selected' : '' }}>CF</option>
                                                <option value="Bag"
                                                    {{ $salesOrder->so_category == 'Bag' ? 'selected' : '' }}>Bag</option>
                                                <option value="Box"
                                                    {{ $salesOrder->so_category == 'Box' ? 'selected' : '' }}>Box</option>
                                                <option value="Return"
                                                    {{ $salesOrder->so_category == 'Return' ? 'selected' : '' }}>Return
                                                </option>
                                                <option value="Selongsong"
                                                    {{ $salesOrder->so_category == 'Selongsong' ? 'selected' : '' }}>
                                                    Selongsong</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="date" class="col-sm-3 col-form-label">Date</label>
                                        <div class="col-sm-9">
                                            <input type="date" class="form-control" name="date" id="date"
                                                value="{{ $salesOrder->date }}" required>
                                        </div>
                                    </div>
                                    <div class="row mb-4 customerSection field-wrapper required-field">
                                        <label for="customerSelect" class="col-sm-3 col-form-label">Customer</label>
                                        <div class="col-sm-9">
                                            <select class="form-control data-select2" name="id_master_customers"
                                                id="customerSelect" style="width: 100%" required disabled>
                                                <option value="">** Please select a Customers</option>
                                                @foreach ($customers as $customer)
                                                    <option value="{{ $customer->id }}"
                                                        {{ $salesOrder->id_master_customers == $customer->id ? 'selected' : '' }}>
                                                        {{ $customer->customer_code }} -
                                                        {{ $customer->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 customerAddressSection field-wrapper required-field">
                                        <label for="customerAddressSelect" class="col-sm-3 col-form-label">Customer
                                            Address</label>
                                        <div class="col-sm-9">
                                            <select class="form-control data-select2" name="id_master_customer_addresses"
                                                id="customerAddressSelect" style="width: 100%" required>
                                                <option value="">** Please select a Customers Address</option>
                                                @foreach ($customer_addresses as $address)
                                                    <option value="{{ $address->id }}"
                                                        {{ $salesOrder->id_master_customer_addresses == $address->id ? 'selected' : '' }}>
                                                        {{ $address->address }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 salesmanSection field-wrapper required-field">
                                        <label for="salesmanSelect" class="col-sm-3 col-form-label">Salesman</label>
                                        <div class="col-sm-9">
                                            <select class="form-control data-select2" name="id_master_salesmen"
                                                id="salesmanSelect" style="width: 100%" required disabled>
                                                <option value="">** Please select a Salesman</option>
                                                @foreach ($salesmans as $salesman)
                                                    <option value="{{ $salesman->id }}"
                                                        {{ $salesOrder->id_master_salesmen == $salesman->id ? 'selected' : '' }}>
                                                        {{ $salesman->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="reference_number" class="col-sm-3 col-form-label">Reference Number
                                            (PO)</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="reference_number"
                                                id="reference_number" value="{{ $salesOrder->reference_number }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="due_date" class="col-sm-3 col-form-label">Due Date</label>
                                        <div class="col-sm-9">
                                            <input type="date" class="form-control" name="due_date" id="due_date"
                                                value="{{ $salesOrder->due_date }}">
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="colorSelect" class="col-sm-3 col-form-label">Color</label>
                                        <div class="col-sm-9">
                                            <select class="form-control data-select2" name="color" id="colorSelect"
                                                style="width: 100%" required>
                                                <option value="">** Please select a Color</option>
                                                <option value="Y" {{ $salesOrder->color == 'Y' ? 'selected' : '' }}>Y
                                                </option>
                                                <option value="N" {{ $salesOrder->color == 'N' ? 'selected' : '' }}>N
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="nonInvoiceableSelect" class="col-sm-3 col-form-label">Non
                                            Invoiceable</label>
                                        <div class="col-sm-9">
                                            <select class="form-control data-select2" name="non_invoiceable"
                                                id="nonInvoiceableSelect" style="width: 100%" required>
                                                <option value="">** Please select a Non Invoiceable</option>
                                                <option value="Y"
                                                    {{ $salesOrder->non_invoiceable == 'Y' ? 'selected' : '' }}>Y</option>
                                                <option value="N"
                                                    {{ $salesOrder->non_invoiceable == 'N' ? 'selected' : '' }}>N</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper">
                                        <label for="remark" class="col-sm-3 col-form-label">Remark</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control" name="remark" id="remark" rows="5">{{ $salesOrder->remarks }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="statusOrder" class="col-sm-3 col-form-label">Status</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="status" id="statusOrder"
                                                value="{{ $salesOrder->status }}" required readonly>
                                            <input type="text" class="form-control" name="total_price"
                                                id="total-Price" hidden>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="table-responsive table-bordered">
                                        <table class="table table-striped table-hover" id="productTable">
                                            <thead>
                                                <tr class="text-center">
                                                    <th>#</th>
                                                    <th>Type <br>Product</th>
                                                    <th>Product</th>
                                                    <th>Cust Product Code</th>
                                                    <th>Unit</th>
                                                    <th>Qty</th>
                                                    <th>Price</th>
                                                    <th>Subtotal</th>
                                                    <td class="align-middle text-center">
                                                        <input type="checkbox" id="checkAllRows">
                                                    </td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-center" colspan="9">There is no data yet, please
                                                        select Order Confirmation</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- end col -->
                            </div>
                            <!-- end row -->

                            <div class="card-header pb-0" style="cursor: pointer" id="headerPayment"
                                onclick="toggle('#bodyPayment')">
                                <h4><i class="mdi mdi-checkbox-marked-outline"></i> Payment</h4>
                            </div>
                            <div class="card-body" id="bodyPayment">
                                <div class="mt-4 mt-lg-0">
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="termPaymentSelect" class="col-sm-3 col-form-label">Term
                                            Payment</label>
                                        <div class="col-sm-9">
                                            <select class="form-control data-select2" name="id_master_term_payments"
                                                id="termPaymentSelect" style="width: 100%" required disabled>
                                                <option value="">** Please select a Term Payment</option>
                                                @foreach ($termPayments as $termPayment)
                                                    <option value="{{ $termPayment->id }}"
                                                        {{ $salesOrder->id_master_term_payments == $termPayment->id ? 'selected' : '' }}>
                                                        {{ $termPayment->term_payment }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 field-wrapper required-field">
                                        <label for="ppnSelect" class="col-sm-3 col-form-label">Ppn</label>
                                        <div class="col-sm-9">
                                            <select class="form-control data-select2" name="ppn" id="ppnSelect"
                                                style="width: 100%" required disabled>
                                                <option value="">** Please select a Ppn</option>
                                                <option value="Include"
                                                    {{ $salesOrder->ppn == 'Include' ? 'selected' : '' }}>Inclue</option>
                                                <option value="Exclude"
                                                    {{ $salesOrder->ppn == 'Exclude' ? 'selected' : '' }}>Exclude</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="card-footer">
                                <div class="row justify-content-end">
                                    <div class="col-sm-9">
                                        <div>
                                            <a href="{{ route('marketing.salesOrder.index') }}"
                                                class="btn btn-light w-md"><i class="fas fa-arrow-left"></i>&nbsp;
                                                Back</a>
                                            <input type="submit" class="btn btn-primary w-md saveSalesOrder"
                                                value="Save & Add More" name="save_add_more">
                                            <input type="submit" class="btn btn-success w-md saveSalesOrder"
                                                value="Save" name="save">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Static Backdrop Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to <span class="text-danger">delete</span> this data?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger waves-effect btn-label waves-light"
                        onclick="removeRow()"><i class="mdi mdi-delete label-icon"></i>Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection
