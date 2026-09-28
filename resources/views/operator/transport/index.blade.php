@extends('layouts.app')

@section('title', 'Transport Management | Operator Dashboard')

@section('content')
<div class="container mt-0">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @include('operator.registration._sidebar_main')
        </div>
        <div class="col-md-9 my-pro">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;margin-top:0px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h2 style="font-weight:700;margin:0;">Transport Management</h2>
                        <p style="margin:6px 0 0 0;color:#666;">Manage your transport services.</p>
                    </div>
                    <div>
                        <a href="{{ route('operator.transport.create') }}" class="btn" style="background:#19b5b5;color:#fff;padding:8px 14px;border-radius:6px;border:none;">+ Add Vehicle</a>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:12px;margin-bottom:12px;color:#2e7d32;">{{ session('success') }}</div>
            @endif

            <form method="GET" action="{{ route('operator.transport.index') }}" class="row g-2 mb-3 align-items-end">
                <div class="col-sm-6 col-lg-3">
                    <label for="filter-vehicle-name" class="form-label">Vehicle Name</label>
                    <input type="search" id="filter-vehicle-name" name="vehicle_name" value="{{ $filters['vehicle_name'] }}" class="form-control" placeholder="Vehicle name" list="vehicle-name-suggestions" autocomplete="off">
                    <datalist id="vehicle-name-suggestions">
                        @foreach ($vehicleNameSuggestions as $suggestion)
                            <option value="{{ $suggestion }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="filter-vehicle-type" class="form-label">Vehicle Type</label>
                    <input type="search" id="filter-vehicle-type" name="vehicle_type" value="{{ $filters['vehicle_type'] }}" class="form-control" placeholder="Vehicle type" list="vehicle-type-suggestions" autocomplete="off">
                    <datalist id="vehicle-type-suggestions">
                        @foreach ($vehicleTypeSuggestions as $suggestion)
                            <option value="{{ $suggestion }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label for="filter-status" class="form-label">Status</label>
                    <select id="filter-status" name="status" class="form-control">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label for="filter-created-date" class="form-label">Created</label>
                    <input type="date" id="filter-created-date" name="created_date" value="{{ $filters['created_date'] }}" class="form-control">
                </div>
                <div class="col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    @if ($hasFilters)
                        <a href="{{ route('operator.transport.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </form>

            @if ($transports->count() > 0)
                <div style="background:#fff;border-radius:12px;padding:12px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Vehicle Name</th>
                                    <th>Vehicle Type</th>
                                    <th>Total Qty</th>
                                    <th>Available Qty</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transports as $transport)
                                    <tr>
                                        <td>{{ $transport->vehicle_display_name }}</td>
                                        <td>{{ $transport->vehicle_type ?: '—' }}</td>
                                        <td>{{ $transport->total_vehicle_qty }}</td>
                                        <td>{{ $transport->available_vehicle_qty }}</td>
                                        <td>
                                            {{ ucfirst($transport->status ?? 'draft') }}
                                            @if($transport->has_expired_vehicle)
                                                <span class="badge badge-warning" style="color:#d32f2f;background:#ffebee;" title="At least one physical vehicle has an expired license or insurance policy">Vehicle policy expired</span>
                                                @if($transport->expired_vehicle_license_numbers->isNotEmpty())
                                                    <span style="color:#d32f2f;font-size:12px;display:block;">License: {{ $transport->expired_vehicle_license_numbers->join(', ') }}</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>{{ optional($transport->created_at)->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('operator.transport.show', $transport->id) }}" class="btn btn-sm btn-info">View</a>
                                            <a href="{{ route('operator.transport.edit', $transport->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $transports->links() }}</div>
                </div>
            @else
                <div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
                    <div class="alert" style="background:transparent;color:#666;margin:0;">
                        @if ($hasFilters)
                            No transport records match your search. <a href="{{ route('operator.transport.index') }}">Clear search</a>.
                        @else
                            No transport records found. <a href="{{ route('operator.transport.create') }}">Create your first vehicle</a>.
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
