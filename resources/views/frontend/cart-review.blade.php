@extends('frontend.layout')

@section('title', 'Review Your Booking | Holidays.io')
@section('meta_description', 'Review your selected items before proceeding to checkout.')

@section('content')

    {{-- ── Flash Messages ──────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="cart-flash cart-flash--ok">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="cart-flash cart-flash--err">{{ session('error') }}</div>
    @endif

    <section class="page-section cart-section">
        <div class="wrap">

            {{-- ── Page Header ─────────────────────────────────────────────── --}}
            <div class="cart-page-header">
                <div class="breadcrumbs">
                    <a href="{{ url('/') }}">{{ __('site.home') }}</a>
                    <span>/</span>
                    <span>{{ __('cart.booking_cart') }}</span>
                </div>
                <h1 class="cart-title">
                    {{ __('cart.review_title') }}
                    @if(!empty($cart))
                        <span class="cart-badge">{{ count($cart) }}</span>
                    @endif
                </h1>
            </div>

            @if(empty($cart))
                {{-- ── Empty State ──────────────────────────────────────────── --}}
                <div class="cart-empty">
                    <div class="cart-empty-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                    <h2>{{ __('cart.empty_title') }}</h2>
                    <p>{{ __('cart.empty_paragraph') }}</p>
                    <a href="{{ url('/') }}" class="btn-primary">{{ __('cart.explore_now') }}</a>
                </div>
            @else
                <div class="cart-layout">

                    {{-- ════════════════════════════════════════════════════════
                         LEFT — Cart Items
                    ═══════════════════════════════════════════════════════════ --}}
                    <div class="cart-items">

                        @foreach($cart as $cartKey => $item)
                            @php
                                $isAccom = $item['type'] === 'accommodation';
                                $isActivity = $item['type'] === 'activity';
                                $isTransport = $item['type'] === 'transport';
                                $nights  = (int) ($item['nights'] ?? 1);
                                $rooms   = (int) ($item['rooms'] ?? 1);
                                $adults  = (int) ($item['adults'] ?? 0);
                                $children = (int) ($item['children'] ?? 0);
                                $infants = (int) ($item['infants'] ?? 0);
                                if ($isAccom) {
                                    $roomLabel = trans_choice('cart.rooms', $rooms, ['count' => $rooms]);
                                    $nightLabel = trans_choice('cart.nights', $nights, ['count' => $nights]);
                                    $label = $roomLabel . ' · ' . $nightLabel . ' · ' . ($item['room_name'] ?? __('traveler.trip_detail.room'));
                                    $subParts = [];
                                    if ($adults > 0) $subParts[] = trans_choice('accommodation.summary.adults', $adults, ['count' => $adults]);
                                    if ($children > 0) $subParts[] = trans_choice('accommodation.summary.children', $children, ['count' => $children]);
                                    if ($infants > 0) $subParts[] = trans_choice('accommodation.summary.infants', $infants, ['count' => $infants]);
                                    $subLabel = implode(', ', $subParts);
                                    $subLabel = $subLabel ? ($subLabel . ' · ' . $rooms . '× ' . ($item['room_name'] ?? __('traveler.trip_detail.room'))) : '';
                                } else if ($isActivity) {
                                    $label = __('cart.type.activity') . ' · ' . ($item['variant_name'] ?? $item['title'] ?? __('traveler.trip_detail.standard'));
                                    $subLabel = $adults > 0 ? trans_choice('accommodation.summary.adults', $adults, ['count' => $adults]) : '';
                                } else if ($isTransport) {
                                    $label = __('cart.type.transport') . ' · ' . trim((string) (($item['route_from'] ?? '') . ($item['route_to'] ? ' → ' . $item['route_to'] : '')));
                                    $subLabel = __('home.search.passengers') . ': ' . ($item['passengers'] ?? '1');
                                } else {
                                    $label = $item['variant_name'] ?? $item['title'] ?? 'Booking';
                                    $subLabel = '';
                                }
                            @endphp

                            <div class="cart-item-card">
                                {{-- Image --}}
                                <div class="cart-item-img">
                                    @if(!empty($item['image']))
                                        <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                                    @else
                                        <div class="cart-item-img-placeholder">
                                            <i class="fa-solid {{ $isAccom ? 'fa-hotel' : ($isTransport ? 'fa-car' : 'fa-person-hiking') }}"></i>
                                        </div>
                                    @endif
                                </div>

                                {{-- Details --}}
                                <div class="cart-item-body">
                                    <div class="cart-item-top">
                                        <div>
                                            <span class="cart-item-badge">{{ $isAccom ? __('cart.type.stay') : ($isTransport ? __('cart.type.transport') : __('cart.type.activity')) }}</span>
                                            <h3 class="cart-item-title">{{ $item['title'] }}</h3>
                                            @if($isAccom && !empty($item['plan_label']))
                                                <p class="cart-item-sub" style="color: #19b5b5; font-weight: 500;">{{ $item['plan_label'] }} • {{ $item['pricing_setting'] ?? 'Per Room/Night' }}</p>
                                            @endif
                                            <p class="cart-item-sub">{{ $subLabel }}</p>
                                        </div>
                                        <div class="cart-item-price-col">
                                            <div class="cart-item-price">
                                                {{ $item['currency'] }} {{ number_format($item['total_price'], 2) }}
                                            </div>
                                        @php
                                        if($isAccom) {
                                        @endphp
                                            <div class="cart-item-nights">
                                                {{ trans_choice('cart.nights_total', $nights, ['count' => $nights]) }}
                                            </div>
                                        @php
                                        }
                                        @endphp
                                        </div>
                                    </div>

                                    @php
                                        $checkInDisplay = $item['check_in_display'] ?? $item['pickup_date_display'] ?? '';
                                        $checkOutDisplay = $item['check_out_display'] ?? $item['return_date_display'] ?? '';
                                        $checkInValue = $item['check_in'] ?? $item['pickup_date'] ?? null;
                                        $checkOutValue = $item['check_out'] ?? $item['return_date'] ?? null;
                                    @endphp
                                    <div class="cart-item-dates">
                                        <span><i class="fa-regular fa-calendar"></i>
                                            {{ $checkInDisplay }}
                                        </span>
                                        @if(!$isAccom || $checkInValue !== $checkOutValue)
                                            <span class="cart-item-arrow">→</span>
                                            <span>{{ $checkOutDisplay }}</span>
                                        @endif
                                    </div>

                                    @if($item['discount_amount'] > 0)
                                        <div class="cart-item-promo">
                                            <i class="fa-solid fa-tag"></i>
                                            {{ __('cart.discount_applied') }} −{{ $item['currency'] }} {{ number_format($item['discount_amount'], 2) }}
                                            @if($item['is_non_refundable'])
                                                <span class="non-refundable-badge">{{ __('cart.non_refundable') }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="cart-item-actions">
                                        <a href="{{ $isAccom
                                            ? route('frontend.accommodations.show', $item['accommodation_id'])
                                            : ($isActivity
                                                ? route('frontend.activities.show', $item['activity_id'])
                                                : route('frontend.transports.show', $item['transport_id'] ?? null)) }}"
                                           class="cart-link">
                                            <i class="fa-solid fa-eye"></i> {{ __('cart.view_rules') }}
                                        </a>
                                        @if($isAccom && !empty($item['accommodation_id']))
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($item['title']) }}"
                                               target="_blank" rel="noopener" class="cart-link">
                                                <i class="fa-solid fa-location-dot"></i> {{ __('cart.get_directions') }}
                                            </a>
                                        @endif
                                        <a href="tel:+23052511153" class="cart-link">
                                            <i class="fa-solid fa-phone"></i> {{ __('cart.call_us') }}
                                        </a>

                                        {{-- Remove --}}
                                        <form method="POST" action="{{ route('frontend.booking.cart.remove') }}" class="cart-remove-form">
                                            @csrf
                                            <input type="hidden" name="cart_key" value="{{ $cartKey }}">
                                            <button type="submit" class="cart-remove-btn" title="{{ __('cart.remove_item') }}">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>

                    {{-- ════════════════════════════════════════════════════════
                         RIGHT — Checkout Summary Sidebar
                    ═══════════════════════════════════════════════════════════ --}}
                    <aside class="checkout-summary">
                        <div class="summary-card">
                            <h2 class="summary-heading">{{ __('cart.checkout_summary') }}</h2>

                            {{-- Promo Code --}}
                            <!-- <div class="promo-box">
                                <label class="promo-label">Have a promo code?</label>
                                <div class="promo-input-row">
                                    <input type="text" placeholder="Enter code" class="promo-input" id="promoCodeInput">
                                    <button class="promo-apply-btn" type="button">Apply</button>
                                </div>
                            </div> -->

                            <div class="summary-divider"></div>

                            {{-- Fare Summary --}}
                            <h3 class="fare-heading">{{ __('cart.fare_summary') }}</h3>

                            <div class="fare-rows">
                                @foreach($cart as $item)
                                    @php
                                        $nights = (int) ($item['nights'] ?? 1);
                                        $rooms  = (int) ($item['rooms'] ?? 1);
                                        if ($item['type'] === 'accommodation') {
                                            $label = trans_choice('cart.rooms', $rooms, ['count' => $rooms]) . ' · ' . trans_choice('cart.nights', $nights, ['count' => $nights]);
                                        } else if ($item['type'] === 'activity') {
                                            $label = __('cart.type.activity') . ': ' . ($item['variant_name'] ?? $item['title']);
                                        } else {
                                            $transportServiceType = trim((string) ($item['service_type'] ?? ''));
                                            $transportServiceTypeLabel = '';
                                            if ($transportServiceType !== '') {
                                                $translatedServiceType = __('transport.form.' . $transportServiceType, [], app()->getLocale());
                                                $transportServiceTypeLabel = $translatedServiceType !== 'transport.form.' . $transportServiceType
                                                    ? $translatedServiceType
                                                    : ucwords(str_replace(['_', '-'], ' ', $transportServiceType));
                                            }
                                            $routeLabel = trim((string) (($item['route_from'] ?? '') . ($item['route_to'] ? ' → ' . $item['route_to'] : '')));
                                            $label = __('cart.type.transport') . ': ' . ($transportServiceTypeLabel ?: __('cart.type.transport'));
                                            if ($routeLabel !== '') {
                                                $label .= ' · ' . $routeLabel;
                                            }
                                        }
                                    @endphp
                                    <div class="fare-row">
                                        <span>{{ $label }}</span>
                                        <span>{{ $item['currency'] }} {{ number_format($item['total_price'], 2) }}</span>
                                    </div>
                                @endforeach

                                @if($summary['total_discount'] > 0)
                                    <div class="fare-row fare-row--discount">
                                        <span><i class="fa-solid fa-tag"></i> {{ __('cart.discounts') }}</span>
                                        <span>−{{ $summary['currency'] }} {{ number_format($summary['total_discount'], 2) }}</span>
                                    </div>
                                    <div class="fare-row">
                                        <span>{{ __('cart.price_after_discount') }}</span>
                                        <span>{{ $summary['currency'] }} {{ number_format($summary['price_after_discount'], 2) }}</span>
                                    </div>
                                @endif

                                @if($summary['total_tax'] > 0)
                                    <div class="fare-row">
                                        <span>{{ __('booking.taxes_charges') }}</span>
                                        <span>{{ $summary['currency'] }} {{ number_format($summary['total_tax'], 2) }}</span>
                                    </div>
                                @endif

                                @if($summary['total_fees'] > 0)
                                    <div class="fare-row">
                                        <span>{{ __('cart.fees') }}</span>
                                        <span>{{ $summary['currency'] }} {{ number_format($summary['total_fees'], 2) }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="summary-divider"></div>

                            <div class="fare-row fare-row--total">
                                <span>{{ __('cart.net_amount_payable') }}</span>
                                <span>{{ $summary['currency'] }} {{ number_format($summary['net_payable'], 2) }}</span>
                            </div>

                            <button type="button" class="btn-checkout" id="proceedCheckoutBtn">
                                {{ __('cart.proceed_to_checkout') }}
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>

                            <!-- <p class="summary-note">
                                <i class="fa-solid fa-shield-halved"></i>
                                Cash on Delivery — Pay when your booking is confirmed.
                            </p> -->
                        </div>
                    </aside>

                </div>{{-- .cart-layout --}}
            @endif
            
            <div class="tax-notice">
                <p><strong><i class="fa-solid fa-circle-exclamation"></i> Tourism Tax Notice</strong>
Please be informed that the tourism tax is not included in your booking total. This must be paid directly at the property in cash (EUR) during your stay and is charged at EUR 3.00 per person per night.</p>
            </div>

            <!-- ═════════════════════════════════════════════════════════════
                  Checkout Options Modal (Guest vs Login)
                 ═════════════════════════════════════════════════════════════ -->
            <div id="checkoutOptionsModal" class="modal" style="display: none;">
                <div class="modal-overlay"></div>
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>{{ __('cart.checkout_modal.title') }}</h2>
                        <button type="button" class="modal-close" onclick="closeCheckoutModal()">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="checkout-options">
                            <!-- Option 1: Guest Checkout -->
                            <div class="checkout-option-card">
                                <div class="option-icon">
                                    <i class="fa-solid fa-user-plus"></i>
                                </div>
                                <h3>{{ __('cart.checkout_modal.guest_title') }}</h3>
                                <p>{{ __('cart.checkout_modal.guest_desc') }}</p>
                                <button type="button" class="btn-option-primary" onclick="proceedGuestCheckout()">
                                    {{ __('cart.checkout_modal.guest_button') }}
                                </button>
                            </div>

                            <!-- Option 2: Login -->
                            <div class="checkout-option-card">
                                <div class="option-icon">
                                    <i class="fa-solid fa-sign-in-alt"></i>
                                </div>
                                <h3>{{ __('cart.checkout_modal.login_title') }}</h3>
                                <p>{{ __('cart.checkout_modal.login_desc') }}</p>
                                <button type="button" class="btn-option-primary" onclick="proceedLogin()">
                                    {{ __('cart.checkout_modal.login_button') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <p class="option-note">
                            <i class="fa-solid fa-lock-open"></i>
                            {{ __('cart.checkout_modal.footer_note') }}
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </section>

@endsection

@push('styles')
<style>

.tax-notice {
        max-width: 740px;
    margin: 50px auto 0;
    font-size: 14px;
    border: 0px;
    padding: 10px 30px;
    color: #ffffff;
    background-color: #ff8a00;
}

.tax-notice strong {
    display: block;
    margin-bottom: 8px;
    color:#ffffff
}


/* ── Flash ── */
.cart-flash {
    padding: 14px 20px;
    text-align: center;
    font-weight: 600;
    font-size: 14px;
}
.cart-flash--ok  { background: #d4edda; color: #155724; }
.cart-flash--err { background: #f8d7da; color: #721c24; }

/* ── Page Header ── */
.cart-section { padding-top: 32px; }

.cart-page-header { margin-bottom: 28px; }

.cart-title {
    font-size: 26px;
    font-weight: 800;
    color: #1a1a2e;
    margin: 6px 0 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.cart-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: #1a1a2e;
    color: #fff;
    border-radius: 50%;
    font-size: 13px;
}

/* ── Empty ── */
.cart-empty {
    text-align: center;
    padding: 72px 24px;
}
.cart-empty-icon {
    font-size: 56px;
    color: #ccc;
    margin-bottom: 20px;
}
.cart-empty h2 { margin: 0 0 12px; font-size: 22px; }
.cart-empty p  { color: #666; margin-bottom: 24px; }
.cart-empty a:not(.btn-primary) { color: #1a1a2e; font-weight: 600; }

/* ── Layout ── */
.cart-layout {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 28px;
    align-items: start;
}

/* ── Item Cards ── */
.cart-items { display: flex; flex-direction: column; gap: 18px; }

.cart-item-card {
    display: flex;
    gap: 16px;
    background: #fff;
    border: 1px solid #e8e8ef;
    border-radius: 16px;
    padding: 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}

.cart-item-img {
    flex-shrink: 0;
    width: 120px;
    height: 100px;
    border-radius: 12px;
    overflow: hidden;
    background: #f0f0f5;
}
.cart-item-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cart-item-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #bbb;
}

.cart-item-body { flex: 1; min-width: 0; }

.cart-item-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
}

.cart-item-badge {
    display: inline-block;
    background: #eff0ff;
    color: #3a3abf;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 4px;
    margin-bottom: 4px;
}

.cart-item-title {
    font-size: 15px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 320px;
}

.cart-item-sub {
    font-size: 13px;
    color: #666;
    margin: 0;
}

.cart-item-price-col { text-align: right; flex-shrink: 0; }

.cart-item-price {
    font-size: 18px;
    font-weight: 800;
    color: #1a1a2e;
    white-space: nowrap;
}
.cart-item-nights {
    font-size: 12px;
    color: #888;
    margin-top: 2px;
}

.cart-item-dates {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 10px 0 8px;
    font-size: 13px;
    color: #555;
}
.cart-item-dates i { color: #888; margin-right: 3px; }
.cart-item-arrow { color: #bbb; }

.cart-item-promo {
    font-size: 12px;
    color: #1a7f37;
    font-weight: 600;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.non-refundable-badge {
    background: #fff3cd;
    color: #856404;
    font-size: 11px;
    padding: 1px 6px;
    border-radius: 4px;
}

.cart-item-actions {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-top: 4px;
}
.cart-link {
    font-size: 12px;
    color: #1a1a2e;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.cart-link:hover { text-decoration: underline; }

.cart-remove-form { margin-left: auto; }
.cart-remove-btn {
    background: none;
    border: none;
    color: #e53e3e;
    font-size: 16px;
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 6px;
    transition: background .15s;
}
.cart-remove-btn:hover { background: #fff5f5; }

/* ── Summary Sidebar ── */
.checkout-summary { position: sticky; top: 24px; }

.summary-card {
    background: #fff;
    border: 1px solid #e8e8ef;
    border-radius: 20px;
    padding: 28px 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,.06);
}

.summary-heading {
    font-size: 18px;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0 0 18px;
}

/* Promo */
.promo-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.promo-input-row {
    display: flex;
    gap: 8px;
}
.promo-input {
    flex: 1;
    padding: 10px 12px;
    border: 1.5px solid #ddd;
    border-radius: 10px;
    font-size: 13px;
    outline: none;
    transition: border-color .2s;
}
.promo-input:focus { border-color: #1a1a2e; }
.promo-apply-btn {
    padding: 10px 14px;
    background: #f0f0f5;
    border: none;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    color: #1a1a2e;
    cursor: pointer;
    transition: background .15s;
}
.promo-apply-btn:hover { background: #e2e2eb; }

.summary-divider {
    height: 1px;
    background: #eee;
    margin: 18px 0;
}

.fare-heading {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #888;
    margin: 0 0 12px;
}

.fare-rows { display: flex; flex-direction: column; gap: 10px; width:100%; }

.fare-row {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    color: #444;
}
.fare-row span:first-child { display: flex; align-items: center; gap: 5px; flex: 1;font-weight: 500; padding-right: 20px;}
.fare-row span:last-child {     text-align: right;
    flex-basis: 130px;}
.fare-row--discount { color: #1a7f37; font-weight: 600; }
.fare-row--total {
    font-size: 16px;
    font-weight: 800;
    color: #1a1a2e;
    margin-top: 4px;
        width: 100%;
}

.btn-checkout {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    margin-top: 18px;
    padding: 15px 20px;
    background: #1a1a2e;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    border-radius: 12px;
    text-decoration: none;
    transition: background .2s;
    cursor: pointer;
}
.btn-checkout:hover { background: #16213e; color: #fff; }

.summary-note {
    margin: 14px 0 0;
    font-size: 12px;
    color: #888;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

/* ── Responsive ── */
@media (max-width: 860px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
    .checkout-summary {
        position: static;
    }
    .cart-item-card {
        flex-direction: column;
    }
    .cart-item-img {
        width: 100%;
        height: 160px;
    }
    .cart-item-title { max-width: 100%; white-space: normal; }
}

/* ═════════════════════════════════════════════════════════════
   Checkout Options Modal
═════════════════════════════════════════════════════════════ */

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: -1;
}

.modal-content {
    background: #fff;
    border-radius: 16px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 28px;
    border-bottom: 1px solid #f0f0f5;
}

.modal-header h2 {
    font-size: 22px;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #999;
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: #1a1a2e;
}

.modal-body {
    padding: 32px 28px;
}

.checkout-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.checkout-option-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 24px;
    border: 2px solid #f0f0f5;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.checkout-option-card:hover {
    border-color: #1a1a2e;
    box-shadow: 0 8px 20px rgba(26, 26, 46, 0.1);
}

.option-icon {
    font-size: 48px;
    color: #1a1a2e;
    margin-bottom: 16px;
}

.checkout-option-card h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 8px;
}

.checkout-option-card p {
    font-size: 14px;
    color: #666;
    margin: 0 0 16px;
    line-height: 1.5;
}

.btn-option-primary {
    background: #1a1a2e;
    color: #fff;
    border: none;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    align-self: center;
}

.btn-option-primary:hover {
    background: #0f0f1e;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.modal-footer {
    padding: 20px 28px;
    border-top: 1px solid #f0f0f5;
    background: #fafafa;
}

.option-note {
    font-size: 13px;
    color: #666;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.option-note i {
    color: #1a1a2e;
}

@media (max-width: 600px) {
    .checkout-options {
        grid-template-columns: 1fr;
    }

    .modal-content {
        width: 95%;
    }

    .modal-header h2 {
        font-size: 18px;
    }

    .modal-header {
        padding: 20px;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        padding: 16px 20px;
    }
}
</style>
@endpush

@push('scripts')
<script>
// ═════════════════════════════════════════════════════════════
//  Checkout Options Modal Functions
// ═════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function() {
    const proceedBtn = document.getElementById('proceedCheckoutBtn');
    if (proceedBtn) {
        proceedBtn.addEventListener('click', openCheckoutModal);
    }
});

function openCheckoutModal() {
    const isAuthenticated = {{ auth('traveler')->check() ? 'true' : 'false' }};
    const operatorToken = '{{ request()->query('operator_token') }}';
    const operatorQuery = operatorToken ? '?operator_token=' + encodeURIComponent(operatorToken) : '';
    const targetUrl = isAuthenticated ? '{{ route("frontend.booking.checkout") }}' + operatorQuery : '{{ route("frontend.booking.guest-checkout") }}' + operatorQuery;
    window.location.href = targetUrl;
}

</script>
@endpush
