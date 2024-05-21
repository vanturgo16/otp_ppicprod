<a href="/good-lote-number-detail/{{ $data->id }}" class="btn btn-sm btn-primary waves-effect waves-light"><i class=" bx bx-show-alt" ></i></a>
@if($data->qc_passed != 'Y')
    <form action="/qc_passed/{{ $data->id }}" method="post"
        class="d-inline" data-id="">
        @method('PUT')
        @csrf
        <button type="submit" class="btn btn-sm btn-success"
        onclick="return confirm('Anda yakin mau QC Passed item ini ?')">
            <!-- <i class="bx bx-paper-plane" title="Posted" ></i> -->
            <i class="bx bx-select-multiple" title="QC Passed" > QC Passed</i>
        </button></center>
    </form>
@elseif($data->qc_passed == 'Y')
    <form action="/un_qc_passed/{{ $data->id }}" method="post"
        class="d-inline" data-id="">
        @method('PUT')
        @csrf
        <button type="submit" class="btn btn-sm btn-warning"
        onclick="return confirm('Anda yakin mau QC Un Passed item ini ?')">
            <!-- <i class="bx bx-paper-plane" title="Posted" ></i> -->
            <i class="bx bx-x" title="QC Un Passed" > QC Un Passed</i>
        </button></center>
    </form>
@endif  