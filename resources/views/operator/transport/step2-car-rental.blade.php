@extends('layouts.app')

@section('title', 'Transport Step 2 - Car Rental Pricing | Operator Dashboard')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3 net-section">
            @php $currentStep = '2b'; @endphp
            @include('operator.transport._steps_sidebar')
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                <h2 style="font-weight:700;margin:0;">Step 2-B: Car Rental Pricing</h2>
                <p style="margin:8px 0 0 0;color:#666;">Set per-hour and block pricing for car rental services for this vehicle.</p>
            </div>

            <form method="POST" action="{{ route('operator.transport.step2.car_rental.save', $transport->id) }}">
                @csrf
                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    @php
                        $prices = old('car_rental_prices', $transport->car_rental_prices ?? []);
                    @endphp

                    <div class="mb-3 row">
                        <label class="col-md-3 form-label">Per Hour Price (USD)</label>
                        <div class="col-md-9"><input type="number" step="0.01" min="0" name="car_rental_prices[per_hour]" class="form-control" value="{{ $prices['per_hour'] ?? '' }}"></div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-3 form-label">4 Hour Price (USD)</label>
                        <div class="col-md-9"><input type="number" step="0.01" min="0" name="car_rental_prices[per_4h]" class="form-control" value="{{ $prices['per_4h'] ?? '' }}"></div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-3 form-label">8 Hour Price (USD)</label>
                        <div class="col-md-9"><input type="number" step="0.01" min="0" name="car_rental_prices[per_8h]" class="form-control" value="{{ $prices['per_8h'] ?? '' }}"></div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-3 form-label">12 Hour Price (USD)</label>
                        <div class="col-md-9"><input type="number" step="0.01" min="0" name="car_rental_prices[per_12h]" class="form-control" value="{{ $prices['per_12h'] ?? '' }}"></div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-3 form-label">24 Hour Price (USD)</label>
                        <div class="col-md-9"><input type="number" step="0.01" min="0" name="car_rental_prices[per_24h]" class="form-control" value="{{ $prices['per_24h'] ?? '' }}"></div>
                    </div>
                </div>

                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save Car Rental Prices</button>
            </form>
        </div>
    </div>
</div>
@endsection
