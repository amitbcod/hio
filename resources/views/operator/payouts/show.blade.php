@extends('layouts.app')

@section('title', 'Payout Statement')

@section('content')
<div class="container-fluid mt-0">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @include('operator.registration._sidebar_main')
        </div>
        <div class="col-md-9 my-pro">
            <div class="container-middle">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div>
                        <div class="mb-2"><a href="{{ route('operator.payouts.index') }}" class="text-decoration-none">← Payouts</a></div>
                        <h2 class="fw-bold mb-2">{{ $payout->reference }}</h2>
                        <p class="text-muted mb-0">Payout statement · {{ $payout->currency }}</p>
                    </div>
                    <span class="operator-status-badge operator-status-badge--{{ match (strtolower($payout->status)) {
                        'paid', 'received', 'acknowledged' => 'success',
                        'transfer initiated', 'awaiting authorisation', 'requested' => 'warning',
                        'partially paid' => 'info',
                        'rejected', 'cancelled', 'failed' => 'danger',
                        default => 'neutral',
                    } }}">{{ $payout->status }}</span>
                </div>

                @if(session('success'))
                    <div class="alert alert-success mb-3">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @if($transferNotifications->isNotEmpty())
                    <div class="alert alert-info mb-3">
                        <strong>Settlement notifications</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($transferNotifications as $notification)
                                <li><a href="{{ $notification->data['url'] }}">{{ $notification->data['title'] }}</a> — {{ $notification->data['message'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="card border-0 shadow-sm mb-3" style="border-radius:16px;overflow:hidden;">
                    <div class="card-header bg-white border-bottom px-3 px-lg-4 py-3">
                        <h5 class="mb-0 fw-bold">Payout summary</h5>
                    </div>
                    <div class="card-body px-3 px-lg-4 py-4">
                        <div class="row g-3">
                            <div class="col-sm-6 col-xl-3"><div class="text-muted small">Period</div><div class="fw-semibold">{{ $payout->period_start?->format('M d, Y') }} – {{ $payout->period_end?->format('M d, Y') }}</div></div>
                            <div class="col-sm-6 col-xl-3"><div class="text-muted small">Payment schedule</div><div class="fw-semibold">{{ $payout->payment_schedule }}</div></div>
                            <div class="col-sm-6 col-xl-3"><div class="text-muted small">Currency</div><div class="fw-semibold">{{ $payout->currency }}</div></div>
                            <div class="col-sm-6 col-xl-3"><div class="text-muted small">Gross amount</div><div class="fw-semibold">{{ $payout->currency }} {{ number_format((float) $payout->gross_amount, 2) }}</div></div>
                            <div class="col-sm-6 col-xl-3"><div class="text-muted small">Commission</div><div class="fw-semibold">{{ $payout->currency }} {{ number_format((float) $payout->operator_commission_amount, 2) }}</div></div>
                            <div class="col-sm-6 col-xl-3"><div class="text-muted small">Net payable</div><div class="fw-bold fs-5">{{ $payout->currency }} {{ number_format((float) $payout->net_amount, 2) }}</div></div>
                        </div>
                    </div>
                </section>

                <section class="card border-0 shadow-sm mb-3" style="border-radius:16px;overflow:hidden;">
                    <div class="card-header bg-white border-bottom px-3 px-lg-4 py-3">
                        <h5 class="mb-0 fw-bold">Booking calculation snapshots</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead><tr><th>Reference</th><th>Service</th><th>Date</th><th>Gross USD</th><th>Agreement reference</th><th>Agreement type</th><th>Commission value</th><th>Commission USD</th><th>Net USD</th></tr></thead>
                            <tbody>
                            @forelse($payout->items as $item)
                                <tr>
                                    <td class="fw-semibold">{{ $item->booking_reference }}</td>
                                    <td>{{ ucfirst($item->service_type) }}</td>
                                    <td>{{ $item->service_date?->format('M d, Y') }}</td>
                                    <td>{{ number_format((float) $item->gross_amount, 2) }}</td>
                                    <td>{{ $item->agreement_reference ?? '—' }}</td>
                                    <td>{{ $item->agreement_type ?? '—' }}</td>
                                    <td>{{ $item->commission_value ?? '—' }} {{ $item->commission_type ?? '' }}</td>
                                    <td>{{ number_format((float) $item->operator_commission_amount, 2) }}</td>
                                    <td class="fw-semibold">{{ number_format((float) $item->net_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-4">No bookings.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="card border-0 shadow-sm mb-3" style="border-radius:16px;overflow:hidden;">
                    <div class="card-header bg-white border-bottom px-3 px-lg-4 py-3">
                        <h5 class="mb-0 fw-bold">Transfers</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead><tr><th>Reference</th><th>Transaction</th><th>Date</th><th>Amount</th><th>Status</th><th>Evidence</th><th>Action</th></tr></thead>
                            <tbody>
                            @forelse($payout->transfers as $transfer)
                                @php
                                    $transferTone = match (strtolower($transfer->status)) {
                                        'transfer verified', 'verified' => 'success',
                                        'transfer initiated', 'recorded' => 'warning',
                                        default => 'neutral',
                                    };
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $transfer->transfer_reference }}</td>
                                    <td>{{ $transfer->transaction_number }}</td>
                                    <td>{{ $transfer->transferred_on?->format('M d, Y') }}</td>
                                    <td>{{ $transfer->currency }} {{ number_format((float) $transfer->amount, 2) }}</td>
                                    <td><span class="operator-status-badge operator-status-badge--{{ $transferTone }}">{{ $transfer->status }}</span></td>
                                    <td><a href="{{ route('operator.payouts.transfers.evidence', [$payout, $transfer]) }}" class="btn btn-sm btn-outline-secondary">View evidence</a></td>
                                    <td>
                                        @if(($canConfirmReceipt ?? false) && in_array($transfer->status, ['Transfer Initiated', 'Recorded'], true))
                                            <form method="POST" action="{{ route('operator.payouts.transfers.confirm-receipt', [$payout, $transfer]) }}">
                                                @csrf
                                                <button class="btn btn-sm" style="background:#19b5b5;color:#fff;border:0;white-space:nowrap;">Confirm receipt</button>
                                            </form>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No transfers yet.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
