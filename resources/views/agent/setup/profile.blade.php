@extends('layouts.agent')
@section('title', 'Agent Profile')
@section('content')
<h1 class="h3 mb-4">Agent Profile</h1>
<form method="POST" action="{{ route('agent.profile.save') }}" enctype="multipart/form-data" class="card"><div class="card-body">@csrf
    <div class="mb-3"><label class="form-label">Agent ID</label><input class="form-control" value="{{ $agent->agent_id }}" disabled></div>
    <div class="mb-3"><label class="form-label">Agency Name</label><input class="form-control" value="{{ $agent->agency_name }}" disabled></div>
    <div class="mb-3"><label class="form-label">Contact Person</label><input class="form-control" value="{{ $agent->contact_person }}" disabled></div>
    <div class="mb-3"><label class="form-label">Country</label><input class="form-control" value="{{ $agent->country?->name }}" disabled></div>
    <div class="mb-3"><label class="form-label">Account Status</label><input class="form-control" value="{{ ucfirst($agent->account_status) }}" disabled></div>
    <div class="mb-3"><label class="form-label">MPO Approval Date</label><input class="form-control" value="{{ $agent->approved_at?->format('Y-m-d H:i') ?? 'Pending' }}" disabled></div>
    <div class="mb-3"><label class="form-label">Address *</label><input class="form-control" name="address" value="{{ old('address', $profile?->address) }}" required></div>
    <div class="mb-3"><label class="form-label">City *</label><input class="form-control" name="city" value="{{ old('city', $profile?->city) }}" required></div>
    <div class="mb-3"><label class="form-label">Agency Type</label><select class="form-select" name="agency_type"><option value="">Select</option>@foreach(['Travel Agency','Tour Operator','Corporate Travel Desk'] as $type)<option @selected(old('agency_type', $profile?->agency_type) === $type)>{{ $type }}</option>@endforeach</select></div>
    <fieldset class="mb-3"><legend class="fs-6">Focus Segments</legend>@foreach(['Accommodation','Transport','Activities','Packages'] as $segment)<label class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="focus_segments[]" value="{{ $segment }}" @checked(in_array($segment, old('focus_segments', $profile?->focus_segments ?? [])))>{{ $segment }}</label>@endforeach</fieldset>
    <div class="mb-3"><label class="form-label">Agency Logo</label><input class="form-control" type="file" name="logo" accept="image/jpeg,image/png,image/webp"></div>
    <div class="mb-3"><label class="form-label">Agency Description (max 500 characters)</label><textarea class="form-control" name="description" maxlength="500" rows="4">{{ old('description', $profile?->description) }}</textarea></div>
    <button class="btn btn-primary">Save and Continue</button>
</div></form>
@endsection
