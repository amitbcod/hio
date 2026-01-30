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
    @php $currentStep = 9; @endphp
    <div class="col-md-3">
        @include('operator.registration._sidebar', ['currentStep' => $currentStep, 'progress' => $progress ?? null])
    </div>
    <div class="col-md-9 d-flex align-items-center justify-content-center" style="min-height: 90vh;">
        <div style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 32px 32px 24px 32px; width: 100%; max-width: 900px;">
            <h2 style="font-weight: bold; margin-bottom: 24px;">STATUS REVIEW</h2>
            <div class="alert alert-info">Please review all your information before submitting.</div>
            <form method="POST" action="{{ route('operator.status.submit') }}">
                @csrf
            @if(!$statusReview)
                <div class="alert alert-warning">No status review data found for this operator.</div>
            @else
    
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Account Status</label>
                        <input type="text" class="form-control" value="{{ $statusReview->account_status }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Last Approval Date</label>
                        <input type="text" class="form-control" value="{{ $statusReview->last_approval_date }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Profile Verified By (User ID)</label>
                        <input type="text" class="form-control" value="{{ $statusReview->profile_verified_by }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Profile Verified Date</label>
                        <input type="text" class="form-control" value="{{ $statusReview->profile_verified_date }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Operator Rating</label>
                        <input type="text" class="form-control" value="{{ $statusReview->operator_rating }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Testimonials Count</label>
                        <input type="text" class="form-control" value="{{ $statusReview->testimonials_count }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Average Rating</label>
                        <input type="text" class="form-control" value="{{ $statusReview->average_rating }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Renewal Reminder Date</label>
                        <input type="text" class="form-control" value="{{ $statusReview->renewal_reminder_date }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Agreement Duration (days)</label>
                        <input type="text" class="form-control" value="{{ $statusReview->agreement_duration_days }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Agreement Expiry Date</label>
                        <input type="text" class="form-control" value="{{ $statusReview->agreement_expiry_date }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Compliance Percentage</label>
                        <input type="text" class="form-control" value="{{ $statusReview->compliance_percentage }}%" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Last Compliance Check</label>
                        <input type="text" class="form-control" value="{{ $statusReview->last_compliance_check }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Created At</label>
                        <input type="text" class="form-control" value="{{ $statusReview->created_at }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Updated At</label>
                        <input type="text" class="form-control" value="{{ $statusReview->updated_at }}" readonly>
                    </div>
                </div>
             
            @endif
               <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success px-4">
                        Submit for Approval
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
