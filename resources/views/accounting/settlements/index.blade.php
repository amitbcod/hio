@extends('layouts.admin')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 mb-1">Financial Settlements</h1>
            <p class="text-muted mb-0">USD-only HIO SLA and Operator payout ledger.</p>
        </div>
        @if($canGenerate)
            <form method="POST" action="{{ route('mpo.accounting-settlements.generate') }}">
                @csrf
                <button class="btn btn-primary" type="submit">Generate eligible settlements</button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @if($reviews->isNotEmpty())
        <section class="card mb-4">
            <div class="card-header fw-bold">Bookings requiring authorised review</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead><tr><th>Reference</th><th>Service</th><th>Operator</th>@if($isGlobalAdmin)<th>Country</th><th>MPO</th>@endif<th>Reason</th><th>Flagged</th></tr></thead>
                    <tbody>
                    @foreach($reviews as $review)
                        <tr>
                            <td>{{ $review->booking_reference }}</td>
                            <td>{{ ucfirst($review->service_type) }}</td>
                            <td>{{ $review->operator?->business?->business_name ?? $review->operator?->business_legal_name ?? $review->operator?->full_name ?? '—' }}</td>
                            @if($isGlobalAdmin)<td>{{ $review->country?->name ?? '—' }}</td><td>{{ $review->mpo?->name ?? '—' }}</td>@endif
                            <td><span class="fw-semibold">{{ str_replace('_', ' ', ucfirst($review->reason_code)) }}</span>: {{ $review->reason }}</td>
                            <td>{{ $review->created_at?->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <form method="GET" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            @if($isGlobalAdmin)
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="country-filter">Country</label>
                    <select class="form-select" id="country-filter" name="country_id">
                        <option value="">All countries</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" @selected((string) request('country_id') === (string) $country->id)>{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-sm-6 col-lg-2">
                <label class="form-label" for="type-filter">Type</label>
                <select class="form-select" id="type-filter" name="type">
                    <option value="">All</option>
                    <option value="hio_sla" @selected(request('type') === 'hio_sla')>HIO SLA</option>
                    <option value="operator_payout" @selected(request('type') === 'operator_payout')>Operator payout</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label" for="status-filter">Status</label>
                <input class="form-control" id="status-filter" name="status" value="{{ request('status') }}" placeholder="e.g. Paid">
            </div>
            <div class="col-sm-6 col-lg-3">
                <label class="form-label" for="operator-filter">Operator</label>
                <input class="form-control" id="operator-filter" type="search" name="operator" value="{{ request('operator') }}">
            </div>
            <div class="col-auto"><button class="btn btn-primary">Filter</button></div>
            <div class="col-auto"><a class="btn btn-outline-secondary" href="{{ $isGlobalAdmin ? route('admin.accounting-settlements.index') : route('mpo.accounting-settlements.index') }}">Reset</a></div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Reference</th><th>Type</th><th>Country</th><th>Operator / MPO</th><th>Period</th><th>Gross USD</th><th>Net USD</th><th>Verified USD</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($settlements as $settlement)
                    <tr>
                        <td>{{ $settlement->reference }}</td>
                        <td>{{ $settlement->settlement_type === 'hio_sla' ? 'HIO SLA' : 'Operator payout' }}</td>
                        <td>{{ $settlement->country?->name ?? '—' }}</td>
                        <td>
                            @if($settlement->settlement_type === 'operator_payout')
                                {{ $settlement->operator?->business?->business_name ?? $settlement->operator?->business_legal_name ?? $settlement->operator?->full_name ?? '—' }}
                            @else
                                {{ $settlement->mpo?->name ?? 'MPO' }} → HIO Global
                            @endif
                        </td>
                        <td>{{ $settlement->period_start?->format('Y-m-d') }} – {{ $settlement->period_end?->format('Y-m-d') }}</td>
                        <td>{{ number_format((float) $settlement->gross_amount, 2) }}</td>
                        <td>{{ number_format((float) $settlement->net_amount, 2) }}</td>
                        <td>{{ number_format((float) $settlement->verified_amount, 2) }}</td>
                        <td><span class="badge text-bg-secondary">{{ $settlement->status }}</span></td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ $isGlobalAdmin ? route('admin.accounting-settlements.show', $settlement) : route('mpo.accounting-settlements.show', $settlement) }}">Details</a></td>
                    </tr>
                @empty
                    <tr><td class="text-center text-muted py-4" colspan="10">No settlements found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $settlements->links() }}</div>
    </div>
</div>
@endsection
