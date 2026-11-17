@extends('layouts.admin')

@section('title', 'Transport Listing Details')

@section('content')
@php
    $operator = $transport->operator;
    $operatorName = $operator?->business_legal_name
        ?: $operator?->business?->legal_name
        ?: $operator?->profile?->business_legal_name
        ?: $operator?->full_name
        ?: $operator?->email
        ?: 'Unknown operator';
    $canReview = $transport->approval_status === 'Pending';
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $transport->vehicle_name ?: $transport->vehicleName?->name ?: 'Transport Listing' }}</h1>
            <p class="text-muted mb-0">Service ID: {{ $transport->service_id ?: $transport->id }}</p>
        </div>
        <div class="d-flex gap-2">
            @if(! $isMpo || $canEdit)
                <a href="{{ route($isMpo ? 'mpo.transport.edit' : 'admin.transport.edit', $transport) }}" class="btn btn-primary">Edit</a>
            @endif
            <a href="{{ route($indexRoute) }}" class="btn btn-outline-secondary">Back to Transport Listings</a>
        </div>
    </div>

    <div class="card mb-3"><div class="card-header">Listing and Operator</div><div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Vehicle Name</dt><dd class="col-sm-9">{{ $transport->vehicle_name ?: $transport->vehicleName?->name ?: '—' }}</dd>
            <dt class="col-sm-3">Vehicle Type</dt><dd class="col-sm-9">{{ $transport->vehicle_type ?: $transport->vehicleName?->vehicleType?->name ?: '—' }}</dd>
            <dt class="col-sm-3">Operator / Business</dt><dd class="col-sm-9">{{ $operatorName }}</dd>
            <dt class="col-sm-3">Operator Email</dt><dd class="col-sm-9">{{ $operator?->email ?? '—' }}</dd>
            <dt class="col-sm-3">Country</dt><dd class="col-sm-9">{{ $operator?->country?->name ?? '—' }}</dd>
            <dt class="col-sm-3">Registration</dt><dd class="col-sm-9">{{ $transport->registration_number ?: '—' }}</dd>
            <dt class="col-sm-3">Seating Capacity</dt><dd class="col-sm-9">{{ $transport->seating_capacity ?? $transport->vehicleName?->seat_capacity ?? '—' }}</dd>
            <dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ $transport->status ?? '—' }} / {{ $transport->approval_status ?? '—' }}</dd>
            <dt class="col-sm-3">Service Description</dt><dd class="col-sm-9">{{ $transport->service_description ?: $transport->overview ?: '—' }}</dd>
            <dt class="col-sm-3">Contact</dt><dd class="col-sm-9">{{ $transport->contact_person ?: '—' }} · {{ $transport->contact_phone ?: '—' }} · {{ $transport->contact_email ?: '—' }}</dd>
            <dt class="col-sm-3">Submitted</dt><dd class="col-sm-9">{{ $transport->submitted_for_approval_at?->format('Y-m-d H:i') ?? '—' }}</dd>
            <dt class="col-sm-3">Created / Updated</dt><dd class="col-sm-9">{{ $transport->created_at?->format('Y-m-d H:i') ?? '—' }} / {{ $transport->updated_at?->format('Y-m-d H:i') ?? '—' }}</dd>
        </dl>
    </div></div>

    @if(! $isMpo && $canReview)
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.transport.approve', $transport) }}">@csrf<button class="btn btn-success">Approve Transport</button></form>
            <form method="POST" action="{{ route('admin.transport.reject', $transport) }}">@csrf<button class="btn btn-outline-danger">Reject Transport</button></form>
        </div>
    @endif
</div>
@endsection
