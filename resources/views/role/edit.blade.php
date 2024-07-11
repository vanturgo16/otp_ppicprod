@extends('layouts.master')

@section('konten')

<div class="page-content">
    <div class="container-fluid">
    @if (session('pesan'))
        <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-check-all label-icon"></i><strong>Success</strong> - {{ session('pesan') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
     @endif

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> Create Permissions</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">PPIC</a></li>
                            <li class="breadcrumb-item active"> Create Permissions</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">

                            <div>
                                <a href="{{url('/role/create') }}" class="btn btn-primary waves-effect waves-light">Back to Role</a>
                                
                                <!-- Include modal content -->
                               
                            </div>
                        </div>
                    </div>

            <div class="card-body">
                <div class="card-body">
                    <form action="/role/update/{{ $role->id }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('patch')
                        <div class="form-group">
                            <label>NAMA ROLE</label>
                            <input type="text" name="name" value="{{ old('name', $role->name) }}" placeholder="Masukkan Nama Role"
                                class="form-control @error('title') is-invalid @enderror">

                            @error('name')
                            <div class="invalid-feedback" style="display: block">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        @php
                    $regularPermissions = $permissions->filter(function($permission) {
                        return !Str::contains($permission->name, '_ujian');
                    });
                    $halfIndex = ceil($regularPermissions->count() / 2);
                    $firstHalf = $regularPermissions->slice(0, $halfIndex);
                    $secondHalf = $regularPermissions->slice($halfIndex);
                @endphp

                <div class="row">
                    <div class="col-12">
                        <br>
                        <h4>Regular Permissions</h4>
                        <div class="row">
                            @foreach ($firstHalf as $permission)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                            name="permissions[]" 
                                            value="{{ $permission->name }}" 
                                            id="check-{{ $permission->id }}"
                                            @if ($role->permissions->contains($permission)) checked @endif>
                                        <label class="form-check-label" for="check-{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            @foreach ($secondHalf as $permission)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                            name="permissions[]" 
                                            value="{{ $permission->name }}" 
                                            id="check-{{ $permission->id }}"
                                            @if ($role->permissions->contains($permission)) checked @endif>
                                        <label class="form-check-label" for="check-{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- <div class="col-12 mt-3">
                        <h4>Ujian Permissions</h4>
                        @foreach ($permissions as $permission)
                            @if (Str::contains($permission->name, '_ujian'))
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                            name="permissions[]" 
                                            value="{{ $permission->name }}" 
                                            id="check-{{ $permission->id }}"
                                            @if ($role->permissions->contains($permission)) checked @endif>
                                        <label class="form-check-label" for="check-{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div> --}}
                </div>

                        
<br>
                        <button class="btn btn-primary mr-1 btn-submit" type="submit"><i class="fa fa-paper-plane"></i>
                            UPDATE</button>
                        <button class="btn btn-warning btn-reset" type="reset"><i class="fa fa-redo"></i> RESET</button>

                    </form>

            </div>
        </div>
    </div> <!-- end col -->
</div> <!-- end row -->

@endsection
