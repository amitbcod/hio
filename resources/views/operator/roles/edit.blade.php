@extends('layouts.app')

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card mt-5">
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
@endsection