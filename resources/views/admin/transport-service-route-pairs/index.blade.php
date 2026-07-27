@extends('layouts.admin')

@section('title', 'Transport Service Route Pairs')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Transport Service Route Pairs</h1>
            <p class="text-muted mb-0">Manage which region routes are available for each transport service type.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.transport-service-route-pairs.store') }}" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Service Type</label>
                    <select name="service_type" class="form-select" required>
                        @foreach($serviceTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Region</label>
                    <select name="route_from" class="form-select" required>
                        <option value="">Select</option>
                        @foreach($regions as $region)
                            <option value="{{ $region }}">{{ $region }}</option>
                        @endforeach
                        <option value="Airport">Airport</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Region</label>
                    <select name="route_to" class="form-select" required>
                        <option value="">Select</option>
                        @foreach($regions as $region)
                            <option value="{{ $region }}">{{ $region }}</option>
                        @endforeach
                        <option value="Airport">Airport</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Active</label>
                    <select name="is_active" class="form-select">
                        <option value="1" selected>Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($pairs->count())
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Service Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pairs as $pair)
                            <tr>
                                <td>{{ $serviceTypes[$pair->service_type] ?? ucfirst(str_replace('_', ' ', $pair->service_type)) }}</td>
                                <td>{{ $pair->route_from }}</td>
                                <td>{{ $pair->route_to }}</td>
                                <td>{{ $pair->is_active ? 'Active' : 'Inactive' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.transport-service-route-pairs.destroy', $pair->id) }}" style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted mb-0">No route pairs configured yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
