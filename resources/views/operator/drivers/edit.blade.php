@extends('layouts.app')

@section('title', 'Edit Driver | Operator Dashboard')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            @include('operator.drivers._sidebar')
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                <h2 style="font-weight:700;margin:0;">Edit Driver - {{ $driver->driver_name ?? $driver->full_name }}</h2>
                <p style="margin:8px 0 0 0;color:#666;">Update driver details</p>
            </div>

            @if ($errors->any())
                <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;">
                    <h5 style="margin-top:0;color:#c62828;">❌ Validation Errors:</h5>
                    @foreach ($errors->all() as $error)
                        <div style="margin-bottom:4px;">• {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('operator.drivers.update', $driver->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')

                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Personal Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label style="font-weight:600;">Full Name <span style="color:#d32f2f">*</span></label>
                            <input type="text" name="driver_name" class="form-control @error('driver_name') is-invalid @enderror" value="{{ old('driver_name', $driver->driver_name ?? $driver->full_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label style="font-weight:600;">Mobile No</label>
                            <input type="tel" name="driver_mobile_no" class="form-control @error('driver_mobile_no') is-invalid @enderror" value="{{ old('driver_mobile_no', $driver->driver_mobile_no ?? $driver->mobile ?? '') }}">
                        </div>
                    </div>
                </div>

                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Driver License</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label style="font-weight:600;">License Number <span style="color:#d32f2f">*</span></label>
                            <input type="text" name="driver_license_no" class="form-control @error('driver_license_no') is-invalid @enderror" value="{{ old('driver_license_no', $driver->driver_license_no ?? $driver->license_number ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label style="font-weight:600;">License Expiry Date <span style="color:#d32f2f">*</span></label>
                            <input type="date" name="license_expiry_date" class="form-control @error('license_expiry_date') is-invalid @enderror" value="{{ old('license_expiry_date', ($driver->license_expiry_date ?? $driver->license_expiry)?->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>

                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Operational Details</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label style="font-weight:600;">Status</label>
                            <select name="driver_status" class="form-control @error('driver_status') is-invalid @enderror">
                                <option value="Active" {{ old('driver_status', $driver->driver_status) === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Off Duty" {{ old('driver_status', $driver->driver_status) === 'Off Duty' ? 'selected' : '' }}>Off Duty</option>
                                <option value="Sick Leave" {{ old('driver_status', $driver->driver_status) === 'Sick Leave' ? 'selected' : '' }}>Sick Leave</option>
                                <option value="Suspended" {{ old('driver_status', $driver->driver_status) === 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="Inactive" {{ old('driver_status', $driver->driver_status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label style="font-weight:600;">Shift Start</label>
                            <input type="time" name="shift_start_time" class="form-control @error('shift_start_time') is-invalid @enderror" value="{{ old('shift_start_time', $driver->shift_start_time ? substr($driver->shift_start_time, 0, 5) : '') }}">
                        </div>

                        <div class="col-md-4">
                            <label style="font-weight:600;">Shift End</label>
                            <input type="time" name="shift_end_time" class="form-control @error('shift_end_time') is-invalid @enderror" value="{{ old('shift_end_time', $driver->shift_end_time ? substr($driver->shift_end_time, 0, 5) : '') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label style="font-weight:600;">Break (min)</label>
                            <input type="number" name="driver_break_min" class="form-control @error('driver_break_min') is-invalid @enderror" value="{{ old('driver_break_min', $driver->driver_break_min ?? 30) }}" min="0">
                        </div>
                        <div class="col-md-4">
                            <label style="font-weight:600;">Languages</label>
                            <input type="text" name="languages" class="form-control @error('languages') is-invalid @enderror" value="{{ old('languages', $driver->languages) }}" placeholder="English, French">
                        </div>
                        <div class="col-md-4">
                            <label style="font-weight:600;">Home Zone</label>
                            <input type="text" name="home_zone" class="form-control @error('home_zone') is-invalid @enderror" value="{{ old('home_zone', $driver->home_zone) }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label style="font-weight:600;">Remarks</label>
                            <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3">{{ old('remarks', $driver->remarks) }}</textarea>
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:12px;">
                    <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;">Update Driver</button>
                    <a href="{{ route('operator.drivers.index') }}" class="btn" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
