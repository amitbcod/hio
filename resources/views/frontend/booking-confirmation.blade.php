@extends('frontend.layout')

@section('title', 'Booking Confirmed | Holidays.io')
@section('meta_description', 'Your booking has been received and is pending confirmation.')

@section('content')

    <section class="page-section confirm-section">
        <div class="wrap">

            <div class="confirm-box">

                {{-- Success Icon --}}
                <div class="confirm-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <h1 class="confirm-heading">Booking Received!</h1>
                <p class="confirm-sub">
                    Thank you{{ $guestName ? ', ' . $guestName : '' }}. Your booking is <strong>pending confirmation</strong>.
                    Our team will get back to you within <strong>24 hours</strong>.
                </p>

                {{-- Booking Reference(s) --}}
                <div class="confirm-refs">
                    <p class="ref-label">Booking Reference{{ count($bookingRefs) > 1 ? 's' : '' }}</p>
                    @foreach($bookingRefs as $bref)
                        <div class="ref-chip">{{ $bref }}</div>
                    @endforeach
                </div>

                {{-- Summary (if available from session) --}}
                @if(!empty($summary) && ($summary['net_payable'] ?? 0) > 0)
                    <div class="confirm-summary">
                        <div class="confirm-summary-row">
                            <span>Subtotal</span>
                            <span>{{ $summary['currency'] }} {{ number_format($summary['subtotal'], 2) }}</span>
                        </div>
                        @if(($summary['total_discount'] ?? 0) > 0)
                            <div class="confirm-summary-row confirm-summary-row--green">
                                <span>Discounts</span>
                                <span>−{{ $summary['currency'] }} {{ number_format($summary['total_discount'], 2) }}</span>
                            </div>
                        @endif
                        @if(($summary['total_tax'] ?? 0) > 0)
                            <div class="confirm-summary-row">
                                <span>Taxes &amp; Charges</span>
                                <span>{{ $summary['currency'] }} {{ number_format($summary['total_tax'], 2) }}</span>
                            </div>
                        @endif
                        <div class="confirm-summary-divider"></div>
                        <div class="confirm-summary-row confirm-summary-row--total">
                            <span>Net Amount Payable</span>
                            <span>{{ $summary['currency'] }} {{ number_format($summary['net_payable'], 2) }}</span>
                        </div>
                        <div class="confirm-summary-row">
                            <span>Payment Method</span>
                            <span class="cod-badge"><i class="fa-solid fa-money-bill-wave"></i> Cash on Delivery</span>
                        </div>
                    </div>
                @endif

                {{-- Booking Details (from DB) --}}
                @if($booking)
                    <div class="confirm-details">
                        <h2 class="confirm-details-title">Booking Details</h2>
                        <div class="confirm-details-grid">
                            @if($type === 'accommodation')
                                @if($booking->check_in_date)
                                    <div class="confirm-detail-item">
                                        <span class="detail-key">Check-in</span>
                                        <span class="detail-val">{{ $booking->check_in_date->format('d M Y') }}</span>
                                    </div>
                                    <div class="confirm-detail-item">
                                        <span class="detail-key">Check-out</span>
                                        <span class="detail-val">{{ $booking->check_out_date->format('d M Y') }}</span>
                                    </div>
                                @endif
                            @else
                                @if($booking->activity_date)
                                    <div class="confirm-detail-item">
                                        <span class="detail-key">Activity Date</span>
                                        <span class="detail-val">{{ $booking->activity_date->format('d M Y') }}</span>
                                    </div>
                                @endif
                            @endif
                            <div class="confirm-detail-item">
                                <span class="detail-key">Guests</span>
                                <span class="detail-val">{{ $booking->adults }} Adult{{ $booking->adults != 1 ? 's' : '' }}
                                    @if($booking->children > 0)
                                        , {{ $booking->children }} Child{{ $booking->children != 1 ? 'ren' : '' }}
                                    @endif
                                </span>
                            </div>
                            <div class="confirm-detail-item">
                                <span class="detail-key">Status</span>
                                <span class="detail-val status-badge status-{{ strtolower($booking->booking_status) }}">
                                    {{ $booking->booking_status }}
                                </span>
                            </div>
                            @if($booking->guest_email)
                                <div class="confirm-detail-item">
                                    <span class="detail-key">Email</span>
                                    <span class="detail-val">{{ $booking->guest_email }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- What's next --}}
                <div class="confirm-next">
                    <h3>What happens next?</h3>
                    <ol class="confirm-steps">
                        <li>
                            <i class="fa-solid fa-envelope"></i>
                            <div>
                                <strong>Confirmation Email</strong>
                                <p>We'll send booking details to your email address.</p>
                            </div>
                        </li>
                        <li>
                            <i class="fa-solid fa-phone"></i>
                            <div>
                                <strong>Operator Contact</strong>
                                <p>The property / activity operator may call to confirm.</p>
                            </div>
                        </li>
                        <li>
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <div>
                                <strong>Pay on Arrival</strong>
                                <p>Cash on Delivery — settle payment when you check in.</p>
                            </div>
                        </li>
                    </ol>
                </div>

                {{-- CTA Buttons --}}
                <div class="confirm-actions">
                    <a href="{{ url('/') }}" class="btn-primary">
                        <i class="fa-solid fa-house"></i> Back to Home
                    </a>
                    <a href="{{ url('/category-list') }}" class="btn-outline">
                        <i class="fa-solid fa-magnifying-glass"></i> Continue Browsing
                    </a>
                </div>

                {{-- Support --}}
                <p class="confirm-support">
                    Need help? Call us: <a href="tel:+23052511153">+230 5251 11 53</a>
                </p>

            </div>

        </div>
    </section>

@endsection

@push('styles')
<style>
.confirm-section { padding: 48px 0 64px; }

.confirm-box {
    max-width: 680px;
    margin: 0 auto;
    text-align: center;
}

.confirm-icon {
    font-size: 72px;
    color: #1a7f37;
    margin-bottom: 20px;
    animation: popIn .4s ease;
}
@keyframes popIn {
    0%   { transform: scale(.5); opacity: 0; }
    80%  { transform: scale(1.1); }
    100% { transform: scale(1);   opacity: 1; }
}

.confirm-heading {
    font-size: 32px;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0 0 8px;
}
.confirm-sub {
    font-size: 15px;
    color: #555;
    margin: 0 0 24px;
    line-height: 1.7;
}

/* Refs */
.confirm-refs { margin-bottom: 28px; }
.ref-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #888;
    margin-bottom: 8px;
}
.ref-chip {
    display: inline-block;
    background: #f0f0f5;
    border: 1px solid #d8d8e8;
    border-radius: 8px;
    padding: 8px 18px;
    font-size: 16px;
    font-weight: 700;
    font-family: monospace;
    color: #1a1a2e;
    letter-spacing: .04em;
    margin: 4px;
}

/* Summary */
.confirm-summary {
    background: #fff;
    border: 1px solid #e8e8ef;
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 24px;
    text-align: left;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.confirm-summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    color: #444;
    padding: 6px 0;
}
.confirm-summary-row--green { color: #1a7f37; font-weight: 600; }
.confirm-summary-row--total { font-size: 16px; font-weight: 800; color: #1a1a2e; }
.confirm-summary-divider { height: 1px; background: #eee; margin: 8px 0; }
.cod-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #fffbe6;
    border: 1px solid #f6d860;
    border-radius: 6px;
    padding: 2px 10px;
    font-size: 12px;
    font-weight: 600;
    color: #7a5e00;
}

/* Details Grid */
.confirm-details {
    background: #fff;
    border: 1px solid #e8e8ef;
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 24px;
    text-align: left;
}
.confirm-details-title {
    font-size: 15px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 14px;
}
.confirm-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.confirm-detail-item { display: flex; flex-direction: column; gap: 3px; }
.detail-key {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #888;
}
.detail-val { font-size: 14px; font-weight: 600; color: #1a1a2e; }

.status-pending  { color: #d97706; }
.status-confirmed { color: #1a7f37; }
.status-cancelled { color: #e53e3e; }

/* What's Next */
.confirm-next {
    background: #f8f8ff;
    border-radius: 16px;
    padding: 22px 24px;
    margin-bottom: 28px;
    text-align: left;
}
.confirm-next h3 {
    font-size: 15px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 14px;
}
.confirm-steps {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.confirm-steps li {
    display: flex;
    gap: 14px;
    align-items: flex-start;
}
.confirm-steps li > i {
    width: 36px; height: 36px;
    background: #1a1a2e;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 14px;
}
.confirm-steps li strong { display: block; font-size: 14px; color: #1a1a2e; margin-bottom: 2px; }
.confirm-steps li p { font-size: 13px; color: #666; margin: 0; }

/* Actions */
.confirm-actions {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 13px 24px;
    border: 2px solid #1a1a2e;
    color: #1a1a2e;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    transition: background .2s, color .2s;
}
.btn-outline:hover { background: #1a1a2e; color: #fff; }

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 13px 24px;
    background: #1a1a2e;
    color: #fff;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    transition: background .2s;
}
.btn-primary:hover { background: #16213e; color: #fff; }

.confirm-support {
    font-size: 13px;
    color: #888;
}
.confirm-support a {
    color: #1a1a2e;
    font-weight: 600;
    text-decoration: none;
}
.confirm-support a:hover { text-decoration: underline; }

@media (max-width: 560px) {
    .confirm-details-grid { grid-template-columns: 1fr; }
    .confirm-heading { font-size: 24px; }
}
</style>
@endpush
