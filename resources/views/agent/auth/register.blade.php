<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Agent Registration | Holidays.io</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
<body class="bg-light"><main class="container py-5" style="max-width:760px"><div class="card shadow-sm"><div class="card-body p-4 p-md-5">
<h1 class="h3 mb-4">Agent Registration</h1>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('agent.register.store') }}">@csrf
    <div class="mb-3"><label class="form-label">Agency Name *</label><input class="form-control" name="agency_name" value="{{ old('agency_name') }}" required></div>
    <div class="mb-3"><label class="form-label">Contact Person *</label><input class="form-control" name="contact_person" value="{{ old('contact_person') }}" required></div>
    <div class="mb-3"><label class="form-label">Email *</label><input class="form-control" type="email" name="email" value="{{ old('email') }}" required></div>
    <div class="mb-3"><label class="form-label">Office Phone (E.164) *</label><input class="form-control" name="office_phone" placeholder="+23012345678" value="{{ old('office_phone') }}" required></div>
    <div class="mb-3"><label class="form-label">Mobile (Emergency, E.164) *</label><input class="form-control" name="emergency_mobile" placeholder="+23012345678" value="{{ old('emergency_mobile') }}" required></div>
    <div class="mb-3"><label class="form-label">Country *</label><select class="form-select" name="country_id" required><option value="">Select your country</option>@foreach($countries as $country)<option value="{{ $country->id }}" @selected(old('country_id') == $country->id)>{{ $country->name }}</option>@endforeach</select></div>
    <div class="mb-3"><label class="form-label">Password *</label><input class="form-control" type="password" name="password" required><div class="form-text">Use at least 8 characters with uppercase, lowercase, number, and one of @ # % ! * ^ $ &amp;.</div></div>
    <div class="mb-3"><label class="form-label">Confirm Password *</label><input class="form-control" type="password" name="password_confirmation" required></div>
    <div class="form-check mb-4"><input class="form-check-input" type="checkbox" name="terms" value="1" required><label class="form-check-label">I agree to the terms *</label></div>
    <button class="btn btn-primary">Create Agent Account</button> <a href="{{ route('agent.login') }}">Already registered? Sign in</a>
</form></div></div></main></body></html>
