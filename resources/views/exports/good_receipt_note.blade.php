@php
    $rowCounts = [];
    $rowIndex = 1; // Start numbering from 1
    foreach ($datas as $data) {
        $rowCounts[$data->id] = isset($rowCounts[$data->id]) ? $rowCounts[$data->id] + 1 : 1;
    }
    $printedIds = [];
@endphp

<table>
    <thead>
        <!-- Export Details -->
        <tr>
            <th colspan="10"><strong>Export Details</strong></th>
        </tr>
        <tr>
            <td colspan="2">Type Item</td>
            <td colspan="8">: {{ $typeItem }}</td>
        </tr>
        <tr>
            <td colspan="2">Status GRN</td>
            <td colspan="8">: {{ $status }}</td>
        </tr>
        <tr>
            <td colspan="2">Receipt Date</td>
            <td colspan="8">: {{ $dateFrom }} - {{ $dateTo }}</td>
        </tr>
        <tr>
            <td colspan="2">Exported By</td>
            <td colspan="8">: {{ $exportedBy }} at {{ $exportedAt }}</td>
        </tr>
        <tr><td colspan="10"></td></tr>

        <!-- Column Headers -->
        <tr>
            <th>No</th>
            <th>Receipt Number</th>
            <th>Receipt Date</th>
            <th>PO Number</th>
            <th>Request Number</th>
            <th>Supplier Name</th>
            <th>QC Check</th>
            <th>External Doc Number</th>
            <th>Non Invoiceable</th>
            <th>Type</th>
            <th>Status GRN</th>
            <th>Created GRN</th>
            <th>Updated GRN</th>
            <th>Product Description</th>
            <th>Qty</th>
            <th>Receipt Qty</th>
            <th>Outstanding Qty</th>
            <th>Unit</th>
            <th>QC Passed</th>
            <th>Lot Number</th>
            <th>Ext. No Lot</th>
            <th>Status Item</th>
            <th>Created Item</th>
            <th>Updated Item</th>
        </tr>
    </thead>
    <tbody>
        @foreach($datas as $data)
            <tr>
                @if (!isset($printedIds[$data->id]))
                    @php
                        $rowspan = $rowCounts[$data->id] ?? 1;
                        $printedIds[$data->id] = true;
                    @endphp
                    <td rowspan="{{ $rowspan }}">{{ $rowIndex++ }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->receipt_number ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->date ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->po_number ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->request_number ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->supplier_name ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->qc_status ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->external_doc_number ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->non_invoiceable ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->type ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->statusGRN ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->createdGRN ?? '-' }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $data->updatedGRN ?? '-' }}</td>
                @endif

                <!-- Columns without merging -->
                <td>{{ $data->product_desc ?? '-' }}</td>
                <td>
                    {{ $data->qty 
                        ? (strpos(strval($data->qty), '.') !== false 
                            ? rtrim(rtrim(number_format($data->qty, 6, ',', '.'), '0'), ',') 
                            : number_format($data->qty, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>
                    {{ $data->receipt_qty 
                        ? (strpos(strval($data->receipt_qty), '.') !== false 
                            ? rtrim(rtrim(number_format($data->receipt_qty, 6, ',', '.'), '0'), ',') 
                            : number_format($data->receipt_qty, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>
                    {{ $data->outstanding_qty 
                        ? (strpos(strval($data->outstanding_qty), '.') !== false 
                            ? rtrim(rtrim(number_format($data->outstanding_qty, 6, ',', '.'), '0'), ',') 
                            : number_format($data->outstanding_qty, 0, ',', '.')) 
                        : '0' }}
                </td>
                <td>{{ $data->unit ?? '-' }}</td>
                <td>{{ $data->qc_passed ?? '-' }}</td>
                <td>{{ $data->lot_number ?? '-' }}</td>
                <td>{{ $data->external_no_lot ?? '-' }}</td>
                <td>{{ $data->statusItem ?? '-' }}</td>
                <td>{{ $data->createdItem ?? '-' }}</td>
                <td>{{ $data->updatedItem ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
