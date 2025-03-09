<button class="btn btn-sm btn-info my-half" data-bs-toggle="modal" data-bs-target="#edit{{ $data->id }}" title="Edit Data">
    <i class="mdi mdi-pen"></i>
</button>
<button class="btn btn-sm btn-danger my-half" data-bs-toggle="modal" data-bs-target="#delete{{ $data->id }}" title="Delete Data">
    <i class="mdi mdi-trash-can"></i>
</button>

{{-- Modal Edit --}}
<div class="modal fade" id="edit{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-top" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Edit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('grn_gln.detail.update', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-5 col-form-label">Lot Number</label>
                        <div class="col-sm-7">
                            <input type="text" name="lot_number" class="form-control custom-bg-gray" value="{{ $data->lot_number }}" readonly required>
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper">
                        <label class="col-sm-5 col-form-label">Ext. Lot Number</label>
                        <div class="col-sm-7">
                            <input type="text" name="ext_lot_number" class="form-control" placeholder="Input Ext. Lot Number..(Optional)" value="{{ $data->ext_lot_number }}">
                        </div>
                    </div>
                    <div class="row mb-4 field-wrapper required-field">
                        <label class="col-sm-5 col-form-label">Qty</label>
                        <div class="col-sm-7">
                            <input type="text" name="qty" class="form-control number-format"
                            value="{{ $data->qty 
                                ? (strpos(strval($data->qty ), '.') !== false 
                                    ? rtrim(rtrim(number_format($data->qty , 6, ',', '.'), '0'), ',') 
                                    : number_format($data->qty , 0, ',', '.')) 
                                : '0' }}" 
                            placeholder="Input Qty.." required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary waves-effect btn-label waves-light">
                        <i class="mdi mdi-update label-icon"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Delete --}}
<div class="modal fade" id="delete{{ $data->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-top" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('grn_gln.detail.delete', encrypt($data->id)) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="text-center">
                        Apakah Anda Yakin Untuk <b>Menghapus</b> Data?
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger waves-effect btn-label waves-light">
                        <i class="mdi mdi-delete-alert label-icon"></i>Delete
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

<script>
    function formatNumberInput(event) {
        let input = event.target;
        let value = input.value.replace(/[^0-9,.]/g, "");
        value = value.replace(/\./g, "");
        let parts = value.split(",");
        let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        if (parts.length > 1) {
            let decimalPart = parts[1].substring(0, 6); // Limit to 6 decimal places
            input.value = integerPart + "," + decimalPart;
        } else {
            input.value = integerPart;
        }
    }
    document.querySelectorAll(".number-format").forEach(input => {
        input.addEventListener("input", formatNumberInput);
    });
</script>