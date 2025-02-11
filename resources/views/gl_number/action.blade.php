<a href="#" type="button" class="btn btn-sm btn-info waves-effect btn-label waves-light my-half">
    <i class="mdi mdi-eye label-icon"></i>Detail
</a>
<button class="btn btn-sm btn-success waves-effect btn-label waves-light my-half" data-bs-toggle="modal" data-bs-target="#genLot{{ $data->id }}">
    <i class="mdi mdi-pen label-icon"></i>Input Lot
</button>

{{-- Modal --}}
<div class="modal fade" id="genLot{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-top" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Input Lot Number</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('grn_gln.update', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="decision" value="reset">
                <div class="modal-body p-4">
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-4 col-form-label">Lot Number</label>
                        <div class="col-sm-8">
                            <input type="text" name="lot_number" class="form-control custom-bg-gray" value="{{ isset($data->lot_number) ? $data->lot_number : $genLotNumber }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-4 col-form-label">Qty</label>
                        <div class="col-sm-8">
                            <input type="text" name="qty" class="form-control" value="" placeholder="Input Qty.." required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success waves-effect btn-label waves-light">
                        <i class="mdi mdi-check label-icon"></i>Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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