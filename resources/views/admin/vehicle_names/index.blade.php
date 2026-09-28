@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h3>Vehicle Names</h3>
        <a href="{{ route('admin.vehicle-names.create') }}" class="btn btn-primary">Add Vehicle Name</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead><tr><th>Name</th><th>Vehicle Type</th><th>Seats</th><th>Active</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($vehicleNames as $vehicleName)
                    <tr>
                        <td>{{ $vehicleName->name }}</td>
                        <td>{{ $vehicleName->vehicleType?->name ?? '-' }}</td>
                        <td>{{ $vehicleName->seat_capacity }}</td>
                        <td>{{ $vehicleName->is_active ? 'Yes' : 'No' }}</td>
                        <td>
                            <a href="{{ route('admin.vehicle-names.edit', $vehicleName) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.vehicle-names.destroy', $vehicleName) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this vehicle name?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No vehicle names available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $vehicleNames->links() }}
</div>
@endsection