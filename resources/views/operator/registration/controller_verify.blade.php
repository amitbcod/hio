@extends('layouts.app')

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card mt-5">
        <div class="card-body">
            <h4>Owner verification for business: <strong>{{ $cv->business->legal_name }}</strong></h4>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            @if(isset($owner) && $owner)
                <p>We found an existing account for <strong>{{ $owner->email }}</strong>.</p>
                <p>Please <a href="{{ route('operator.login') }}">login</a> with that account and then click <em>Accept</em> below to confirm the requester.</p>
                <form method="POST" action="{{ url('/operator/register/controller/verify/'.$cv->token.'/accept') }}">
                    @csrf
                    <button class="btn btn-success">Accept</button>
                    <a class="btn btn-secondary" href="{{ url('/') }}">Close</a>
                </form>
            @else
                <p>No existing owner account found for <strong>{{ $cv->owner_email }}</strong>.</p>
                <p>You can claim and create an owner account below. We will not ask you to re-enter business details — they are shown below for confirmation.</p>

                <div class="alert alert-light">
                    <h6>Business Summary</h6>
                    <p><strong>{{ $cv->business->legal_name }}</strong></p>
                    @if($cv->business->registration_number)<p>Registration #: {{ $cv->business->registration_number }}</p>@endif
                    @if($cv->business->country)<p>Country: {{ $cv->business->country }}</p>@endif
                    @if($cv->business->primary_contact_email)<p>Primary contact: {{ $cv->business->primary_contact_email }}</p>@endif
                </div>

                <form method="POST" action="{{ url('/operator/register/controller/verify/'.$cv->token.'/claim') }}">
                    @csrf
                    <input type="hidden" name="owner_email" value="{{ $cv->owner_email }}">
                    <div class="mb-3">
                        <label>Your Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email (readonly)</label>
                        <input type="email" name="owner_email_readonly" class="form-control" value="{{ $cv->owner_email }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Phone (optional)</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="confirm_authority" id="confirm_authority" required>
                        <label class="form-check-label" for="confirm_authority">I confirm I am authorised to act for this business and accept the details provided.</label>
                    </div>
                    <button class="btn btn-primary">Create & Claim</button>
                </form>
            @endif

        </div>
    </div>
</div>
@endsection