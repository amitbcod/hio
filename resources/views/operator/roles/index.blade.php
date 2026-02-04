@extends('layouts.app')

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card mt-5">
        <div class="card-body">
            <h4>Business Roles</h4>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="mb-3 d-flex justify-content-between">
                <h5>Available Roles</h5>
                <div>
                    {{-- Creation of roles is done by system administrators in the admin area. --}}
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr><th>Name</th><th>Scope</th><th>Permissions</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->business_id ? 'Business' : 'Global' }}</td>
                            <td>{{ implode(', ', $role->permissions->pluck('name')->toArray()) }}</td>
                            <td>
                                @if(!empty(auth()->user()->business_id) && (auth()->user()->is_owner ?? '') === 'yes' && (is_null($role->business_id) || ($role->business_id ?? null) == auth()->user()->business_id))
                                    <a class="btn btn-sm btn-secondary" href="{{ route('operator.roles.permissions', $role->id) }}">Manage Permissions</a>
                                    <a class="btn btn-sm btn-warning" href="{{ route('operator.roles.edit', $role->id) }}">Edit</a>
                                    <form method="POST" action="{{ route('operator.roles.destroy', $role->id) }}" style="display:inline-block;" onsubmit="return confirm('Delete role?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection