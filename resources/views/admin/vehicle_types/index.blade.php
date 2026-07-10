@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h3>Vehicle Types</h3>
        <a href="{{ route('admin.vehicle-types.create') }}" class="btn btn-primary">Add Vehicle Type</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Seats</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicleTypes as $vehicleType)
                    <tr>
                        <td>{{ $vehicleType->name }}</td>
                        <td>{{ $vehicleType->seat_capacity ?? '-' }}</td>
                        <td>{{ $vehicleType->is_active ? 'Yes' : 'No' }}</td>
                        <td>
                            <a href="{{ route('admin.vehicle-types.edit', $vehicleType->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.vehicle-types.destroy', $vehicleType->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this vehicle type?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No vehicle types available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
