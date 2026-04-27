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
                    <a href="{{ url('/') }}">Home</a>
                    <span>/</span>
                    <span>Booking Cart</span>
                </div>
                <h1 class="cart-title">
                    Review Your Items
                    @if(!empty($cart))
                        <span class="cart-badge">{{ count($cart) }}</span>
                    @endif
                </h1>
            </div>

            @if(empty($cart))
                {{-- ── Empty State ──────────────────────────────────────────── --}}
                <div class="cart-empty">
                    <div class="cart-empty-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                    <h2>Your cart is empty</h2>
                    <p>Browse our <a href="{{ url('/#accommodations-section') }}">accommodations</a> or <a href="{{ url('/#activities-section') }}">activities</a> and add items to get started.</p>
                    <a href="{{ url('/') }}" class="btn-primary">Explore Now</a>
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
                                $nights  = (int) ($item['nights'] ?? 1);
                                $rooms   = (int) ($item['rooms'] ?? 1);
                                $label   = $isAccom
                                    ? ($rooms . ' Room' . ($rooms !== 1 ? 's' : '') . ' · ' . $nights . ' Night' . ($nights !== 1 ? 's' : '') . ' · ' . $item['room_name'])
                                    : ('Activity · ' . ($item['variant_name'] ?: 'Standard'));
                                $subLabel = $isAccom
                                    ? ($item['adults'] . ' Adults' . ($item['children'] > 0 ? ', ' . $item['children'] . ' Children' : '') . ' · ' . $rooms . '× ' . $item['room_name'])
                                    : ($item['adults'] . ' Adults' . ($item['children'] > 0 ? ', ' . $item['children'] . ' Children' : ''));
                            @endphp

                            <div class="cart-item-card">
                                {{-- Image --}}
                                <div class="cart-item-img">
                                    @if(!empty($item['image']))
                                        <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                                    @else
                                        <div class="cart-item-img-placeholder">
                                            <i class="fa-solid {{ $isAccom ? 'fa-hotel' : 'fa-person-hiking' }}"></i>
                                        </div>
                                    @endif
                                </div>

                                {{-- Details --}}
                                <div class="cart-item-body">
                                    <div class="cart-item-top">
                                        <div>
                                            <span class="cart-item-badge">{{ $isAccom ? 'Stay' : 'Activity' }}</span>
                                            <h3 class="cart-item-title">{{ $item['title'] }}</h3>
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
                                                {{ $nights }} Night{{ $nights !== 1 ? 's' : '' }} Total
                                            </div>
                                        @php
                                        }
                                        @endphp
                                        </div>
                                    </div>

                                    <div class="cart-item-dates">
                                        <span><i class="fa-regular fa-calendar"></i>
                                            {{ $item['check_in_display'] }}
                                        </span>
                                        @if(!$isAccom || $item['check_in'] !== $item['check_out'])
                                            <span class="cart-item-arrow">→</span>
                                            <span>{{ $item['check_out_display'] }}</span>
                                        @endif
                                    </div>

                                    @if($item['discount_amount'] > 0)
                                        <div class="cart-item-promo">
                                            <i class="fa-solid fa-tag"></i>
                                            Discount applied: −{{ $item['currency'] }} {{ number_format($item['discount_amount'], 2) }}
                                            @if($item['is_non_refundable'])
                                                <span class="non-refundable-badge">Non-refundable</span>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="cart-item-actions">
                                        <a href="{{ $isAccom
                                            ? route('frontend.accommodations.show', $item['accommodation_id'])
                                            : route('frontend.activities.show', $item['activity_id']) }}"
                                           class="cart-link">
                                            <i class="fa-solid fa-eye"></i> View Rules
                                        </a>
                                        @if($isAccom && !empty($item['accommodation_id']))
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($item['title']) }}"
                                               target="_blank" rel="noopener" class="cart-link">
                                                <i class="fa-solid fa-location-dot"></i> Get Directions
                                            </a>
                                        @endif
                                        <a href="tel:+23052511153" class="cart-link">
                                            <i class="fa-solid fa-phone"></i> Call Us
                                        </a>

                                        {{-- Remove --}}
                                        <form method="POST" action="{{ route('frontend.booking.cart.remove') }}" class="cart-remove-form">
                                            @csrf
                                            <input type="hidden" name="cart_key" value="{{ $cartKey }}">
                                            <button type="submit" class="cart-remove-btn" title="Remove item">
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
                            <h2 class="summary-heading">Checkout Summary</h2>

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
                            <h3 class="fare-heading">Fare Summary</h3>

                            <div class="fare-rows">
                                @foreach($cart as $item)
                                    @php
                                        $nights = (int) ($item['nights'] ?? 1);
                                        $rooms  = (int) ($item['rooms'] ?? 1);
                                        $label  = $item['type'] === 'accommodation'
                                            ? $rooms . ' Room' . ($rooms !== 1 ? 's' : '') . ' · ' . $nights . ' Night' . ($nights !== 1 ? 's' : '')
                                            : 'Activity: ' . ($item['variant_name'] ?: $item['title']);
                                    @endphp
                                    <div class="fare-row">
                                        <span>{{ $label }}</span>
                                        <span>{{ $item['currency'] }} {{ number_format($item['total_price'], 2) }}</span>
                                    </div>
                                @endforeach

                                @if($summary['total_discount'] > 0)
                                    <div class="fare-row fare-row--discount">
                                        <span><i class="fa-solid fa-tag"></i> Discounts</span>
                                        <span>−{{ $summary['currency'] }} {{ number_format($summary['total_discount'], 2) }}</span>
                                    </div>
                                    <div class="fare-row">
                                        <span>Price After Discount</span>
                                        <span>{{ $summary['currency'] }} {{ number_format($summary['price_after_discount'], 2) }}</span>
                                    </div>
                                @endif

                                @if($summary['total_tax'] > 0)
                                    <div class="fare-row">
                                        <span>Taxes &amp; Charges</span>
                                        <span>{{ $summary['currency'] }} {{ number_format($summary['total_tax'], 2) }}</span>
                                    </div>
                                @endif

                                @if($summary['total_fees'] > 0)
                                    <div class="fare-row">
                                        <span>Fees (Cleaning / Resort)</span>
                                        <span>{{ $summary['currency'] }} {{ number_format($summary['total_fees'], 2) }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="summary-divider"></div>

                            <div class="fare-row fare-row--total">
                                <span>Net Amount Payable</span>
                                <span>{{ $summary['currency'] }} {{ number_format($summary['net_payable'], 2) }}</span>
                            </div>

                            <a href="{{ route('frontend.booking.checkout') }}" class="btn-checkout">
                                Proceed to Checkout
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>

                            <p class="summary-note">
                                <i class="fa-solid fa-shield-halved"></i>
                                Cash on Delivery — Pay when your booking is confirmed.
                            </p>
                        </div>
                    </aside>

                </div>{{-- .cart-layout --}}
            @endif

        </div>
    </section>

@endsection

@push('styles')
<style>
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

.fare-rows { display: flex; flex-direction: column; gap: 10px; }

.fare-row {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    color: #444;
}
.fare-row span:first-child { display: flex; align-items: center; gap: 5px; }
.fare-row--discount { color: #1a7f37; font-weight: 600; }
.fare-row--total {
    font-size: 16px;
    font-weight: 800;
    color: #1a1a2e;
    margin-top: 4px;
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
</style>
@endpush
