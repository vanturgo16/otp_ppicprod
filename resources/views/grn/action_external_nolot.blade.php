@if($data->ext_lot_number!='')   
       
<form action="{{ route('generateBarcode', ['lot_number' => $data->lot_number]) }}" method="GET">
    <input type="number" class="form-control" name="qty" placeholder="Jumlah Barcode" required>
    <button type="submit" class="btn btn-info">Generate Barcode</button>
</form>
{{-- <a href="/generateBarcode/{{ $data->lot_number }}" class="btn btn-sm btn-info"><i class=" bx bx-barcode" >Print Barcode</i></a> --}}
<a href="/detail-external-no-lot/{{ $data->lot_number }}" class="btn btn-sm btn-primary"><i class=" bx bx-file" >Detail</i></a>
@else
<button type="submit" class="btn btn-sm btn-danger" >
    <i class="bx bx-info-circle" > Please Generate Barcode</i>
</button>
@endif
