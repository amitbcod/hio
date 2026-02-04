
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
@php $currentStep = 5; @endphp
<div class="row">
    <div class="col-md-3">
        @include('operator.registration._sidebar', ['currentStep' => $currentStep, 'progress' => $progress ?? null])
    </div>
    <div class="col-md-9 d-flex align-items-center justify-content-center" style="min-height: 90vh;">
        <div style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 32px 32px 24px 32px; width: 100%; max-width: 700px;">
            <h2 style="font-weight: bold; margin-bottom: 24px;">COLLABORATION AGREEMENT</h2>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ url('operator/register/step5-collaboration') }}" enctype="multipart/form-data">
                @csrf
                <h5>Contact Information</h5>
                <div class="form-group mb-2">
                    <label>Management Contact Name *</label>
                    <input type="text" name="contact_management_name" class="form-control" value="{{ old('contact_management_name', $collab->contact_management_name ?? '') }}" required>
                </div>
                <div class="form-group mb-2">
                    <label>Management Email</label>
                    <input type="email" name="contact_management_email" class="form-control" value="{{ old('contact_management_email', $collab->contact_management_email ?? '') }}">
                </div>
                <div class="form-group mb-2">
                    <label>Management Mobile</label>
                    <input type="text" name="contact_management_mobile" class="form-control" value="{{ old('contact_management_mobile', $collab->contact_management_mobile ?? '') }}">
                </div>
                <h5 class="mt-4">Accounting Contact Information</h5>
                <div class="form-group mb-2">
                    <label>Accounting Contact Name</label>
                    <input type="text" name="contact_accounting_name" class="form-control" value="{{ old('contact_accounting_name', $collab->contact_accounting_name ?? '') }}">
                </div>
                <div class="form-group mb-2">
                    <label>Accounting Email</label>
                    <input type="email" name="contact_accounting_email" class="form-control" value="{{ old('contact_accounting_email', $collab->contact_accounting_email ?? '') }}">
                </div>
                <div class="form-group mb-2">
                    <label>Accounting Mobile</label>
                    <input type="text" name="contact_accounting_mobile" class="form-control" value="{{ old('contact_accounting_mobile', $collab->contact_accounting_mobile ?? '') }}">
                </div>
                <h5 class="mt-4">Agreement Details</h5>
                <div class="form-group mb-2 d-flex align-items-start">
                    <div style="flex: 1;">
                        <label>Agreement Type</label>
                        @php
                            $agreementType = old('agreement_type', $business->agreement_type ?? $collab->agreement_type ?? '');
                        @endphp
                        <input type="text" class="form-control" value="{{ $agreementType }}" readonly>
                        <input type="hidden" id="agreement_type" name="agreement_type" value="{{ $agreementType }}">
                    </div>
                    <div style="width: 260px; margin-left: 16px;">
                        <div style="background:#f8f9fa;border:1px solid #e9ecef;padding:12px;border-radius:6px;">
                            <strong>HIO Service Agreement</strong>
                            <p style="font-size:13px;margin:8px 0 0;color:#666;">Select the agreement type that applies to this business. <a href="{{ url('/operator/hio-agreement') }}" target="_blank">Read full agreement (PDF)</a></p>
                        </div>
                    </div>
                </div>

                @if(($operator->is_owner ?? '') === 'yes')
                    <form method="POST" action="{{ url('operator/register/step5-collaboration/confirm') }}" class="mt-3">
                        @csrf
                        <div class="form-group mb-2">
                            <label>Confirm Agreement (type full name)</label>
                            <input type="text" name="agreement_confirm_name" class="form-control" required placeholder="Type full name to confirm">
                        </div>
                        <button type="submit" class="btn btn-primary">I Agree</button>
                    </form>
                @endif
                <div class="form-group mb-2">
                    <label>Signed Agreement (PDF)</label>
                    @php
                        // Prefer business-scoped legal record when present
                        if (!empty($operator->business_id)) {
                            $legal = \App\Models\OperatorLegalCompliance::where('business_id', $operator->business_id)->first();
                        } else {
                            $legal = \App\Models\OperatorLegalCompliance::where('operator_id', $operator->operator_id)->first();
                        }
                        $signedAgreementPath = $legal && $legal->signed_agreement ? $legal->signed_agreement : null;
                    @endphp
                    @if($signedAgreementPath)
                        <div style="padding: 10px; background: #f0f9ff; border: 1px solid #b3d9ff; border-radius: 4px;">
                            <i class="fas fa-file-pdf" style="color: #dc3545;"></i>
                            <a href="{{ asset('storage/' . $signedAgreementPath) }}" target="_blank" style="margin-left: 8px; color: #007bff;">
                                Download Signed Agreement
                            </a>
                        </div>
                    @else
                        <div style="padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; color: #856404;">
                            <i class="fas fa-exclamation-circle"></i> No signed agreement uploaded yet. Please upload in the Legal & Compliance section.
                        </div>
                    @endif
                </div>
                <div class="form-group mb-2">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $collab->start_date ?? (date('Y-m-d'))) }}" readonly>
                </div>
                <div class="form-group mb-2">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $collab->end_date ?? (date('Y-m-d', strtotime('+1 year')))) }}" readonly>
                </div>
                <div class="form-group mb-2">
                    <label>Renewal Date</label>
                    <input type="date" name="renewal_date" class="form-control" value="{{ old('renewal_date', $collab->renewal_date ?? (date('Y-m-d', strtotime('+1 year')))) }}" readonly>
                </div>
                <div class="form-group mb-2">
                    <label>Commission Model</label>
                    <input type="text" name="commission_model" class="form-control" value="{{ old('commission_model', $collab->commission_model ?? 0) }}" readonly>
                </div>
                <div class="form-group mb-2">
                    <label>Commission Value %</label>
                    <input type="number" step="0.01" id="commission_value" name="commission_value" class="form-control" value="{{ old('commission_value', $collab->commission_value ?? 0) }}" readonly>
                </div>
                <div class="form-group mb-2">
                    <label>Marketing Contribution %</label>
                    <input type="number" step="0.01" name="marketing_contribution_percent" class="form-control" value="{{ old('marketing_contribution_percent', $collab->marketing_contribution_percent ?? 0) }}" readonly>
                </div>
                <div class="form-group mb-2">
                    <label>Status</label>
                    <select name="status" class="form-control" readonly disabled>
                        <option value="Active" selected>Active</option>
                    </select>
                    <input type="hidden" name="status" value="Active">
                </div>
                <div class="form-group mb-2">
                    <label>Responsibilities</label>
                    <div class="d-flex align-items-center">
                        <input type="text" name="responsibilities_document" class="form-control me-2" value="Operator Responsibilities PDF" readonly>
                        <a href="{{ route('operator.responsibilities.pdf') }}" class="btn btn-outline-primary" target="_blank">Download PDF</a>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-success">Save Agreement</button>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function updateCommissionValue() {
    const agreementType = document.getElementById('agreement_type').value;
    const commissionValues = {
        'Listing Only': 0,
        'OTO': 20,
        'Widget Only': 15,
        'OTO + Widget': 20,
        'Full Service': 25
    };
    
    const commissionValue = commissionValues[agreementType] || 0;
    document.getElementById('commission_value').value = commissionValue;
}

// Call updateCommissionValue on page load to set initial value
document.addEventListener('DOMContentLoaded', function() {
    updateCommissionValue();
});
</script>
@endsection
