<div class="btn-group" role="group">
    <button id="btnGroupDrop" type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown"
        aria-expanded="false">
        Action <i class="mdi mdi-chevron-down"></i>
    </button>
    <ul class="dropdown-menu" aria-labelledby="btnGroupDrop">
        <li>
            <button class="dropdown-item drpdwn-{{ $data->status == 'Request' ? 'scs' : 'wrn' }}"
                data-wo-number="{{ $data->wo_number }}" data-status="{{ $data->status }}"
                onclick="showModal(this);"><span
                    class="mdi {{ $data->status == 'Request' ? 'mdi-check-bold' : 'mdi-arrow-left-top-bold' }}"></span>
                |
                {{ $data->status == 'Request' ? 'Posted' : 'Un Posted' }}</button>
        </li>
        @if ($data->status == 'Request')
            <li>
                <button class="dropdown-item drpdwn-dgr" data-wo-number="{{ $data->wo_number }}"
                    data-status="{{ $data->status }}" onclick="showModal(this, 'Delete');"><span
                        class="mdi mdi-trash-can"></span>
                    | Delete</button>
            </li>
            <li>
                <a class="dropdown-item drpdwn-pri"
                    href="{{ route('ppic.workOrder.edit', encrypt($data->wo_number)) }}"><span
                        class="mdi mdi-circle-edit-outline"></span> | Edit
                    Data</a>
            </li>
        @endif
        <li>
            <a class="dropdown-item drpdwn"
                href="{{ route('ppic.workOrder.view', encrypt($data->wo_number)) }}"><span
                    class="mdi mdi-eye"></span> | View Data</a>
        </li>
    </ul>
</div>
