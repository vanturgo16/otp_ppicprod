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
                    <h4 class="mb-sm-0 font-size-18"><a href="/barcode"> Barcode </a></h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">PPIC</a></li>
                            <li class="breadcrumb-item active">Barcode</li>
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
                            
                            <div>
                                <a href="{{ url('/create-barcode') }}" class="btn btn-success waves-effect waves-light">Add New Generate Barcode</a>
                                <a href="{{ url('/create-barcode-mesin') }}" class="btn btn-info waves-effect waves-light">Add New Generate Barcode (AUX,RM)</a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                       
                        <form action="{{ route('barcode') }}" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="searchSO" class="form-label">Sales Orders</label>
                                <input type="text" id="searchSO" name="so_number" class="form-control" placeholder="Search Sales Orders">
                            </div>
                            <div class="col-md-2">
                                <label for="searchWO" class="form-label">Work Orders</label>
                                <input type="text" id="searchWO" name="wo_number" class="form-control" placeholder="Search Work Orders">
                            </div>
                            <div class="col-md-2">
                                <label for="searchWC" class="form-label">Work Centers</label>
                                <input type="text" id="searchWC" name="work_center" class="form-control" placeholder="Search Work Centers">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Search</button>
                            
                            </div>

                        </form>
                        <br>
                        <table class="table table-bordered dt-responsive w-100">
                            <style>
                                .table-header {
                                    background-color: #007bff; /* Warna biru */
                                    color: white; /* Warna teks putih */
                                }
                            </style>
                            <thead>
                                <thead>
                                    <tr class="table-header">
                                        <th style="background-color: #007bff;color: white;">No</th>
                                        <th style="background-color: #007bff;color: white;">Sales Orders</th>
                                        <th style="background-color: #007bff;color: white;">Customers</th>
                                        <th style="background-color: #007bff;color: white;">Work Orders</th>
                                        <th style="background-color: #007bff;color: white;">Work Centers</th>
                                        <th style="background-color: #007bff;color: white;">Group</th>
                                        <th style="background-color: #007bff;color: white;">Staff</th>
                                        <th style="background-color: #007bff;color: white;">Created_at</th>
                                        <th style="background-color: #007bff;color: white;">jml</th>
                                        <th style="background-color: #007bff;color: white;">Action</th>
                                    </tr>
                                </thead>
                                
                            </thead>
                            <tbody>
                                @foreach ($results as $data)
                                @php
                                   $createdTime = \Carbon\Carbon::parse($data->created_at);
                                $isNew = $createdTime->gt(\Carbon\Carbon::now()->subDay()); // Tidak mengubah $currentTime
                                @endphp
                                <style>
                                    .newly-created {
                                        background-color: rgb(125, 157, 237);
                                    }
                                    </style>
                                <tr>
                                    <td  style="{{ $isNew ? 'background-color: rgb(248, 248, 243);' : '' }}">{{ $loop->iteration }}</td>
                                    <td  style="{{ $isNew ? 'background-color: rgb(248, 248, 243);' : '' }}">{{ $data->so_number }}</td>
                                    <td  style="{{ $isNew ? 'background-color: rgb(248, 248, 243);' : '' }}">{{ $data->name_cust ?? 'N/A' }}</td>
                                    <td  style="{{ $isNew ? 'background-color: rgb(248, 248, 243);' : '' }}">{{ $data->wo_number }}</td>
                                    <td style="{{ $isNew ? 'background-color: rgb(248, 248, 243);' : '' }}">{{ $data->work_center }}</td>
                                    <td style="{{ $isNew ? 'background-color: rgb(248, 248, 243);' : '' }}">{{ $data->shift }}</td>
                                    <td style="{{ $isNew ? 'background-color: rgb(248, 248, 243);' : '' }}">{{ $data->staff }}</td>
                                    <td style="{{ $isNew ? 'background-color: rgb(248, 248, 243);' : '' }}">{{ $data->created_at }}</td>
                                    <td style="{{ $isNew ? 'background-color: rgb(248, 248, 243);' : '' }}"><b>{{ $data->barcode_count }}</b></td>
                                    <td style="{{ $isNew ? 'background-color: rgb(248, 248, 243);' : '' }}">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-success btn-sm waves-effect waves-light">Print</button>
                                            <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split btn-sm waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-chevron-down"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('print_standar', $data->id) }}">Print Standard</a>
                                                <a class="dropdown-item" href="{{ route('print_broker', $data->id) }}">Print Broker</a>
                                                <a class="dropdown-item" href="{{ route('print_cbc', $data->id) }}">Print CBC</a>
                                                <hr>
                                                <a class="dropdown-item" href="{{ route('table_print') }}">Traceability</a>
                                            </div>
                                        </div>
                                        <a href="{{ route('show_barcode', $data->id) }}" class="btn btn-primary btn-sm waves-effect waves-light">Detail Barcode</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            
                            
                            
                        </table>
                    </div>
                    <div style="text-align: center">
                        {{$results->links("vendor.pagination.bootstrap-4")}}
                    </div>
                    {{-- {{ $results->appends(request()->except('page'))->links() }} --}}
                </div> <!-- end card -->
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div> <!-- end container-fluid -->
</div> <!-- end page-content -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize DataTable without pagination.
        $('#datatable').DataTable({
            paging: false,  // Disable pagination
            // searching: false, // Optionally disable searching if not needed.
            ordering: false // Optionally disable ordering if managed by server-side.
        });
    });
    </script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Retrieve the current page from local storage
            const currentPage = localStorage.getItem('currentPage');
            if (currentPage) {
                const paginationLinks = document.querySelectorAll('.pagination a');
                paginationLinks.forEach(link => {
                    if (link.textContent.trim() === currentPage) {
                        link.click();
                    }
                });
            }
    
            // Save the clicked page number into local storage
            document.querySelectorAll('.pagination a').forEach(link => {
                link.addEventListener('click', function() {
                    const pageNumber = this.textContent.trim();
                    if (pageNumber) {
                        localStorage.setItem('currentPage', pageNumber);
                    }
                });
            });
        });
    </script>


@endsection
