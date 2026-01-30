@extends('layouts.app')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Admin Dashboard</h3>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
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
</div>
@endsection