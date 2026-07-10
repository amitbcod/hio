@extends('layouts.app')

@section('title', 'Transport Setup | Operator Dashboard')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            @include('operator.transport._steps_wizard_sidebar', ['step' => $step ?? 5])
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                <h2 style="font-weight:700;margin:0;">Step 5: {{ $title ?? 'Promotions & Offers' }}</h2>
                <p style="margin:8px 0 0 0;color:#666;">{{ $description ?? 'Add promotions, offers, or discount information for your transport operations.' }}</p>
            </div>

            <form method="POST" action="{{ route('operator.transport.promotions-offers.save') }}">
                @csrf
                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    <div class="mb-3">
                        <label class="form-label">Offer Summary</label>
                        <input type="text" name="promotions_offers[summary]" class="form-control" value="{{ old('promotions_offers.summary', data_get($transportSettings, 'promotions_offers.summary')) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Offer Details</label>
                        <textarea name="promotions_offers[details]" class="form-control" rows="5">{{ old('promotions_offers.details', data_get($transportSettings, 'promotions_offers.details')) }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save & Continue</button>
            </form>
        </div>
    </div>
</div>
@endsection
