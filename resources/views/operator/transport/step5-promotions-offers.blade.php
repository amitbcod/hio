@extends('layouts.app')

@section('title', 'Transport Step 5 | Operator Dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @php $currentStep = 5; @endphp
            @include('operator.transport._steps_sidebar')
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px; margin-top:40px">
                <h2 style="font-weight:700;margin:0;">Step 5: Promotions & Offers</h2>
                <p style="margin:8px 0 0 0;color:#666;">Add promotions, offers, or discount information for your transport service.</p>
            </div>

            <form method="POST" action="{{ route('operator.transport.step5.save', $transport->id) }}">
                @csrf
                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    <div class="mb-3">
                        <label class="form-label">Promotion Type</label>
                        <select name="promotions_offers[promo_type]" class="form-control">
                            <option value="">Select promotion type</option>
                            <option value="% off" {{ old('promotions_offers.promo_type', data_get($transport->promotions_offers, 'promo_type')) === '% off' ? 'selected' : '' }}>% off</option>
                            <option value="Fixed amount" {{ old('promotions_offers.promo_type', data_get($transport->promotions_offers, 'promo_type')) === 'Fixed amount' ? 'selected' : '' }}>Fixed amount</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Promotion Value</label>
                        <input type="number" step="0.01" name="promotions_offers[promo_value]" class="form-control" value="{{ old('promotions_offers.promo_value', data_get($transport->promotions_offers, 'promo_value')) }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valid From</label>
                            <input type="datetime-local" name="promotions_offers[valid_from]" class="form-control" value="{{ old('promotions_offers.valid_from', data_get($transport->promotions_offers, 'valid_from')) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valid To</label>
                            <input type="datetime-local" name="promotions_offers[valid_to]" class="form-control" value="{{ old('promotions_offers.valid_to', data_get($transport->promotions_offers, 'valid_to')) }}">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save & Continue</button>
            </form>
        </div>
    </div>
</div>
@endsection
