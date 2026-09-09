@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Groups</h2>
        <a href="{{ route('admin.groups.create') }}" class="btn btn-primary">Add New Group</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Group Type</th>
                        <th>Status</th>
                        <th>Days</th>
                        <th>Available From</th>
                        <th>Available To</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $group)
                        <tr>
                            <td>{{ $group->id }}</td>
                            <td>{{ $group->name }}</td>
                            <td>{{ $group->group_type ?? '-' }}</td>
                            <td>
                                <span class="badge {{ ($group->status === 'published') ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($group->status ?? 'draft') }}
                                </span>
                            </td>
                            <td>{{ $group->no_of_days ?? '-' }}</td>
                            <td>{{ optional($group->available_from)->toDateString() ?? '-' }}</td>
                            <td>{{ optional($group->available_to)->toDateString() ?? '-' }}</td>
                            <td>{{ $group->created_at ? $group->created_at->format('d M Y') : '-' }}</td>
                            <td>
                                <a href="{{ route('admin.groups.edit', $group->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">No groups found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $groups->links() }}
    </div>
</div>
@endsection
