@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Admin Settings</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label">Business Name</label>
            <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $admin->business_name) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Business Address</label>
            <textarea name="business_address" class="form-control" rows="3">{{ old('business_address', $admin->business_address) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $admin->phone_number) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">VAT Number</label>
            <input type="text" name="vat_number" class="form-control" value="{{ old('vat_number', $admin->vat_number) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">BRN Number</label>
            <input type="text" name="brn_number" class="form-control" value="{{ old('brn_number', $admin->brn_number) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Logo</label>
            <input type="file" name="logo" class="form-control" accept="image/*">
            @if($admin->logo_path)
                <div class="mt-2">
                    <img src="{{ asset($admin->logo_path) }}" alt="Admin Logo" style="max-width: 180px; max-height: 120px; border:1px solid #ddd; padding:4px; background:#fff;">
                </div>
            @endif
        </div>

        <hr class="my-4">
        <h4>Trip Action Deadline Defaults</h4>
        <p class="text-muted">Set how many hours before each action's target its deadline falls. These defaults do not replace individual overrides.</p>
        @php
            $deadlineSettings = [
                'guest_details' => 'Guest Details',
                'driver_license_expire' => 'Driver License Expire',
                'driver_assign' => 'Driver Assign',
                'vehicle_assign' => 'Vehicle Assign',
            ];
        @endphp
        @foreach($deadlineSettings as $actionType => $label)
            <div class="mb-3">
                <label class="form-label" for="trip-action-deadline-{{ $actionType }}">{{ $label }} — hours before target</label>
                <input
                    id="trip-action-deadline-{{ $actionType }}"
                    class="form-control"
                    type="number"
                    name="trip_action_deadlines[{{ $actionType }}]"
                    min="0"
                    max="8760"
                    step="1"
                    value="{{ old('trip_action_deadlines.' . $actionType, $tripActionDeadlineIntervals[$actionType] ?? 24) }}"
                    required
                >
            </div>
        @endforeach
        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>
@endsection
