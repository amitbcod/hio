@extends('layouts.admin')

@php $sidebar = 'admin.packages._steps_sidebar'; $currentStep = 1; @endphp

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-10">
            <div style="background:#fff;border-radius:12px;padding:18px;margin-bottom:16px;">
                <h1 style="margin:0;font-weight:700;">Add New Package</h1>
                <p style="color:#666;margin-top:6px;">Start by providing your package's basic information</p>
            </div>

            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
                <form id="package-step1-form" method="POST" action="{{ (isset($package) && $package->exists) ? route('admin.packages.update', $package->id) : route('admin.packages.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Package Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., 4 Nights Coco Verde Escape" value="{{ old('name', $package->name ?? '') }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">No. of Days *</label>
                            <input type="number" name="no_of_days" class="form-control" placeholder="e.g., 4" value="{{ old('no_of_days', $package->no_of_days ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">No. of Nights *</label>
                            <input type="number" name="no_of_nights" class="form-control" placeholder="e.g., 3" value="{{ old('no_of_nights', $package->no_of_nights ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Booking Cutoff (days) *</label>
                            <input type="number" name="booking_cutoff_days" class="form-control" placeholder="e.g., 15" value="{{ old('booking_cutoff_days', $package->booking_cutoff_days ?? '') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Package Available From *</label>
                            <input type="date" name="available_from" class="form-control" value="{{ old('available_from', optional($package->available_from)->toDateString() ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Package Available To *</label>
                            <input type="date" name="available_to" class="form-control" value="{{ old('available_to', optional($package->available_to)->toDateString() ?? '') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Minimum Pax *</label>
                            <input type="number" name="minimum_pax" class="form-control" placeholder="e.g., 1" value="{{ old('minimum_pax', $package->minimum_pax ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maximum Pax *</label>
                            <input type="number" name="maximum_pax" class="form-control" placeholder="e.g., 10" value="{{ old('maximum_pax', $package->maximum_pax ?? '') }}">
                        </div>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:12px;">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-light">Back</a>
                        <button type="submit" class="btn btn-primary next-prefix">Next: Add Package</button>
                    </div>
                </form>
            </div>
            @push('styles')
                <style>
                    .next-prefix { min-width: 170px; }
                </style>
            @endpush
        </div>
    </div>
</div>
@endsection
