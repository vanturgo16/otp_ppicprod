@if($data->lot_number)
-
@else
    @if($data->qc_passed)
        <button class="btn btn-sm btn-secondary waves-effect btn-label waves-light" data-bs-toggle="modal" data-bs-target="#reset{{ $data->id }}">
            <i class="mdi mdi-update label-icon"></i>Reset
        </button>

        {{-- Modal --}}
        <div class="modal fade" id="reset{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-top" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Reset Decision QC</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('grn_qc.update', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="decision" value="reset">
                        <div class="modal-body p-4">
                            <div class="text-center">
                                Apakah Anda Yakin Untuk <b>Reset Decision QC</b>?
                                <br>
                                <br><b>"{{ $data->receipt_number }}"</b>
                                <br>{{ $data->product_desc }}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-secondary waves-effect btn-label waves-light">
                                <i class="mdi mdi-update label-icon"></i>Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        <button class="btn btn-sm btn-success my-half" data-bs-toggle="modal" data-bs-target="#passed{{ $data->id }}">
            <i class="mdi mdi-check"></i>
        </button>
        <button class="btn btn-sm btn-danger my-half" data-bs-toggle="modal" data-bs-target="#notpassed{{ $data->id }}">
            <i class="mdi mdi-close-box"></i>
        </button>

        {{-- Modal --}}
        <div class="modal fade" id="passed{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-top" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">QC Passed</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('grn_qc.update', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="decision" value="Y">
                        <div class="modal-body p-4">
                            <div class="text-center">
                                Apakah Anda Yakin Untuk <b>QC Passed</b> Data?
                                <br>
                                <br><b>"{{ $data->receipt_number }}"</b>
                                <br>{{ $data->product_desc }}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success waves-effect btn-label waves-light">
                                <i class="mdi mdi-check label-icon"></i>QC Passed
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="notpassed{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-top" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">QC Not Passed</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('grn_qc.update', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="decision" value="N">
                        <div class="modal-body p-4">
                            <div class="text-center">
                                Apakah Anda Yakin Untuk <b>QC Not Passed</b> Data?
                                <br>
                                <br><b>"{{ $data->receipt_number }}"</b>
                                <br>{{ $data->product_desc }}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger waves-effect btn-label waves-light">
                                <i class="mdi mdi-close-box label-icon"></i>QC Not Passed
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endif

<script>
    $(document).on('click', '[data-bs-toggle="modal"]', function(e) {
        e.preventDefault();
        let targetModal = $(this).attr('data-bs-target'); 
        // Ensure modal is moved outside the sticky column before opening
        $(targetModal).appendTo('body').modal('show');
    });
    $(document).on('submit', 'form', function () {
        let btn = $(this).find('button[type="submit"]');
        if (!$(this).valid()) return false;
        btn.prop("disabled", true).html('<i class="mdi mdi-loading mdi-spin label-icon"></i> Please Wait...');
        return true;
    });
</script>