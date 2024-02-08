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
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        @if (session('success'))
            <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                <i class="mdi mdi-check-all label-icon"></i><strong>Success</strong> - {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row justify-content-center mt-3">
                            <div class="col-12">
                                <div class="text-center">
                                    <h5>Welcome to the "Configuration Dashboard"</h5>
                                    <p class="text-muted">Here you can manage users to access SSO PT Olefina Tifaplas Polikemindo, & You are able to manage your Master Data on the system</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="background-color: #f0f3f8;">
                <div class="row">
                    <div class="col-6">
                        <span><b>All Menu</b></span>
                    </div>
                    <div class="col-3"></div>
                    <div class="col-3">
                        <div class="app-search" style="margin-bottom: -1.5rem; margin-top: -1.5rem;">
                            <div class="position-relative">
                                <input type="text" class="form-control search-box" placeholder="Search..." style="background-color: #ffff;">
                                <button class="btn btn-primary" type="button"><i class="bx bx-search align-middle"></i></button>
                            </div>
                        </div>
                        {{-- <input type="text" class="form-control search-box" placeholder="Search..."> --}}
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="custom-row d-flex">
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('company.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-office-building"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Company</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('department.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-graph-outline"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Department</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('employee.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-account-group"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Employee</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('salesman.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-account-tie"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Salesman</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('province.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-map-marker"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Province</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('country.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-wan"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Country</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('costcenter.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-cash-multiple"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Cost Center</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('customer.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-account-switch"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Customer</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('supplier.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-inbox-arrow-down"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Supplier</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('processproduction.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-cogs"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Process Production</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('group.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-google-circles-group"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Group</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('groupsub.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-lan"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Group Sub</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('unit.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-camera-control"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Unit</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('waste.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-recycle"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Waste</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('downtime.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-package-down"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Downtime</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('warehouse.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-warehouse"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Warehose</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('vehicle.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-rv-truck"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Vehicle</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('rawmaterial.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-apps-box"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Raw Material</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('wip.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-cog-box"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">WIP</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('fg.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-check-network"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Product FG</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('sparepart.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-archive"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Sparepart & Aux.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('termpayment.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-file-alert"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Term Payment</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('currency.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-credit-card-marker"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Currency</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('reason.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-file-question"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Reason</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" custom-col mb-2 px-2 py-2">
                        <a href="{{ route('approval.index') }}" class="card-link">
                            <div class="custom-card px-2">
                                <div class="container-icon">
                                    <i class="mdi mdi-check-decagram"></i>
                                </div>
                                <div class="container-text">
                                    <p class="card-text">Approval</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const searchBox = document.querySelector(".search-box");
                    
                    searchBox.addEventListener("keyup", function(event) {
                        const value = event.target.value.toLowerCase();
                        const customCols = document.querySelectorAll(".custom-row .custom-col");
                        
                        customCols.forEach(function(col) {
                            const cardText = col.querySelector('.card-text').textContent.toLowerCase();
                            if (cardText.indexOf(value) > -1) {
                                col.style.display = ""; // Show if matches search
                            } else {
                                col.style.display = "none"; // Hide if it doesn't match search
                            }
                        });
                    });
                });
            </script>
        </div>
    </div>
</div>

@endsection