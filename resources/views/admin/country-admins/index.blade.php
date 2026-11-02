@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">MPO Management</h1>
        <a href="{{ route('admin.country-admins.create') }}" class="btn btn-primary">Create MPO</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead><tr><th>Name</th><th>Email</th><th>Country</th><th>Status</th><th>Created</th><th></th></tr></thead>
            <tbody>
                @forelse($administrators as $administrator)
                    <tr>
                        <td>{{ $administrator->name }}</td><td>{{ $administrator->email }}</td>
                        <td>{{ $administrator->country?->name ?? 'Unassigned' }}</td>
                        <td>{{ ucfirst($administrator->status) }}</td>
                        <td>{{ $administrator->created_at?->format('d M Y') }}</td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.country-admins.edit', $administrator) }}">Edit</a>
                            @if($administrator->admin_type === 'mpo')
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.country-admins.compliance.show', $administrator) }}">Compliance</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.country-admins.accounting-commission.show', $administrator) }}">Accounting Commission</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.country-admins.accounting-payouts.show', $administrator) }}">Accounting Payouts</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.country-admins.users', $administrator) }}">Users</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.country-admins.collaboration-agreements.create', $administrator) }}">Create Agreement</a>
                                @foreach($administrator->collaborationAgreements as $agreement)
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.country-admins.collaboration-agreements.edit', [$administrator, $agreement]) }}">Agreement {{ $agreement->agreement_id }} ({{ $agreement->status }})</a>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No MPO administrators found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $administrators->links('pagination::bootstrap-5') }}
</div>
@endsection
