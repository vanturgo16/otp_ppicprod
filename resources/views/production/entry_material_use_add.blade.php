@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">
    <form method="post" action="/production-ent-material-use-save" class="form-material m-t-40" enctype="multipart/form-data">
    @csrf
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
					<a href="/production-req-sparepart-auxiliaries" class="btn btn-dark waves-effect waves-light mb-3"> 
					<i class="bx bx-list-ul" title="Back"></i> REPORT MATERIAL USE</a>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Production</a></li>
                            <li class="breadcrumb-item active"> Add Report Material Use</li>
                        </ol>
                    </div>
                </div>
                
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Report Material Use</h4>
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                    
								<div class="row mb-4 field-wrapper required-field">
									<label for="horizontal-password-input" class="col-sm-3 col-form-label">Work Orders </label>
									<div class="col-sm-9">
										<select class="form-select" name="id_work_orders" id="id_work_orders" required>
											<option value="">** Please Select A Work Orders</option>
											@foreach ($ms_work_orders as $data)
												<option value="{{ $data->id }}" data-id_master_process_productions="{{ $data->id_master_process_productions }}">{{ $data->wo_number }}</option>
											@endforeach
										</select>
										@if($errors->has('id_work_orders'))
											<div class="text-danger"><b>{{ $errors->first('id_work_orders') }}</b></div>
										@endif
									</div>
								</div>
								<div class="row mb-4 field-wrapper required-field">
									<label for="horizontal-email-input" class="col-sm-3 col-form-label">Date</label>
									<div class="col-sm-9">
										<input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
										@if($errors->has('date'))
											<div class="text-danger"><b>{{ $errors->first('date') }}</b></div>
										@endif
									</div>
								</div>
								<div class="row mb-4 field-wrapper required-field">
									<label for="horizontal-password-input" class="col-sm-3 col-form-label">Work Centers </label>
									<div class="col-sm-9">
										<select class="form-select" name="id_master_work_centers" id="id_master_work_centers" required>
											<option value="">** Please Select A Work Centers</option>
										</select>
										@if($errors->has('id_master_work_centers'))
											<div class="text-danger"><b>{{ $errors->first('id_master_work_centers') }}</b></div>
										@endif
									</div>
								</div>
								<div class="row mb-4 field-wrapper required-field">
									<label for="horizontal-password-input" class="col-sm-3 col-form-label">Regu  </label>
									<div class="col-sm-9">
										<select class="form-select" name="id_master_regus" id="id_master_regus" required>
											<option value="">** Please Select A Regu</option>											
										</select>
										@if($errors->has('id_master_regus'))
											<div class="text-danger"><b>{{ $errors->first('id_master_regus') }}</b></div>
										@endif
									</div>
								</div>
								<div class="row mb-4 field-wrapper required-field">
									<label for="horizontal-password-input" class="col-sm-3 col-form-label">Shift  </label>
									<div class="col-sm-9">
										<select class="form-select" name="shift" id="shift" required>
											<option value="">** Please Select A Shift</option>
											<option value="1">1</option>
											<option value="2">2</option>
											<option value="3">3</option>
										</select>
										@if($errors->has('shift'))
											<div class="text-danger"><b>{{ $errors->first('shift') }}</b></div>
										@endif
									</div>
								</div>	
								<script>
									$(document).ready(function(){
										$('#id_work_orders').prop('selectedIndex', 0);
										$('#id_master_work_centers').prop('selectedIndex', 0);
										$('#id_master_regus').prop('selectedIndex', 0);
										$('#shift').prop('selectedIndex', 0);
										
										$("#id_work_orders").change(function(){										
											$.ajax({
												type: "GET",
												url: "/json_get_work_center",
												data: { id_master_process_productions : $('#id_work_orders option:selected').attr('data-id_master_process_productions') },
												dataType: "json",
												beforeSend: function(e) {
													if(e && e.overrideMimeType) {
														e.overrideMimeType("application/json;charset=UTF-8");
													}
												},
												success: function(response){
													$("#id_master_work_centers").html(response.list_work_center).show();
													$('#id_master_regus').prop('selectedIndex', 0);
													$('#shift').prop('selectedIndex', 0);
												},
												error: function (xhr, ajaxOptions, thrownError) {
													alert(xhr.status + "\n" + xhr.responseText + "\n" + thrownError);
												}
											});
										});
										
										$("#id_master_work_centers").change(function(){										
											$.ajax({
												type: "GET",
												url: "/json_get_regu",
												data: { id_master_work_centers : $("#id_master_work_centers").val() },
												dataType: "json",
												beforeSend: function(e) {
													if(e && e.overrideMimeType) {
														e.overrideMimeType("application/json;charset=UTF-8");
													}
												},
												success: function(response){
													$("#id_master_regus").html(response.list_regu).show();
													$('#shift').prop('selectedIndex', 0);
												},
												error: function (xhr, ajaxOptions, thrownError) {
													alert(xhr.status + "\n" + xhr.responseText + "\n" + thrownError);
												}
											});
										});
										
										$("#id_master_regus").change(function(){	
											$('#shift').prop('selectedIndex', 0);
										});
										
									});
								</script>							
								<div class="row justify-content-end">
									<div class="col-sm-9">
										<div>
											<a href="/production-ent-material-use" class="btn btn-danger waves-effect waves-light"><i class="bx bx-chevron-left" title="Back"></i> BACK</a>
											
											<button type="submit" class="btn btn-success w-md" name="save"><i class="bx bx-save" title="Back"></i> SAVE</button>
										</div>
									</div>
								</div>                        
                        
                            </div>
							
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </form>
                    <!-- end row -->
    </div>
</div>

@endsection