@extends('layouts.admin')

@section('title', 'Transport Listings')

@section('content')
@php
    $sortLink = static function (string $column) use ($indexRoute, $filters): string {
        $direction = $filters['sort'] === $column && $filters['direction'] === 'asc' ? 'desc' : 'asc';

        return route($indexRoute, array_merge(request()->query(), ['sort' => $column, 'direction' => $direction]));
    };
    $operatorName = static fn ($operator): string => $operator?->business_legal_name
        ?: $operator?->business?->legal_name
        ?: $operator?->profile?->business_legal_name
        ?: $operator?->full_name
        ?: $operator?->email
        ?: 'Unknown operator';
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Transport Listings</h1>
            <p class="text-muted mb-0">{{ $isMpo ? 'Listings owned by operators registered in your assigned country.' : 'Listings from operators across all countries.' }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route($indexRoute) }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-sm-6 col-lg-2">
                <label for="filter-vehicle-name" class="form-label">Vehicle Name</label>
                <input id="filter-vehicle-name" class="form-control" type="search" name="vehicle_name" value="{{ $filters['vehicle_name'] }}">
            </div>
            <div class="col-sm-6 col-lg-2">
                <label for="filter-vehicle-type" class="form-label">Vehicle Type</label>
                <input id="filter-vehicle-type" class="form-control" type="search" name="vehicle_type" value="{{ $filters['vehicle_type'] }}">
            </div>
            <div class="col-sm-6 col-lg-2">
                <label for="filter-operator" class="form-label">Operator / Business</label>
                <input id="filter-operator" class="form-control" type="search" name="operator" value="{{ $filters['operator'] }}">
            </div>
            @unless($isMpo)
                <div class="col-sm-6 col-lg-2">
                    <label for="filter-country" class="form-label">Country</label>
                    <select id="filter-country" class="form-select" name="country_id">
                        <option value="">All countries</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" @selected((string) $filters['country_id'] === (string) $country->id)>{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless
            <div class="col-sm-6 col-lg-2">
                <label for="filter-status" class="form-label">Status</label>
                <select id="filter-status" class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label for="filter-created-date" class="form-label">Created</label>
                <input id="filter-created-date" class="form-control" type="date" name="created_date" value="{{ $filters['created_date'] }}">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route($indexRoute) }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th><a href="{{ $sortLink('vehicle_name') }}">Vehicle Name</a></th>
                        <th><a href="{{ $sortLink('vehicle_type') }}">Vehicle Type</a></th>
                        <th><a href="{{ $sortLink('operator') }}">Operator / Business</a></th>
                        @unless($isMpo)<th>Country</th>@endunless
                        <th>Total Qty</th>
                        <th>Available Qty</th>
                        <th><a href="{{ $sortLink('status') }}">Status</a></th>
                        <th><a href="{{ $sortLink('created_at') }}">Created</a></th>
                        <th><a href="{{ $sortLink('updated_at') }}">Updated</a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transports as $transport)
                        <tr>
                            <td>{{ $transport->vehicle_name ?: $transport->vehicleName?->name ?: '—' }}</td>
                            <td>{{ $transport->vehicle_type ?: $transport->vehicleName?->vehicleType?->name ?: '—' }}</td>
                            <td>{{ $operatorName($transport->operator) }}</td>
                            @unless($isMpo)<td>{{ $transport->operator?->country?->name ?? '—' }}</td>@endunless
                            <td>{{ $transport->total_vehicle_qty }}</td>
                            <td>{{ $transport->available_vehicle_qty }}</td>
                            <td>{{ $transport->status ?? '—' }}</td>
                            <td>{{ $transport->created_at?->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $transport->updated_at?->format('Y-m-d') ?? '—' }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route($showRoute, $transport) }}" class="btn btn-sm btn-outline-primary">View</a>
                                @if(! $isMpo || $canEdit)
                                    <a href="{{ route($isMpo ? 'mpo.transport.edit' : 'admin.transport.edit', $transport) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $isMpo ? 9 : 10 }}" class="text-center py-4">No transport listings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $transports->links() }}</div>
    </div>
</div>
@endsection
