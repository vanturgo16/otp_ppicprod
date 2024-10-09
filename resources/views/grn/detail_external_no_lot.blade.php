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
                                <h5 class="mb-0">Detail External Lot Number</h5>
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
                                            
                                            <th>Action</th>
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
                                                
                                                <td>
                                                @if($data->ext_lot_number!='')   
                                                <div class="input-group" style="max-width: 200px;">
                                                    <div class="input-group-append">
                                                        <form action="{{ route('generateBarcode', ['lot_number' => $data->lot_number]) }}" method="GET">
                                                            <input type="number" class="form-control" name="qty" placeholder="Jumlah Barcode" required>
                                                            <button type="submit" class="btn btn-info">Generate Barcode</button>
                                                        </form>
                                                                                                       
                                                        <a href="/edit-detail-ext-no-lot/{{ $data->id }}" class="btn btn-sm btn-primary waves-effect waves-light">
                                                            <i class="bx bx-edit-alt" title="Edit data"></i>
                                                    </a>
                                                    </div>
                                                </div>
                                                
                                                
                                               {{-- <a href="/generateBarcode/{{ $data->lot_number }}" class="btn btn-sm btn-info"><i class=" bx bx-barcode" >Print Barcode</i></a> --}}
                                               
                                               
                                               @else
                                               <button type="submit" class="btn btn-sm btn-danger" >
                                                   <i class="bx bx-info-circle" > Please Generate Barcode</i>
                                               </button>
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