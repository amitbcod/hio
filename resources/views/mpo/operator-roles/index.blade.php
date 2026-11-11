@extends('layouts.mpo-portal')

@section('title', 'Operator Roles')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Operator Roles</h1>
            <p class="text-muted mb-0">Global roles are read-only. You can manage roles for {{ $owner->country->name }}.</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-3">
        <div class="card-header">Create Country Role</div>
        <div class="card-body">
            <form method="POST" action="{{ route('mpo.operator-roles.store') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-8">
                    <label class="form-label" for="role-name">Role name</label>
                    <input class="form-control" id="role-name" name="name" maxlength="150" required value="{{ old('name') }}">
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary">Add Country Role</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead><tr><th>Role</th><th>Scope</th><th>Management</th></tr></thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td>
                                @if($role->scope === 'country' && (int) $role->country_id === (int) $owner->country_id)
                                    <form method="POST" action="{{ route('mpo.operator-roles.update', $role) }}" class="d-flex gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input class="form-control" name="name" value="{{ $role->name }}" maxlength="150" required>
                                        <button class="btn btn-sm btn-outline-primary">Save</button>
                                    </form>
                                @else
                                    {{ $role->name }}
                                @endif
                            </td>
                            <td>{{ $role->scope === 'global' ? 'Global' : $owner->country->name }}</td>
                            <td>
                                @if($role->scope === 'country' && (int) $role->country_id === (int) $owner->country_id)
                                    <form method="POST" action="{{ route('mpo.operator-roles.destroy', $role) }}" onsubmit="return confirm('Delete this country role?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @else
                                    <span class="text-muted">Read only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center py-4">No roles are available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
