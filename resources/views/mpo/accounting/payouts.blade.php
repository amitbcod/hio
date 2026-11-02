@extends($isAdminView ? 'layouts.admin' : 'layouts.mpo-portal')

@section('title', 'Accounting Payouts')

@section('content')
<div class="container-fluid py-3">
    @if($isAdminView)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0">Accounting Payouts</h1>
            <a class="btn btn-outline-secondary" href="{{ route('admin.country-admins.index') }}">Back to MPOs</a>
        </div>
        <p class="text-muted">{{ $mpoAdmin->registered_business_name ?: $mpoAdmin->name }} — {{ $mpoAdmin->country?->name ?? 'Country not assigned' }}</p>
    @endif

    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($completion['complete'])
        <div class="alert alert-success" role="status">Payout configuration is complete. This is configuration only; it does not request or execute a payout.</div>
    @else
        <div class="alert alert-warning" role="status">
            <strong>Onboarding is incomplete.</strong>
            <ul class="mb-0 mt-2">
                @foreach($completion['requirements'] as $requirement)
                    <li>{{ $requirement }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="card mb-4">
        <div class="card-header">A. Beneficiary Information</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4">Payout Configuration ID</dt>
                <dd class="col-sm-8">{{ $configuration?->payout_id ?? 'Assigned automatically when preferences are first saved' }}</dd>
                <dt class="col-sm-4">Beneficiary ID</dt><dd class="col-sm-8">{{ $mpoAdmin->id }}</dd>
                <dt class="col-sm-4">Beneficiary Name</dt><dd class="col-sm-8">{{ $mpoAdmin->registered_business_name ?: $mpoAdmin->name }}</dd>
                <dt class="col-sm-4">Country / Currency</dt>
                <dd class="col-sm-8">{{ $mpoAdmin->country?->name ?? 'Not assigned' }} / {{ $mpoAdmin->country?->currency_code ?? 'Not configured' }}</dd>
            </dl>
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header">B. Commission and Processing Fees</div>
        <div class="card-body">
            @if(!$agreement)
                <div class="alert alert-warning mb-3">No Collaboration Agreement is configured. Commission terms are unavailable.</div>
            @elseif($agreement->status !== 'Active')
                <div class="alert alert-info mb-3">The Collaboration Agreement is {{ $agreement->status }}. Its terms are reference-only and do not activate or change the agreement.</div>
            @endif
            <dl class="row mb-0">
                <dt class="col-sm-4">Agreement</dt><dd class="col-sm-8">{{ $agreement?->agreement_id ?? 'Not configured' }}</dd>
                <dt class="col-sm-4">Commission Type (inherited)</dt><dd class="col-sm-8">{{ $agreement ? ucfirst($agreement->commission_model) : 'Unavailable' }}</dd>
                <dt class="col-sm-4">Commission Value (read-only)</dt>
                <dd class="col-sm-8">
                    {{ $agreement?->commission_value !== null ? $agreement->commission_value.($agreement->commission_model === 'percentage' ? '%' : '') : 'Not configured' }}
                    <span class="text-muted">— managed through the Collaboration Agreement</span>
                </dd>
                <dt class="col-sm-4">Processing Fee Applies</dt><dd class="col-sm-8">Not configured — no MPO gateway fee rules are available</dd>
                <dt class="col-sm-4">Total Commission / Net Payout Preview</dt>
                <dd class="col-sm-8">Unavailable — no eligible transaction base or applicable fee rules are available. No estimated amount is shown.</dd>
            </dl>
            <p class="form-text mt-3 mb-0">Country VAT is not treated as a commission deduction. Saving this page does not calculate or create a financial transaction.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('mpo.accounting-payouts.update') }}">
        @csrf
        @method('PUT')
        <fieldset @disabled(!$canUpdate || $isAdminView)>
            <section class="card mb-4">
                <div class="card-header">C. Payout Schedule</div>
                <div class="card-body">
                    <label class="form-label" for="payment_schedule">Preferred payout schedule</label>
                    <select class="form-select" id="payment_schedule" name="payment_schedule" required>
                        <option value="">Select a schedule</option>
                        @foreach(['Monthly', 'Service Provided', 'On Request'] as $schedule)
                            <option value="{{ $schedule }}" @selected(old('payment_schedule', $configuration?->payment_schedule) === $schedule)>{{ $schedule }}</option>
                        @endforeach
                    </select>
                    @error('payment_schedule')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div class="form-text">This preference does not set dates, cut-offs, eligibility rules, or execute payments.</div>
                </div>
            </section>

            <section class="card mb-4">
                <div class="card-header">D. Credit Configuration</div>
                <div class="card-body">
                    <p class="mb-0">Unavailable. The application has no MPO credit facility with downstream enforcement. No credit limit or credit value is accepted or implied.</p>
                </div>
            </section>

            <section class="card mb-4">
                <div class="card-header">E. Adjustments</div>
                <div class="card-body">
                    <p class="mb-0">Unavailable. No MPO adjustment approval, reversal, or capacity-limit workflow exists. Financial adjustments cannot be entered here.</p>
                </div>
            </section>

            <section class="card mb-4">
                <div class="card-header">F. Payout Method</div>
                <div class="card-body">
                    <label class="form-label" for="payout_method">Payout method</label>
                    <select class="form-select" id="payout_method" name="payout_method">
                        <option value="">Not selected</option>
                        <option value="Bank" @selected(old('payout_method', $configuration?->payout_method) === 'Bank')>Bank</option>
                    </select>
                    @error('payout_method')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div class="form-text mb-3">Bank details are reused from Accounting Commission and are not copied to this configuration. Wallet is unavailable because no supported wallet provider/account model exists.</div>
                    @if($bankSummary['valid'])
                        <p class="mb-0">Bank destination: {{ $bankSummary['bank_name'] }} — IBAN ending {{ $bankSummary['iban'] }}. Full bank information remains in Accounting Commission.</p>
                    @else
                        <div class="alert alert-warning mb-0">Bank destination is missing or invalid. Complete Accounting Commission before selecting Bank.</div>
                    @endif
                </div>
            </section>

            @error('request')<div class="alert alert-danger">{{ $message }}</div>@enderror
            @if($canUpdate && !$isAdminView)
                <div class="d-flex gap-2 mb-4">
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                    <a class="btn btn-outline-secondary" href="{{ route('mpo.accounting-payouts') }}">Cancel</a>
                </div>
            @endif
        </fieldset>
    </form>

    <div class="alert alert-secondary" role="note">
        <strong>Configuration only:</strong> this page does not create payout requests or batches, contact a gateway, deduct funds, or mark any payout as Processing, Paid, or Settled.
    </div>
</div>
@endsection
