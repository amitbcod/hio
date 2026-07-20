@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Create Operator</h3>
    @if($errors->any())<div class="alert alert-danger">Please fix the errors below.</div>@endif
    <form method="POST" action="{{ route('admin.operators.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Business</label>
            <select name="business_id" class="form-select" required>
                <option value="">Choose business...</option>
                @foreach($businesses as $id => $name)
                    <option value="{{ $id }}" {{ old('business_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Is Owner?</label>
            <select name="is_owner" class="form-select">
                <option value="no">No</option>
                <option value="yes">Yes</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Account Status</label>
            <select name="account_status" class="form-select" required>
                <option value="pending_verification">Pending Verification</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
                <option value="archived">Archived</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Password (optional)</label>
            <input type="password" name="password" class="form-control">
            <small class="text-muted">Leave blank to create without setting a password.</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>
        <div class="mb-3">
            <button class="btn btn-primary" style="margin-bottom: 10px;">Create</button>
    
            <a href="{{ route('admin.operators.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>
@endsection