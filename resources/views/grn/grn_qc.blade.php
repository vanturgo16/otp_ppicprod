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
                                <h5 class="mb-0">Quality Control Check</h5>
                                <div>
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
                                                <td>{{ $data->qc_passed }}<br>
                                                    Checked By : {{ $data->name }}
                                                </td>
                                                <td>{{ $data->lot_number }}</td>
                                                <td>{{ $data->note }}</td>
                                                <td>
                                                    <a href="/good-lote-number-detail/{{ $data->id }}" class="btn btn-sm btn-primary waves-effect waves-light"><i class=" bx bx-show-alt" ></i></a>
                                                @if($data->qc_passed != 'Y')
                                                    <form action="/qc_passed/{{ $data->id }}" method="post"
                                                        class="d-inline" data-id="">
                                                        @method('PUT')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                        onclick="return confirm('Anda yakin mau QC Passed item ini ?')">
                                                            <!-- <i class="bx bx-paper-plane" title="Posted" ></i> -->
                                                            <i class="bx bx-select-multiple" title="QC Passed" > QC Passed</i>
                                                        </button></center>
                                                    </form>
                                                @elseif($data->qc_passed == 'Y')
                                                    <form action="/un_qc_passed/{{ $data->id }}" method="post"
                                                        class="d-inline" data-id="">
                                                        @method('PUT')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning"
                                                        onclick="return confirm('Anda yakin mau QC Un Passed item ini ?')">
                                                            <!-- <i class="bx bx-paper-plane" title="Posted" ></i> -->
                                                            <i class="bx bx-x" title="QC Un Passed" > QC Un Passed</i>
                                                        </button></center>
                                                    </form>
                                                @endif   
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