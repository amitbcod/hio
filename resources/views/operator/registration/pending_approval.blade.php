@extends('layouts.app')

@section('content')
    @php
        $currentStep = 9;
        $operator = auth('operator')->user() ?? auth('operator_staff')->user();
        $progress = !empty($operator->business_id)
            ? \App\Models\OperatorRegistrationProgress::where('business_id', $operator->business_id)->first()
            : \App\Models\OperatorRegistrationProgress::where('operator_id', $operator->operator_id ?? null)->first();
        $completionPercent = $progress ? round((($progress->step2_profile ?? 0)
            + ($progress->step3_legal ?? 0)
            + ($progress->step4_system_process ?? 0)
            + ($progress->step5_collaboration ?? 0)
            + ($progress->step7_accounting ?? 0)
            + ($progress->step8_operations ?? 0)
            + ($progress->step9_review ?? 0)) / 7 * 100) : 0;
    @endphp
    <div id="sidebar" class="col-md-3 net-section">
        @include('operator.registration._sidebar', ['currentStep' => $currentStep, 'progress' => $progress ?? null])
    </div>
    <div class="col-md-6 d-flex align-items-center justify-content-center py-4">
        <div class="card border-0 shadow-sm rounded-4 w-100 text-center p-4">
            <h3 class="mb-3 fw-bold">Your profile is pending approval</h3>
            <p class="text-muted mb-0" style="font-size:18px;">
                We’re reviewing your details and will notify you as soon as it’s approved.
            </p>
        </div>
    </div>
    @include('operator.registration._progress', ['completionPercent' => $completionPercent])
@endsection
