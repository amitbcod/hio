@extends('layouts.admin')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h1 class="h3 mb-1">{{ $settlement->reference }}</h1>
            <p class="text-muted mb-0">{{ $settlement->settlement_type === 'hio_sla' ? 'HIO Global SLA' : 'Operator payout' }} · {{ $settlement->currency }}</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ $isGlobalAdmin ? route('admin.accounting-settlements.index') : route('mpo.accounting-settlements.index') }}">Back to settlements</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if($transferNotifications->isNotEmpty())
        <section class="alert alert-info">
            <strong>Settlement notifications</strong>
            <ul class="mb-0 mt-2">
                @foreach($transferNotifications as $notification)
                    <li><a href="{{ $notification->data['url'] }}">{{ $notification->data['title'] }}</a> — {{ $notification->data['message'] }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="card mb-3"><div class="card-body">
        <div class="row g-3">
            <div class="col-md-3"><strong>Country</strong><div>{{ $settlement->country?->name ?? '—' }}</div></div>
            <div class="col-md-3"><strong>MPO</strong><div>{{ $settlement->mpo?->name ?? '—' }}</div></div>
            <div class="col-md-3"><strong>Operator</strong><div>{{ $settlement->operator?->business?->business_name ?? $settlement->operator?->business_legal_name ?? $settlement->operator?->full_name ?? 'HIO Global' }}</div></div>
            <div class="col-md-3"><strong>Period / schedule</strong><div>{{ $settlement->period_start?->format('Y-m-d') }} – {{ $settlement->period_end?->format('Y-m-d') }} · {{ $settlement->payment_schedule }}</div></div>
            <div class="col-md-3"><strong>Gross</strong><div>USD {{ number_format((float) $settlement->gross_amount, 2) }}</div></div>
            <div class="col-md-3"><strong>Operator commission</strong><div>USD {{ number_format((float) $settlement->operator_commission_amount, 2) }}</div></div>
            <div class="col-md-3"><strong>HIO SLA</strong><div>USD {{ number_format((float) $settlement->hio_sla_amount, 2) }}</div></div>
            <div class="col-md-3"><strong>Net payable</strong><div>USD {{ number_format((float) $settlement->net_amount, 2) }}</div></div>
            <div class="col-md-3"><strong>Verified</strong><div>USD {{ number_format((float) $settlement->verified_amount, 2) }}</div></div>
            <div class="col-md-3"><strong>Outstanding</strong><div>USD {{ number_format((float) $settlement->outstanding_amount, 2) }}</div></div>
            <div class="col-md-3"><strong>Status</strong><div>{{ $settlement->status }}</div></div>
            @if($settlement->settlement_type === 'hio_sla' && $settlement->invoice_reference)
                <div class="col-md-3"><strong>Invoice reference</strong><div>{{ $settlement->invoice_reference }}</div></div>
                <div class="col-md-3"><strong>Invoice issued</strong><div>{{ $settlement->invoice_issued_on?->format('Y-m-d') ?? '—' }}</div></div>
                <div class="col-md-3"><strong>Invoice due</strong><div>{{ $settlement->invoice_due_on?->format('Y-m-d') ?? '—' }}</div></div>
            @endif
        </div>
    </div></div>

    @if(($canAuthorizeOperatorPayout ?? false) && in_array($settlement->status, ['Awaiting Authorisation', 'Requested'], true))
        <form method="POST" action="{{ route('mpo.accounting-settlements.authorize', $settlement) }}" class="mb-3">
            @csrf
            <button class="btn btn-success">Authorize Operator payout</button>
        </form>
    @endif

    @if(($canIssueHioInvoice ?? false) && $settlement->status === 'Statement Generated')
        <section class="card mb-3">
            <div class="card-header fw-bold">Issue HIO Global SLA invoice</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.accounting-settlements.invoice.issue', $settlement) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Invoice reference</label><input class="form-control" name="invoice_reference" maxlength="64" required></div>
                        <div class="col-md-4"><label class="form-label">Issue date</label><input class="form-control" type="date" name="invoice_issued_on" value="{{ now()->toDateString() }}" required></div>
                        <div class="col-md-4"><label class="form-label">Due date</label><input class="form-control" type="date" name="invoice_due_on" min="{{ now()->toDateString() }}" required></div>
                    </div>
                    <button class="btn btn-primary mt-3">Issue invoice</button>
                </form>
            </div>
        </section>
    @endif

    <section class="card mb-3">
        <div class="card-header fw-bold">Booking calculation snapshots</div>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead><tr><th>Booking</th><th>Service</th><th>Service date</th><th>Operator</th><th>Gross USD</th><th>Agreement</th><th>Commission value</th><th>Commission USD</th><th>SLA USD</th><th>Net USD</th></tr></thead>
            <tbody>
            @forelse($settlement->items as $item)
                <tr>
                    <td>{{ $item->booking_reference }}</td><td>{{ ucfirst($item->service_type) }}</td>
                    <td>{{ $item->service_date?->format('Y-m-d') }}</td>
                    <td>{{ $item->operator?->business?->business_name ?? $item->operator?->business_legal_name ?? $item->operator?->full_name ?? '—' }}</td>
                    <td>{{ number_format((float) $item->gross_amount, 2) }}</td>
                    <td>{{ $item->agreement_reference ?? 'Review required' }}{{ $item->agreement_type ? ' · '.$item->agreement_type : '' }}</td>
                    <td>{{ $item->commission_value === null ? '—' : $item->commission_value.' '.($item->commission_type ?? '') }}</td>
                    <td>{{ number_format((float) $item->operator_commission_amount, 2) }}</td>
                    <td>{{ number_format((float) $item->hio_sla_amount, 2) }}</td>
                    <td>{{ number_format((float) $item->net_amount, 2) }}</td>
                </tr>
            @empty<tr><td colspan="10" class="text-center text-muted py-3">No booking allocations.</td></tr>@endforelse
            </tbody>
        </table></div>
    </section>

    <section class="card mb-3">
        <div class="card-header fw-bold">Transfer records</div>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead><tr><th>Reference</th><th>Transaction</th><th>Date</th><th>Amount</th><th>Status</th><th>Evidence</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($settlement->transfers as $transfer)
                <tr>
                    <td>{{ $transfer->transfer_reference }}</td><td>{{ $transfer->transaction_number }}</td>
                    <td>{{ $transfer->transferred_on?->format('Y-m-d') }}</td><td>USD {{ number_format((float) $transfer->amount, 2) }}</td>
                    <td>{{ $transfer->status }}</td><td><a href="{{ $isGlobalAdmin ? route('admin.accounting-settlements.transfers.evidence', [$settlement, $transfer]) : route('mpo.accounting-settlements.transfers.evidence', [$settlement, $transfer]) }}">View evidence</a></td>
                    <td>
                        @if($canVerifyHioTransfer ?? false)
                            @if(in_array($transfer->status, ['Transfer Initiated', 'Recorded'], true))
                                <form method="POST" action="{{ route('admin.accounting-settlements.transfers.verify', [$settlement, $transfer]) }}">@csrf<button class="btn btn-sm btn-success">Confirm receipt</button></form>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty<tr><td colspan="7" class="text-center text-muted py-3">No transfers recorded.</td></tr>@endforelse
            </tbody>
        </table></div>
    </section>

    @php
        $transferAllowed = $settlement->settlement_type === 'hio_sla'
            ? in_array($settlement->status, ['Payable', 'Partially Paid', 'Awaiting Verification', 'Transfer Initiated'], true)
            : in_array($settlement->status, ['Authorised', 'Partially Paid', 'Transfer Initiated'], true);
    @endphp
    @if($canRecordTransfer && $transferAllowed && (float) $settlement->outstanding_amount > 0)
        <section class="card mb-3"><div class="card-header fw-bold">Record USD transfer</div><div class="card-body">
            <form method="POST" enctype="multipart/form-data" action="{{ route('mpo.accounting-settlements.transfers.store', $settlement) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Amount (USD)</label><input class="form-control" type="number" name="amount" min="0.01" max="{{ $settlement->outstanding_amount }}" step="0.01" required></div>
                    <div class="col-md-3"><label class="form-label">Transaction number</label><input class="form-control" name="transaction_number" maxlength="100" required></div>
                    <div class="col-md-3"><label class="form-label">Transfer date</label><input class="form-control" type="date" name="transferred_on" max="{{ now()->toDateString() }}" required></div>
                    <div class="col-md-3"><label class="form-label">Evidence (PDF/JPG/PNG)</label><input class="form-control" type="file" name="evidence" accept=".pdf,.jpg,.jpeg,.png" required></div>
                    <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2" maxlength="2000"></textarea></div>
                </div>
                <button class="btn btn-primary mt-3">Record transfer</button>
            </form>
        </div></section>
    @endif

    @if(($canAcknowledge ?? false) && $settlement->status === 'Received')
        <form method="POST" action="{{ route('admin.accounting-settlements.receipt.acknowledge', $settlement) }}" class="mb-3">
            @csrf
            <button class="btn btn-success">Acknowledge HIO receipt</button>
        </form>
    @endif

    @if(! $isGlobalAdmin && $settlement->status === 'Transfer Initiated')
        <div class="alert alert-info">Transfer initiated. Receipt confirmation is pending from the payment recipient.</div>
    @endif

    <section class="card">
        <div class="card-header fw-bold">Audit history</div>
        <ul class="list-group list-group-flush">
            @forelse($settlement->events as $event)
                <li class="list-group-item">{{ $event->created_at?->format('Y-m-d H:i:s') }} · {{ str_replace('_', ' ', ucfirst($event->event_type)) }} · {{ $event->from_status ?? '—' }} → {{ $event->to_status ?? '—' }} @if($event->remarks) · {{ $event->remarks }} @endif</li>
            @empty<li class="list-group-item text-muted">No events recorded.</li>@endforelse
        </ul>
    </section>
</div>
@endsection
