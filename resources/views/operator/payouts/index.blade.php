@extends('layouts.app')

@section('title', 'Operator Payouts')

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
                        <h2 class="fw-bold mb-2">Operator Payouts</h2>
                        <p class="text-muted mb-0">Payout statements and historical agreement snapshots (USD).</p>
                    </div>
                    @if($canRequest)
                        <form method="POST" action="{{ route('operator.payouts.store') }}">
                            @csrf
                            <button class="btn" style="background:#19b5b5;color:#fff;border:0;padding:10px 24px;border-radius:4px;font-weight:600;">
                                Request eligible payout
                            </button>
                        </form>
                    @endif
                </div>

                @if(session('success'))
                    <div class="alert alert-success mb-3">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <section class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
                    <div class="card-header bg-white border-bottom px-3 px-lg-4 py-3">
                        <h5 class="mb-0 fw-bold">Payout history</h5>
                    </div>
                    @if($payouts->isEmpty())
                        <div class="card-body text-center py-5">
                            <h5 class="fw-semibold mb-2">No payout statements yet</h5>
                            <p class="text-muted mb-0">Eligible payout statements will appear here.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Reference</th><th>Period</th><th>Schedule</th><th>Bookings</th>
                                        <th>Gross USD</th><th>Commission USD</th><th>Net USD</th><th>Status</th><th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($payouts as $payout)
                                    @php
                                        $statusTone = match (strtolower($payout->status)) {
                                            'paid', 'received', 'acknowledged' => 'success',
                                            'transfer initiated', 'awaiting authorisation', 'requested' => 'warning',
                                            'partially paid' => 'info',
                                            'rejected', 'cancelled', 'failed' => 'danger',
                                            default => 'neutral',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $payout->reference }}</td>
                                        <td>{{ $payout->period_start?->format('M d, Y') }} – {{ $payout->period_end?->format('M d, Y') }}</td>
                                        <td>{{ $payout->payment_schedule }}</td>
                                        <td>{{ $payout->items->count() }}</td>
                                        <td>{{ number_format((float) $payout->gross_amount, 2) }}</td>
                                        <td>{{ number_format((float) $payout->operator_commission_amount, 2) }}</td>
                                        <td class="fw-semibold">{{ $payout->currency }} {{ number_format((float) $payout->net_amount, 2) }}</td>
                                        <td><span class="operator-status-badge operator-status-badge--{{ $statusTone }}">{{ $payout->status }}</span></td>
                                        <td>
                                            <a href="{{ route('operator.payouts.show', $payout) }}" class="btn btn-sm" style="background:#19b5b5;color:#fff;border:0;">View details</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-body border-top py-3">{{ $payouts->links() }}</div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
