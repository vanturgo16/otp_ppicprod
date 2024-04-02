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
                                <h5 class="mb-0">External Lot Number</h5>
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
                                            <th>id grn detail</th>
                                            <th>Lot Number</th>
                                            <th>Ext Lot Number</th>
                                            <th>Qty</th>
                                            <th>Generate External Lot</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $no = 1; // Inisialisasi nomor urut
                                    @endphp
                                    @foreach ($details as $data)
                                            <tr><td>{{ $no++ }}</td>
                                                <td>{{ $data->id_grn_detail }}</td>
                                                <td>{{ $data->lot_number }}</td>
                                                <td>{{ $data->ext_lot_number }}</td>
                                                <td>{{ $data->qty }}</td>
                                                <td><button type="button" class="btn btn-success btn-sm waves-effect waves-light"
                                                        data-bs-toggle="modal" onclick="ext_lot_number('{{ $data->id }}');"
                                                        data-bs-target="#external_lot"><i class="bx bx-edit-alt" title="Input Ext Lot"></i> Input External Lot</button></td>
                                                        @include('grn.modal')
                                             
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