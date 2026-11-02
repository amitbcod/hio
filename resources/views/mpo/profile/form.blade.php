@extends($isAdminEdit ? 'layouts.admin' : 'layouts.mpo-portal')

@section('title', 'MPO Profile')

@section('content')
@php
    $formAction = $isAdminEdit
        ? route('admin.country-admins.profile.update', $mpoAdmin)
        : route('mpo.profile.save');
    $selectedLanguages = old('supported_languages', $mpoAdmin->supported_languages ?? []);
    $selectedRegions = old('regions_covered', $mpoAdmin->covered_region_ids ?? []);
    $timezoneValue = old('operating_timezone', $mpoAdmin->operating_timezone ?: $mpoAdmin->country?->time_zone);
    $selectedLanguages = is_array($selectedLanguages) ? $selectedLanguages : [];
    $selectedRegions = is_array($selectedRegions) ? $selectedRegions : [];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
    <div>
        <p class="text-muted mb-0">
            @if($isAdminEdit)
                Editing the profile for {{ $mpoAdmin->name }} ({{ $mpoAdmin->email }}).
            @else
                Add your business details. You can save a draft and finish the required fields later.
            @endif
        </p>
        @unless($isAdminEdit)
            <p class="small text-muted mb-0">Login email: {{ $mpoAdmin->email }} (separate from official business email)</p>
        @endunless
    </div>
    @if($isAdminEdit)
        <a class="btn btn-outline-secondary" href="{{ route('admin.country-admins.edit', $mpoAdmin) }}">Back to MPO account</a>
    @endif
</div>

<div class="alert {{ $mpoAdmin->hasCompleteMpoProfile() ? 'alert-success' : 'alert-warning' }}" role="status">
    Profile status: <strong>{{ $mpoAdmin->hasCompleteMpoProfile() ? 'Complete' : 'Incomplete' }}</strong>
    @unless($mpoAdmin->hasCompleteMpoProfile())
        <span>— complete the mandatory fields below to finish this step.</span>
    @endunless
</div>

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
    @csrf
    @if($isAdminEdit) @method('PUT') @endif

    <section class="card mb-4">
        <div class="card-header">Section 1: MPO Information</div>
        <div class="card-body row g-3">
            @if($isAdminEdit)
            <div class="col-md-6">
                <label class="form-label" for="mpo-id">MPO ID</label>
                <input class="form-control" id="mpo-id" value="{{ $mpoAdmin->getKey() }}" readonly>
                <div class="form-text">The account's primary record ID cannot be changed.</div>
            </div>
            @endif
            <div class="col-md-6">
                <label class="form-label" for="country">Country</label>
                <input class="form-control" id="country" value="{{ $mpoAdmin->country?->name ?? 'Not assigned' }}" readonly>
                <div class="form-text">Country assignment is managed by the global Admin.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="registered_business_name">Registered business name <span class="text-danger">*</span></label>
                <input class="form-control" id="registered_business_name" name="registered_business_name" maxlength="191" value="{{ old('registered_business_name', $mpoAdmin->registered_business_name) }}" autocomplete="organization">
                @error('registered_business_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header">Section 2: Business Registration</div>
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label" for="registration_number">Registration Number <span class="text-danger">*</span></label>
                <input class="form-control" id="registration_number" name="registration_number" maxlength="100" value="{{ old('registration_number', $mpoAdmin->brn_number) }}">
                @error('registration_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="address_line1">Business Address Line 1 <span class="text-danger">*</span></label>
                <input class="form-control" id="address_line1" name="address_line1" maxlength="255" value="{{ old('address_line1', $mpoAdmin->business_address) }}" autocomplete="address-line1">
                @error('address_line1')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="address_line2">Business Address Line 2</label>
                <input class="form-control" id="address_line2" name="address_line2" maxlength="255" value="{{ old('address_line2', $mpoAdmin->business_address_line2) }}" autocomplete="address-line2">
                @error('address_line2')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header">Section 3: Official Contact Information</div>
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label" for="official_email">Official Email <span class="text-danger">*</span></label>
                <input class="form-control" id="official_email" name="official_email" type="email" maxlength="191" value="{{ old('official_email', $mpoAdmin->official_business_email) }}" autocomplete="email">
                @error('official_email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="office_phone">Office Phone <span class="text-danger">*</span></label>
                <input class="form-control" id="office_phone" name="office_phone" type="tel" placeholder="+23057011234" value="{{ old('office_phone', $mpoAdmin->phone_number) }}" autocomplete="tel">
                @error('office_phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="emergency_mobile">Mobile (Emergency) <span class="text-danger">*</span></label>
                <input class="form-control" id="emergency_mobile" name="emergency_mobile" type="tel" placeholder="+23057011234" value="{{ old('emergency_mobile', $mpoAdmin->emergency_mobile) }}" autocomplete="tel">
                <div class="form-text">Use international E.164 format. This field is not OTP-verified.</div>
                @error('emergency_mobile')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header">Section 4: Operating Information</div>
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label" for="operating_timezone">Operating Timezone <span class="text-danger">*</span></label>
                <select class="form-select" id="operating_timezone" name="operating_timezone">
                    <option value="">Select a timezone</option>
                    @foreach($timezones as $timezone)
                        <option value="{{ $timezone }}" @selected($timezoneValue === $timezone)>{{ $timezone }}</option>
                    @endforeach
                </select>
                @error('operating_timezone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <fieldset class="col-12">
                <legend class="form-label">Languages Supported <span class="text-danger">*</span></legend>
                <div class="row g-2">
                    @foreach($languages as $code => $language)
                        <div class="col-sm-6 col-lg-4">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="supported_languages[]" value="{{ $code }}" @checked(in_array($code, $selectedLanguages, true))>
                                <span class="form-check-label">{{ $language }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('supported_languages')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('supported_languages.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </fieldset>
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header">Section 5: Public Brand and Online Presence</div>
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label" for="brand_name">Brand Name <span class="text-danger">*</span></label>
                <input class="form-control" id="brand_name" name="brand_name" maxlength="191" value="{{ old('brand_name', $mpoAdmin->brand_name) }}">
                @error('brand_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="logo">Logo</label>
                <input class="form-control" id="logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">JPEG, PNG, or WebP; maximum 5 MB.</div>
                @if($mpoAdmin->logo_path)
                    <img class="mt-2 border rounded p-1" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($mpoAdmin->logo_path) }}" alt="Current MPO logo" style="max-width: 180px; max-height: 100px">
                @endif
                @error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" maxlength="5000">{{ old('description', $mpoAdmin->brand_description) }}</textarea>
                @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="regions_covered">Regions Covered</label>
                <select class="form-select" id="regions_covered" name="regions_covered[]" multiple size="5">
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" @selected(in_array((string) $region->id, array_map('strval', $selectedRegions), true))>{{ $region->name }}</option>
                    @endforeach
                </select>
                @if($regions->isEmpty())
                    <div class="form-text">No regions are assigned to this country yet. Ask the global Admin to configure the region master data.</div>
                @endif
                @error('regions_covered.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="public_contact_email">Public Contact Email</label>
                <input class="form-control" id="public_contact_email" name="public_contact_email" type="email" maxlength="191" value="{{ old('public_contact_email', $mpoAdmin->public_contact_email) }}">
                @error('public_contact_email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="public_contact_phone">Public Contact Phone</label>
                <input class="form-control" id="public_contact_phone" name="public_contact_phone" type="tel" placeholder="+23057011234" value="{{ old('public_contact_phone', $mpoAdmin->public_contact_phone) }}">
                @error('public_contact_phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            @foreach(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X', 'linkedin' => 'LinkedIn'] as $network => $label)
                <div class="col-md-6">
                    <label class="form-label" for="social_{{ $network }}">{{ $label }} URL</label>
                    <input class="form-control" id="social_{{ $network }}" name="social_links[{{ $network }}]" type="url" value="{{ old('social_links.'.$network, data_get($mpoAdmin->social_links, $network)) }}" placeholder="https://">
                    @error('social_links.'.$network)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            @endforeach
            <div class="col-md-6">
                <label class="form-label" for="office_hours">Office Hours</label>
                <input class="form-control" id="office_hours" name="office_hours" maxlength="500" value="{{ old('office_hours', $mpoAdmin->office_hours) }}">
                @error('office_hours')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="website">Website</label>
                <input class="form-control" id="website" name="website" type="url" value="{{ old('website', $mpoAdmin->website_url) }}" placeholder="https://example.com">
                @error('website')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <button class="btn btn-outline-primary" type="submit">Save Profile Draft</button>
        <button class="btn btn-primary" type="submit" name="mark_complete" value="1">Save and Mark Complete</button>
    </div>
</form>
@endsection
