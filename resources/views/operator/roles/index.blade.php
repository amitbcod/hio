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
                    <tr><th>Name</th><th>Scope</th><th>Configured staff actions</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td>{{ ($role->scope ?? 'business') === 'global' ? 'Global' : (($role->scope ?? '') === 'country' ? 'Country' : 'Business (legacy)') }}</td>
                            <td>{{ $role->configured_actions }}</td>
                            <td>
                                <a class="btn btn-sm btn-secondary" href="{{ route('operator.roles.permissions', $role->id) }}">Manage Permissions</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection