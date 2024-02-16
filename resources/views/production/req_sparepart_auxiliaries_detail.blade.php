@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">
    @csrf
		
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <!--h4 class="mb-sm-0 font-size-18"> Add Request Sparepart & Auxiliaries</h4-->
					<a href="/production-req-sparepart-auxiliaries" class="btn btn-dark waves-effect waves-light mb-3"> 
					<i class="bx bx-list-ul" title="Back"></i> REQUEST SPAREPART & AUXILIARIES</a>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Production</a></li>
                            <li class="breadcrumb-item active"> Add Request Sparepart & Auxiliaries</li>
                        </ol>
                    </div>
                </div>
                
            </div>
        </div>
		@if (session('pesan'))
            <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-check-all label-icon"></i><strong>Success</strong> - {{ session('pesan') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
		@if(!empty($data[0]))
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Request Sparepart & Auxiliaries</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
								<form method="post" action="/production-req-sparepart-auxiliaries-detail-update" class="form-material m-t-40" enctype="multipart/form-data">
								@csrf
									<input id="request_number_original" type="hidden" class="form-control" name="request_number" value="{{ Request::segment(2) }}">
									<div class="row mb-4 field-wrapper required-field">
										<label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Request Number</label>
										<div class="col-sm-9">
											<input type="text" name="request_number_view" class="form-control" value="{{ $data[0]->request_number; }}" readonly>
											@if($errors->has('request_number_view'))
												<div class="text-danger"><b>{{ $errors->first('request_number_view') }}</b></div>
											@endif
										</div>
									</div>
									<div class="row mb-4 field-wrapper required-field">
										<label for="horizontal-email-input" class="col-sm-3 col-form-label">Date</label>
										<div class="col-sm-9">
											<input type="date" name="date" class="form-control" value="{{ $data[0]->date; }}">
											@if($errors->has('date'))
												<div class="text-danger"><b>{{ $errors->first('date') }}</b></div>
											@endif
										</div>
									</div>
									<div class="row mb-4 field-wrapper required-field">
										<label for="horizontal-password-input" class="col-sm-3 col-form-label">Departements </label>
										<div class="col-sm-9">
											<select class="form-select " name="id_master_departements" id="">
												<option>** Please Select A Departements</option>
												@foreach ($ms_departements as $data_for)
													<option value="{{ $data_for->id }}" {{ $data_for->id == $data[0]->id_master_departements ? 'selected' : '' }}>{{ $data_for->name }}</option>
												@endforeach
											</select>
											@if($errors->has('id_master_departements'))
												<div class="text-danger"><b>{{ $errors->first('id_master_departements') }}</b></div>
											@endif
										</div>
									</div>
									<div class="row mb-4 field-wrapper required-field">
										<label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Status </label>
										<div class="col-sm-9">
											<input type="text" class="form-control" name="status" value="Request" readonly>
											@if($errors->has('status'))
												<div class="text-danger"><b>{{ $errors->first('status') }}</b></div>
											@endif
										</div>
									</div>
									<div class="row justify-content-end">
										<div class="col-sm-9">
											<div>
												<button type="submit" class="btn btn-success w-md" name="update"><i class="bx bx-save" title="Back"></i> UPDATE</button>
											</div>
										</div>
									</div>
                                </form>    
                        
                            </div>
							
                        </div>
                    </div>
                </div>
            </div>
        </div> 
		<div class="row">
			<div class="col-lg-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title">Detail Request Sparepart & Auxiliaries</h4>
						<!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
					</div>
					<div class="card-body p-4">

					<div class="col-sm-12">
							<div class="mt-4 mt-lg-0">
								<form method="post" action="/production-req-sparepart-auxiliaries-detail-add" class="form-material m-t-40" enctype="multipart/form-data">
								@csrf
									<input type="hidden" class="form-control" name="request_number" value="{{ Request::segment(2) }}">
									<div class="row mb-4 field-wrapper">
										<label for="horizontal-password-input" class="col-sm-3 col-form-label">Sparepart & Auxiliaries </label>
										<div class="col-sm-9">
											<select class="form-select" name="id_master_tool_auxiliaries" id="">
												<option value="">** Please Select A Sparepart & Auxiliaries </option>
												@foreach ($ms_tool_auxiliaries as $data)
													<option data-tokens="{{ $data->description }}" value="{{ $data->id }}">{{ $data->description }}</option>
												@endforeach
											</select>
											@if($errors->has('id_master_tool_auxiliaries'))
												<div class="text-danger"><b>{{ $errors->first('id_master_tool_auxiliaries') }}</b></div>
											@endif
										</div>
									</div>
									<!--div class="row mb-4 field-wrapper">
										<label for="horizontal-password-input" class="col-sm-3 col-form-label">Sparepart & Auxiliaries </label>
										<div class="col-sm-9">
											<input id="custom_field1" name="custom_field1" type="text" list="custom_field1_datalist" class="form-control" placeholder="** Please Select A Sparepart & Auxiliaries">
											<datalist id="custom_field1_datalist">
												<option>** Please Select A Sparepart & Auxiliaries </option>
												@foreach ($ms_tool_auxiliaries as $data)
													<option data-tokens="{{ $data->description }}" value="{{ $data->id }}">{{ $data->description }}</option>
												@endforeach
											</datalist>
											<span id="error" class="text-danger"></span>
										</div>
									</div>
									<script>
										$('#your-form-id').on('submit', e => {
											$('#error').empty();
											let form = $(e.target);
											let validOptions = form.find('#custom_field1_datalist option').map((key, option) => option.value).toArray();
											let customField1Value = form.find('input[name=custom_field1]').eq(0).val();

											// check if custom_field_1's value is in the datalist. If it's not, it's an invalid choice
											if ( !(validOptions.indexOf(customField1Value) > -1) ) {
												// show error
												$('#error').text('Invalid Choice');
												// prevent form submission (you should still validate in the backend)
												e.preventDefault();
											}
										});
									</script-->
									<div class="row mb-4 field-wrapper">
										<label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Qty</label>
										<div class="col-sm-9">
											<input type="number" class="form-control" name="qty">
											@if($errors->has('qty'))
												<div class="text-danger"><b>{{ $errors->first('qty') }}</b></div>
											@endif
										</div>
									</div>
									<div class="row mb-4 field-wrapper">
										<label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Remarks</label>
										<div class="col-sm-9">
											<input type="text" class="form-control" name="remarks">
											@if($errors->has('remarks'))
												<div class="text-danger"><b>{{ $errors->first('remarks') }}</b></div>
											@endif
										</div>
									</div>
									<div class="row justify-content-end">
										<div class="col-sm-9">
											<div>
												<button type="reset" class="btn btn-secondary w-md"><i class="bx bx-refresh" title="Reset"></i> RESET</button>
												<button type="submit" class="btn btn-primary w-md" name="save"><i class="bx bx-list-plus" title="Add"></i> ADD to TABLE DETAIL</button>
											</div>
										</div>
									</div>				
								</form>
							</div>
						</div>
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
								<h4 class="mb-sm-0 font-size-18"><i class="bx bx-list-check" title="Add"></i> Table Detail</h4>                                
							</div>
						</div>
					</div>
					<div class="card-body">
						@if(!empty($data_detail[0]))
						<div class="table-responsive">
							<table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
								<thead>
									<tr>
									<tr>
										<th width="20%">Request Sparepart & Auxiliaries</th>
										<th width="20%">QTY</th>
										<th width="20%">Remarks</th>
										<th width="20%">Aksi</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($data_detail as $data)
									<tr>
										<td>{{ $data->description }}</td>
										<td>{{ $data->qty }}</td> 
										<td>{{ $data->remarks }}</td>
										
										
										<td>	
											<center>
												<form action="" method="post"
													class="d-inline">
													@method('delete')
													@csrf
												   
													<button type="submit" class="btn btn-danger"
													onclick="return confirm('Anda yakin mau menghapus item ini ?')">
														<i class="bx bx-trash-alt" title="Delete" ></i> DELETE
													</button>
												</form>	
												<button type="button" class="btn btn-info waves-effect waves-light" id=""
													data-bs-toggle="modal"
													onclick="detail_sparepart_auxiliaries_edit('{{ $data->id }}')"
													data-bs-target="#detail-sparepart-auxiliaries-edit" data-id="">
													<i class="bx bx-edit-alt" title="Edit"></i> EDIT
												</button>
											</center>
											@include('production.modal')
										</td>
									 
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
						@else
							<div class="row">
								<div class="col-lg-12 text-center">
									<label>Data Tidak Tersedia</label>
								</div>
							</div>
							
						@endif
					</div>
				</div>
			</div>
		</div>
		@else
			<div class="row">
				<div class="col-lg-12 text-center">
					<div class="card">
						<div class="card-body">
							<label>Data Tidak Tersedia</label>
						</div>
                    </div>
                </div>
            </div>
			
		@endif
    
                    <!-- end row -->
    </div>
</div>

@endsection