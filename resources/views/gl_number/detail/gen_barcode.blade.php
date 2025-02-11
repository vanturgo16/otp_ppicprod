
<form action="{{ route('grn_gln.generateBarcode', encrypt($data->lot_number)) }}" method="GET">
    <div class="d-flex align-items-center gap-2">
        <input type="number" name="qty" class="form-control" placeholder="Input Jumlah Barcode" max="{{ $data->qty }}" required>
        <button type="submit" class="btn btn-primary">Generate</button>
    </div>
</form>
