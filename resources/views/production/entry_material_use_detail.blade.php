@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">
    @csrf
		
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <!--h4 class="mb-sm-0 font-size-18"> Add Request Sparepart & Auxiliaries</h4-->
					<a href="/production-ent-material-use" class="btn btn-dark waves-effect waves-light mb-3"> 
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
                        <h4 class="card-title">Report Material Use</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

						<div class="col-sm-12">
							<div class="mt-4 mt-lg-0">
								<form method="post" action="/production-ent-material-use-detail-update" class="form-material m-t-40" enctype="multipart/form-data">
								@csrf
									<input id="request_id_original" type="hidden" class="form-control" name="request_id" value="{{ Request::segment(2) }}">
									<div class="row mb-4 field-wrapper required-field">
										<label for="horizontal-password-input" class="col-sm-3 col-form-label">Work Orders</label>
										<div class="col-sm-9">
											<select class="form-select " name="id_work_orders" id="id_work_orders">
												<option value="">** Please Select A Work Orders</option>
												@foreach ($ms_work_orders as $data_for)
													<option value="{{ $data_for->id }}" data-id_master_process_productions="{{ $data_for->id_master_process_productions }}" {{ $data_for->id == $data[0]->id_work_orders ? 'selected' : '' }}>{{ $data_for->wo_number }}</option>
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
											<input type="date" name="date" class="form-control" value="{{ $data[0]->date; }}">
											@if($errors->has('date'))
												<div class="text-danger"><b>{{ $errors->first('date') }}</b></div>
											@endif
										</div>
									</div>
									
									<div class="row mb-4 field-wrapper required-field">
										<label for="horizontal-password-input" class="col-sm-3 col-form-label">Work Centers </label>
										<div class="col-sm-9">
											<select class="form-select" name="id_master_work_centers" id="id_master_work_centers" required readonly>
												<option value="">** Please Select A Work Centers</option>
												@foreach ($ms_work_centers as $data_for)
													<option value="{{ $data_for->id }}" {{ $data_for->id == $data[0]->id_master_work_centers ? 'selected' : '' }}>{{ $data_for->work_center_code }} - {{ $data_for->work_center }}</option>
												@endforeach
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
												@foreach ($ms_regus as $data_for)
													<option value="{{ $data_for->id }}" {{ $data_for->id == $data[0]->id_master_regus ? 'selected' : '' }}>{{ $data_for->regu }}</option>
												@endforeach												
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
												<option value="1" {{ $data[0]->shift=='1'?'selected':'' }}>1</option>
												<option value="2" {{ $data[0]->shift=='2'?'selected':'' }}>2</option>
												<option value="3" {{ $data[0]->shift=='3'?'selected':'' }}>3</option>
											</select>
											@if($errors->has('shift'))
												<div class="text-danger"><b>{{ $errors->first('shift') }}</b></div>
											@endif
										</div>
									</div>	
									<script>
										$(document).ready(function(){
											
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
						<h4 class="card-title">Detail Report Material Use</h4>
						
					</div>
					<div class="card-body p-4">
						<p class="card-title-desc"></p>
						<div class="alert alert-info alert-border-left alert-dismissible fade show mb-0 text-justify" role="alert">
							<i class="mdi mdi-alert-circle-outline align-middle me-3"></i><strong>Info</strong> - Jadikan external lot sebagai acuan penggunaan material. Lot number memiliki beberapa external lot number, penggunaan secara terperinci dapat memastikan jumlah stok material yang tersedia secara pasti di setiap external lot number. Jika material yang tersedia di salah satu external lot tidak mencukupi kebutuhan produksi, silahkan gunakan material di external lot yang lainnya.
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
						<div class="mt-4 col-sm-12">
							<div class="mt-4 mt-lg-0">
								<form method="post" action="/production-entry-material-use-detail-add" class="form-material m-t-40" enctype="multipart/form-data">
								@csrf
								
									<input id="request_id_original" type="hidden" class="form-control" name="request_id" value="{{ Request::segment(2) }}">
									
									<div class="row mb-4 field-wrapper required-field">
										<label for="horizontal-password-input" class="col-sm-3 col-form-label">Barcode  </label>
										<div class="col-sm-9">
											<select class="form-select" name="id_barcode" id="id_barcode" required>
												<option value="">** Please Select A Barcode</option>
												@foreach ($ms_barcodes as $data)
													<option value="{{ $data->id }}" data-lot_number="{{ $data->lot_number }}" data-sisa="{{ $data->sisa }}" data-id="{{ sha1($data->id) }}">{{ $data->lot_number }} ( EXT : {{ $data->ext_lot_number }} ) - {{ $data->description }} ( Sisa Per EXT : {{ $data->sisa }} Kg)</option>
												@endforeach	
											</select>
											@if($errors->has('id_barcode'))
												<div class="text-danger"><b>{{ $errors->first('id_barcode') }}</b></div>
											@endif
											<input type="hidden" name="token" id="token">
											<input type="hidden" name="token_rmu" id="token_rmu" class="form-control" value="{{ Request::segment(2) }}">
											<input type="hidden" name="sisa_ext" id="sisa_ext">
										</div>
									</div>	
									<script>
										$(document).ready(function(){
											konten = '<div class="alert alert-dark alert-dismissible alert-label-icon label-arrow fade show" role="alert"><i class="mdi mdi-alert-outline label-icon"></i><font class="text-white">Informasi Tidak Tersedia</font></div>';
											$( "#div-informasi" ).html( konten );
											
											$("#id_barcode").change(function(){
												konten_remaining = '<div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert"><i class="mdi mdi-alert-outline label-icon"></i><font class="text-white">Informasi Tidak Tersedia</font></div>';
												$( "#div-info-remaining" ).html( konten_remaining );
											
												document.getElementById('token').value = $('#id_barcode option:selected').attr('data-id');
												document.getElementById('sisa_ext').value = $('#id_barcode option:selected').attr('data-sisa');
												document.getElementById('taking').value = '';
												document.getElementById('usage').value = '';
												
												$.ajax({
													type: "GET",
													url: "/json_get_material_info",
													data: { lot_number : $('#id_barcode option:selected').attr('data-lot_number') },
													
													dataType: "json",
													beforeSend: function(e) {
														if(e && e.overrideMimeType) {
															e.overrideMimeType("application/json;charset=UTF-8");
														}
													},
													success: function(response){
														konten = '<div class="alert alert-dark alert-dismissible alert-label-icon label-arrow fade show" role="alert"><i class="mdi mdi-alert-outline label-icon"></i><strong><font id="sisa_camp">'+response.stok_ext_all+'</font></strong> Kg - <font class="text-white">Sisa material secara keseluruhan</font></div>';
														$( "#div-informasi" ).html( konten );
													},
													error: function (xhr, ajaxOptions, thrownError) {
														konten = '<div class="alert alert-dark alert-dismissible alert-label-icon label-arrow fade show" role="alert"><i class="mdi mdi-alert-outline label-icon"></i><font class="text-white">Informasi Tidak Tersedia</font></div>';
														$( "#div-informasi" ).html( konten );
														alert(xhr.status + "\n" + xhr.responseText + "\n" + thrownError);
													}
												});
												
											});
											
										});
									</script>
									<div class="row mb-4 field-wrapper">
										<label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
										<div class="col-sm-9">
											<div id="div-informasi"></div>
										</div>
									</div>
									<div class="row mb-4 field-wrapper required-field">
										<label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Taking</label>
										<div class="col-sm-9">
											<input class="form-control" name="taking" id="taking" type="text">
											<p class="card-title-desc"><code>.Kg - Catatan : Jumlah taking tidak boleh lebih banyak dari <strong>( Sisa Per EXT )</strong>.</code></p>
											@if($errors->has('taking'))
												<div class="text-danger"><b>{{ $errors->first('taking') }}</b></div>
											@endif
										</div>
									</div>
									<script>
										$(document).ready(function(){
											$("#taking").keyup(function() {
												var n_taking = parseFloat(document.getElementById('taking').value);
												var n_sisa_ext = parseFloat(document.getElementById('sisa_ext').value);

												if (!isNaN(n_taking) && !isNaN(n_sisa_ext) && n_taking>n_sisa_ext) {
													document.getElementById('taking').value = n_sisa_ext;
												}
											});
										});
									</script>
									<div class="row mb-4 field-wrapper required-field">
										<label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Usage</label>
										<div class="col-sm-9">
											<input type="text" class="form-control" name="usage" id="usage">
											<p class="card-title-desc"><code>.Kg</code></p>
											@if($errors->has('usage'))
												<div class="text-danger"><b>{{ $errors->first('usage') }}</b></div>
											@endif
										</div>
									</div>
									<script>
										$(document).ready(function(){
											$("#usage").keyup(function() {
												var n_taking = parseFloat(document.getElementById('taking').value);
												var n_usage = parseFloat(document.getElementById('usage').value);

												if (!isNaN(n_taking) && !isNaN(n_usage) && n_usage > n_taking) {
													document.getElementById('usage').value = n_taking;
												}
											});
										});
									</script>
									<div class="row mb-4 field-wrapper required-field">
										<label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Sisa Campuran</label>
										<div class="col-sm-9">
											<input class="form-control" name="sisa_campuran" id="sisa_campuran" type="text">
											<p class="card-title-desc"><code>.Kg</code></p>
											@if($errors->has('sisa_campuran'))
												<div class="text-danger"><b>{{ $errors->first('sisa_campuran') }}</b></div>
											@endif
										</div>
									</div>
									
									<div class="row mb-4 field-wrapper">
										<label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Remaining</label>
										<div class="col-sm-9">
											<div id="div-info-remaining"></div>
										</div>
									</div>
									<script>
										$(document).ready(function(){
											konten = '<div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert"><i class="mdi mdi-alert-outline label-icon"></i><font class="text-white">Informasi Tidak Tersedia</font></div>';
											$( "#div-info-remaining" ).html( konten );
											
											var usage = document.getElementById('usage');
											
											usage.addEventListener('input', function() {
												var n_taking = parseFloat(document.getElementById('taking').value);
												var n_usage = parseFloat(document.getElementById('usage').value);
												
												var n_usage = n_usage > n_taking ? n_taking : n_usage ;
												
												var hasil = (n_taking-n_usage).toFixed(1);
												konten = '<div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert"><i class="mdi mdi-alert-outline label-icon"></i><strong><font>'+hasil+'</font></strong> Kg</div>';
												$( "#div-info-remaining" ).html( konten );
											});
										});
									</script>
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
										<th width="20%">Barcode</th>
										<th width="25%">Raw Material</th>
										<th width="15%">Sisa Camp</th>
										<th width="20%">Capacity</th>
										<th width="20%">Aksi</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($data_detail as $data)
									<tr>
										<td>
											{{ $data->lot_number }} <br>
											<font style="font-size:8px:"> 
												EXT : <b>{{ $data->ext_lot_number}}</b>
											</font>
										</td>
										<td>
										    ID : <b>{{ $data->id_master_products }}</b> <br>
											{{ $data->description }}
										</td>
										<td>{{ $data->sisa_camp }}</td>
										<td>
											Taking : <b>{{ $data->taking }}</b> <br>
											Usage : <b>{{ $data->usage }}</b> <br>
											Remaining : <b>{{ $data->remaining }}</b>
										</td>
										
										
										<td>	
											<center>
												<form action="/production-entry-material-use-detail-delete" method="post" class="d-inline" enctype="multipart/form-data">
													@csrf		
													<input type="hidden" class="form-control" name="token_rmu" value="{{ Request::segment(2) }}">
													<button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure to delete this item ?')" value="{{ sha1($data->id) }}" name="hapus_detail">
														<i class="bx bx-trash-alt" title="Delete" ></i> DELETE
													</button>
												</form>	
												<button type="button" class="btn btn-info waves-effect waves-light" id=""
													data-bs-toggle="modal"
													onclick="#"
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