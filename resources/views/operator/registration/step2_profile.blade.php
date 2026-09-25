
@extends('layouts.app')



@section('content')
    @php $currentStep = 2; @endphp
    <div id="sidebar" class="col-md-3 net-section">
        @include('operator.registration._sidebar', ['currentStep' => $currentStep, 'progress' => $progress ?? null])
    </div>
    <div class="col-md-6 align-items-center justify-content-center" style="min-height: 90vh;">
        <div class="media-fixed">
            <h2 style="font-weight: normal; margin-bottom: 24px;">PROFILE</h2>
            @if(isset($business) && $business)
                <div class="alert alert-info">Business: <strong>{{ $business->legal_name }}</strong> — ID: <code>{{ $business->business_id }}</code> (Status: {{ $business->status }})</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ url('operator/register/step2-profile') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group mb-3">
                    <label>Business Legal Name <span style="color:#d32f2f">*</span></label>
                    <input type="text" name="business_legal_name" class="form-control" required value="{{ old('business_legal_name', $businessLegalName ?? '') }}">
                </div>
                <div class="form-group mb-3">
                    <label>Business Registration Number</label>
                    <input type="text" name="business_registration_number" class="form-control" value="{{ old('business_registration_number', $profile->business_registration_number ?? '') }}">
                </div>
                <div class="form-group mb-3">
                    <label>Registered Address</label>
                    <input type="text" name="registered_address" class="form-control" value="{{ old('registered_address', $profile->registered_address ?? '') }}">
                </div>
                <div class="form-group mb-3">
                    <label>Operational Address</label>
                    <input type="text" name="operational_address" class="form-control" value="{{ old('operational_address', $profile->operational_address ?? '') }}">
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                    <label>Service Type <span style="color:#d32f2f">*</span></label>
                    @php
                        $selectedServiceTypes = old('service_types', isset($profile->service_types)
                            ? (is_array($profile->service_types) ? $profile->service_types : json_decode($profile->service_types, true))
                            : []);
                        $serviceProfiles = is_array($profile->contact_details ?? null)
                            ? ($profile->contact_details['service_profiles'] ?? [])
                            : [];
                    @endphp
                    <select id="service-types-select" name="service_types[]" class="form-control" multiple>
                        <option value="Accommodation" {{ in_array('Accommodation', $selectedServiceTypes ?? []) ? 'selected' : '' }}>Accommodation</option>
                        <option value="Transport" {{ in_array('Transport', $selectedServiceTypes ?? []) ? 'selected' : '' }}>Transport</option>
                        <option value="Activity" {{ in_array('Activity', $selectedServiceTypes ?? []) ? 'selected' : '' }}>Activity</option>
                        <option value="Food" {{ in_array('Food', $selectedServiceTypes ?? []) ? 'selected' : '' }}>Food</option>
                    </select>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>Years in Operation</label>
                        <input type="number" name="years_in_operation" class="form-control" value="{{ old('years_in_operation', $profile->years_in_operation ?? '') }}">
                    </div>
                </div>

                <div id="accommodation-section" class="service-profile-section form-group mb-3" style="display:none;clear:both;float:none;width:100%;border:1px solid #ddd;padding:16px;border-radius:8px;">
                    <h4 style="font-size:18px;margin:0 0 16px;text-align:center;">Accommodation Details</h4>
                    <label>Accommodation has Same as Business Address?</label>
                    <div class="d-flex gap-3 mt-2 mb-3">
                        <label class="d-flex align-items-center gap-2 mb-0"><input type="radio" name="accommodation_same_as_business_address" value="1" {{ old('accommodation_same_as_business_address', $serviceProfiles['accommodation']['same_as_business_address'] ?? null) == 1 || old('accommodation_same_as_business_address', $serviceProfiles['accommodation']['same_as_business_address'] ?? null) === '1' || old('accommodation_same_as_business_address', $serviceProfiles['accommodation']['same_as_business_address'] ?? null) === 'yes' ? 'checked' : '' }}>{{ __('operator.registration.yes') }}</label>
                        <label class="d-flex align-items-center gap-2 mb-0"><input type="radio" name="accommodation_same_as_business_address" value="0" {{ old('accommodation_same_as_business_address', $serviceProfiles['accommodation']['same_as_business_address'] ?? '0') === 0 || old('accommodation_same_as_business_address', $serviceProfiles['accommodation']['same_as_business_address'] ?? '0') === '0' || old('accommodation_same_as_business_address', $serviceProfiles['accommodation']['same_as_business_address'] ?? '0') === 'no' || old('accommodation_same_as_business_address', $serviceProfiles['accommodation']['same_as_business_address'] ?? '0') === false ? 'checked' : '' }}>{{ __('operator.registration.no') }}</label>
                    </div>
                    <div id="accommodation-address-fields" class="row">
                        <div class="col-md-6 mb-3"><label>Address <span style="color:#d32f2f">*</span></label><input type="text" name="accommodation_address" class="form-control" value="{{ old('accommodation_address', $serviceProfiles['accommodation']['address'] ?? '') }}"></div>
                        <div class="col-md-6 mb-3"><label>Region / Location <span style="color:#d32f2f">*</span></label><input type="text" name="accommodation_region_location" class="form-control" value="{{ old('accommodation_region_location', $serviceProfiles['accommodation']['region_location'] ?? '') }}"></div>
                        <div class="col-md-6 mb-3"><label>Map / Geolocation</label><input type="text" name="accommodation_geolocation" class="form-control" placeholder="e.g. -20.1609, 57.5012" value="{{ old('accommodation_geolocation', $serviceProfiles['accommodation']['geolocation'] ?? '') }}"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Logo</label><input type="file" name="accommodation_logo" class="form-control" accept="image/*">@if(!empty($serviceProfiles['accommodation']['logo']))<small class="text-muted d-block">Existing logo retained unless replaced.</small>@endif</div>
                        <div class="col-md-6 mb-3"><label>Contact Number</label><input type="text" name="accommodation_contact_number" class="form-control" value="{{ old('accommodation_contact_number', $serviceProfiles['accommodation']['contact_number'] ?? '') }}"></div>
                        <div class="col-md-6 mb-3"><label>Contact Email</label><input type="email" name="accommodation_contact_email" class="form-control" value="{{ old('accommodation_contact_email', $serviceProfiles['accommodation']['contact_email'] ?? '') }}"></div>
                    </div>
                </div>

                <div id="transport-section" class="service-profile-section form-group mb-3" style="display:none;clear:both;float:none;width:100%;border:1px solid #ddd;padding:16px;border-radius:8px;">
                    <h4 style="font-size:18px;margin:0 0 16px;text-align:center;">Transport Details</h4>
                    <label>{{ __('operator.registration.transport_same_as_business_address') }}</label>
                    <div class="d-flex gap-3 mt-2 mb-3">
                        <label class="d-flex align-items-center gap-2 mb-0">
                            <input type="radio" name="transport_same_as_business_address" value="1" {{ old('transport_same_as_business_address', $operator->transport_same_as_business_address ?? null) == 1 || old('transport_same_as_business_address', $operator->transport_same_as_business_address ?? null) == '1' || old('transport_same_as_business_address', $operator->transport_same_as_business_address ?? null) == 'yes' ? 'checked' : '' }}>
                            {{ __('operator.registration.yes') }}
                        </label>
                        <label class="d-flex align-items-center gap-2 mb-0">
                            <input type="radio" name="transport_same_as_business_address" value="0" {{ old('transport_same_as_business_address', $operator->transport_same_as_business_address ?? '0') === 0 || old('transport_same_as_business_address', $operator->transport_same_as_business_address ?? '0') === '0' || old('transport_same_as_business_address', $operator->transport_same_as_business_address ?? '0') === 'no' || old('transport_same_as_business_address', $operator->transport_same_as_business_address ?? '0') === false ? 'checked' : '' }}>
                            {{ __('operator.registration.no') }}
                        </label>
                    </div>
                    <div id="transport-address-fields" class="row">
                        <div class="col-md-6 mb-3"><label>Address <span style="color:#d32f2f">*</span></label><input type="text" name="transport_address" class="form-control" value="{{ old('transport_address', $operator->transport_address ?? '') }}" data-transport-field></div>
                        <div class="col-md-6 mb-3"><label>Region / Location <span style="color:#d32f2f">*</span></label><input type="text" name="transport_region_location" class="form-control" value="{{ old('transport_region_location', $operator->transport_region_location ?? '') }}" data-transport-field></div>
                        <div class="col-md-6 mb-3"><label>Map / Geolocation</label><input type="text" name="transport_geolocation" class="form-control" placeholder="e.g. -20.1609, 57.5012" value="{{ old('transport_geolocation', $operator->transport_geolocation ?? '') }}"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Logo</label><input type="file" name="transport_logo" class="form-control" accept="image/*">@if(!empty($serviceProfiles['transport']['logo']))<small class="text-muted d-block">Existing logo retained unless replaced.</small>@endif</div>
                        <div class="col-md-6 mb-3"><label>Contact Number</label><input type="text" name="transport_contact_number" class="form-control" value="{{ old('transport_contact_number', $serviceProfiles['transport']['contact_number'] ?? '') }}"></div>
                        <div class="col-md-6 mb-3"><label>Contact Email</label><input type="email" name="transport_contact_email" class="form-control" value="{{ old('transport_contact_email', $serviceProfiles['transport']['contact_email'] ?? '') }}"></div>
                    </div>
                </div>

                <div id="activity-section" class="service-profile-section form-group mb-3" style="display:none;clear:both;float:none;width:100%;border:1px solid #ddd;padding:16px;border-radius:8px;">
                    <h4 style="font-size:18px;margin:0 0 16px;text-align:center;">Activity Details</h4>
                    <label>Activity has Same as Business Address?</label>
                    <div class="d-flex gap-3 mt-2 mb-3">
                        <label class="d-flex align-items-center gap-2 mb-0"><input type="radio" name="activity_same_as_business_address" value="1" {{ old('activity_same_as_business_address', $serviceProfiles['activity']['same_as_business_address'] ?? null) == 1 || old('activity_same_as_business_address', $serviceProfiles['activity']['same_as_business_address'] ?? null) === '1' || old('activity_same_as_business_address', $serviceProfiles['activity']['same_as_business_address'] ?? null) === 'yes' ? 'checked' : '' }}>{{ __('operator.registration.yes') }}</label>
                        <label class="d-flex align-items-center gap-2 mb-0"><input type="radio" name="activity_same_as_business_address" value="0" {{ old('activity_same_as_business_address', $serviceProfiles['activity']['same_as_business_address'] ?? '0') === 0 || old('activity_same_as_business_address', $serviceProfiles['activity']['same_as_business_address'] ?? '0') === '0' || old('activity_same_as_business_address', $serviceProfiles['activity']['same_as_business_address'] ?? '0') === 'no' || old('activity_same_as_business_address', $serviceProfiles['activity']['same_as_business_address'] ?? '0') === false ? 'checked' : '' }}>{{ __('operator.registration.no') }}</label>
                    </div>
                    <div id="activity-address-fields" class="row">
                        <div class="col-md-6 mb-3"><label>Address <span style="color:#d32f2f">*</span></label><input type="text" name="activity_address" class="form-control" value="{{ old('activity_address', $serviceProfiles['activity']['address'] ?? '') }}"></div>
                        <div class="col-md-6 mb-3"><label>Region / Location <span style="color:#d32f2f">*</span></label><input type="text" name="activity_region_location" class="form-control" value="{{ old('activity_region_location', $serviceProfiles['activity']['region_location'] ?? '') }}"></div>
                        <div class="col-md-6 mb-3"><label>Map / Geolocation</label><input type="text" name="activity_geolocation" class="form-control" placeholder="e.g. -20.1609, 57.5012" value="{{ old('activity_geolocation', $serviceProfiles['activity']['geolocation'] ?? '') }}"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Logo</label><input type="file" name="activity_logo" class="form-control" accept="image/*">@if(!empty($serviceProfiles['activity']['logo']))<small class="text-muted d-block">Existing logo retained unless replaced.</small>@endif</div>
                        <div class="col-md-6 mb-3"><label>Contact Number</label><input type="text" name="activity_contact_number" class="form-control" value="{{ old('activity_contact_number', $serviceProfiles['activity']['contact_number'] ?? '') }}"></div>
                        <div class="col-md-6 mb-3"><label>Contact Email</label><input type="email" name="activity_contact_email" class="form-control" value="{{ old('activity_contact_email', $serviceProfiles['activity']['contact_email'] ?? '') }}"></div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label>Contact Details</label>
                    <input type="text" name="contact_name" class="form-control mb-1" placeholder="Name" value="{{ old('contact_name', $profile->contact_name ?? '') }}">
                    <input type="text" name="contact_phone" class="form-control mb-1" placeholder="Phone" value="{{ old('contact_phone', $profile->contact_phone ?? '') }}">
                    <input type="email" name="contact_email" class="form-control" placeholder="Email" value="{{ old('contact_email', $profile->contact_email ?? '') }}">
                </div>
                <div class="form-group mb-3">
                    <label>Trading Name</label>
                    <input type="text" name="trading_name" class="form-control" value="{{ old('trading_name', $profile->trading_name ?? '') }}">
                </div>
                <div class="form-group mb-3">
                    <label>Company Logo</label>
                    <input type="file" name="company_logo" class="form-control">
                    @if(!empty($profile->company_logo))
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $profile->company_logo) }}" alt="Company Logo" style="max-width:120px;max-height:120px;border:1px solid #ccc;">
                        </div>
                    @endif
                </div>
                <div class="form-group mb-3">
                    <label>Company Description</label>
                    <textarea name="company_description" class="form-control">{{ old('company_description', $profile->company_description ?? '') }}</textarea>
                </div>
                <div class="form-group mb-3">
                    <label>Social Media Links</label>
                    <input type="text" name="facebook_link" class="form-control mb-1" placeholder="Facebook" value="{{ old('facebook_link', $profile->facebook_link ?? '') }}">
                    <input type="text" name="instagram_link" class="form-control mb-1" placeholder="Instagram" value="{{ old('instagram_link', $profile->instagram_link ?? '') }}">
                    <input type="text" name="linkedin_link" class="form-control" placeholder="Linkedin" value="{{ old('linkedin_link', $profile->linkedin_link ?? '') }}">
                </div>

                <div class="form-group mt-3">
                    <button type="button" class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#legalComplianceModal">Advanced Settings</button>
                    <button type="submit" class="btn btn-primary">Save and Continue</button>
                </div>
            </form>
        </div>
    </div>

    @section('progressbar')
    @php
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
 

    @include('operator.registration.step2_profile_legal_modal', ['legal' => $legal ?? null])

    @push('scripts')
    <script>
    (function () {
        const serviceTypeSelect = document.getElementById('service-types-select');
        const sections = {
            Accommodation: document.getElementById('accommodation-section'),
            Transport: document.getElementById('transport-section'),
            Activity: document.getElementById('activity-section')
        };
        const serviceAddressConfig = {
            Transport: {
                fields: document.getElementById('transport-address-fields'),
                address: document.querySelector('input[name="transport_address"]'),
                region: document.querySelector('input[name="transport_region_location"]'),
                radios: document.querySelectorAll('input[name="transport_same_as_business_address"]')
            },
            Accommodation: {
                fields: document.getElementById('accommodation-address-fields'),
                address: document.querySelector('input[name="accommodation_address"]'),
                region: document.querySelector('input[name="accommodation_region_location"]'),
                radios: document.querySelectorAll('input[name="accommodation_same_as_business_address"]')
            },
            Activity: {
                fields: document.getElementById('activity-address-fields'),
                address: document.querySelector('input[name="activity_address"]'),
                region: document.querySelector('input[name="activity_region_location"]'),
                radios: document.querySelectorAll('input[name="activity_same_as_business_address"]')
            }
        };

        function updateServiceFields(service) {
            const config = serviceAddressConfig[service];
            const selectedValues = Array.from(serviceTypeSelect.selectedOptions).map(option => option.value);
            const isSelected = selectedValues.includes(service);
            const checkedValue = document.querySelector(`input[name="${service.toLowerCase()}_same_as_business_address"]:checked`)?.value;
            const sameAsBusiness = checkedValue === '1' || checkedValue === 'yes';

            if (!isSelected) {
                config.radios.forEach(radio => { radio.checked = false; });
                config.fields.style.display = 'none';
                config.address?.removeAttribute('required');
                config.region?.removeAttribute('required');
                return;
            }

            config.fields.style.display = sameAsBusiness || checkedValue === undefined ? 'none' : 'block';
            if (sameAsBusiness || checkedValue === undefined) {
                config.address?.removeAttribute('required');
                config.region?.removeAttribute('required');
            } else {
                config.address?.setAttribute('required', 'required');
                config.region?.setAttribute('required', 'required');
            }
        }

        function updateServiceFieldsVisibility() {
            const selectedValues = Array.from(serviceTypeSelect.selectedOptions).map(option => option.value);

            Object.entries(sections).forEach(([service, section]) => {
                if (section) {
                    section.style.display = selectedValues.includes(service) ? 'block' : 'none';
                }
            });
            Object.keys(serviceAddressConfig).forEach(updateServiceFields);
        }

        if (serviceTypeSelect) {
            serviceTypeSelect.addEventListener('change', updateServiceFieldsVisibility);
            Object.values(serviceAddressConfig).forEach(function (config) {
                config.radios.forEach(function (radio) {
                    radio.addEventListener('change', updateServiceFieldsVisibility);
                });
            });
            updateServiceFieldsVisibility();
        }
    })();

    // Optional: focus first input when modal opens
    $('#legalComplianceModal').on('shown.bs.modal', function () {
        $(this).find('input:visible:enabled:first').focus();
    });
    </script>

<script>
      function toggleMenu(element) {
         let submenu = element.nextElementSibling;
         let arrow = element.querySelector(".arrow-icon i");

         submenu.classList.toggle("hidden");
         arrow.classList.toggle("rotate");
      }
   </script>
   <script>
      function toggleMenu(element) {
         let submenu = element.nextElementSibling;

         element.classList.toggle("active");
         submenu.classList.toggle("hidden");
      }
   </script>
   <script>
      function toggleSidebar() {
         document.getElementById("sidebar").classList.toggle("active");
      }
   </script>

   <script>
      function toggleSidebar() {
         document.getElementById("sidebar").classList.toggle("active");
      }

      document.addEventListener("click", function (e) {
         let sidebar = document.getElementById("sidebar");
         let hamburger = document.querySelector(".hamburger");

         if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
            sidebar.classList.remove("active");
         }
      });
   </script>

    @endpush
@endsection
