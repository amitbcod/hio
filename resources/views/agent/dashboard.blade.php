@extends('layouts.agent')

@section('title', 'Agent Dashboard')

@section('content')
<h1 class="h3 mb-3">Welcome, {{ $agent->contact_person }}</h1>
<p class="text-muted">{{ $agent->agency_name }} · {{ $agent->country?->name }} · Account: {{ ucfirst($agent->account_status) }}</p>
<div class="card"><div class="card-body">
    @if($progress->profile_complete && $progress->compliance_complete && $progress->users_complete && $progress->operations_complete)
        <h2 class="h5">Setup complete</h2>
        <p>Your Agent profile, compliance, users, and operations setup is complete.</p>
    @else
        <h2 class="h5">Setup in progress</h2>
        <p>The Agent account owner is completing the required setup steps.</p>
    @endif
    @if($agent->account_status === 'pending')<div class="alert alert-warning mb-0">Your account is awaiting Admin/MPO review.</div>@endif
</div></div>
@endsection
