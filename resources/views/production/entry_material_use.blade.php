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
                        <h4 class="mb-sm-0 font-size-18"> Report Material Use</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Production</a></li>
                                <li class="breadcrumb-item active"> Report Material Use</li>
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
                                    <a href="/production-ent-material-use-add" class="btn btn-success waves-effect waves-light">
										<i class="bx bx-plus" title="Add Data" ></i>
										ADD
									</a>                                   
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
								<table class="table table-bordered dt-responsive nowrap w-100 datatable-emu-json">
                                    <thead>
                                        <tr>
                                            <th width='10%'>Work Order</th>
                                            <th width='10%'>Date</th>
                                            <th width='20%'>Regus</th>
                                            <th width='10%'>Shift</th>
                                            <th width='20%'>Work Centers</th>
                                            <th width='10%'>Status</th>
                                            <th width='20%'>Aksi</th>
                                        </tr>
                                    </thead>
								</table>
								<script type="text/javascript">
								  $(function () {
									var table = $('.datatable-emu-json').DataTable({
										processing: true,
										serverSide: true,
										ajax: '/production-ent-material-use-json',
										columns: [
											{data: 'wo_number', name: 'wo_number', orderable: true, searchable: true},
											{data: 'date', name: 'date', orderable: true, searchable: true},
											{data: 'regu', name: 'regu', orderable: true, searchable: true},
											{data: 'shift', name: 'shift', orderable: true, searchable: true},
											{data: 'work_center', name: 'work_center', orderable: true, searchable: true},
											{data: 'status', name: 'status', orderable: true, searchable: true},
											{data: 'action', name: 'action', orderable: false, searchable: false},
										],
										aaSorting: [
											[1, 'desc']
										],
									});
								  });
								</script>
							</div>							
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
