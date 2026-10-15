@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <h1 class="h3">{{ $administrator->exists ? 'Edit MPO' : 'Create MPO' }}</h1>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ $administrator->exists ? route('admin.country-admins.update', $administrator) : route('admin.country-admins.store') }}">
        @csrf
        @if($administrator->exists) @method('PUT') @endif
        <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $administrator->name) }}" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $administrator->email) }}" required></div>
        <div class="mb-3"><label class="form-label">Country</label><select class="form-select" name="country_id" required>@foreach($countries as $country)<option value="{{ $country->id }}" @selected((string) old('country_id', $administrator->country_id) === (string) $country->id)>{{ $country->name }}</option>@endforeach</select></div>
        <div class="mb-3"><label class="form-label">Status</label><select class="form-select" name="status" required><option value="active" @selected(old('status', $administrator->status ?: 'active') === 'active')>Active</option><option value="inactive" @selected(old('status', $administrator->status) === 'inactive')>Inactive</option></select></div>
        <div class="mb-3"><label class="form-label">{{ $administrator->exists ? 'Reset password (optional)' : 'Password' }}</label><input class="form-control" type="password" name="password" @required(!$administrator->exists) minlength="8" autocomplete="new-password"></div>
        <div class="mb-3"><label class="form-label">Confirm password</label><input class="form-control" type="password" name="password_confirmation" @required(!$administrator->exists) minlength="8" autocomplete="new-password"></div>
        <button class="btn btn-primary" type="submit">Save MPO</button>
        <a class="btn btn-secondary" href="{{ route('admin.country-admins.index') }}">Cancel</a>
    </form>
</div>
@endsection
