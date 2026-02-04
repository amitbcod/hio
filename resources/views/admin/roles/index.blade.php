@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h3>Manage Roles</h3>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <label>Role Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Assign to Business (optional)</label>
                        <select name="business_id" class="form-control">
                            <option value="">Global (all businesses)</option>
                            @foreach(\App\Models\Business::orderBy('legal_name')->get() as $b)
                                <option value="{{ $b->id }}">{{ $b->legal_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary">Create Role</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Name</th><th>Scope</th><th>Permissions</th></tr></thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->business_id ? (\App\Models\Business::find($role->business_id)->legal_name ?? 'Business') : 'Global'}}</td>
                            <td>{{ implode(', ', $role->permissions->pluck('name')->toArray()) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection