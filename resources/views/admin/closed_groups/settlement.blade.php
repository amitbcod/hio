@extends('layouts.admin')

@section('content')
<div class="container py-4" style="max-width: 1040px;">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2 class="mb-1">Bank Transfer Settlement</h2>
            <p class="text-muted mb-0">Review Agent bookings and record bank-transfer verification.</p>
        </div>
        <a href="{{ route('admin.closed-groups.book') }}" class="btn btn-outline-secondary">Closed Group Booking</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <section class="bg-white border rounded p-4 mb-4">
        <form method="GET" action="{{ route('admin.closed-groups.settlement') }}" class="row g-2 align-items-end">
            <div class="col-md-9">
                <label for="booking_reference" class="form-label">Common Booking Reference <span class="text-danger">*</span></label>
                <input id="booking_reference" name="booking_reference" value="{{ old('booking_reference', $reference) }}" class="form-control" required placeholder="BR-202-20261001-XXXX" autocomplete="off">
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-primary">Find Invoice</button>
            </div>
        </form>
    </section>

    @if($order)
        @php
            $payment = $order['payment'];
            $settlementStatus = $payment->settlement_status ?: 'pending_verification';
            $statusLabel = $settlementStatus === 'verified_settled' ? 'VERIFIED & SETTLED' : 'PENDING VERIFICATION';
            $traveller = $order['traveller'];
            $agentReviewPending = $order['booking']->agent_account_id
                && $order['booking']->agent_verification_status !== 'verified';
        @endphp
        <section class="bg-white border rounded p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 border-bottom pb-3 mb-3">
                <div>
                    <h3 class="h5 mb-1">{{ $order['group']?->name ?? $order['trip']?->title ?? 'Group Booking' }}</h3>
                    <div class="text-muted">{{ $traveller?->name ?? 'Traveller details unavailable' }}{{ $traveller?->email ? ' · ' . $traveller->email : '' }}</div>
                </div>
                <span class="badge {{ $settlementStatus === 'verified_settled' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $statusLabel }}</span>
            </div>

            <dl class="row mb-3">
                <dt class="col-sm-4">Common Booking Reference</dt>
                <dd class="col-sm-8 fw-semibold">{{ $order['booking_ref']->booking_ref_code }}</dd>
                <dt class="col-sm-4">Invoice / Booking Reference</dt>
                <dd class="col-sm-8">INV-{{ now()->format('Y') }}-{{ str_pad((string) $order['booking_ref']->id, 6, '0', STR_PAD_LEFT) }}</dd>
                <dt class="col-sm-4">Booking Date</dt>
                <dd class="col-sm-8">{{ $order['booking_ref']->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</dd>
                <dt class="col-sm-4">Payment Method</dt>
                <dd class="col-sm-8">Bank Transfer</dd>
                <dt class="col-sm-4">Booking Status</dt>
                <dd class="col-sm-8">{{ ucfirst($order['booking']->status ?? 'pending') }}</dd>
                @if($order['agent'])
                    <dt class="col-sm-4">Created by Agency</dt>
                    <dd class="col-sm-8">{{ $order['agent']->agency_name }}{{ $order['agent_staff'] ? ' · ' . $order['agent_staff']->full_name : '' }}</dd>
                    <dt class="col-sm-4">Agent Review</dt>
                    <dd class="col-sm-8">{{ strtoupper(str_replace('_', ' ', $order['booking']->agent_verification_status ?? 'pending_admin_verification')) }}</dd>
                @endif
                <dt class="col-sm-4">Payment Status</dt>
                <dd class="col-sm-8">{{ $statusLabel }}</dd>
                <dt class="col-sm-4">Total Amount</dt>
                <dd class="col-sm-8 fw-bold">{{ $order['currency'] }} {{ number_format($order['total'], 2) }}</dd>
            </dl>

            @if($order['agent'])
                <div class="border rounded p-3 mb-3">
                    <h4 class="h6 mb-2">Agent-submitted Traveller Roster</h4>
                    <ul class="mb-0">
                        @foreach($order['trip']?->travellers ?? [] as $rosterTraveller)
                            <li>
                                {{ $rosterTraveller->name }}
                                @if($rosterTraveller->relationship === 'lead') (Responsible Traveller) @endif
                                @if($rosterTraveller->email) · {{ $rosterTraveller->email }} @endif
                                @if($rosterTraveller->phone) · {{ $rosterTraveller->phone }} @endif
                                @if($rosterTraveller->relationship === 'lead' && $rosterTraveller->passport_number) · Passport: {{ $rosterTraveller->passport_number }} @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($agentReviewPending)
                <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <span>This Agent-created booking must be reviewed before payment settlement can continue.</span>
                    <form method="POST" action="{{ route('admin.closed-groups.booking.verify-agent', $order['booking']->id) }}" onsubmit="return confirm('Approve this Agent-created group booking?')">
                        @csrf
                        <button type="submit" class="btn btn-success">Approve Agent Booking</button>
                    </form>
                </div>
            @elseif($order['agent'])
                <div class="alert alert-success">Agent booking approved {{ $order['booking']->agent_verified_at?->format('d/m/Y H:i') ?? '' }}.</div>
            @endif

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <h4 class="h6 mb-0">Invoice Line Items</h4>
                <a href="{{ route('admin.closed-groups.settlement.invoice', $order['booking_ref']->id) }}" class="btn btn-sm btn-outline-primary">Download Invoice PDF</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead>
                        <tr><th>Service</th><th>Description</th><th>Service Reference</th><th>Date</th><th>Qty</th><th class="text-end">Unit (USD)</th><th class="text-end">Amount (USD)</th></tr>
                    </thead>
                    <tbody>
                        @forelse($order['items'] as $item)
                            <tr>
                                <td>{{ $item['type'] }}</td>
                                <td>{{ $item['name'] }}<small class="d-block text-muted">{{ $item['details'] }}</small></td>
                                <td>{{ $item['reference'] }}</td>
                                <td>{{ $item['date'] ?? 'N/A' }}@if($item['end_date'] && $item['end_date'] !== $item['date'])<br>{{ $item['end_date'] }}@endif</td>
                                <td>{{ $item['quantity'] }}</td>
                                <td class="text-end">{{ number_format((float) $item['unit_price'], 2) }}</td>
                                <td class="text-end">{{ number_format((float) $item['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-muted">No service bookings are attached to this common reference.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        @if(abs($order['order_adjustment']) >= 0.01)
                            <tr><th colspan="6" class="text-end">Persisted Order Adjustment</th><th class="text-end">{{ number_format($order['order_adjustment'], 2) }}</th></tr>
                        @endif
                        <tr><th colspan="6" class="text-end">Persisted Order Total</th><th class="text-end">{{ number_format($order['total'], 2) }}</th></tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <section class="bg-white border rounded p-4 mb-4">
            <h3 class="h5 mb-3">Bank Transfer Proof</h3>
            @if($payment->bank_transfer_receipt_path)
                <div class="alert alert-light border d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <strong>{{ $order['agent'] ? 'Agent-submitted receipt' : 'Receipt submitted' }}</strong>
                        <div class="small text-muted">Transfer date: {{ $payment->bank_transfer_date?->format('d/m/Y') ?? 'N/A' }} · Submitted {{ $payment->submitted_at?->format('d/m/Y H:i') ?? '' }}</div>
                        @if($payment->admin_notes)<div class="small mt-1">Notes: {{ $payment->admin_notes }}</div>@endif
                    </div>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.closed-groups.settlement.receipt', $order['booking_ref']->id) }}">Download Receipt</a>
                </div>
                @if($agentReviewPending)
                    <div class="alert alert-warning mt-3 mb-0">The Agent has submitted bank-transfer proof. Approve the Agent booking above before verifying and settling the payment.</div>
                @endif
            @elseif($agentReviewPending)
                <div class="alert alert-secondary mb-0">Waiting for the Agent to upload bank-transfer proof. You can approve the booking above; settlement verification will be available after proof is submitted.</div>
            @elseif($settlementStatus !== 'verified_settled')
                @if(!$order['agent'])
                    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.closed-groups.settlement.proof', $order['booking_ref']->id) }}" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label" for="bank_transfer_date">Date of Transfer <span class="text-danger">*</span></label>
                        <input class="form-control" id="bank_transfer_date" name="bank_transfer_date" type="date" value="{{ old('bank_transfer_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="receipt">Upload Bank Transfer Receipt <span class="text-danger">*</span></label>
                        <input class="form-control" id="receipt" name="receipt" type="file" accept=".pdf,.png,.jpg,.jpeg" required>
                        <div class="form-text">PDF, PNG, JPG or JPEG; maximum 5 MB.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="admin_notes">Admin Notes / Comments</label>
                        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" maxlength="2000">{{ old('admin_notes') }}</textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Submit Bank Transfer Proof</button>
                    </div>
                    </form>
                @else
                    <div class="alert alert-secondary mb-0">Waiting for the Agent to upload bank-transfer proof.</div>
                @endif
            @endif

            @if($payment->bank_transfer_receipt_path && $settlementStatus !== 'verified_settled' && !$agentReviewPending)
                <form method="POST" action="{{ route('admin.closed-groups.settlement.verify', $order['booking_ref']->id) }}" class="mt-3" onsubmit="return confirm('Verify this bank transfer and mark the group order as settled?')">
                    @csrf
                    <button type="submit" class="btn btn-success">Verify &amp; Settle</button>
                </form>
            @elseif($settlementStatus === 'verified_settled')
                <div class="small text-success mt-2">Verified {{ $payment->verified_at?->format('d/m/Y H:i') ?? '' }}. Linked Accommodation, Activity and Transport orders are Processing.</div>
            @endif
        </section>
    @endif

    <section class="bg-white border rounded p-4">
        <h3 class="h5 mb-3">Recent Group Orders</h3>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Common Booking Reference</th><th>Group / Trip</th><th>Amount</th><th>Settlement Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($recentOrders as $recent)
                        <tr>
                            <td>{{ $recent->booking_ref_code }}</td>
                            <td>{{ $recent->trip?->title ?? 'Group booking' }}@php($recentAgent = $recent->bookings->first()?->agentAccount)@if($recentAgent)<small class="d-block text-muted">Agent: {{ $recentAgent->agency_name }}</small>@endif</td>
                            <td>USD {{ number_format((float) $recent->total_amount, 2) }}</td>
                            <td>{{ strtoupper(str_replace('_', ' ', $recent->paymentTransaction?->settlement_status ?? 'pending_verification')) }}</td>
                            <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.closed-groups.settlement', ['booking_reference' => $recent->booking_ref_code]) }}">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">No group orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $recentOrders->links('pagination::bootstrap-5') }}</div>
    </section>
</div>
@endsection