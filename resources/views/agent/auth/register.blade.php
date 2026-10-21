@extends('layouts.agent-auth')

@section('title', 'Agent Registration')

@section('content')
<h1>Create Agent Account</h1>
<form method="POST" action="{{ route('agent.register.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="agency_name">Agency Name <span class="text-danger">*</span></label>
        <input class="form-control" id="agency_name" name="agency_name" value="{{ old('agency_name') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="contact_person">Contact Person <span class="text-danger">*</span></label>
        <input class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
        <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="office_phone">Office Phone (E.164) <span class="text-danger">*</span></label>
            <input class="form-control" id="office_phone" name="office_phone" placeholder="+23012345678" value="{{ old('office_phone') }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="emergency_mobile">Mobile (Emergency, E.164) <span class="text-danger">*</span></label>
            <input class="form-control" id="emergency_mobile" name="emergency_mobile" placeholder="+23012345678" value="{{ old('emergency_mobile') }}" required>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="country_id">Country <span class="text-danger">*</span></label>
        <select class="form-select" id="country_id" name="country_id" required>
            <option value="">Select your country</option>
            @foreach($countries as $country)
                <option value="{{ $country->id }}" @selected(old('country_id') == $country->id)>{{ $country->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
        <input class="form-control" id="password" type="password" name="password" autocomplete="new-password" required>
        <div class="form-text">Use at least 8 characters with uppercase, lowercase, a number, and one of @ # % ! * ^ $ &amp;.</div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
        <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
    </div>
    <div class="form-check mb-4">
        <input class="form-check-input" id="terms" type="checkbox" name="terms" value="1" required>
        <label class="form-check-label" for="terms">I agree to the terms <span class="text-danger">*</span></label>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <button class="btn btn-primary" type="submit">Create Agent Account</button>
        <a href="{{ route('agent.login') }}">Already registered? Sign in</a>
    </div>
</form>
@endsection
