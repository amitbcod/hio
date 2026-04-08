
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
                    <label>Business Legal Name</label>
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
                <div class="form-group mb-3">
                    <label>Service Type</label>
                    @php
                        $serviceTypes = [];
                        if (isset($profile->service_types)) {
                            $serviceTypes = is_array($profile->service_types) ? $profile->service_types : json_decode($profile->service_types, true);
                        }
                    @endphp
                    <select name="service_types[]" class="form-control" multiple>
                        <option value="Accommodation" {{ in_array('Accommodation', $serviceTypes ?? []) ? 'selected' : '' }}>Accommodation</option>
                        <option value="Transport" {{ in_array('Transport', $serviceTypes ?? []) ? 'selected' : '' }}>Transport</option>
                        <option value="Activity" {{ in_array('Activity', $serviceTypes ?? []) ? 'selected' : '' }}>Activity</option>
                        <option value="Food" {{ in_array('Food', $serviceTypes ?? []) ? 'selected' : '' }}>Food</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label>Years in Operation</label>
                    <input type="number" name="years_in_operation" class="form-control" value="{{ old('years_in_operation', $profile->years_in_operation ?? '') }}">
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
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#legalComplianceModal">Advanced Settings</button>
                    <button type="submit" class="btn btn-success">Save and Continue</button>
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
