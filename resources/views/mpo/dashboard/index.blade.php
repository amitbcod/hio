@extends('layouts.mpo-portal')

@section('title', 'Dashboard')

@section('content')
<p class="text-muted">Review pending registrations, service approvals, and traveller feedback for {{ $mpo->country->name }}.</p>

<div class="row g-3 mb-4">
    @foreach([
        ['Pending MPO Accounts', $pendingMpos->count(), '#pending-mpos'],
        ['Pending Agent Accounts', $pendingAgents->count(), '#pending-agents'],
        ['Pending Businesses', $businesses->count(), '#pending-businesses'],
        ['Accommodation Approvals', $accommodations->count(), '#pending-accommodations'],
        ['Vehicle Approvals', $transports->count(), '#pending-transports'],
        ['Activity Approvals', $activities->count(), '#pending-activities'],
        ['Feedback', $feedbacks->count(), '#feedback'],
    ] as [$label, $count, $target])
        <div class="col-12 col-sm-6 col-xl-4">
            <a href="{{ $target }}" class="card h-100 text-decoration-none shadow-sm">
                <div class="card-body">
                    <div class="text-muted">{{ $label }}</div>
                    <div class="display-6 fw-semibold">{{ $count }}</div>
                    <span class="small">View items</span>
                </div>
            </a>
        </div>
    @endforeach
</div>

<section id="pending-mpos" class="mb-5">
    <h2 class="h5">Pending MPO Accounts</h2>
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>Name</th><th>Email</th><th>Business</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($pendingMpos as $item)
            <tr>
                <td>{{ $item->full_name ?? 'N/A' }}</td><td>{{ $item->email }}</td>
                <td>{{ $item->business_legal_name ?? 'N/A' }}</td>
                <td>{{ $item->account_status }}</td>
                <td class="text-nowrap">
                    <form method="POST" action="{{ route('mpo.dashboard.mpo.approve', $item->id) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                    <form method="POST" action="{{ route('mpo.dashboard.mpo.reject', $item->id) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Reject</button></form>
                </td>
            </tr>
        @empty<tr><td colspan="5">No pending MPO accounts.</td></tr>@endforelse
        </tbody>
    </table></div>
</section>

<section id="pending-agents" class="mb-5">
    <h2 class="h5">Pending Agent Accounts</h2>
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>Agency</th><th>Email</th><th>Country</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($pendingAgents as $item)
            <tr>
                <td>{{ $item->agency_name }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->country?->name ?? 'N/A' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $item->account_status)) }}</td>
                <td class="text-nowrap">
                    @if($item->account_status === 'pending' && ($item->is_owner === 'yes' || $item->ownerVerifications()->where('status', 'accepted')->exists()))
                        <form method="POST" action="{{ route('mpo.dashboard.agent.approve', $item) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                    @else
                        <button class="btn btn-sm btn-success" disabled>Approve</button>
                    @endif
                    <form method="POST" action="{{ route('mpo.dashboard.agent.suspend', $item) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Suspend</button></form>
                </td>
            </tr>
        @empty<tr><td colspan="5">No pending agent accounts.</td></tr>@endforelse
        </tbody>
    </table></div>
</section>

<section id="pending-businesses" class="mb-5">
    <h2 class="h5">Pending Businesses</h2>
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>Business</th><th>Primary Contact</th><th>Submitted</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($businesses as $item)
            <tr><td>{{ $item->legal_name }}</td><td>{{ $item->primary_contact_email }}</td><td>{{ $item->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                <td class="text-nowrap">
                    <form method="POST" action="{{ route('mpo.dashboard.business.approve', $item) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                    <form method="POST" action="{{ route('mpo.dashboard.business.reject', $item) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Reject</button></form>
                </td>
            </tr>
        @empty<tr><td colspan="4">No pending businesses.</td></tr>@endforelse
        </tbody>
    </table></div>
</section>

<section id="pending-accommodations" class="mb-5">
    <h2 class="h5">Pending Accommodation Approvals</h2>
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>Property</th><th>Operator</th><th>Business</th><th>Submitted</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($accommodations as $item)
            <tr><td>{{ $item->property_name }}</td><td>{{ $item->operator->email ?? 'N/A' }}</td><td>{{ $item->business->legal_name ?? 'N/A' }}</td><td>{{ $item->submitted_for_approval_at?->format('Y-m-d H:i') ?? $item->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                <td class="text-nowrap">
                    <form method="POST" action="{{ route('mpo.dashboard.accommodation.approve', $item) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                    <form method="POST" action="{{ route('mpo.dashboard.accommodation.reject', $item) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Reject</button></form>
                </td>
            </tr>
        @empty<tr><td colspan="5">No pending accommodation submissions.</td></tr>@endforelse
        </tbody>
    </table></div>
</section>

<section id="pending-transports" class="mb-5">
    <h2 class="h5">Pending Vehicle Approvals</h2>
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>Vehicle</th><th>Operator</th><th>Submitted</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($transports as $item)
            <tr><td>{{ $item->vehicle_display_name }}</td><td>{{ $item->operator->email ?? 'N/A' }}</td><td>{{ $item->submitted_for_approval_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                <td class="text-nowrap">
                    <form method="POST" action="{{ route('mpo.dashboard.transport.approve', $item) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                    <form method="POST" action="{{ route('mpo.dashboard.transport.reject', $item) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Reject</button></form>
                </td>
            </tr>
        @empty<tr><td colspan="4">No pending vehicle submissions.</td></tr>@endforelse
        </tbody>
    </table></div>
</section>

<section id="pending-activities" class="mb-5">
    <h2 class="h5">Pending Activity Approvals</h2>
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>Activity</th><th>Operator</th><th>Submitted</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($activities as $item)
            <tr><td>{{ $item->activity_name }}</td><td>{{ $item->operator->email ?? 'N/A' }}</td><td>{{ $item->submitted_for_approval_at?->format('Y-m-d H:i') ?? $item->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                <td class="text-nowrap">
                    <form method="POST" action="{{ route('mpo.dashboard.activity.approve', $item) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                    <form method="POST" action="{{ route('mpo.dashboard.activity.reject', $item) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Reject</button></form>
                </td>
            </tr>
        @empty<tr><td colspan="4">No pending activity submissions.</td></tr>@endforelse
        </tbody>
    </table></div>
</section>

<section id="feedback" class="mb-4">
    <h2 class="h5">Traveller Feedback</h2>
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>Trip</th><th>Traveler</th><th>Rating</th><th>Submitted</th></tr></thead>
        <tbody>
        @forelse($feedbacks as $item)
            <tr><td>{{ $item->trip_id }}</td><td>{{ $item->trip?->traveler?->full_name ?? $item->trip?->traveler?->email ?? 'N/A' }}</td><td>{{ $item->overall_rating ?? 'N/A' }}</td><td>{{ $item->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</td></tr>
        @empty<tr><td colspan="4">No feedback has been submitted.</td></tr>@endforelse
        </tbody>
    </table></div>
</section>
@endsection
