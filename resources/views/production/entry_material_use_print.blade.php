<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.75, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PRINT ENTRY REPORT MATERIAL USE</title>
    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="container-fluid">
        <div class="row mt-2 mb-3">
			<div class="col-md-12 text-center">
			  <table border="1" cellpadding="0" cellspacing="0" style="width: 100%" style="border: 1px solid black">
				
					<tr>
					  <td rowspan="4" style="width: 20%;" class="text-center">
						<img src="http://eks.olefinatifaplas.my.id/img/otp-icon.jpg" width="60" height="60" class="mt-2">
						<br>
						<strong><small>PT OLEFINA TIFAPLAS POLIKEMINDO</small></strong>
					  </td>
					</tr>
					<tr>
					  <td>FORM</td>
					</tr>
					<tr>
					  <td style="background-color: #ecf0f1;">LAPORAN PEMAKAIAN BAHAN (EXTRUDER)</td>
					</tr>
					<tr>
					  <td><small>FM-SM-PO EXT 02, REV 03, 22 Januari 2018</small></td>
					</tr>
				
			  </table>
			</div>
		</div>
		
		<div class="row d-flex justify-content-between pb-3">
            <div class="col-6">
				<table>
					<tr>
					  <td>Tanggal</td>
					  <td> : {{ $data[0]->created_at; }}</td>
					</tr>
					<tr>
					  <td>Shift</td>
					  <td> : {{ $data[0]->shift; }}</td>
					</tr>
				</table>
			</div>
            <div class="col-6">
                <table>
					<tr>
					  <td>No. Mesin</td>
					  <td> : {{ $data[0]->work_center; }}</td>
					</tr>
					<tr>
					  <td>No. WO</td>
				      <td> : {{ $data[0]->wo_number; }}</td>
					</tr>
				</table>
            </div>
        </div>
		
		<div class="row">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-10">
                    <thead class="table-light">
                        <tr>
						  <th>No</th>
						  <th>Nama Material</th>
						  <th>Ratio</th>
						  <th>Sisa Camp (Kg)</th>
						  <th>Pengambilan (Kg)</th>
						  <th>Pakai (Kg)</th>
						  <th>Sisa (Kg)</th>
						  <th>Barcode</th>
						</tr>
                    </thead>
                    <tbody>
						
							@forelse ($data_detail as $data)	
								
								<tr>
									<td>{{ $loop->iteration }}x</td>
									<td>{{ $data->rm_name }}</td>
									<td>-</td>
									<td><pre>{{ $data->sisa_champ==0?0:$data->sisa_champ }}</pre></td>
									<td>{{ $data->taking==0?0:$data->taking }}</td>
									<td>{{ $data->usage==0?0:$data->usage }}</td>
									<td>{{ $data->remaining==0?0:$data->remaining }}</td>
									<td>{{ $data->lot_number }}</td>
										
								</tr>	
							@empty  	
								<tr>	
									<td colspan="8" align="center">-</td>
								</tr>	
								
							@endforelse
						
					
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-4 text-center">
                <p class="mb-5">Operator,</p>
                <p>(.............)</p>
            </div>
            <div class="col-4 text-center">
                <p class="mb-5">Kepala Produksi,</p>
                <p>(.............)</p>
            </div>
            <div class="col-4 text-center">
                <p class="mb-5">Kepala Shift</p>
                <p>(.............)</p>
            </div>
        </div>



    </div>
</body>

</html>
