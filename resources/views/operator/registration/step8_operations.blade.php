@extends('layouts.app')

@section('progressbar')
    @php
        // Prefer business progress
        $progress = !empty(auth()->user()->business_id)
            ? \App\Models\OperatorRegistrationProgress::where('business_id', auth()->user()->business_id)->first()
            : \App\Models\OperatorRegistrationProgress::where('operator_id', auth()->user()->operator_id ?? null)->first();
        $completionPercent = isset($progress) ? round((($progress->step2_profile ?? 0)
            + ($progress->step3_legal ?? 0)
            + ($progress->step4_system_process ?? 0)
            + ($progress->step5_collaboration ?? 0)
            + ($progress->step6_users ?? 0)
            + ($progress->step7_accounting ?? 0)
            + ($progress->step8_operations ?? 0)
            + ($progress->step9_review ?? 0)) / 8 * 100) : 0;
    @endphp
    @include('operator.registration._progress', ['completionPercent' => $completionPercent])
@endsection

@section('content')
    @php $currentStep = 8; @endphp
    <div class="col-md-3">
        @include('operator.registration._sidebar', ['currentStep' => $currentStep, 'progress' => $progress ?? null])
    </div>
    <div class="col-md-9 d-flex align-items-center justify-content-center" style="min-height: 90vh;">
        <div style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 32px 32px 24px 32px; width: 100%; max-width: 900px;">
            <h2 style="font-weight: bold; margin-bottom: 24px;">SERVICE OPERATIONS</h2>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('operator.register.step8') }}">
                @csrf
                <div class="card mb-4">
                    <div class="card-body">
                        <h5>Service Details</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Service Location *</label>
                                <select id="service_location" name="service_location" class="form-control" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background-color: #fff;">
                                    <option value="">-- select --</option>
                                    <option value="fixed" {{ old('service_location', $serviceOps?->service_location) == 'fixed' ? 'selected' : '' }}>Fixed Location</option>
                                    <option value="gps" {{ old('service_location', $serviceOps?->service_location) == 'gps' ? 'selected' : '' }}>GPS / Mobile</option>
                                    <option value="multiple" {{ old('service_location', $serviceOps?->service_location) == 'multiple' ? 'selected' : '' }}>Multiple Locations</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3" id="gpsCoordinatesField" style="display: {{ old('service_location', $serviceOps?->service_location) == 'gps' ? 'block' : 'none' }};">
                                <label>GPS Coordinates (lat,lng)</label>
                                <input type="text" name="gps_coordinates" class="form-control" value="{{ old('gps_coordinates', $serviceOps?->gps_coordinates) }}" placeholder="e.g. -20.1609,57.5012">
                            </div>
                        </div>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var locationSelect = document.getElementById('service_location');
                            var gpsField = document.getElementById('gpsCoordinatesField');
                            function toggleGpsField() {
                                gpsField.style.display = locationSelect.value === 'gps' ? 'block' : 'none';
                            }
                            locationSelect.addEventListener('change', toggleGpsField);
                            toggleGpsField();
                        });
                        </script>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label>Operating Area *</label><br>
                                @php
                                    $areas = ['North','South','East','West','Central'];
                                    $selectedAreas = old('operating_areas', $serviceOps?->operating_areas ? json_decode($serviceOps->operating_areas, true) : []);
                                @endphp
                                @foreach($areas as $area)
                                    <label class="me-2"><input type="checkbox" name="operating_areas[]" value="{{ $area }}" {{ in_array($area, $selectedAreas) ? 'checked' : '' }}> {{ $area }}</label>
                                @endforeach
                                <label class="ms-2"><input type="checkbox" name="is_nationwide" value="1" {{ old('is_nationwide', $serviceOps?->is_nationwide) ? 'checked' : '' }}> Nationwide</label>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Pickup / Drop-off Options</label>
                                <select name="has_pickup_dropoff" class="form-control" id="has_pickup_dropoff">
                                    <option value="0" {{ old('has_pickup_dropoff', $serviceOps?->has_pickup_dropoff) == 0 ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('has_pickup_dropoff', $serviceOps?->has_pickup_dropoff) == 1 ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                             <div class="col-md-6">
                                    <label>Pickup / Drop-off Details</label>
                                    <input type="text" name="pickup_dropoff_details" class="form-control" value="{{ old('pickup_dropoff_details', $serviceOps?->pickup_dropoff_details) }}">
                            </div>
                        </div>
                        <div id="pickupDropoffFields" style="display: {{ old('has_pickup_dropoff', $serviceOps?->has_pickup_dropoff) == 1 ? 'block' : 'none' }};">
                            <div class="row mb-3">
                                <div class="col-md-6 d-flex align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="pickup_dropoff_free" id="pickup_dropoff_free" value="1" {{ old('pickup_dropoff_free', $serviceOps?->pickup_dropoff_free) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pickup_dropoff_free">Free Of Charge</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label>Surcharge amount</label>
                                    <input type="number" step="0.01" name="pickup_dropoff_surcharge" class="form-control" value="{{ old('pickup_dropoff_surcharge', $serviceOps?->pickup_dropoff_surcharge) }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                               
                                <div class="col-md-6">
                                    <label>Additional details</label>
                                    <textarea name="service_notes" class="form-control">{{ old('service_notes', $serviceOps?->service_notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var select = document.getElementById('has_pickup_dropoff');
                            var fields = document.getElementById('pickupDropoffFields');
                            select.addEventListener('change', function() {
                                if (select.value == '1') {
                                    fields.style.display = 'block';
                                } else {
                                    fields.style.display = 'none';
                                }
                            });
                        });
                        </script>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Emergency Contact *</label>
                                <input type="text" name="emergency_contact_name" class="form-control" required value="{{ old('emergency_contact_name', $serviceOps?->emergency_contact_name) }}">
                            </div>
                            <div class="col-md-4">
                                <label>Emergency Phone *</label>
                                <input type="text" name="emergency_contact_phone" class="form-control" required maxlength="20" pattern="[0-9+\-\s()]{6,20}" title="Use only digits, +, -, spaces, and parentheses (6-20 chars)." value="{{ old('emergency_contact_phone', $serviceOps?->emergency_contact_phone) }}">
                            </div>
                            <div class="col-md-4">
                                <label>Emergency Email *</label>
                                <input type="email" name="emergency_contact_email" class="form-control" required value="{{ old('emergency_contact_email', $serviceOps?->emergency_contact_email) }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ route('operator.register.step7') }}" class="btn btn-secondary ms-2">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    var freeChargeCheckbox = document.getElementById('pickup_dropoff_free');
    var surchargeInput = document.querySelector('input[name="pickup_dropoff_surcharge"]');

    // Function to toggle the surcharge field
    function toggleSurchargeField() {
        if (freeChargeCheckbox.checked) {
            surchargeInput.value = '0';  // Set surcharge to 0
            surchargeInput.readOnly = true;  // Make the field not editable
        } else {
            surchargeInput.readOnly = false;  // Make the field editable again
        }
    }

    // Initialize field state based on checkbox value
    toggleSurchargeField();

    // Add event listener to handle checkbox changes
    freeChargeCheckbox.addEventListener('change', toggleSurchargeField);
});
</script>