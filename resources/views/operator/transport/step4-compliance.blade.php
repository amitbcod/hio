@extends('layouts.app')

@section('title', 'Transport Step 4 | Operator Dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-3 net-section">
            @php $currentStep = 4; @endphp
            @include('operator.transport._steps_sidebar')
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px; margin-top:40px">
                <h2 style="font-weight:700;margin:0;">Step 4: Compliance</h2>
                <p style="margin:8px 0 0 0;color:#666;">Add license and insurance details for your vehicle.</p>
            </div>

            <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                <form method="POST" action="{{ route('operator.transport.step4.save', $transport->id) }}">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label style="font-weight:600;">Insurance Provider</label>
                            <input type="text" name="insurance_provider" class="form-control" value="{{ old('insurance_provider', $transport->insurance_provider) }}">
                        </div>
                        <div class="col-md-6">
                            <label style="font-weight:600;">Insurance Policy Number</label>
                            <input type="text" name="insurance_policy_number" class="form-control" value="{{ old('insurance_policy_number', $transport->insurance_policy_number) }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label style="font-weight:600;">License Number</label>
                            <input type="text" name="license_number" class="form-control" value="{{ old('license_number', $transport->license_number) }}">
                        </div>
                        <div class="col-md-6">
                            <label style="font-weight:600;">License Expiration</label>
                            <input type="date" name="license_expiration" class="form-control" value="{{ old('license_expiration', optional($transport->license_expiration)->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save Step 4</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
