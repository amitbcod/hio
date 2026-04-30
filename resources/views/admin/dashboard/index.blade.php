@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Admin Dashboard</h3>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
    <h5 class="mt-3">Pending Businesses</h5>
    <table class="table">
        <thead>
            <tr><th>ID</th><th>Business ID</th><th>Legal Name</th><th>Primary Contact</th><th>Created</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @forelse($businesses as $b)
                <tr>
                    <td>{{ $b->id }}</td>
                    <td>{{ $b->business_id }}</td>
                    <td>{{ $b->legal_name }}</td>
                    <td>{{ $b->primary_contact_email }}</td>
                    <td>{{ $b->created_at }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.business.approve', $b) }}" style="display:inline">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                        <form method="POST" action="{{ route('admin.business.reject', $b) }}" style="display:inline">@csrf<button class="btn btn-sm btn-danger">Reject</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No pending businesses.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h5 class="mt-5">Pending Accommodation Approvals</h5>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Property</th>
                <th>Operator</th>
                <th>Business</th>
                <th>Submitted</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($pendingAccommodations ?? collect()) as $accommodation)
                <tr>
                    <td>{{ $accommodation->id }}</td>
                    <td>
                        <strong>{{ $accommodation->property_name }}</strong><br>
                        <small class="text-muted">{{ $accommodation->property_type }}</small>
                    </td>
                    <td>{{ $accommodation->operator->email ?? 'N/A' }}</td>
                    <td>{{ $accommodation->business->legal_name ?? 'N/A' }}</td>
                    <td>{{ $accommodation->submitted_for_approval_at ? $accommodation->submitted_for_approval_at->format('Y-m-d H:i') : ($accommodation->created_at ? $accommodation->created_at->format('Y-m-d H:i') : 'N/A') }}</td>
                    <td>{{ $accommodation->approval_status ?? $accommodation->status }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.accommodation.approve', $accommodation) }}" style="display:inline">
                            @csrf
                            <button class="btn btn-sm btn-success">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.accommodation.reject', $accommodation) }}" style="display:inline" onsubmit="return confirm('Reject this accommodation submission?');">
                            @csrf
                            <button class="btn btn-sm btn-danger">Reject</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No pending accommodation submissions.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h5 class="mt-5">Pending Activity Approvals</h5>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Activity</th>
                <th>Operator</th>
                <th>Submitted</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($pendingActivities ?? collect()) as $activity)
                <tr>
                    <td>{{ $activity->id }}</td>
                    <td>
                        <strong>{{ $activity->activity_name }}</strong><br>
                        <small class="text-muted">{{ $activity->service_type }}</small>
                    </td>
                    <td>{{ $activity->operator->email ?? 'N/A' }}</td>
                    <td>{{ $activity->submitted_for_approval_at ? $activity->submitted_for_approval_at->format('Y-m-d H:i') : ($activity->created_at ? $activity->created_at->format('Y-m-d H:i') : 'N/A') }}</td>
                    <td>{{ $activity->approval_status ?? $activity->status }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.activity.approve', $activity) }}" style="display:inline">
                            @csrf
                            <button class="btn btn-sm btn-success">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.activity.reject', $activity) }}" style="display:inline" onsubmit="return confirm('Reject this activity submission?');">
                            @csrf
                            <button class="btn btn-sm btn-danger">Reject</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No pending activity submissions.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection