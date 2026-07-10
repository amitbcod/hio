@extends('layouts.app')

@section('title', 'Add New Driver | Operator Dashboard')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            @include('operator.drivers._sidebar')
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                <h2 style="font-weight:700;margin:0;">Add New Driver</h2>
                <p style="margin:8px 0 0 0;color:#666;">Fill in the driver details</p>
            </div>

            @if ($errors->any())
                <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;">
                    <h5 style="margin-top:0;color:#c62828;">❌ Validation Errors:</h5>
                    @foreach ($errors->all() as $error)
                        <div style="margin-bottom:4px;">• {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('operator.drivers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    {{-- Personal --}}
                    <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Personal Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label style="font-weight:600;">Driver Name <span style="color:#d32f2f">*</span></label>
                            <input type="text" name="driver_name" class="form-control @error('driver_name') is-invalid @enderror" value="{{ old('driver_name') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label style="font-weight:600;">Mobile No</label>
                            <input type="tel" name="driver_mobile_no" class="form-control @error('driver_mobile_no') is-invalid @enderror" value="{{ old('driver_mobile_no') }}">
                        </div>
                    </div>
                </div>

                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    {{-- Licence --}}
                    <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Driver License</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label style="font-weight:600;">License Number <span style="color:#d32f2f">*</span></label>
                            <input type="text" name="driver_license_no" class="form-control @error('driver_license_no') is-invalid @enderror" value="{{ old('driver_license_no') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label style="font-weight:600;">License Expiry Date <span style="color:#d32f2f">*</span></label>
                            <input type="date" name="license_expiry_date" class="form-control @error('license_expiry_date') is-invalid @enderror" value="{{ old('license_expiry_date') }}" required>
                        </div>
                    </div>
                </div>

                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    {{-- Operational details --}}
                    <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Operational Details</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label style="font-weight:600;">Status</label>
                            <select name="driver_status" class="form-control @error('driver_status') is-invalid @enderror">
                                <option value="Active" {{ old('driver_status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Off Duty" {{ old('driver_status') === 'Off Duty' ? 'selected' : '' }}>Off Duty</option>
                                <option value="Sick Leave" {{ old('driver_status') === 'Sick Leave' ? 'selected' : '' }}>Sick Leave</option>
                                <option value="Suspended" {{ old('driver_status') === 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="Inactive" {{ old('driver_status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label style="font-weight:600;">Shift Start</label>
                            <input type="time" name="shift_start_time" class="form-control @error('shift_start_time') is-invalid @enderror" value="{{ old('shift_start_time') }}">
                        </div>

                        <div class="col-md-4">
                            <label style="font-weight:600;">Shift End</label>
                            <input type="time" name="shift_end_time" class="form-control @error('shift_end_time') is-invalid @enderror" value="{{ old('shift_end_time') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label style="font-weight:600;">Break (min)</label>
                            <input type="number" name="driver_break_min" class="form-control @error('driver_break_min') is-invalid @enderror" value="{{ old('driver_break_min', 30) }}" min="0">
                        </div>
                        <div class="col-md-4">
                            <label style="font-weight:600;">Languages</label>
                            <input type="text" name="languages" class="form-control @error('languages') is-invalid @enderror" value="{{ old('languages') }}" placeholder="English, French">
                        </div>
                        <div class="col-md-4">
                            <label style="font-weight:600;">Home Zone</label>
                            <input type="text" name="home_zone" class="form-control @error('home_zone') is-invalid @enderror" value="{{ old('home_zone') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label style="font-weight:600;">Remarks</label>
                            <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3">{{ old('remarks') }}</textarea>
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:12px;">
                    <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;">Create Driver</button>
                    <a href="{{ route('operator.drivers.index') }}" class="btn" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
