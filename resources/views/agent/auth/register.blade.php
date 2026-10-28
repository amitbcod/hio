@extends('layouts.agent-auth')

@section('title', 'Agent Registration')

@section('content')
<h1>Create Agent Account</h1>
@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form method="POST" action="{{ route('agent.register.store') }}">
    @csrf
    <fieldset class="mb-3">
        <legend class="form-label">Are you the owner of this business? <span class="text-danger">*</span></legend>
        <div class="form-check form-check-inline">
            <input class="form-check-input" id="is_owner_yes" type="radio" name="is_owner" value="yes" @checked(old('is_owner', 'yes') === 'yes') required>
            <label class="form-check-label" for="is_owner_yes">Yes</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" id="is_owner_no" type="radio" name="is_owner" value="no" @checked(old('is_owner') === 'no') required>
            <label class="form-check-label" for="is_owner_no">No</label>
        </div>
    </fieldset>
    <div id="agent-applicant-fields" class="mb-3">
        <h2 class="h5">Your Information</h2>
        <div class="mb-3">
            <label class="form-label" for="applicant_email">User ID / Email Address <span class="text-danger">*</span></label>
            <input class="form-control" id="applicant_email" type="email" name="applicant_email" value="{{ old('applicant_email') }}" autocomplete="email" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="applicant_mobile">Mobile Number / WhatsApp <span class="text-danger">*</span></label>
            <input class="form-control" id="applicant_mobile" name="applicant_mobile" value="{{ old('applicant_mobile') }}" placeholder="+230 57011234" required>
            <div class="form-text">Use E.164 format, including the +country code. Spaces are allowed.</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="applicant_full_name">Your Full Name <span class="text-danger">*</span></label>
            <input class="form-control" id="applicant_full_name" name="applicant_full_name" value="{{ old('applicant_full_name') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="applicant_role">User Role <span class="text-danger">*</span></label>
            <select class="form-select" id="applicant_role" name="applicant_role" required>
                <option value="">Select a role</option>
                @foreach(['Manager', 'Consultant', 'Accountant', 'Support'] as $role)
                    <option value="{{ $role }}" @selected(old('applicant_role') === $role)>{{ $role }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div id="agent-owner-fields" class="mb-3" @if(old('is_owner', 'yes') !== 'no') hidden @endif>
        <h2 class="h5">Owner Information</h2>
        <div class="mb-3">
            <label class="form-label" for="owner_full_name">Owner's Full Name <span class="text-danger">*</span></label>
            <input class="form-control" id="owner_full_name" name="owner_full_name" value="{{ old('owner_full_name') }}" @required(old('is_owner') === 'no')>
        </div>
        <div class="mb-3">
            <label class="form-label" for="owner_email">Owner's Email Address <span class="text-danger">*</span></label>
            <input class="form-control" id="owner_email" type="email" name="owner_email" value="{{ old('owner_email') }}" autocomplete="email" @required(old('is_owner') === 'no')>
        </div>
        <div class="mb-3">
            <label class="form-label" for="owner_phone">Owner's Mobile Number <span class="text-danger">*</span></label>
            <input class="form-control" id="owner_phone" name="owner_phone" value="{{ old('owner_phone') }}" placeholder="+23012345678" @required(old('is_owner') === 'no')>
        </div>
    </div>
    <h2 class="h5">Agency Information</h2>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ownerFields = document.getElementById('agent-owner-fields');
        const conditionalInputs = document.querySelectorAll('#agent-owner-fields input');
        const ownerRadios = document.querySelectorAll('input[name="is_owner"]');

        function updateOwnerFields() {
            const isNonOwner = document.querySelector('input[name="is_owner"]:checked')?.value === 'no';
            ownerFields.hidden = !isNonOwner;
            conditionalInputs.forEach(function (input) {
                input.required = isNonOwner;
                input.disabled = !isNonOwner;
            });
        }

        ownerRadios.forEach(function (radio) {
            radio.addEventListener('change', updateOwnerFields);
        });
        updateOwnerFields();
    });
</script>
@endsection
