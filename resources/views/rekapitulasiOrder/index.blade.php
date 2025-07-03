<style>
    tr.group td {
        background-color: #99BEEA !important;
        font-weight: bold;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
@extends('layouts.master')

@section('konten')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Rekapitulasi Order</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                {{-- <li class="breadcrumb-item"><a href="javascript: void(0);">Marketing</a></li> --}}
                                <li class="breadcrumb-item active">Rekapitulasi Order</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row d-flex justify-content-center mb-4">
                <div class="col-6">
                    <h1>OUTSTANDING PURCHASE ORDER</h1>
                    <canvas id="barChart" width="600" height="400"></canvas>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            {{-- <a href="#" class="btn btn-success waves-effect btn-label waves-light"
                                data-search = "Stock" id="so_stock" onclick="filterSearch(this)">
                                <i class="mdi mdi-file-multiple label-icon"></i> SO Stock
                            </a>
                            <button type="button" class="btn btn-light waves-effect btn-label waves-light"
                                id="modalExportData">
                                <i class="mdi mdi-export label-icon"></i> Export Data
                            </button> --}}
                            <table class="fs-6">
                                <tr>
                                    <td class="pe-2 text-nowrap">TOTAL SINGLE</td>
                                    <td>:</td>
                                    <td class="text-end">{{ $results['total_single']['formatted'] }} KG</td>
                                </tr>
                                <tr>
                                    <td class="pe-2 text-nowrap">TOTAL FOLDING</td>
                                    <td>:</td>
                                    <td class="text-end">{{ $results['total_folding']['formatted'] }} KG</td>
                                </tr>
                                <tr>
                                    <td class="pe-2 text-nowrap">TOTAL BAG MAKING</td>
                                    <td>:</td>
                                    <td class="text-end">{{ $results['total_bag_making']['formatted'] }} KG</td>
                                </tr>
                                <tr>
                                    <td class="pe-2 text-nowrap">TOTAL RAW MATERIAL</td>
                                    <td>:</td>
                                    <td class="text-end">KG</td>
                                </tr>
                                <tr>
                                    <td class="pe-2 text-nowrap">TOTAL MACHINE & AUX</td>
                                    <td>:</td>
                                    <td class="text-end">UNIT/PCS</td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />

                            <div class="table-responsive">
                                <table id="rekapitulasi-order" class="table table-hover table-bordered"
                                    style="font-size: small;">
                                    <thead>
                                        <tr>
                                            <th class="align-middle text-center" data-name="so_number">SO<br>Number</th>
                                            <th class="align-middle text-center" data-name="customer">Customer</th>
                                            <th class="align-middle text-center" data-name="description">Description</th>
                                            <th class="align-middle text-center" data-name="qty_pcs">Qty Pcs</th>
                                            <th class="align-middle text-center" data-name="kd">KG</th>
                                            <th class="align-middle text-center" data-name="unit">Unit</th>
                                            <th class="align-middle text-center" data-name="product">Product</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($sales_order as $item)
                                            <tr>
                                                <td class="align-middle text-center">{{ $item->so_number }}</td>
                                                <td class="align-middle">
                                                    @php
                                                        $perforasi =
                                                            $item->perforasi === 'NULL' || $item->perforasi === null
                                                                ? '-'
                                                                : $item->perforasi;
                                                    @endphp
                                                    {{ $item->product_code . ' - ' . $item->description . ' | Perforasi: ' . $perforasi }}
                                                </td>
                                                <td class="align-middle text-center">{{ $item->customer }}</td>
                                                <td class="align-middle text-end">{{ $item->outstanding_delivery_qty }}
                                                </td>
                                                <td class="align-middle text-end">
                                                    @php
                                                        // Konversi string ke float
                                                        $weightFloat = (float) $item->weight;

                                                        // Kalikan
                                                        $totalWeight = $item->outstanding_delivery_qty * $weightFloat;
                                                    @endphp
                                                    {{ $totalWeight }}
                                                </td>
                                                <td class="align-middle text-center">KG</td>
                                                <td class="align-middle text-center">
                                                    @php
                                                        $product =
                                                            $item->so_category == 'S/W'
                                                                ? 'SINGLE'
                                                                : ($item->so_category == 'CF'
                                                                    ? 'FOLDING'
                                                                    : ($item->so_category == 'Bag'
                                                                        ? 'BAG MAKING'
                                                                        : ''));
                                                    @endphp
                                                    {{ $product }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var groupColumn = 2;
            var table = $('#rekapitulasi-order').DataTable({
                columnDefs: [{
                    visible: false,
                    targets: groupColumn
                }],
                order: [
                    [groupColumn, 'asc']
                ],
                displayLength: 25,
                drawCallback: function(settings) {
                    var api = this.api();
                    var rows = api.rows({
                        page: 'current'
                    }).nodes();
                    var last = null;

                    api.column(groupColumn, {
                            page: 'current'
                        })
                        .data()
                        .each(function(group, i) {
                            if (last !== group) {
                                $(rows)
                                    .eq(i)
                                    .before(
                                        '<tr class="group text-center fs-4"><td colspan="6">' +
                                        group + '</td></tr>'
                                    );
                                last = group;
                            }
                        });
                },
                //
                dom: '<"top d-flex"<"position-absolute top-0 end-0 d-flex search-type"fl>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>><"clear:both">',
                initComplete: function(settings, json) {
                    // Setelah DataTable selesai diinisialisasi
                    // Tambahkan elemen kustom ke dalam DOM
                    $('.top').addClass('mb-5');
                },
                processing: true,
                ordering: false,
                // serverSide: true,
                // scrollX: true,
                language: {
                    lengthMenu: "_MENU_",
                    search: "",
                    searchPlaceholder: "Search",
                },
                pageLength: 20,
                lengthMenu: [
                    [5, 10, 20, 25, 50, 100, 200],
                    [5, 10, 20, 25, 50, 100, 200]
                ],
                aaSorting: [
                    [1, 'desc']
                ], // start to sort data in second column
                bAutoWidth: false,
            });

            const ctx = document.getElementById('barChart').getContext('2d');

            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Single', 'Folding', 'Bag Making'],
                    datasets: [{
                        label: 'Total (raw)',
                        data: [
                            {{ $results['total_single']['raw'] }},
                            {{ $results['total_folding']['raw'] }},
                            {{ $results['total_bag_making']['raw'] }}
                        ],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        datalabels: {
                            anchor: 'end',
                            align: 'end',
                            formatter: function(value, context) {
                                const formatted = [
                                    '{{ $results['total_single']['formatted'] }}',
                                    '{{ $results['total_folding']['formatted'] }}',
                                    '{{ $results['total_bag_making']['formatted'] }}'
                                ];
                                return formatted[context.dataIndex];
                            },
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        });
    </script>
@endpush
