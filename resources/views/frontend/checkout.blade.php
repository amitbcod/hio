@extends('frontend.layout')

@section('title', 'Checkout | Holidays.io')
@section('meta_description', 'Enter your details to complete your booking.')

@section('content')

    <section class="page-section checkout-section">
        <div class="wrap">

            <div class="checkout-page-header">
                <div class="breadcrumbs">
                    <a href="{{ url('/') }}">Home</a>
                    <span>/</span>
                    <a href="{{ route('frontend.booking.cart') }}">Cart</a>
                    <span>/</span>
                    <span>Checkout</span>
                </div>
                <h1>Complete Your Booking</h1>
                <p class="checkout-subtitle">Cash on Delivery — your booking will be confirmed within 24 hours.</p>
            </div>

            <div class="checkout-layout">

                {{-- ════════ LEFT — Guest Info Form ════════ --}}
                <div class="checkout-form-wrap">
                    <form method="POST" action="{{ route('frontend.booking.place-order') }}" class="checkout-form" id="checkoutForm">
                        @csrf

                        @if($errors->any())
                            <div class="form-errors">
                                <ul>
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-card">
                            <h2 class="form-section-title">
                                <span class="step-num">1</span> Guest Information
                            </h2>

                            <div class="form-grid">
                                <div class="form-group form-group--full">
                                    <label for="guest_name">Full Name <span class="req">*</span></label>
                                    <input type="text" id="guest_name" name="guest_name"
                                           value="{{ old('guest_name') }}"
                                           placeholder="e.g. Jean-Pierre Dupont"
                                           required class="form-input">
                                </div>

                                <div class="form-group">
                                    <label for="guest_email">Email Address</label>
                                    <input type="email" id="guest_email" name="guest_email"
                                           value="{{ old('guest_email') }}"
                                           placeholder="you@example.com"
                                           class="form-input">
                                    <p class="form-hint">Booking confirmation will be sent here.</p>
                                </div>

                                <div class="form-group">
                                    <label for="guest_phone">Phone Number</label>
                                    <input type="tel" id="guest_phone" name="guest_phone"
                                           value="{{ old('guest_phone') }}"
                                           placeholder="+230 5xxx xxxx"
                                           class="form-input">
                                </div>
                            </div>
                        </div>

                        <div class="form-card">
                            <h2 class="form-section-title">
                                <span class="step-num">2</span> Special Requests
                            </h2>
                            <div class="form-group form-group--full">
                                <label for="special_requests">Special Requests (Optional)</label>
                                <textarea id="special_requests" name="special_requests"
                                          rows="3" placeholder="Any special requirements, diet preferences, accessibility needs…"
                                          class="form-input form-textarea">{{ old('special_requests') }}</textarea>
                            </div>
                        </div>

                        <div class="form-card">
                            <h2 class="form-section-title">
                                <span class="step-num">3</span> Payment Method
                            </h2>
                            <div class="payment-option selected">
                                <div class="payment-option-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                                <div>
                                    <strong>Cash on Delivery</strong>
                                    <p>Pay when you check in. No advance payment required.</p>
                                </div>
                                <i class="fa-solid fa-circle-check payment-tick"></i>
                            </div>
                            <p class="form-hint" style="margin-top:10px;">
                                Payment gateway integration coming soon. Currently COD only.
                            </p>
                        </div>

                        {{-- Items review mini list --}}
                        <div class="form-card">
                            <h2 class="form-section-title">
                                <span class="step-num">4</span> Your Items
                            </h2>
                            <div class="mini-items">
                                @foreach($cart as $item)
                                    <div class="mini-item">
                                        <div class="mini-item-img">
                                            @if(!empty($item['image']))
                                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                                            @else
                                                <div class="mini-item-placeholder">
                                                    <i class="fa-solid {{ $item['type'] === 'accommodation' ? 'fa-hotel' : 'fa-person-hiking' }}"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="mini-item-info">
                                            <strong>{{ $item['title'] }}</strong>
                                            <span>
                                                {{ $item['check_in_display'] }}
                                                @if($item['check_in'] !== $item['check_out'])
                                                    → {{ $item['check_out_display'] }}
                                                @endif
                                            </span>
                                            @if($item['type'] === 'accommodation')
                                                <span>{{ $item['room_name'] }} · {{ $item['nights'] }} night{{ $item['nights'] != 1 ? 's' : '' }}</span>
                                            @else
                                                <span>{{ $item['variant_name'] ?: 'Standard' }}</span>
                                            @endif
                                        </div>
                                        <div class="mini-item-price">
                                            {{ $item['currency'] }} {{ number_format($item['net_amount'], 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </form>
                </div>

                {{-- ════════ RIGHT — Summary Sidebar ════════ --}}
                <aside class="checkout-summary">
                    <div class="summary-card">
                        <h2 class="summary-heading">Order Summary</h2>

                        <div class="fare-rows">
                            @foreach($cart as $item)
                                @php
                                    $nights = (int) ($item['nights'] ?? 1);
                                    $lbl = $item['type'] === 'accommodation'
                                        ? '1 Room · ' . $nights . ' Night' . ($nights !== 1 ? 's' : '')
                                        : 'Activity: ' . ($item['variant_name'] ?: $item['title']);
                                @endphp
                                <div class="fare-row">
                                    <span>{{ $lbl }}</span>
                                    <span>{{ $item['currency'] }} {{ number_format($item['total_price'], 2) }}</span>
                                </div>
                            @endforeach

                            @if($summary['total_discount'] > 0)
                                <div class="fare-row fare-row--discount">
                                    <span><i class="fa-solid fa-tag"></i> Discounts</span>
                                    <span>−{{ $summary['currency'] }} {{ number_format($summary['total_discount'], 2) }}</span>
                                </div>
                                <div class="fare-row">
                                    <span>After Discount</span>
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
                                    <span>Fees</span>
                                    <span>{{ $summary['currency'] }} {{ number_format($summary['total_fees'], 2) }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="summary-divider"></div>

                        <div class="fare-row fare-row--total">
                            <span>Net Amount Payable</span>
                            <span>{{ $summary['currency'] }} {{ number_format($summary['net_payable'], 2) }}</span>
                        </div>

                        <button type="submit" form="checkoutForm" class="btn-checkout">
                            <i class="fa-solid fa-lock"></i>
                            Confirm Booking (COD)
                        </button>

                        <p class="summary-note">
                            <i class="fa-solid fa-shield-halved"></i>
                            Secure booking · No advance payment
                        </p>

                        <a href="{{ route('frontend.booking.cart') }}" class="back-to-cart">
                            <i class="fa-solid fa-arrow-left"></i> Back to Cart
                        </a>
                    </div>
                </aside>

            </div>
        </div>
    </section>

@endsection

@push('styles')
<style>
.checkout-section { padding-top: 32px; }

.checkout-page-header { margin-bottom: 28px; }
.checkout-page-header h1 { font-size: 26px; font-weight: 800; color: #1a1a2e; margin: 6px 0 4px; }
.checkout-subtitle { color: #666; font-size: 14px; margin: 0; }

.checkout-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 28px;
    align-items: start;
}

/* Form Cards */
.form-card {
    background: #fff;
    border: 1px solid #e8e8ef;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}

.form-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 18px;
}
.step-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px; height: 26px;
    background: #1a1a2e;
    color: #fff;
    border-radius: 50%;
    font-size: 13px;
    flex-shrink: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.form-group--full { grid-column: 1 / -1; }

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #444;
    margin-bottom: 6px;
}
.req { color: #e53e3e; }

.form-input {
    width: 100%;
    padding: 11px 14px;
    border: 1.5px solid #ddd;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    color: #1a1a2e;
    outline: none;
    transition: border-color .2s;
    box-sizing: border-box;
}
.form-input:focus { border-color: #1a1a2e; }
.form-textarea { resize: vertical; min-height: 80px; }

.form-hint { font-size: 12px; color: #888; margin: 5px 0 0; }

/* Payment Option */
.payment-option {
    display: flex;
    align-items: center;
    gap: 14px;
    border: 2px solid #1a1a2e;
    border-radius: 12px;
    padding: 14px 16px;
    background: #f8f8ff;
}
.payment-option-icon {
    font-size: 22px;
    color: #1a1a2e;
    flex-shrink: 0;
}
.payment-option strong { display: block; font-size: 14px; color: #1a1a2e; }
.payment-option p { font-size: 12px; color: #666; margin: 2px 0 0; }
.payment-tick { margin-left: auto; color: #1a7f37; font-size: 20px; }

/* Mini Items */
.mini-items { display: flex; flex-direction: column; gap: 12px; }
.mini-item {
    display: flex;
    gap: 12px;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid #f0f0f5;
}
.mini-item:last-child { border-bottom: none; padding-bottom: 0; }
.mini-item-img {
    width: 60px; height: 50px;
    border-radius: 8px;
    overflow: hidden;
    background: #f0f0f5;
    flex-shrink: 0;
}
.mini-item-img img { width: 100%; height: 100%; object-fit: cover; }
.mini-item-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; color: #bbb;
}
.mini-item-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 13px;
    color: #555;
}
.mini-item-info strong { font-size: 14px; color: #1a1a2e; }
.mini-item-price { font-weight: 700; font-size: 14px; color: #1a1a2e; white-space: nowrap; }

/* Form Errors */
.form-errors {
    background: #fff5f5; border: 1px solid #fc8181;
    border-radius: 10px; padding: 12px 16px; margin-bottom: 18px;
}
.form-errors ul { margin: 0; padding-left: 18px; }
.form-errors li { font-size: 13px; color: #c53030; margin-bottom: 4px; }

/* Summary Sidebar (reuse cart-review styles, add here) */
.checkout-summary { position: sticky; top: 24px; }
.summary-card {
    background: #fff;
    border: 1px solid #e8e8ef;
    border-radius: 20px;
    padding: 28px 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,.06);
}
.summary-heading { font-size: 18px; font-weight: 800; color: #1a1a2e; margin: 0 0 18px; }
.summary-divider { height: 1px; background: #eee; margin: 16px 0; }
.fare-rows { display: flex; flex-direction: column; gap: 10px; }
.fare-row { display: flex; justify-content: space-between; font-size: 14px; color: #444; }
.fare-row span:first-child { display: flex; align-items: center; gap: 5px; }
.fare-row--discount { color: #1a7f37; font-weight: 600; }
.fare-row--total { font-size: 16px; font-weight: 800; color: #1a1a2e; margin-top: 4px; }

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
    border: none;
    cursor: pointer;
    transition: background .2s;
    text-decoration: none;
}
.btn-checkout:hover { background: #16213e; }

.summary-note {
    margin: 14px 0 6px;
    font-size: 12px;
    color: #888;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.back-to-cart {
    display: block;
    text-align: center;
    margin-top: 12px;
    font-size: 13px;
    color: #666;
    text-decoration: none;
    font-weight: 600;
}
.back-to-cart:hover { color: #1a1a2e; }

@media (max-width: 860px) {
    .checkout-layout { grid-template-columns: 1fr; }
    .checkout-summary { position: static; }
    .form-grid { grid-template-columns: 1fr; }
}
</style>
@endpush
