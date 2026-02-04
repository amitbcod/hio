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
  @php $currentStep = 10; @endphp
 <div class="col-md-3">
        @include('operator.registration._sidebar', ['currentStep' => $currentStep, 'progress' => $progress ?? null])
    </div>
<div class="col-md-9 d-flex align-items-center justify-content-center" style="min-height:90vh;">
    <div style="text-align:center; max-width:700px;">
        <h3 class="mb-3 fw-bold">Your profile is pending approval</h3>
        <p class="text-muted" style="font-size:18px;">
            We’re reviewing your details and will notify you as soon as it’s approved.
        </p>
    </div>
</div>
@endsection
