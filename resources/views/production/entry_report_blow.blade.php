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
                                    <a href="/hapus_request_number" class="btn btn-success waves-effect waves-light">
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
                                            <th width="20%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
										@foreach ($datas as $data)
										<tr>
											<td>{{ $data->request_number }}</td>
											<td>{{ $data->date }}</td>
											<td>{{ $data->name }}</td>
											<td>{{ $data->status }}</td>
											
											
											<td>											
												<a href="/production-req-sparepart-auxiliaries-hold/{{ $data->request_number }}" class="btn btn-warning waves-effect waves-light">
													<i class="bx bx-block" title="Hold"></i> HOLD
												</a>
												<a href="/production-req-sparepart-auxiliaries-view/{{ $data->request_number }}" class="btn btn-primary waves-effect waves-light">
													<i class="bx bx-search-alt" title="Print"></i> VIEW
												</a>		
												<!--a href="/production-req-sparepart-auxiliaries--print/{{ $data->request_number }}" class="btn btn-dark waves-effect waves-light">
													<i class="bx bx-printer" title="Print"></i> PRINT
												</a>	
												<a href="/production-req-sparepart-auxiliaries-edit/{{ $data->request_number }}" class="btn btn-info waves-effect waves-light">
													<i class="bx bx-edit-alt" title="Edit"></i> EDIT
												</a>
												<form action="/production-req-sparepart-auxiliaries-delete/{{ $data->request_number }}" method="post"
													class="d-inline">
													@method('delete')
													@csrf
												   
													<button type="submit" class="btn btn-danger"
													onclick="return confirm('Anda yakin mau menghapus item ini ?')">
														<i class="bx bx-trash-alt" title="Delete" ></i> DELETE
													</button>
												</form-->												
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