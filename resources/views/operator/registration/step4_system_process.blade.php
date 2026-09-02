@extends('layouts.app')
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

@section('content')
@php $currentStep = 4; @endphp
<div class="row">
    <div id="sidebar" class="col-md-3 net-section">
        @include('operator.registration._sidebar', ['currentStep' => $currentStep, 'progress' => $progress ?? null])
    </div>
    <div class="col-md-6 d-flex align-items-center justify-content-center" style="">
        <div class="container-middle">
            <h2 style="font-weight: bold; margin-bottom: 24px;">System Configuration</h2>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ url('operator/register/step4-system-process') }}">
                @csrf
                <div class="form-group mb-3">
                    <label>Service Categories</label>
                    @php
                        $profile = null;
                        if (!empty($operator->business_id)) {
                            $profile = \App\Models\OperatorProfile::where('business_id', $operator->business_id)->first();
                        }
                        if (!$profile) {
                            $profile = \App\Models\OperatorProfile::where('operator_id', $operator->operator_id)->first();
                        }
                        $serviceTypes = $profile && $profile->service_types ? (is_array($profile->service_types) ? $profile->service_types : json_decode($profile->service_types, true)) : [];
                    @endphp
                    <!-- <select class="form-control" multiple disabled>
                        @foreach($serviceTypes as $type)
                            <option selected>{{ $type }}</option>
                        @endforeach
                    </select>
                    @foreach($serviceTypes as $type)
                        <input type="hidden" name="service_category[]" value="{{ $type }}">
                    @endforeach -->
                    @foreach($serviceTypes as $type)
    <span class="badge bg-primary me-1">{{ $type }}</span>
@endforeach
                </div>
                <div class="form-group mb-3">
                    <label>Communication Preference</label>
                    @php
                        $commPref = old('communication_preference', $system->communication_preference ?? '');
                    @endphp
                    <select name="communication_preference" class="form-control">
                        <option value="Email" {{ $commPref == 'Email' ? 'selected' : '' }}>Email</option>
                        <option value="Messaging System" {{ $commPref == 'Messaging System' ? 'selected' : '' }}>Messaging System</option>
                        <option value="WhatsApp" {{ $commPref == 'WhatsApp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="Phone" {{ $commPref == 'Phone' ? 'selected' : '' }}>Phone</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label>Assigned Operator Name</label>
                    <input type="text" name="assigned_operator_name" class="form-control" value="{{ old('assigned_operator_name', $system->assigned_operator_name ?? $operator->full_name ?? '') }}">
                </div>
                <div class="form-group mb-3">
                    <label>Assigned Operator Role</label>
                    <select name="assigned_operator_role" class="form-control">
                        <option value="Primary Operator" {{ (old('assigned_operator_role', $system->assigned_operator_role ?? '') == 'Primary Operator') ? 'selected' : '' }}>Primary Operator</option>
                        <option value="System Administrator" {{ (old('assigned_operator_role', $system->assigned_operator_role ?? '') == 'System Administrator') ? 'selected' : '' }}>System Administrator</option>
                    </select>
                </div>
                <div class="back-section mt-4">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>    
                <button type="submit" class="btn btn-primary">Save System Settings</button>
                    
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

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
