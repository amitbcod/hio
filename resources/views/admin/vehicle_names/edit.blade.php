@extends('layouts.admin')

@section('content')
<div class="col-md-8 offset-md-2">
    <h3 class="mt-4">Edit Vehicle Name</h3>
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" action="{{ route('admin.vehicle-names.update', $vehicleName) }}">
        @csrf
        @method('PUT')
        <div class="mb-3"><label class="form-label">Vehicle Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $vehicleName->name) }}" required></div>
        <div class="mb-3"><label class="form-label">Vehicle Type</label><select name="transport_vehicle_type_id" class="form-control" required>@foreach($vehicleTypes as $vehicleType)<option value="{{ $vehicleType->id }}" @selected(old('transport_vehicle_type_id', $vehicleName->transport_vehicle_type_id) == $vehicleType->id)>{{ $vehicleType->name }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label">Seats</label><input type="number" name="seat_capacity" class="form-control" value="{{ old('seat_capacity', $vehicleName->seat_capacity) }}" min="1" max="200" required></div>
        <div class="mb-3 form-check"><input type="checkbox" name="is_active" class="form-check-input" id="is_active" @checked(old('is_active', $vehicleName->is_active))><label class="form-check-label" for="is_active">Active</label></div>
        <button type="submit" class="btn btn-primary">Update Vehicle Name</button>
        <a href="{{ route('admin.vehicle-names.index') }}" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
@endsection