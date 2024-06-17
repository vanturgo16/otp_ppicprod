<div class="btn-group" role="group" aria-label="Action Buttons">
    <a href="/packing-list/{{ $data->id }}" class="btn btn-sm btn-primary waves-effect waves-light">
        <i class="bx bx-show-alt"></i>
    </a>

    @if($data->status == 'Request')
    <form action="/post/{{ $data->id }}" method="post" class="d-inline" data-id="">
        @method('PUT')
        @csrf
        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Anda yakin mau Post item ini ?')">
            <i class="bx bx-check-circle" title="Posted"> Posted</i>
        </button>
    </form>
    @elseif($data->status == 'Posted')
    <form action="/unpost/{{ $data->id }}" method="post" class="d-inline" data-id="">
        @method('PUT')
        @csrf
        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Anda yakin mau Un Post item ini ?')">
            <i class="bx bx-undo" title="Un Posted"> Un Posted</i>
        </button>
    </form>
    @endif

    <a href="/print/{{ $data->id }}" class="btn btn-sm btn-secondary">
        <i class="bx bx-printer"></i> Print
    </a>
    <a href="/packing-list/{{ $data->id }}/edit" class="btn btn-sm btn-warning">
        <i class="bx bx-edit"></i> Edit
    </a>
    <form action="/delete/{{ $data->id }}" method="post" class="d-inline" data-id="">
        @method('DELETE')
        @csrf
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Anda yakin mau menghapus item ini ?')">
            <i class="bx bx-trash"></i> Delete
        </button>
    </form>
</div>