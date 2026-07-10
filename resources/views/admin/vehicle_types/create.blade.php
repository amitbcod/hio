@extends('layouts.admin')

@section('content')
<div class="col-md-8 offset-md-2">
    <h3 class="mt-4">Add Vehicle Type</h3>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.vehicle-types.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Type Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Seat Capacity</label>
            <input type="number" name="seat_capacity" class="form-control" value="{{ old('seat_capacity') }}" min="1">
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
        <button type="submit" class="btn btn-primary">Save Vehicle Type</button>
        <a href="{{ route('admin.vehicle-types.index') }}" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
@endsection
