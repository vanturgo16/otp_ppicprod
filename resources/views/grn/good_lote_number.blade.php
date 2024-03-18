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
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> Good Receipt Note</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">PPIC</a></li>
                                <li class="breadcrumb-item active"> Good Receipt Note</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Good Receipt Note</h5>
                                <div>
                                    <a href="/grn-pr-add" class="btn btn-primary waves-effect waves-light">Add Data From PR</a>
                                    <a href="/grn-po-add" class="btn btn-success waves-effect waves-light">Add Data From PO</a>
                                    
                                    <!-- Include modal content -->
                                   
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr>
                                        <tr>
                                            <th>No</th>
                                            <th>Good Receipt Note</th>
                                            <th>Products</th>
                                            <th>Receipt Qty</th>
                                            <th>Units</th>
                                            <th>Qc Passed</th>
                                            <th>Lot Number</th>
                                            <th>Note</th>
                                            <th>Generate Lot </th>
                                            <th>Print </th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($receiptDetails as $data)
                                            <tr><td></td>
                                                <td>{{ $data->receipt_number }}</td>
                                                <td>{{ $data->description }}</td>
                                                <td>{{ $data->receipt_qty }}</td>
                                                <td>{{ $data->unit_code }}</td>
                                                <td>{{ $data->lot_number }}</td>
                                                <td>{{ $data->note }}</td>
                                                <td></td>
                                                <td><button type="submit" class="btn btn-sm btn-success">
                                                        
                                                        </button></td>
                                                <td></td>
                                                <td><form action="/hapus_grn/" method="post"
                                                        class="d-inline">
                                                        @method('delete')
                                                        @csrf
                                                       
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Anda yakin mau menghapus item ini ?')">
                                                            <i class="bx bx-trash-alt" title="Hapus data" ></i>
                                                        </button>
                                                    </form>
                                                    <a href="/print-pr/" class="btn btn-sm btn-info waves-effect waves-light">
                                                            <i class="bx bx-printer" title="print in English"></i>
                                                    </a>
                                                    <a href="/print-pr-ind/" class="btn btn-sm btn-success waves-effect waves-light">
                                                            <i class="bx bx-printer" title="print dalam B Indo"></i>
                                                    </a>
                                                    <a href="/edit-pr/" class="btn btn-sm btn-info waves-effect waves-light">
                                                            <i class="bx bx-edit-alt" title="Edit data"></i>
                                                    </a>
                                                   
                                                    <form action="/posted_pr/" method="post"
                                                        class="d-inline" data-id="">
                                                        @method('PUT')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                        onclick="return confirm('Anda yakin mau Posted item ini ?')">
                                                            <i class="bx bx-paper-plane" title="Posted" ></i>
                                                            <!-- <i class="mdi mdi-arrow-left-top-bold" title="Posted" >Un Posted</i> -->
                                                        </button></center>
                                                    </form>
                                                    
                                                    <form action="/unposted_pr/" method="post"
                                                        class="d-inline" data-id="">
                                                        @method('PUT')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary"
                                                        onclick="return confirm('Anda yakin mau Un Posted item ini ?')">
                                                            <!-- <i class="bx bx-paper-plane" title="Posted" ></i> -->
                                                            <i class="mdi mdi-arrow-left-top-bold" title="Un Posted" >Un Posted</i>
                                                        </button></center>
                                                    </form>
                                                   
                                                    </td>
                                             
                                            </tr>
                                   @endforeach
                                        <!-- Add more rows as needed -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection