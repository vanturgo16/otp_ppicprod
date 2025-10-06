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
          <h4 class="mb-sm-0 font-size-18"><a href="{{ route('barcode') }}">Barcode</a></h4>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><a href="javascript:void(0);">PPIC</a></li>
              <li class="breadcrumb-item active">Barcode</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">

          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">
              <a href="{{ url('/create-barcode') }}" class="btn btn-success">Add New Generate Barcode</a>
              <a href="{{ url('/create-barcode-mesin') }}" class="btn btn-info">Add New Generate Barcode (AUX,RM)</a>
            </div>
          </div>

          <div class="card-body">

            {{-- Form Filter --}}
            <form action="{{ route('barcode') }}" method="GET" class="row g-3 align-items-end">
              <div class="col-md-2">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control"
                       value="{{ request('start_date') }}">
              </div>
              <div class="col-md-2">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control"
                       value="{{ request('end_date') }}">
              </div>
              <div class="col-md-2">
                <label for="searchSO" class="form-label">Sales Orders</label>
                <input type="text" id="searchSO" name="so_number" class="form-control"
                       placeholder="Search Sales Orders" value="{{ request('so_number') }}">
              </div>
              <div class="col-md-2">
                <label for="searchWO" class="form-label">Work Orders</label>
                <input type="text" id="searchWO" name="wo_number" class="form-control"
                       placeholder="Search Work Orders" value="{{ request('wo_number') }}">
              </div>
              <div class="col-md-2">
                <label for="searchWC" class="form-label">Work Centers</label>
                <input type="text" id="searchWC" name="work_center" class="form-control"
                       placeholder="Search Work Centers" value="{{ request('work_center') }}">
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
              </div>
            </form>

            <br>

            {{-- Styles tabel --}}
            <style>
              .table-header th { background-color:#007bff; color:#fff; }
              .row-new { background-color: rgb(248, 248, 243); }
              .table td, .table th { vertical-align: middle; }
            </style>

            {{-- Tabel Data --}}
            <table class="table table-bordered w-100">
              <thead class="table-header">
                <tr>
                  <th>No</th>
                  <th>Sales Orders</th>
                  <th>Customers</th>
                  <th>Work Orders</th>
                  <th>Work Centers</th>
                  <th>Group</th>
                  <th>Staff</th>
                  <th>Created_at</th>
                  <th>jml</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($results as $data)
                  @php
                    $createdTime = \Carbon\Carbon::parse($data->created_at);
                    $isNew = $createdTime->gt(\Carbon\Carbon::now()->subDay());
                  @endphp
                  <tr class="{{ $isNew ? 'row-new' : '' }}">
                    <td>{{ $loop->iteration + ($results->currentPage() - 1) * $results->perPage() }}</td>
                    <td>{{ $data->so_number ?? '-' }}</td>
                    <td>{{ $data->name_cust ?? '-' }}</td>
                    <td>{{ $data->wo_number ?? '-' }}</td>
                    <td>{{ $data->work_center ?? '-' }}</td>
                    <td>{{ $data->shift ?? '-' }}</td>
                    <td>{{ $data->staff ?? '-' }}</td>
                    <td>{{ $createdTime->format('Y-m-d H:i:s') }}</td>
                    <td><b>{{ $data->barcode_count ?? 0 }}</b></td>
                    <td>
                      <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm">Print</button>
                        <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split btn-sm"
                                data-bs-toggle="dropdown" aria-expanded="false">
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
                      <a href="{{ route('show_barcode', $data->id) }}" class="btn btn-primary btn-sm">Detail Barcode</a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="10" class="text-center">Tidak ada data.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>

          </div>

          {{-- Pagination angka saja (tanpa Next/Prev), tetap bawa query filter --}}
          <div class="text-center mb-3">
            @if ($results->hasPages())
              @php
                $current = $results->currentPage();
                $last    = $results->lastPage();
                $window  = 2; // jumlah halaman di kiri/kanan halaman aktif
                $start   = max(1, $current - $window);
                $end     = min($last, $current + $window);

                // helper buat bikin URL dengan query filter saat ini
                function page_url($page) {
                    $query = request()->query();
                    $query['page'] = $page;
                    return url()->current() . '?' . http_build_query($query);
                }
              @endphp

              <nav>
                <ul class="pagination justify-content-center">
                  {{-- Halaman pertama --}}
                  @if ($start > 1)
                    <li class="page-item">
                      <a class="page-link" href="{{ page_url(1) }}">1</a>
                    </li>
                  @endif
                  {{-- Ellipsis kiri --}}
                  @if ($start > 2)
                    <li class="page-item disabled" aria-disabled="true">
                      <span class="page-link">…</span>
                    </li>
                  @endif

                  {{-- Window halaman tengah --}}
                  @for ($i = $start; $i <= $end; $i++)
                    @if ($i == $current)
                      <li class="page-item active" aria-current="page">
                        <span class="page-link">{{ $i }}</span>
                      </li>
                    @else
                      <li class="page-item">
                        <a class="page-link" href="{{ page_url($i) }}">{{ $i }}</a>
                      </li>
                    @endif
                  @endfor

                  {{-- Ellipsis kanan --}}
                  @if ($end < $last - 1)
                    <li class="page-item disabled" aria-disabled="true">
                      <span class="page-link">…</span>
                    </li>
                  @endif
                  {{-- Halaman terakhir --}}
                  @if ($end < $last)
                    <li class="page-item">
                      <a class="page-link" href="{{ page_url($last) }}">{{ $last }}</a>
                    </li>
                  @endif
                </ul>
              </nav>
            @endif
          </div>

        </div> <!-- end card -->
      </div> <!-- end col -->
    </div> <!-- end row -->

  </div> <!-- end container-fluid -->
</div> <!-- end page-content -->

@endsection
