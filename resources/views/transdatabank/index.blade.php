@extends('layouts.master')

@section('konten')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div class="page-title-left">
                        <button type="button" class="btn btn-primary waves-effect btn-label waves-light" data-bs-toggle="modal" data-bs-target="#add-new"><i class="mdi mdi-plus-box label-icon"></i> Add New Transaction Data Bank</button>
                        {{-- Modal Add --}}
                        <div class="modal fade" id="add-new" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="staticBackdropLabel">Add New Transaction Data Bank</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('transdatabank.store') }}" id="formadd" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-body py-8 px-4" style="max-height: 67vh; overflow-y: auto;">
                                            <div class="row">
                                                <div class="col-6 mb-2">
                                                    <label class="form-label">Currencies</label><label style="color: darkred">*</label>
                                                    <select class="form-select" id="currency" name="currency" required>
                                                        <option value="" selected>--Select Currencies--</option>
                                                        @foreach($currencies as $item)
                                                            <option value="{{ $item->currency_code }}" data-code="{{ $item->currency_code }}">{{ $item->currency_code }} - {{ $item->currency }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="form-label">Bank</label>
                                                    <select class="form-select" name="bank" required>
                                                        <option value="" selected>--Select Bank--</option>
                                                        @foreach($listbank as $item)
                                                            <option value="{{ $item->name_value }}">{{ $item->name_value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-2">
                                                    <label class="form-label">Transaction Date</label><label style="color: darkred">*</label>
                                                    <input class="form-control" name="date" type="date" value="" required>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="form-label">Account Code</label><label style="color: darkred">*</label>
                                                    <select class="form-select" name="id_master_account_codes" required>
                                                        <option value="" selected>--Select Account Code--</option>
                                                        @foreach($accountcodes as $item)
                                                            <option value="{{ $item->id }}">{{ $item->account_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12 mb-2">
                                                    <label class="form-label">Description</label><label style="color: darkred">*</label>
                                                    <textarea class="form-control" rows="3" type="text" class="form-control" name="description" placeholder="Input Description" required></textarea>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-2">
                                                    <label class="form-label">Type Transaction</label><label style="color: darkred">*</label>
                                                    <select class="form-select" id="type_transaction" name="type_transaction" required>
                                                        <option value="" selected>--Select Type Transaction--</option>
                                                        @foreach($typetrans as $item)
                                                            <option value="{{ $item->name_value }}">{{ $item->name_value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-2">
                                                    <label class="form-label">Amount</label><label style="color: darkred">*</label>
                                                    <div class="input-group">
                                                        <div class="input-group-text" style="background-color:rgb(211, 211, 211)" id="currency_type">IDR</div>
                                                        <input class="form-control" name="amount" type="number" placeholder="Input Amount.." required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success waves-effect btn-label waves-light" name="sb"><i class="mdi mdi-plus-box label-icon"></i>Add</button>
                                        </div>
                                    </form>

                                    <script>
                                        $(document).ready(function () {
                                            $('#currency').change(function () {
                                                var selectedOption = $(this).find(':selected');
                                                var code = selectedOption.data('code');
                                                document.getElementById('currency_type').textContent = code;
                                            });
                                        });
                                        document.getElementById('formadd').addEventListener('submit', function(event) {
                                            if (!this.checkValidity()) {
                                                event.preventDefault(); // Prevent form submission if it's not valid
                                                return false;
                                            }
                                            var submitButton = this.querySelector('button[name="sb"]');
                                            submitButton.disabled = true;
                                            submitButton.innerHTML  = '<i class="mdi mdi-reload label-icon"></i>Please Wait...';
                                            return true; // Allow form submission
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-info waves-effect btn-label waves-light" data-bs-toggle="modal" data-bs-target="#sort"><i class="mdi mdi-filter label-icon"></i> Search & Filter</button>
                        {{-- Modal Search --}}
                        <div class="modal fade" id="sort" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="staticBackdropLabel"><i class="mdi mdi-filter label-icon"></i> Search & Filter</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('transdatabank.index') }}" id="formfilter" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-body py-8 px-4" style="max-height: 67vh; overflow-y: auto;">
                                            <div class="row">
                                                <div class="col-6 mb-2">
                                                    <label class="form-label">Transaction Date</label>
                                                    <input class="form-control" name="date" type="date" value="{{ $date }}">
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="form-label">Bank</label>
                                                    <select class="form-select" name="bank" required>
                                                        <option value="" selected>--Select Bank--</option>
                                                        @foreach($listbank as $item)
                                                            <option value="{{ $item->name_value }}" @if($bank == $item->name_value) selected="selected" @endif>{{ $item->name_value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="form-label">Transaction Number</label>
                                                    <input class="form-control" name="trans_number" type="text" value="{{ $trans_number }}">
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="form-label">Description</label>
                                                    <input class="form-control" name="description" type="text" value="{{ $description }}">
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="form-label">Type Transaction</label><label style="color: darkred">*</label>
                                                    <select class="form-select" name="type_transaction">
                                                        <option value="" selected>--Select Type Transaction--</option>
                                                        @foreach($typetrans as $item)
                                                            <option value="{{ $item->name_value }}" @if($type_transaction == $item->name_value) selected="selected" @endif>{{ $item->name_value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <hr class="mt-2">
                                                <div class="col-4 mb-2">
                                                    <label class="form-label">Filter Date</label>
                                                    <select class="form-select" name="searchDate">
                                                        <option value="All" @if($searchDate == 'All') selected @endif>All</option>
                                                        <option value="Custom" @if($searchDate == 'Custom') selected @endif>Custom Date</option>
                                                    </select>
                                                </div>
                                                <div class="col-4 mb-2">
                                                    <label class="form-label">Date From</label>
                                                    <input type="date" name="startdate" id="search1" class="form-control" placeholder="from" value="{{ $startdate }}">
                                                </div>
                                                <div class="col-4 mb-2">
                                                    <label class="form-label">Date To</label>
                                                    <input type="Date" name="enddate" id="search2" class="form-control" placeholder="to" value="{{ $enddate }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-info waves-effect btn-label waves-light" name="sbfilter"><i class="mdi mdi-filter label-icon"></i> Filter</button>
                                        </div>
                                    </form>
                                    <script>
                                        $('select[name="searchDate"]').on('change', function() {
                                            var date = $(this).val();
                                            if(date == 'All'){
                                                $('#search1').val(null);
                                                $('#search2').val(null);
                                                $('#search1').attr("required", false);
                                                $('#search2').attr("required", false);
                                                $('#search1').attr("readonly", true);
                                                $('#search2').attr("readonly", true);
                                            } else {
                                                $('#search1').attr("required", true);
                                                $('#search2').attr("required", true);
                                                $('#search1').attr("readonly", false);
                                                $('#search2').attr("readonly", false);
                                            }
                                        });
                                        var searchDate = $('select[name="searchDate"]').val();
                                        if(searchDate == 'All'){
                                            $('#search1').attr("required", false);
                                            $('#search2').attr("required", false);
                                            $('#search1').attr("readonly", true);
                                            $('#search2').attr("readonly", true);
                                        }

                                        document.getElementById('formfilter').addEventListener('submit', function(event) {
                                            if (!this.checkValidity()) {
                                                event.preventDefault(); // Prevent form submission if it's not valid
                                                return false;
                                            }
                                            var submitButton = this.querySelector('button[name="sbfilter"]');
                                            submitButton.disabled = true;
                                            submitButton.innerHTML  = '<i class="mdi mdi-reload label-icon"></i>Please Wait...';
                                            return true; // Allow form submission
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Accounting</a></li>
                            <li class="breadcrumb-item active">Kas Transaction</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.alert')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-center py-3">
                        <h5 class="mb-0"><b>Transaction Data Bank</b></h5>
                        List of 
                        @if($date != null)
                            (Bank<b> - {{ $bank }}</b>)
                        @endif
                        @if($date != null)
                            (Transaction Date<b> - {{ $date }}</b>)
                        @endif
                        @if($trans_number != null)
                            (Trans Number<b> - {{ $trans_number }}</b>)
                        @endif
                        @if($description != null)
                            (Description<b> - {{ $description }}</b>)
                        @endif
                        @if($type_transaction != null)
                            (Type<b> - {{ $type_transaction }}</b>)
                        @endif
                        @if($searchDate == 'Custom')
                            (Date From<b> {{ $startdate }} </b>Until <b>{{ $enddate }}</b>)
                        @else
                            (<b>All Created Date</b>)
                        @endif 
                    </div>
                    <div class="card-body">

                        <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th class="align-middle text-center">No</th>
                                    <th class="align-middle text-center">Transaction Number</th>
                                    <th class="align-middle text-center">Bank</th>
                                    <th class="align-middle text-center">Description</th>
                                    <th class="align-middle text-center">Account Code</th>
                                    <th class="align-middle text-center">Currency</th>
                                    <th class="align-middle text-center">Amount</th>
                                    <th class="align-middle text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($datas as $data)
                                    <tr>
                                        <td class="align-middle text-center">{{ $data->no }}</td>
                                        <td class="align-middle">
                                            <b>{{ $data->trans_number }} ({{ $data->date }})</b>
                                            <br>
                                            {{ $data->type_transaction }}
                                        </td>
                                        <td class="align-middle text-center">{{ $data->bank }}</td>
                                        <td class="align-middle">{{ $data->description }}</td>
                                        <td class="align-middle text-center"><b>{{ $data->account_code }}</b></td>
                                        <td class="align-middle text-center">{{ $data->currency }}</td>
                                        <td class="align-middle">
                                            <b>{{ $data->currency }} </b>{{ $data->amount }}
                                            <br>
                                            <b>in IDR : </b>{{ $data->amount_in_idr }}
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="btn-group" role="group">
                                                <button id="btnGroupDrop{{ $data->id }}" type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    Action <i class="mdi mdi-chevron-down"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu2" aria-labelledby="btnGroupDrop{{ $data->id }}">
                                                    <li><a class="dropdown-item drpdwn" href="#" data-bs-toggle="modal" data-bs-target="#info{{ $data->id }}"><span class="mdi mdi-information"></span> | Info</a></li>
                                                    <li><a class="dropdown-item drpdwn" href="#" data-bs-toggle="modal" data-bs-target="#update{{ $data->id }}"><span class="mdi mdi-file-edit"></span> | Edit</a></li>
                                                    <li><a class="dropdown-item drpdwn-dgr" href="#" data-bs-toggle="modal" data-bs-target="#delete{{ $data->id }}"><span class="mdi mdi-delete-alert"></span> | Delete</a></li>
                                                </ul>
                                            </div>
                                        </td>

                                        {{-- Modal Info --}}
                                        <div class="modal fade" id="info{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-top" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="staticBackdropLabel">Info Account Type</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-lg-6 mb-2">
                                                                <div class="form-group">
                                                                    <div><span class="fw-bold">Currency :</span></div>
                                                                    <span>
                                                                        <span>{{ $data->currency }}</span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6 mb-2">
                                                                <div class="form-group">
                                                                    <div><span class="fw-bold">Date :</span></div>
                                                                    <span>
                                                                        <span>{{ $data->date }}</span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6 mb-2">
                                                                <div class="form-group">
                                                                    <div><span class="fw-bold">Transaction Number :</span></div>
                                                                    <span>
                                                                        <span>{{ $data->trans_number }}</span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6 mb-2">
                                                                <div class="form-group">
                                                                    <div><span class="fw-bold">Account Code :</span></div>
                                                                    <span>
                                                                        <span>{{ $data->account_code }}</span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-12 mb-2">
                                                                <div class="form-group">
                                                                    <div><span class="fw-bold">Description :</span></div>
                                                                    <span>
                                                                        <span>{{ $data->description }}</span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6 mb-2">
                                                                <div class="form-group">
                                                                    <div><span class="fw-bold">Type Transaction :</span></div>
                                                                    <span>
                                                                        <span>{{ $data->type_transaction }}</span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6 mb-2">
                                                                <div class="form-group">
                                                                    <div><span class="fw-bold">Amount :</span></div>
                                                                    <span>
                                                                        <span>{{ $data->amount }}</span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6 mb-2">
                                                                <div class="form-group">
                                                                    <div><span class="fw-bold">Amount in IDR :</span></div>
                                                                    <span>
                                                                        <span>{{ $data->amount_in_idr }}</span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <div><span class="fw-bold">Created At :</span></div>
                                                                    <span>
                                                                        <span>{{ $data->created_at }}</span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Modal Update --}}
                                        <div class="modal fade" id="update{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="staticBackdropLabel">Edit Transaction Data Bank</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('transdatabank.update', encrypt($data->id)) }}" id="formedit{{ $data->id }}" method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-body py-8 px-4" style="max-height: 67vh; overflow-y: auto;">
                                                            <div class="row">
                                                                <div class="col-6 mb-2">
                                                                    <label class="form-label">Currencies</label><label style="color: darkred">*</label>
                                                                    <select class="form-select" id="currency{{ $data->id }}" name="currency" required>
                                                                        <option value="" selected>--Select Currencies--</option>
                                                                        @foreach($currencies as $item)
                                                                            <option value="{{ $item->currency_code }}" data-code="{{ $item->currency_code }}" @if($data->currency == $item->currency_code) selected="selected" @endif>{{ $item->currency_code }} - {{ $item->currency }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-6 mb-2">
                                                                    <label class="form-label">Bank</label>
                                                                    <select class="form-select" name="bank" required>
                                                                        <option value="" selected>--Select Bank--</option>
                                                                        @foreach($listbank as $item)
                                                                            <option value="{{ $item->name_value }}" @if($data->bank == $item->name_value) selected="selected" @endif>{{ $item->name_value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6 mb-2">
                                                                    <label class="form-label">Transaction Date</label><label style="color: darkred">*</label>
                                                                    <input class="form-control" name="date" type="date" value="{{ $data->date }}" required>
                                                                </div>
                                                                <div class="col-6 mb-2">
                                                                    <label class="form-label">Account Code</label><label style="color: darkred">*</label>
                                                                    <select class="form-select" name="id_master_account_codes" required>
                                                                        <option value="" selected>--Select Account Code--</option>
                                                                        @foreach($accountcodes as $item)
                                                                            <option value="{{ $item->id }}" @if($data->id_master_account_codes == $item->id) selected="selected" @endif>{{ $item->account_name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-12 mb-2">
                                                                    <label class="form-label">Description</label><label style="color: darkred">*</label>
                                                                    <textarea class="form-control" rows="3" type="text" class="form-control" name="description" placeholder="Input Description" required>{{ $data->description }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6 mb-2">
                                                                    <label class="form-label">Type Transaction</label><label style="color: darkred">*</label>
                                                                    <select class="form-select" id="type_transaction" name="type_transaction" required>
                                                                        <option value="" selected>--Select Type Transaction--</option>
                                                                        @foreach($typetrans as $item)
                                                                            <option value="{{ $item->name_value }}" @if($data->type_transaction == $item->name_value) selected="selected" @endif>{{ $item->name_value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6 mb-2">
                                                                    <label class="form-label">Amount</label><label style="color: darkred">*</label>
                                                                    <div class="input-group">
                                                                        <div class="input-group-text" style="background-color:rgb(211, 211, 211)" id="currency_type{{ $data->id }}">{{ $data->currency }}</div>
                                                                        <input class="form-control" name="amount" type="number" placeholder="Input Amount.." value="{{ $data->amount }}" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary waves-effect btn-label waves-light" id="sb-update{{ $data->id }}"><i class="mdi mdi-update label-icon"></i>Update</button>
                                                        </div>
                                                    </form>
                                                    <script>
                                                        $(document).ready(function() {
                                                            let idList = "{{ $data->id }}";
                                                            $('#currency' + idList).change(function () {
                                                                var selectedOption = $(this).find(':selected');
                                                                var code = selectedOption.data('code');
                                                                document.getElementById('currency_type' + idList).textContent = code;
                                                            });
                                                            $('#formedit' + idList).submit(function(e) {
                                                                if (!$('#formedit' + idList).valid()){
                                                                    e.preventDefault();
                                                                } else {
                                                                    $('#sb-update' + idList).attr("disabled", "disabled");
                                                                    $('#sb-update' + idList).html('<i class="mdi mdi-reload label-icon"></i>Please Wait...');
                                                                }
                                                            });
                                                        });
                                                    </script>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Modal Delete --}}
                                        <div class="modal fade" id="delete{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-top" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="staticBackdropLabel">Delete Transaction Data Bank</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('transdatabank.delete', encrypt($data->id)) }}" id="formdelete{{ $data->id }}" method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <p class="text-center">Are You Sure To Delete This Transaction Data Bank?</p>
                                                                <p class="text-center"><b>{{ $data->trans_number }}</b></p>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-danger waves-effect btn-label waves-light" id="sb-delete{{ $data->id }}"><i class="mdi mdi-delete label-icon"></i>Delete</button>
                                                        </div>
                                                    </form>
                                                    <script>
                                                        $(document).ready(function() {
                                                            let userId = "{{ $data->id }}";
                                                            $('#formdelete' + userId).submit(function(e) {
                                                                if (!$('#formdelete' + userId).valid()){
                                                                    e.preventDefault();
                                                                } else {
                                                                    $('#sb-delete' + userId).attr("disabled", "disabled");
                                                                    $('#sb-delete' + userId).html('<i class="mdi mdi-reload label-icon"></i>Please Wait...');
                                                                }
                                                            });
                                                        });
                                                    </script>
                                                </div>
                                            </div>
                                        </div>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $datas->appends([
                            'bank' => $bank,
                            'date' => $date,
                            'trans_number' => $trans_number,
                            'id_master_account_codes' => $id_master_account_codes,
                            'description' => $description,
                            'type_transaction' => $type_transaction,
                            'searchDate' => $searchDate,
                            'startdate' => $startdate,
                            'enddate' => $enddate])
                            ->links('vendor.pagination.bootstrap-5')
                        }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Export Action --}}
        <script>
            $(document).ready(function () {
                var requestData = {
                    bank: {!! json_encode($bank) !!},
                    date: {!! json_encode($date) !!},
                    trans_number: {!! json_encode($trans_number) !!},
                    id_master_account_codes: {!! json_encode($id_master_account_codes) !!},
                    description: {!! json_encode($description) !!},
                    type_transaction: {!! json_encode($type_transaction) !!},
                    searchDate: {!! json_encode($searchDate) !!},
                    startdate: {!! json_encode($startdate) !!},
                    enddate: {!! json_encode($enddate) !!},
                    flag: 1,
                };

                var currentDate = new Date();
                var formattedDate = currentDate.toISOString().split('T')[0];
                var fileName = "Transaction Data Bank Export - " + formattedDate + ".xlsx";

                exportToExcel("{{ route('transdatabank.index') }}", fileName, requestData);
            });
        </script>
    </div>
</div>

@endsection