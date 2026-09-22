@extends('layouts.app')

@section('title', 'Transport Details | Operator Dashboard')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>{{ $transport->vehicle_name }}</h1>
            <p class="text-muted">Service ID: {{ $transport->service_id }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('operator.transport.edit', $transport->id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('operator.transport.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Vehicle Type:</strong> {{ $transport->vehicle_type }}</p>
            <p><strong>Vehicle Quantity:</strong> {{ $transport->vehicles->count() }}</p>
            <p><strong>Seating Capacity:</strong> {{ $transport->seating_capacity }}</p>
            <p><strong>Registration Number:</strong> {{ $transport->registration_number ?? 'N/A' }}</p>
            <p><strong>Status:</strong> {{ ucfirst($transport->status ?? 'draft') }}</p>
            <hr>
            <h5>Physical Vehicles</h5>
            @foreach($transport->vehicles as $vehicle)
                <div style="margin-bottom:14px;">
                    <p style="margin-bottom:4px;">{{ $loop->iteration }}. {{ $vehicle->license_number }} / {{ $vehicle->registration_number }} - {{ $vehicle->status }}</p>
                    <small>License / Permit expiry: {{ optional($vehicle->license_expiry_date)->format('M d, Y') ?: 'N/A' }}</small><br>
                    <small>Insurance expiry: {{ optional($vehicle->insurance_expiry_date)->format('M d, Y') ?: 'N/A' }}</small><br>
                    <small>Insurance provider: {{ $vehicle->insurance_provider ?: 'N/A' }}</small><br>
                    <small>Supporting documents: {{ count($vehicle->documents ?? []) }}</small>
                </div>
            @endforeach
            <p><strong>Description:</strong><br>{{ $transport->service_description ?? 'N/A' }}</p>
        </div>
    </div>
</div>
@endsection
