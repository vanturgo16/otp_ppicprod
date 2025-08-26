@extends('layouts.master')

@section('konten')
<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Dashboard</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item active">PPIC</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.alert')

        {{-- === START WAREHOUSE SECTION === --}}
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Warehouse</h5>
            </div>
            <div class="card-body">

                {{-- Sub Bab: Packing List --}}
                <h6 class="mt-2 mb-3">Packing List</h6>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <p class="text-muted mb-2">Request / Un Post</p>
                                <h4 class="text-warning">{{ $packingRequest?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <p class="text-muted mb-2">Posted</p>
                                <h4 class="text-success">{{ $packingPosted ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <p class="text-muted mb-2">Closed</p>
                                <h4 class="text-danger">{{ $packingClosed ?? 0}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <p class="text-muted mb-2">Total</p>
                                <h4>{{ $packingTotal ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sub Bab: Delivery Note --}}
                <h6 class="mt-4 mb-3">Delivery Note</h6>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <p class="text-muted mb-2">Request / Un Post</p>
                                <h4 class="text-warning">{{ $deliveryRequest ?? 0}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <p class="text-muted mb-2">Posted</p>
                                <h4 class="text-success">{{ $deliveryPosted?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <p class="text-muted mb-2">Closed</p>
                                <h4 class="text-danger">{{ $deliveryClosed ?? 0}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <p class="text-muted mb-2">Total</p>
                                <h4>{{ $deliveryTotal ?? 0}}</h4>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        {{-- === END WAREHOUSE SECTION === --}}

    </div>
</div>
@endsection
