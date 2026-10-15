@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">MPO Management</h1>
        <a href="{{ route('admin.country-admins.create') }}" class="btn btn-primary">Create MPO</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
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
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.country-admins.edit', $administrator) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No MPO administrators found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $administrators->links() }}
</div>
@endsection
