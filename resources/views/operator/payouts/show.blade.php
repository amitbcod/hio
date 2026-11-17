@extends('operator.layout')

@section('title', 'Payout statement')

@section('content')
<div class="mb-3"><a href="{{ route('operator.payouts.index') }}">← Payouts</a></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card mb-3"><div class="card-body">
    <h4>{{ $payout->reference }}</h4>
    <div class="row g-3">
        <div class="col-md-3"><strong>Period</strong><div>{{ $payout->period_start?->format('Y-m-d') }} – {{ $payout->period_end?->format('Y-m-d') }}</div></div>
        <div class="col-md-3"><strong>Schedule</strong><div>{{ $payout->payment_schedule }}</div></div>
        <div class="col-md-3"><strong>Status</strong><div>{{ $payout->status }}</div></div>
        <div class="col-md-3"><strong>Currency</strong><div>{{ $payout->currency }}</div></div>
        <div class="col-md-3"><strong>Gross</strong><div>USD {{ number_format((float) $payout->gross_amount, 2) }}</div></div>
        <div class="col-md-3"><strong>Commission</strong><div>USD {{ number_format((float) $payout->operator_commission_amount, 2) }}</div></div>
        <div class="col-md-3"><strong>Net payable</strong><div>USD {{ number_format((float) $payout->net_amount, 2) }}</div></div>
    </div>
</div></div>
<div class="card mb-3"><div class="card-header fw-bold">Booking calculation snapshots</div><div class="table-responsive">
    <table class="table table-sm align-middle mb-0"><thead><tr><th>Reference</th><th>Service</th><th>Date</th><th>Gross USD</th><th>Agreement reference</th><th>Agreement type</th><th>Commission value</th><th>Commission USD</th><th>Net USD</th></tr></thead>
    <tbody>@forelse($payout->items as $item)
        <tr><td>{{ $item->booking_reference }}</td><td>{{ ucfirst($item->service_type) }}</td><td>{{ $item->service_date?->format('Y-m-d') }}</td>
        <td>{{ number_format((float) $item->gross_amount, 2) }}</td><td>{{ $item->agreement_reference ?? '—' }}</td><td>{{ $item->agreement_type ?? '—' }}</td>
        <td>{{ $item->commission_value ?? '—' }} {{ $item->commission_type ?? '' }}</td><td>{{ number_format((float) $item->operator_commission_amount, 2) }}</td><td>{{ number_format((float) $item->net_amount, 2) }}</td></tr>
    @empty<tr><td colspan="9" class="text-center text-muted">No bookings.</td></tr>@endforelse</tbody></table>
</div></div>
<div class="card mb-3"><div class="card-header fw-bold">Transfers</div><div class="table-responsive">
    <table class="table table-sm align-middle mb-0"><thead><tr><th>Reference</th><th>Transaction</th><th>Date</th><th>Amount</th><th>Status</th><th>Evidence</th></tr></thead>
    <tbody>@forelse($payout->transfers as $transfer)<tr><td>{{ $transfer->transfer_reference }}</td><td>{{ $transfer->transaction_number }}</td><td>{{ $transfer->transferred_on?->format('Y-m-d') }}</td><td>USD {{ number_format((float) $transfer->amount, 2) }}</td><td>{{ $transfer->status }}</td><td><a href="{{ route('operator.payouts.transfers.evidence', [$payout, $transfer]) }}">View evidence</a></td></tr>@empty<tr><td colspan="6" class="text-center text-muted">No transfers yet.</td></tr>@endforelse</tbody></table>
</div></div>
@if($canAcknowledge)
    <form method="POST" action="{{ route('operator.payouts.acknowledge', $payout) }}">@csrf<button class="btn btn-success">Acknowledge receipt</button></form>
@endif
@endsection
