@extends('layouts.app')

@section('title', 'Transport Setup | Operator Dashboard')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            @include('operator.transport._steps_wizard_sidebar', ['step' => $step ?? 3])
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                <h2 style="font-weight:700;margin:0;">Step 3: {{ $title ?? 'Policies and Rules' }}</h2>
                <p style="margin:8px 0 0 0;color:#666;">{{ $description ?? 'Set your transport policies and rules.' }}</p>
            </div>

            <form method="POST" action="{{ route('operator.transport.policies-rules.save') }}">
                @csrf
                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    <input type="hidden" name="policies_rules[cancellation_policy_id]" value="{{ old('policies_rules.cancellation_policy_id', data_get($transportSettings, 'policies_rules.cancellation_policy_id')) }}">
                    <div class="mb-3">
                        <label class="form-label">Cancellation Terms</label>
                        <textarea name="policies_rules[cancellation_terms]" class="form-control" rows="4" required>{{ old('policies_rules.cancellation_terms', data_get($transportSettings, 'policies_rules.cancellation_terms')) }}</textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Cut-off Hours</label>
                            <input type="number" name="policies_rules[cutoff_hours]" class="form-control" required value="{{ old('policies_rules.cutoff_hours', data_get($transportSettings, 'policies_rules.cutoff_hours')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Booking Cut-off Days</label>
                            <input type="number" name="policies_rules[booking_cutoff_days]" class="form-control" value="{{ old('policies_rules.booking_cutoff_days', data_get($transportSettings, 'policies_rules.booking_cutoff_days')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Booking Cut-off Time</label>
                            <input type="time" name="policies_rules[booking_cutoff_time]" class="form-control" value="{{ old('policies_rules.booking_cutoff_time', data_get($transportSettings, 'policies_rules.booking_cutoff_time')) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amendment Rules</label>
                        <textarea name="policies_rules[amendment_rules]" class="form-control" rows="4" required>{{ old('policies_rules.amendment_rules', data_get($transportSettings, 'policies_rules.amendment_rules')) }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save & Continue</button>
            </form>
        </div>
    </div>
</div>
@endsection
