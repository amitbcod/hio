@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Edit Traveller</h3>
    @if($errors->any())<div class="alert alert-danger">Please fix the errors below.</div>@endif
    <form method="POST" action="{{ route('admin.travellers.update', $traveler) }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $traveler->email) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" value="{{ old('first_name', optional($traveler->profile)->first_name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" value="{{ old('last_name', optional($traveler->profile)->last_name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', optional($traveler->profile)->phone) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($traveler->profile)->date_of_birth ? optional($traveler->profile)->date_of_birth->format('Y-m-d') : '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Nationality</label>
            <input type="text" name="nationality" class="form-control" value="{{ old('nationality', optional($traveler->profile)->nationality) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Account Suspended</label>
            <select name="account_suspended" class="form-select">
                <option value="0" {{ !$traveler->account_suspended ? 'selected' : '' }}>No</option>
                <option value="1" {{ $traveler->account_suspended ? 'selected' : '' }}>Yes</option>
            </select>
        </div>
        <div class="mb-3">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.travellers.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>
@endsection