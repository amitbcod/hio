@extends('layouts.admin')

@section('title', 'Agent Management')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Agent Management</h1>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr><th>Agency</th><th>Contact</th><th>Email</th><th>Country</th><th>Status</th><th>Registered</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($agents as $agent)
                    <tr>
                        <td>{{ $agent->agency_name }}</td>
                        <td>{{ $agent->contact_person }}</td>
                        <td>{{ $agent->email }}</td>
                        <td>{{ $agent->country?->name ?? '—' }}</td>
                        <td>{{ ucfirst($agent->account_status) }}</td>
                        <td>{{ $agent->created_at?->format('Y-m-d') }}</td>
                        <td><a href="{{ route('admin.agents.show', $agent) }}" class="btn btn-sm btn-outline-primary">Review</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">No Agents found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $agents->links('pagination::bootstrap-5') }}
</div>
@endsection
