@extends('layouts.agent')
@section('title', 'Agent Operations')
@section('content')
<h1 class="h3 mb-4">Agent Operations</h1>
<p>Agent ID: <strong>{{ $agent->agent_id }}</strong> · Country: <strong>{{ $agent->country?->name }}</strong></p>
<form method="POST" action="{{ route('agent.operations.save') }}" class="card"><div class="card-body">@csrf
    <div class="row"><div class="col-md-6 mb-3"><label class="form-label">Preferred Language UI</label><select class="form-select" name="preferred_language_ui"><option value="">Select</option>@foreach(['EN','FR'] as $lang)<option @selected(old('preferred_language_ui', $operations?->preferred_language_ui) === $lang)>{{ $lang }}</option>@endforeach</select></div>
    <div class="col-md-6 mb-3"><label class="form-label">Preferred Communication Language</label><select class="form-select" name="preferred_language_communication"><option value="">Select</option>@foreach(['EN','FR'] as $lang)<option @selected(old('preferred_language_communication', $operations?->preferred_language_communication) === $lang)>{{ $lang }}</option>@endforeach</select></div></div>
    <div class="mb-3"><label class="form-label">Time Zone</label><select class="form-select" name="time_zone"><option value="">Select</option>@foreach($timeZones as $zone)<option @selected(old('time_zone', $operations?->time_zone) === $zone)>{{ $zone }}</option>@endforeach</select></div>
    <div class="mb-3"><label class="form-label">Primary Currency</label><select class="form-select" name="primary_currency"><option value="">Select</option>@if($currencyCode)<option value="{{ $currencyCode }}" @selected(old('primary_currency', $operations?->primary_currency) === $currencyCode)>{{ $currencyCode }}</option>@else<option value="" disabled>Currency not configured for {{ $agent->country?->name ?? 'your country' }}</option>@endif</select><div class="form-text">Currency is limited to the configured currency for your registered country.</div></div>
    @php($bank = $operations?->bank_details ?? [])
    <fieldset class="border rounded p-3 mb-3"><legend class="float-none w-auto px-2 fs-6">Bank Details (optional)</legend>
        <div class="mb-3"><label class="form-label">Account Holder</label><input class="form-control" name="bank_account_holder" value="{{ old('bank_account_holder', $bank['account_holder'] ?? '') }}"></div>
        <div class="mb-3"><label class="form-label">IBAN</label><input class="form-control" name="bank_iban" value="{{ old('bank_iban', $bank['iban'] ?? '') }}"></div>
        <div class="mb-3"><label class="form-label">SWIFT</label><input class="form-control" name="bank_swift" value="{{ old('bank_swift', $bank['swift'] ?? '') }}"></div>
        <div class="mb-3"><label class="form-label">Currency</label><select class="form-select" name="bank_currency"><option value="">Select</option>@if($currencyCode)<option value="{{ $currencyCode }}" @selected(old('bank_currency', $bank['currency'] ?? '') === $currencyCode)>{{ $currencyCode }}</option>@else<option value="" disabled>Currency not configured for {{ $agent->country?->name ?? 'your country' }}</option>@endif</select></div>
    </fieldset>
    <fieldset class="border rounded p-3 mb-3"><legend class="float-none w-auto px-2 fs-6">Emergency Contact</legend>
        <div class="mb-3"><label class="form-label">Name *</label><input class="form-control" name="emergency_contact_name" value="{{ old('emergency_contact_name', $operations?->emergency_contact_name) }}" required></div>
        <div class="mb-3"><label class="form-label">Mobile / WhatsApp (E.164) *</label><input class="form-control" name="emergency_contact_mobile" value="{{ old('emergency_contact_mobile', $operations?->emergency_contact_mobile) }}" required></div>
    </fieldset>
    @if($operations?->commission_type || $operations?->commission_value !== null)<p>Agreement commission: {{ $operations->commission_type }} {{ $operations->commission_value }}</p>@endif
    <button class="btn btn-primary">Save and Finish</button>
</div></form>
@endsection
