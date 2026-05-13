@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <h2 style="font-weight:700;margin:0;">Edit Seasonal Pricing</h2>
                </div>

                {{-- Edit Seasonal Pricing Form --}}
                <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;">
                    <form method="POST" action="{{ route('operator.accommodation.step9.pricing.update', ['id' => $accommodation->id, 'pricing' => $pricing->id]) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;">Room</label>
                                <input type="text" class="form-control" value="{{ $pricing->room->room_name }} - {{ $pricing->room->room_type }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;">Rate Plan</label>
                                <input type="text" class="form-control" value="{{ $pricing->rate_name }} - {{ $pricing->meal_plan }}" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;">Valid From *</label>
                                <input type="date" name="valid_from" class="form-control" value="{{ $pricing->valid_from }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;">Valid To *</label>
                                <input type="date" name="valid_to" class="form-control" value="{{ $pricing->valid_to }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label style="font-weight:600;">Adult Rate (USD) *</label>
                                <input type="number" name="adult_rate" class="form-control" step="0.01" min="0" value="{{ $pricing->base_rate }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-weight:600;">Extra Adult Rate (USD) *</label>
                                <input type="number" name="extra_adult_rate" class="form-control" step="0.01" min="0" value="{{ $pricing->extra_adult_rate }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-weight:600;">Extra Bed Rate (USD)</label>
                                <input type="number" name="extra_bed" class="form-control" step="0.01" min="0" value="{{ $pricing->extra_bed_rate }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;">Children Rate (USD) *</label>
                                <input type="number" name="children_rate" class="form-control" step="0.01" min="0" value="{{ $pricing->children_rate }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;">Infant Rate (USD) *</label>
                                <input type="number" name="infant_rate" class="form-control" step="0.01" min="0" value="{{ $pricing->infant_rate }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;">Update Seasonal Pricing</button>
                                <a href="{{ route('operator.accommodation.step9.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:4px;margin-left:8px;">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection