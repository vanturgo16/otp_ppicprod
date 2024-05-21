<form action="/hapus_grn/{{ $data->id }}" method="post"
        class="d-inline">
        @method('delete')
        @csrf
        
        <button type="submit" class="btn btn-sm btn-danger"
        onclick="return confirm('Anda yakin mau menghapus item ini ?')">
            <i class="bx bx-trash-alt" title="Hapus data" ></i>
        </button>
    </form>
    <a href="/print-grn/{{ $data->receipt_number }}" class="btn btn-sm btn-info waves-effect waves-light">
            <i class="bx bx-printer" title="print in English"></i>
    </a>
    
    <a href="/edit-grn/{{ $data->id }}" class="btn btn-sm btn-info waves-effect waves-light">
            <i class="bx bx-edit-alt" title="Edit data"></i>
    </a>
    @if($data->status=='Hold' or $data->status=='Un Posted')
    <form action="/posted_grn/{{ $data->id }}" method="post"
        class="d-inline" data-id="">
        @method('PUT')
        @csrf
        <button type="submit" class="btn btn-sm btn-success"
        onclick="return confirm('Anda yakin mau Posted item ini ?')">
            <i class="bx bx-paper-plane" title="Posted" ></i>
            <!-- <i class="mdi mdi-arrow-left-top-bold" title="Posted" >Un Posted</i> -->
        </button></center>
    </form>
    @elseif($data->status=='Posted')
    <form action="/unposted_grn/{{ $data->id }}" method="post"
        class="d-inline" data-id="">
        @method('PUT')
        @csrf
        <button type="submit" class="btn btn-sm btn-primary"
        onclick="return confirm('Anda yakin mau Un Posted item ini ?')">
            <!-- <i class="bx bx-paper-plane" title="Posted" ></i> -->
            <i class="mdi mdi-arrow-left-top-bold" title="Un Posted" >Un Posted</i>
        </button></center>
    </form>
    @endif
