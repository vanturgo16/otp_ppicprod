@if($data->ext_lot_number!='')   
                                               
<a href="/generateBarcode/{{ $data->lot_number }}" class="btn btn-sm btn-info"><i class=" bx bx-barcode" >Print Barcode</i></a>
<a href="/detail-external-no-lot/{{ $data->lot_number }}" class="btn btn-sm btn-primary"><i class=" bx bx-file" >Detail</i></a>
@else
<button type="submit" class="btn btn-sm btn-danger" >
    <i class="bx bx-info-circle" > Please Generate Barcode</i>
</button>
@endif
