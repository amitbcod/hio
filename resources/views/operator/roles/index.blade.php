@extends('layouts.app')

@section('title', 'Business Roles')

@section('content')
<div class="container-fluid mt-0">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @include('operator.registration._sidebar_main')
        </div>
        <div class="col-md-9 my-pro">
            <div class="container-middle">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div>
                        <h2 class="fw-bold mb-2">Business Roles</h2>
                        <p class="text-muted mb-0">Review and configure role permissions for Operator staff.</p>
                    </div>
                    <a href="{{ route('operator.register.step6') }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>Back
                    </a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success mb-3">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger mb-3">{{ session('error') }}</div>
                @endif

                <section class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
                    <div class="card-header bg-white border-bottom px-3 px-lg-4 py-3">
                        <h5 class="mb-0 fw-bold">Available Roles</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr><th>Name</th><th>Scope</th><th>Configured staff actions</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td class="fw-semibold">{{ $role->name }}</td>
                                        <td>{{ ($role->scope ?? 'business') === 'global' ? 'Global' : (($role->scope ?? '') === 'country' ? 'Country' : 'Business (legacy)') }}</td>
                                        <td>{{ $role->configured_actions }}</td>
                                        <td>
                                            <a class="btn btn-sm" style="background:#19b5b5;color:#fff;border:0;" href="{{ route('operator.roles.permissions', $role->id) }}">Manage Permissions</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-5">No roles are available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection