@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">
    <form method="post" action="/production-req-sparepart-auxiliaries-save" class="form-material m-t-40" enctype="multipart/form-data">
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
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Request Sparepart & Auxiliaries</h4>
                        <!--  <p class="card-title-desc"> layout options : from inline, horizontal & custom grid implementations</p> -->
                    </div>
                    <div class="card-body p-4">

                    <div class="col-sm-12">
                            <div class="mt-4 mt-lg-0">
                    
								<div class="row mb-4 field-wrapper required-field">
									<label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Request Number</label>
									<div class="col-sm-9">
										<input type="text" name="request_number" class="form-control" value="{{ $formattedCode }}" readonly>
										<input type="hidden" id="html" name="type" value="RM">
									</div>
								</div>
								<div class="row mb-4 field-wrapper required-field">
									<label for="horizontal-email-input" class="col-sm-3 col-form-label">Date</label>
									<div class="col-sm-9">
										<input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
										@if($errors->has('date'))
											<div class="text-danger"><b>{{ $errors->first('date') }}</b></div>
										@endif
									</div>
								</div>
								<div class="row mb-4 field-wrapper required-field">
									<label for="horizontal-password-input" class="col-sm-3 col-form-label">Departements </label>
									<div class="col-sm-9">
										<select class="form-select" name="id_master_departements" id="">
											<option value="">** Please Select A Departements</option>
											@foreach ($ms_departements as $data)
												<option value="{{ $data->id }}">{{ $data->name }}</option>
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
											<a href="/production-req-sparepart-auxiliaries" class="btn btn-danger waves-effect waves-light"><i class="bx bx-chevron-left" title="Back"></i> BACK</a>
											
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