@extends('layouts.app')

@section('title', 'Edit Business Role')

@section('content')
<div class="container-fluid mt-0">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @include('operator.registration._sidebar_main')
        </div>
        <div class="col-md-9 my-pro">
            <div class="container-middle">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div>
                        <h2 class="fw-bold mb-2">Edit Business Role</h2>
                        <p class="text-muted mb-0">Role details and assignments.</p>
                    </div>
                </div>
    <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
        <div class="card-body">
            <h4>Edit Role: <strong>{{ $role->name }}</strong></h4>

            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

            <form method="POST" action="{{ route('operator.roles.update', $role->id) }}">
                @csrf
                <div class="form-group mb-3">
                    <label>Role Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
                </div>
                <div>
                    <button class="btn btn-success">Save</button>
                    <a href="{{ route('operator.roles.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
            </div>
        </div>
    </div>
</div>
@endsection