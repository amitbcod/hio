@extends('frontend.layout')

@section('title', 'Booking Confirmed | Holidays.io')
@section('meta_description', 'Your booking has been received and is pending confirmation.')

@section('content')

    <section class="page-section confirm-section">
        <div class="wrap">

            <div class="confirm-box">

                {{-- Status Icon & Heading --}}
                @php
                    $pm = $paymentMethod ?? 'cod';
                    $ps = $paymentStatus ?? 'pending';
                    $normalizedStatus = strtolower(trim($ps));
                    $isPaid = ($pm === 'againgency' && in_array($normalizedStatus, ['paid', 'success', 'successful', 'completed'], true));
                    $isFailed = ($pm === 'againgency' && !$isPaid && $normalizedStatus !== 'pending');
                    $statusLabel = ucfirst($normalizedStatus);

                    // If payment failed for an Againgency payment, treat the booking as Cancelled
                    $displayBookingStatus = null;
                    if (isset($booking) && $booking->booking_status) {
                        $displayBookingStatus = $isFailed ? 'Cancelled' : $booking->booking_status;
                    }
                @endphp

                <div class="confirm-icon">
                    @if($isPaid)
                        <i class="fa-solid fa-circle-check"></i>
                    @elseif($isFailed)
                        <i class="fa-solid fa-circle-xmark" style="color: #e53e3e;"></i>
                    @else
                        <i class="fa-solid fa-circle-check"></i>
                    @endif
                </div>

                @if($isFailed)
                    <h1 class="confirm-heading">{{ __('booking.payment_failed_heading', ['status' => $statusLabel]) }}</h1>
                    <p class="confirm-sub">
                        {{ __('booking.payment_failed_message', ['guestName' => $guestName ? ', ' . $guestName : '', 'statusLabel' => $statusLabel]) }}
                    </p>
                @elseif($isPaid)
                    <h1 class="confirm-heading">{{ __('booking.booking_received') }}</h1>
                    <p class="confirm-sub">
                        {{ __('booking.pending_confirmation_message', ['guestName' => $guestName ? ', ' . $guestName : '']) }}
                    </p>
                @else
                    <h1 class="confirm-heading">{{ __('booking.booking_received') }}</h1>
                    <p class="confirm-sub">
                        {{ __('booking.pending_confirmation_message', ['guestName' => $guestName ? ', ' . $guestName : '']) }}
                    </p>
                @endif

                {{-- Booking Reference(s) --}}
                <div class="confirm-refs">
                    <p class="ref-label">{{ trans_choice('booking.reference_label', count($bookingRefs), ['count' => count($bookingRefs)]) }}</p>
                    @foreach($bookingRefs as $bref)
                        <div class="ref-chip">{{ $bref }}</div>
                    @endforeach
                </div>

                {{-- Summary (if available from session) --}}
                @if(!empty($summary) && ($summary['net_payable'] ?? 0) > 0)
                    <div class="confirm-summary">
                        <div class="confirm-summary-row">
                            <span>{{ __('booking.subtotal') }}</span>
                            <span>{{ $summary['currency'] }} {{ number_format($summary['subtotal'], 2) }}</span>
                        </div>
                        @if(($summary['total_discount'] ?? 0) > 0)
                            <div class="confirm-summary-row confirm-summary-row--green">
                                <span>{{ __('booking.discounts') }}</span>
                                <span>−{{ $summary['currency'] }} {{ number_format($summary['total_discount'], 2) }}</span>
                            </div>
                        @endif
                        @if(($summary['total_tax'] ?? 0) > 0)
                            <div class="confirm-summary-row">
                                <span>{{ __('booking.taxes_charges') }}</span>
                                <span>{{ $summary['currency'] }} {{ number_format($summary['total_tax'], 2) }}</span>
                            </div>
                        @endif
                        <div class="confirm-summary-divider"></div>
                        <div class="confirm-summary-row confirm-summary-row--total">
                            <span>{{ __('booking.net_amount_payable') }}</span>
                            <span>{{ $summary['currency'] }} {{ number_format($summary['net_payable'], 2) }}</span>
                        </div>
                        <div class="confirm-summary-row">
                            <span>{{ __('booking.payment_method_label') }}</span>
                            @if(($paymentMethod ?? 'cod') === 'againgency')
                                <span class="cod-badge"><i class="fa-solid fa-credit-card"></i> {{ __('booking.online_payment_received') }}</span>
                            @else
                                <span class="cod-badge"><i class="fa-solid fa-money-bill-wave"></i> {{ __('booking.cash_on_delivery') }}</span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Booking Details (from DB) --}}
                @if($booking)
                    <div class="confirm-details">
                        <h2 class="confirm-details-title">{{ __('booking.details_title') }}</h2>
                        <div class="confirm-details-grid">
                            @if($type === 'accommodation')
                                @if($booking->check_in_date)
                                    <div class="confirm-detail-item">
                                        <span class="detail-key">{{ __('booking.check_in') }}</span>
                                        <span class="detail-val">{{ $booking->check_in_date->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="confirm-detail-item">
                                        <span class="detail-key">{{ __('booking.check_out') }}</span>
                                        <span class="detail-val">{{ $booking->check_out_date->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                            @elseif($type === 'activity')
                                @if($booking->activity_date)
                                    <div class="confirm-detail-item">
                                        <span class="detail-key">{{ __('booking.activity_date') }}</span>
                                        <span class="detail-val">{{ $booking->activity_date->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                           @elseif($type === 'transport')
                                <div class="confirm-detail-item detail-full-width">
                                    <span class="detail-key">{{ __('booking.transport_details') }}</span>
                                </div>
                                @if($booking->pickup_date)
                                    <div class="confirm-detail-item">
                                        <span class="detail-key">{{ __('booking.transport_pickup_date') }}</span>
                                        <span class="detail-val">{{ $booking->pickup_date->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                                @if($booking->pickup_time)
                                    <div class="confirm-detail-item">
                                        <span class="detail-key">{{ __('booking.transport_pickup_time') }}</span>
                                        <span class="detail-val">{{ $booking->pickup_time }}</span>
                                    </div>
                                @endif
                                @if($booking->return_date)
                                    <div class="confirm-detail-item">
                                        <span class="detail-key">{{ __('booking.transport_return_date') }}</span>
                                        <span class="detail-val">{{ $booking->return_date->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                                @if($booking->return_time)
                                    <div class="confirm-detail-item">
                                        <span class="detail-key">{{ __('booking.transport_return_time') }}</span>
                                        <span class="detail-val">{{ $booking->return_time }}</span>
                                    </div>
                                @endif
                                <div class="confirm-detail-item">
                                    <span class="detail-key">{{ __('booking.transport_vehicle') }}</span>
                                    <span class="detail-val">
                                        {{ optional($booking->transport)->vehicle_name ?? __('traveler.common.not_available') }}
                                        {{ optional($booking->transport)->vehicle_type ? ' (' . optional($booking->transport)->vehicle_type . ')' : '' }}
                                    </span>
                                </div>
                                <div class="confirm-detail-item">
                                    <span class="detail-key">{{ __('booking.transport_seating_capacity') }}</span>
                                    <span class="detail-val">
                                        {{ optional($booking->transport)->seating_capacity ?? __('traveler.common.not_available') }}
                                    </span>
                                </div>
                                <div class="confirm-detail-item">
                                    <span class="detail-key">{{ __('booking.transport_route') }}</span>
                                    <span class="detail-val">{{ $booking->route_from }} → {{ $booking->route_to }}</span>
                                </div>
                            @endif
                            <div class="confirm-detail-item">
                                <span class="detail-key">{{ __('booking.guests') }}</span>
                                <span class="detail-val">{{ $booking->adults }} {{ trans_choice('booking.adults_label', $booking->adults, ['count' => $booking->adults]) }}
                                    @if($booking->children > 0)
                                        , {{ $booking->children }} {{ trans_choice('booking.children_label', $booking->children, ['count' => $booking->children]) }}
                                    @endif
                                </span>
                            </div>
                            <div class="confirm-detail-item">
                                <span class="detail-key">{{ __('booking.status') }}</span>
                                <span class="detail-val status-badge status-{{ strtolower($displayBookingStatus ?? ($booking->booking_status ?? 'pending')) }}">
                                    {{ $displayBookingStatus ?? ($booking->booking_status ?? 'Pending') }}
                                </span>
                            </div>
                            @if($booking->guest_email)
                                <div class="confirm-detail-item">
                                    <span class="detail-key">{{ __('booking.email') }}</span>
                                    <span class="detail-val">{{ $booking->guest_email }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if(!empty($relatedTransportBookings) && $relatedTransportBookings->isNotEmpty())
                    <!-- <div class="confirm-details">
                        <h2 class="confirm-details-title">{{ __('booking.transport_details') ?? 'Transport Details' }}</h2>
                        @foreach($relatedTransportBookings as $transportBooking)
                            <div class="confirm-details-grid">
                                @if($transportBooking->pickup_date)
                                    <div class="confirm-detail-item">
                                        <span class="detail-key">Pickup Date</span>
                                        <span class="detail-val">{{ $transportBooking->pickup_date->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                                @if($transportBooking->pickup_time)
                                    <div class="confirm-detail-item">
                                        <span class="detail-key">Pickup Time</span>
                                        <span class="detail-val">{{ $transportBooking->pickup_time }}</span>
                                    </div>
                                @endif
                                @if($transportBooking->return_date)
                                    <div class="confirm-detail-item">
                                        <span class="detail-key">Return Date</span>
                                        <span class="detail-val">{{ $transportBooking->return_date->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                                @if($transportBooking->return_time)
                                    <div class="confirm-detail-item">
                                        <span class="detail-key">Return Time</span>
                                        <span class="detail-val">{{ $transportBooking->return_time }}</span>
                                    </div>
                                @endif
                                <div class="confirm-detail-item">
                                    <span class="detail-key">Vehicle</span>
                                    <span class="detail-val">{{ optional($transportBooking->transport)->vehicle_name ?? 'N/A' }}{{ optional($transportBooking->transport)->vehicle_type ? ' (' . optional($transportBooking->transport)->vehicle_type . ')' : '' }}</span>
                                </div>
                                <div class="confirm-detail-item">
                                    <span class="detail-key">Seating Capacity</span>
                                    <span class="detail-val">{{ optional($transportBooking->transport)->seating_capacity ?? 'N/A' }}</span>
                                </div>
                                <div class="confirm-detail-item">
                                    <span class="detail-key">Route</span>
                                    <span class="detail-val">{{ $transportBooking->route_from }} → {{ $transportBooking->route_to }}</span>
                                </div>
                                <div class="confirm-detail-item">
                                    <span class="detail-key">Booking Reference</span>
                                    <span class="detail-val">{{ $transportBooking->booking_reference }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div> -->

                    <div class="confirm-details">
    <h2 class="confirm-details-title">{{ __('booking.transport_details') }}</h2>

    @foreach($relatedTransportBookings as $transportBooking)
        <div class="confirm-details-grid">

            @if($transportBooking->pickup_date)
                <div class="confirm-detail-item">
                    <span class="detail-key">{{ __('booking.transport_pickup_date') }}</span>
                    <span class="detail-val">{{ $transportBooking->pickup_date->format('d/m/Y') }}</span>
                </div>
            @endif

            @if($transportBooking->pickup_time)
                <div class="confirm-detail-item">
                    <span class="detail-key">{{ __('booking.transport_pickup_time') }}</span>
                    <span class="detail-val">{{ $transportBooking->pickup_time }}</span>
                </div>
            @endif

            @if($transportBooking->return_date)
                <div class="confirm-detail-item">
                    <span class="detail-key">{{ __('booking.transport_return_date') }}</span>
                    <span class="detail-val">{{ $transportBooking->return_date->format('d/m/Y') }}</span>
                </div>
            @endif

            @if($transportBooking->return_time)
                <div class="confirm-detail-item">
                    <span class="detail-key">{{ __('booking.transport_return_time') }}</span>
                    <span class="detail-val">{{ $transportBooking->return_time }}</span>
                </div>
            @endif

            <div class="confirm-detail-item">
                <span class="detail-key">{{ __('booking.transport_vehicle') }}</span>
                <span class="detail-val">
                    {{ optional($transportBooking->transport)->vehicle_name ?? __('traveler.common.not_available') }}
                    {{ optional($transportBooking->transport)->vehicle_type ? ' (' . optional($transportBooking->transport)->vehicle_type . ')' : '' }}
                </span>
            </div>

            <div class="confirm-detail-item">
                <span class="detail-key">{{ __('booking.transport_seating_capacity') }}</span>
                <span class="detail-val">
                    {{ optional($transportBooking->transport)->seating_capacity ?? __('traveler.common.not_available') }}
                </span>
            </div>

            <div class="confirm-detail-item">
                <span class="detail-key">{{ __('booking.transport_route') }}</span>
                <span class="detail-val">{{ $transportBooking->route_from }} → {{ $transportBooking->route_to }}</span>
            </div>

            <div class="confirm-detail-item">
                <span class="detail-key">{{ __('booking.booking_reference') }}</span>
                <span class="detail-val">{{ $transportBooking->booking_reference }}</span>
            </div>

        </div>
    @endforeach
</div>
                @endif

                {{-- What's next --}}
                <div class="confirm-next">
                    <h3>{{ __('booking.what_happens_next') }}</h3>
                    <ol class="confirm-steps">
                        @if(($paymentStatus ?? 'pending') === 'paid')
                        <li>
                            <i class="fa-solid fa-envelope"></i>
                            <div>
                                <strong>{{ __('booking.confirmation_email') }}</strong>
                                <p>{{ __('booking.confirmation_email_message') }}</p>
                            </div>
                        </li>
                        @endif
                        <li>
                            <i class="fa-solid fa-phone"></i>
                            <div>
                                <strong>{{ __('booking.operator_contact') }}</strong>
                                <p>{{ __('booking.operator_contact_message') }}</p>
                            </div>
                        </li>
                        <li>
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <div>
                                @if(($paymentMethod ?? 'cod') === 'againgency')
                                    @if(($paymentStatus ?? 'pending') === 'paid')
                                        <strong>{{ __('booking.online_payment_received') }}</strong>
                                        <p>{{ __('booking.online_payment_received_message') }}</p>
                                    @elseif(($paymentStatus ?? 'pending') === 'failed')
                                        <strong style="color:#e53e3e">{{ __('booking.payment_failed') }}</strong>
                                        <p>{{ __('booking.payment_failed_detail') }}</p>
                                    @else
                                        <strong>{{ __('booking.payment_pending') }}</strong>
                                        <p>{{ __('booking.payment_pending_detail') }}</p>
                                    @endif
                                @else
                                    <strong>{{ __('booking.pay_on_arrival') }}</strong>
                                    <p>{{ __('booking.pay_on_arrival_message') }}</p>
                                @endif
                            </div>
                        </li>
                    </ol>
                </div>

                {{-- CTA Buttons --}}
                <div class="confirm-actions">
                    <a href="{{ url('/') }}" class="btn-primary">
                        <i class="fa-solid fa-house"></i> {{ __('booking.back_to_home') }}
                    </a>
                    <a href="{{ url('/category-list') }}" class="btn-outline">
                        <i class="fa-solid fa-magnifying-glass"></i> {{ __('booking.continue_browsing') }}
                    </a>
                </div>

                {{-- Support --}}
                <p class="confirm-support">
                    {{ __('booking.need_help') }} <a href="tel:+23052511153">+230 5251 11 53</a>
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
.confirm-detail-item.detail-full-width { grid-column: 1 / -1; padding-bottom: 12px; }
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
