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
                        <h4 class="mb-sm-0 font-size-18"> Request Sparepart & Auxiliaries</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Production</a></li>
                                <li class="breadcrumb-item active"> Request Sparepart & Auxiliaries</li>
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
                                    <a href="/production-req-sparepart-auxiliaries-add" class="btn btn-success waves-effect waves-light">
										<i class="bx bx-plus" title="Add Data" ></i>
										ADD
									</a>                                   
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr>
                                        <tr>
                                            <th width="20%">Request Number</th>
                                            <th width="20%">Date</th>
                                            <th width="20%">Departement</th>
                                            <th width="20%">Status</th>
                                            <th width="20%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
										@foreach ($data as $data)
										<tr>
											<td>{{ $data->request_number }}</td>
											<td>{{ $data->date }}</td>
											<td>{{ $data->name }}</td>
											<td>{{ $data->status }}</td>
											
											
											<td>
												<center>
													@if($data->status=='Hold') 
													<form action="/production-req-sparepart-auxiliaries-hold" method="post" class="d-inline" enctype="multipart/form-data">
													@csrf		
														<input type="hidden" class="form-control" name="request_number" value="{{ $data->request_number }}">
														<button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure to hold this item ?')" name="approve" value="{{ sha1($data->id) }}">
															<i class="bx bx-check" title="Approve"></i> APPROVE
														</button>
													</form>
													@elseif($data->status=='Approve')
													<form action="/production-req-sparepart-auxiliaries-hold" method="post" class="d-inline" enctype="multipart/form-data">
													@csrf		
														<input type="hidden" class="form-control" name="request_number" value="{{ $data->request_number }}">
														<button type="submit" class="btn btn-secondary" onclick="return confirm('Are you sure to hold this item ?')" name="hold" value="{{ sha1($data->id) }}">
															<i class="bx bx-block" title="Hold"></i> HOLD
														</button>
													</form>
													@elseif($data->status=='Request')
													<form action="/production-req-sparepart-auxiliaries-hold" method="post" class="d-inline" enctype="multipart/form-data">
													@csrf		
														<input type="hidden" class="form-control" name="request_number" value="{{ $data->request_number }}">
														<button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure to approve this item ?')" name="approve" value="{{ sha1($data->id) }}">
															<i class="bx bx-check" title="Approve"></i> APPROVE
														</button>
													</form>
													<form action="/production-req-sparepart-auxiliaries-hold" method="post" class="d-inline" enctype="multipart/form-data">
													@csrf		
														<input type="hidden" class="form-control" name="request_number" value="{{ $data->request_number }}">
														<button type="submit" class="btn btn-secondary" onclick="return confirm('Are you sure to hold this item ?')" name="hold" value="{{ sha1($data->id) }}">
															<i class="bx bx-block" title="Hold"></i> HOLD
														</button>
													</form>
													@endif
													<form action="/production-req-sparepart-auxiliaries-delete" method="post" class="d-inline" enctype="multipart/form-data">
													@csrf	
														<input type="hidden" class="form-control" name="request_number" value="{{ $data->request_number }}">
														<button type="submit" class="btn btn-danger" onclick="return confirm('This items may have detail, Are you sure to delete this item ?')" name="hapus" value="{{ sha1($data->id) }}">
															<i class="bx bx-trash-alt" title="Delete" ></i> DELETE
														</button>
													</form>												
													<a href="/production-req-sparepart-auxiliaries-detail/{{ sha1($data->request_number) }}" class="btn btn-info waves-effect waves-light">
														<i class="bx bx-edit-alt" title="Edit"></i> EDIT
													</a>
												</center>
											</td>
										 
										</tr>
                                        @endforeach
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