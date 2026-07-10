@extends('layouts.app')

@section('title', 'Transport Setup | Operator Dashboard')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            @include('operator.transport._steps_wizard_sidebar', ['step' => $step ?? 1])
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                <h2 style="font-weight:700;margin:0;">Step 1: {{ $title ?? 'Transport Basic' }}</h2>
                <p style="margin:8px 0 0 0;color:#666;">{{ $description ?? 'Enter the first set of transport details.' }}</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('operator.transport.basic-details.save') }}">
                @csrf
                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Service Name</label>
                            <input type="text" name="transport_basic[service_name]" class="form-control" required value="{{ old('transport_basic.service_name', data_get($transportSettings, 'transport_basic.service_name')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transport Type</label>
                            @php
                                $transportTypes = ['Airport', 'Route', 'Hourly'];
                                $transportTypeValue = old('transport_basic.transport_type', data_get($transportSettings, 'transport_basic.transport_type'));
                            @endphp
                            <select name="transport_basic[transport_type]" class="form-control" required>
                                <option value="">Select Transport Type</option>
                                @foreach($transportTypes as $transportType)
                                    <option value="{{ $transportType }}" {{ $transportTypeValue === $transportType ? 'selected' : '' }}>{{ $transportType }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Trip Type</label>
                            @php
                                $tripTypes = ['One-way', 'Round-trip'];
                                $tripTypeValue = old('transport_basic.trip_type', data_get($transportSettings, 'transport_basic.trip_type'));
                            @endphp
                            <select name="transport_basic[trip_type]" class="form-control" required>
                                <option value="">Select Trip Type</option>
                                @foreach($tripTypes as $tripType)
                                    <option value="{{ $tripType }}" {{ $tripTypeValue === $tripType ? 'selected' : '' }}>{{ $tripType }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transport Service Pattern</label>
                            @php
                                $servicePatterns = [
                                    'ONE_WAY_AIRPORT_ARRIVAL',
                                    'ONE_WAY_AIRPORT_DEPARTURE',
                                    'ROUND_TRIP_AIRPORT',
                                    'ACTIVITY_OUTBOUND_RETURN',
                                    'FULL_DAY_SIGHTSEEING_LOOP',
                                    'SHARED_SEAT_ARRIVAL',
                                    'SHARED_SEAT_DEPARTURE',
                                ];
                                $patternValue = old('transport_basic.transport_service_pattern', data_get($transportSettings, 'transport_basic.transport_service_pattern'));
                            @endphp
                            <select name="transport_basic[transport_service_pattern]" class="form-control" required>
                                <option value="">Select Service Pattern</option>
                                @foreach($servicePatterns as $pattern)
                                    <option value="{{ $pattern }}" {{ $patternValue === $pattern ? 'selected' : '' }}>{{ $pattern }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Service Area</label>
                            @php
                                $serviceAreas = ['Mauritius – East', 'Mauritius – West', 'Mauritius – South', 'Mauritius – North'];
                                $serviceAreaValue = old('transport_basic.service_area', data_get($transportSettings, 'transport_basic.service_area'));
                            @endphp
                            <select name="transport_basic[service_area]" class="form-control">
                                <option value="">Select Service Area</option>
                                @foreach($serviceAreas as $area)
                                    <option value="{{ $area }}" {{ $serviceAreaValue === $area ? 'selected' : '' }}>{{ $area }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Setup Status</label>
                            <select name="transport_basic[status]" class="form-control" required>
                                @php
                                    $statusOptions = ['Draft', 'Submitted', 'Approved', 'Published', 'Suspended'];
                                    $statusValue = old('transport_basic.status', data_get($transportSettings, 'transport_basic.status'));
                                @endphp
                                @foreach($statusOptions as $status)
                                    <option value="{{ $status }}" {{ $statusValue === $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save & Continue</button>
            </form>
        </div>
    </div>
</div>
@endsection
