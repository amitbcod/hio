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
                    <label class="form-label">Trip Time (min)</label>
                    <input type="number" name="trip_time_minutes" class="form-control" min="0" value="60">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Buffer Time (min)</label>
                    <input type="number" name="buffer_time_minutes" class="form-control" min="0" value="30">
                </div>
                <div class="col-md-1">
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
                            <th>Trip Time</th>
                            <th>Buffer Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pairs as $pair)
                            @php $formId = 'route-pair-form-' . $pair->id; @endphp
                            <tr>
                                <td>{{ $serviceTypes[$pair->service_type] ?? ucfirst(str_replace('_', ' ', $pair->service_type)) }}</td>
                                <td>{{ $pair->route_from }}</td>
                                <td>{{ $pair->route_to }}</td>
                                <td>
                                    <input type="number" name="trip_time_minutes" form="{{ $formId }}" class="form-control form-control-sm" min="0" value="{{ (int) ($pair->trip_time_minutes ?? 0) }}">
                                </td>
                                <td>
                                    <input type="number" name="buffer_time_minutes" form="{{ $formId }}" class="form-control form-control-sm" min="0" value="{{ (int) ($pair->buffer_time_minutes ?? 0) }}">
                                </td>
                                <td>
                                    <select name="is_active" form="{{ $formId }}" class="form-select form-select-sm">
                                        <option value="1" {{ $pair->is_active ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !$pair->is_active ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </td>
                                <td>
                                    <form id="{{ $formId }}" method="POST" action="{{ route('admin.transport-service-route-pairs.update', $pair->id) }}" class="d-flex gap-2 align-items-center">
                                        @csrf
                                        <input type="hidden" name="_method" value="PUT">
                                        <input type="hidden" name="service_type" value="{{ $pair->service_type }}">
                                        <input type="hidden" name="route_from" value="{{ $pair->route_from }}">
                                        <input type="hidden" name="route_to" value="{{ $pair->route_to }}">
                                        <button class="btn btn-sm btn-success" type="submit">Save</button>
                                    </form>
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
