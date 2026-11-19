@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <h1 class="h3">{{ $administrator->exists ? 'Edit MPO' : 'Create MPO' }}</h1>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    @php
        $selectedCountryId = old('country_id', $administrator->country_id ?: $countries->first()?->id);
        $selectedCountry = $countries->firstWhere('id', (int) $selectedCountryId);
        $selectedStatus = old('status', $administrator->status ?: 'active');
    @endphp
    <form method="POST" action="{{ $administrator->exists ? route('admin.country-admins.update', $administrator) : route('admin.country-admins.store') }}" @if(!$administrator->exists) enctype="multipart/form-data" @endif>
        @csrf
        @if($administrator->exists) @method('PUT') @endif
        <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $administrator->name) }}" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $administrator->email) }}" required></div>
        <div class="mb-3">
            <label class="form-label" for="country_id">Country</label>
            <select class="form-select" id="country_id" name="country_id" required>
                @foreach($countries as $country)
                    <option value="{{ $country->id }}" data-vat-percentage="{{ $country->vat_percentage ?? '0.0000' }}" @selected((string) $selectedCountryId === (string) $country->id)>{{ $country->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label" for="vat_percentage">Country VAT / Tax (%)</label>
            <input class="form-control" type="number" id="vat_percentage" name="vat_percentage" min="0" max="100" step="0.0001" value="{{ old('vat_percentage', $selectedCountry?->vat_percentage ?? '0.0000') }}" required>
            <div class="form-text">This rate is saved to the selected country and used for MPO accounting.</div>
        </div>
        <div class="mb-3"><label class="form-label">Status</label><select class="form-select" name="status" required><option value="active" @selected($selectedStatus === 'active')>Active</option><option value="inactive" @selected($selectedStatus === 'inactive')>Inactive</option></select></div>
        @unless($administrator->exists)
            <div class="card mb-3">
                <div class="card-header">Controlled Agreement Terms</div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="agreement_type">Agreement Type</label>
                        <select class="form-select" id="agreement_type" name="agreement_type" required>
                            <option value="mpo" @selected(old('agreement_type', 'mpo') === 'mpo')>MPO Agreement</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="start_date">Start Date</label>
                        <input class="form-control" id="start_date" name="start_date" type="date" value="{{ old('start_date', now()->toDateString()) }}" required>
                        <div class="form-text">System onboarding date: {{ $selectedStatus === 'active' ? now()->format('d M Y') : 'Not recorded' }}. Start date is independently configurable.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="commission_model">Commission Model</label>
                        <select class="form-select" id="commission_model" name="commission_model" required>
                            <option value="percentage" @selected(old('commission_model', 'percentage') === 'percentage')>Percentage</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="commission_value">Commission Value (%)</label>
                        <input class="form-control" id="commission_value" name="commission_value" type="number" min="0" max="100" step="0.0001" value="{{ old('commission_value') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="marketing_contribution_percent">Marketing Contribution (%)</label>
                        <input class="form-control" id="marketing_contribution_percent" name="marketing_contribution_percent" type="number" min="0" max="100" step="0.0001" value="{{ old('marketing_contribution_percent') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="marketing_contribution_payer">Marketing Contribution Funded By</label>
                        <select class="form-select" id="marketing_contribution_payer" name="marketing_contribution_payer">
                            <option value="" @selected(old('marketing_contribution_payer', '') === '')>Not configured</option>
                            <option value="mpo" @selected(old('marketing_contribution_payer') === 'mpo')>MPO</option>
                            <option value="operator" @selected(old('marketing_contribution_payer') === 'operator')>Operator</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="responsibilities_document">Responsibilities document (PDF or DOCX, max 10 MB)</label>
                        <input class="form-control" id="responsibilities_document" name="responsibilities_document" type="file" accept=".pdf,.docx">
                    </div>
                </div>
            </div>
        @endunless
        <div class="mb-3"><label class="form-label">{{ $administrator->exists ? 'Reset password (optional)' : 'Password' }}</label><input class="form-control" type="password" name="password" @required(!$administrator->exists) minlength="8" autocomplete="new-password"></div>
        <div class="mb-3"><label class="form-label">Confirm password</label><input class="form-control" type="password" name="password_confirmation" @required(!$administrator->exists) minlength="8" autocomplete="new-password"></div>
        <button class="btn btn-primary" type="submit">Save MPO</button>
        @if($administrator->exists && $administrator->admin_type === 'mpo')
            <a class="btn btn-outline-primary" href="{{ route('admin.country-admins.profile.edit', $administrator) }}">Edit MPO Profile</a>
            <a class="btn btn-outline-primary" href="{{ route('admin.country-admins.compliance.show', $administrator) }}">Review Compliance</a>
            <a class="btn btn-outline-primary" href="{{ route('admin.country-admins.users', $administrator) }}">Manage MPO Users</a>
        @endif
        <a class="btn btn-secondary" href="{{ route('admin.country-admins.index') }}">Cancel</a>
    </form>
</div>
<script>
    document.getElementById('country_id')?.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('vat_percentage').value = selectedOption.dataset.vatPercentage || '0.0000';
    });
</script>
@endsection
