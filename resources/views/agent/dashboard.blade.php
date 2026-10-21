@extends('layouts.agent')

@section('title', 'Agent Dashboard')

@section('content')
@php
    $profileComplete = $progress->profile_complete
        && $progress->compliance_complete
        && $progress->users_complete
        && $progress->operations_complete;
    $expiryDate = $complianceDocument?->expiry_date
        ? \Illuminate\Support\Carbon::parse($complianceDocument->expiry_date)->startOfDay()
        : null;
    $agentDashboardUser = auth('agent_staff')->user();
    $canReadReservations = !$agentDashboardUser
        || app(\App\Services\AgentPermissionService::class)->can($agentDashboardUser, 'reservation', 'read');
    $canReadProfile = !$agentDashboardUser
        || app(\App\Services\AgentPermissionService::class)->can($agentDashboardUser, 'profile', 'read');
    $canReadCompliance = !$agentDashboardUser
        || app(\App\Services\AgentPermissionService::class)->can($agentDashboardUser, 'compliance', 'read');
@endphp

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h2 class="h4 mb-1">Welcome, {{ $agentDashboardUser?->full_name ?? $agent->contact_person }}</h2>
        <p class="text-muted mb-0">{{ $agent->agency_name }} · {{ $agent->country?->name }}</p>
    </div>
    <span class="badge {{ $agent->account_status === 'approved' ? 'bg-success' : 'bg-warning text-dark' }}">
        Account {{ ucfirst($agent->account_status) }}
    </span>
</div>

@if($agent->account_status === 'pending')
    <div class="alert alert-warning" role="status">Your account is awaiting Admin/MPO review.</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Total Group Bookings</div>
            <div class="fs-3 fw-bold">{{ number_format($metrics['total_bookings']) }}</div>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Pending Admin Review</div>
            <div class="fs-3 fw-bold">{{ number_format($metrics['pending_review']) }}</div>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Pending Payments</div>
            <div class="fs-3 fw-bold">{{ number_format($metrics['pending_payments']) }}</div>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Upcoming Travel</div>
            <div class="fs-3 fw-bold">{{ number_format($metrics['upcoming_travel']) }}</div>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl">
        <div class="card h-100"><div class="card-body">
            <div class="text-muted small">Booking Value</div>
            <div class="fs-3 fw-bold">USD {{ number_format($metrics['booking_value'], 2) }}</div>
        </div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <section class="card h-100">
            <div class="card-header py-3">Agency Setup</div>
            <div class="card-body">
                @if($profileComplete)
                    <h3 class="h6">Setup complete</h3>
                    <p class="text-muted mb-0">Your Agent profile, compliance, users, and operations setup is complete.</p>
                @else
                    <h3 class="h6">Setup in progress</h3>
                    <p class="text-muted">Complete the required agency information and documents.</p>
                    <div class="d-flex flex-wrap gap-2">
                        @if($canReadProfile && !$progress->profile_complete)
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('agent.profile') }}">Complete profile</a>
                        @endif
                        @if($canReadCompliance && $progress->profile_complete && !$progress->compliance_complete)
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('agent.compliance') }}">Complete compliance</a>
                        @endif
                    </div>
                @endif
                @if($expiryDate)
                    <div class="border-top mt-3 pt-3 small">
                        <strong>Compliance document expiry:</strong> {{ $expiryDate->format('d/m/Y') }}
                        @if($expiryDate->lt(today()))
                            <span class="badge bg-danger ms-1">Expired</span>
                        @elseif($expiryDate->lte(today()->copy()->addDays(30)))
                            <span class="badge bg-warning text-dark ms-1">Renewal due soon</span>
                        @else
                            <span class="badge bg-success ms-1">Valid</span>
                        @endif
                    </div>
                @endif
            </div>
        </section>
    </div>
    <div class="col-lg-5">
        <section class="card h-100">
            <div class="card-header py-3">Quick Actions</div>
            <div class="card-body d-flex flex-column align-items-start gap-2">
                @if($canReadReservations)
                    <a class="btn btn-primary" href="{{ route('agent.closed-groups.index') }}">View Group Bookings</a>
                    @if($agent->account_status === 'approved')
                        <a class="btn btn-outline-primary" href="{{ route('agent.closed-groups.book') }}">Create Group Booking</a>
                    @endif
                @endif
                @if($canReadProfile)
                    <a class="btn btn-outline-secondary" href="{{ route('agent.profile') }}">Agency Profile</a>
                @endif
                @if($canReadCompliance)
                    <a class="btn btn-outline-secondary" href="{{ route('agent.compliance') }}">Compliance Documents</a>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection
