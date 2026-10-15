@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <h1 class="h3">{{ $country->exists ? 'Edit Country' : 'Add Country' }}</h1>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ $country->exists ? route('admin.countries.update', $country) : route('admin.countries.store') }}">
        @csrf
        @if($country->exists) @method('PUT') @endif
        <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $country->name) }}" required></div>
        <div class="mb-3"><label class="form-label">ISO 3166-1 alpha-2 code</label><input class="form-control text-uppercase" name="iso_code" maxlength="2" value="{{ old('iso_code', $country->iso_code) }}" required></div>
        <div class="mb-3"><label class="form-label">Currency code</label><input class="form-control text-uppercase" name="currency_code" maxlength="3" value="{{ old('currency_code', $country->currency_code) }}"></div>
        <div class="mb-3"><label class="form-label">Timezone</label><input class="form-control" name="time_zone" value="{{ old('time_zone', $country->time_zone) }}" placeholder="e.g. Indian/Mauritius"></div>
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is-active" @checked(old('is_active', $country->exists ? $country->is_active : true))><label class="form-check-label" for="is-active">Active</label></div>
        <button class="btn btn-primary" type="submit">Save Country</button>
        <a class="btn btn-secondary" href="{{ route('admin.countries.index') }}">Cancel</a>
    </form>
</div>
@endsection
