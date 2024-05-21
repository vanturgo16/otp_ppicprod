@if($data->lot_number == '')
    <button type="button" class="btn btn-success btn-sm waves-effect waves-light"
    data-bs-toggle="modal" onclick="lot_number('{{ $data->id }}');"
    data-bs-target="#myModal"><i class="bx bx-edit-alt" title="Input Lot"></i> Input Lot</button>
    <!-- Include modal content -->
@elseif($data->lot_number != '')
<button type="button" class="btn btn-success btn-sm waves-effect waves-light"
    data-bs-toggle="modal" onclick="lot_number_edit('{{ $data->id }}','{{ $data->lot_number }}');"
    data-bs-target="#input_lot_edit"><i class="bx bx-edit-alt" title="Input Lot"></i> Input Lot</button>
@endif


