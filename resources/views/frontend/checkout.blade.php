@extends('frontend.layout')

@section('title', 'Checkout | Holidays.io')
@section('meta_description', 'Enter your details to complete your booking.')

@section('styles')
<style>
/* Guests List */
.guests-list {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 15px;
}

.guest-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    margin-bottom: 10px;
    background: #f9f9f9;
}

.guest-item-info {
    display: flex;
    gap: 10px;
    align-items: center;
}

.guest-item-name {
    font-weight: 500;
    color: #333;
}

.guest-item-age {
    font-size: 12px;
    color: #666;
}

.guest-item-actions {
    display: flex;
    gap: 10px;
}

.btn-edit-guest, .btn-remove-guest {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 14px;
    color: #0066cc;
}

.btn-edit-guest:hover {
    color: #0052a3;
}

.btn-remove-guest {
    color: #dc3545;
}

.btn-remove-guest:hover {
    color: #c82333;
}

.btn-add-guest {
    background: #0066cc;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-add-guest:hover {
    background: #0052a3;
}

/* Saved Guests */
.saved-guests-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.saved-guest-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: #f0f8ff;
}

.saved-guest-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.saved-guest-name {
    font-weight: 500;
    color: #333;
}

.saved-guest-details {
    font-size: 12px;
    color: #666;
}

.saved-guest-actions {
    display: flex;
    gap: 10px;
}

.btn-add-to-booking {
    background: #28a745;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

.btn-add-to-booking:hover {
    background: #218838;
}

.btn-remove-saved {
    background: none;
    border: none;
    cursor: pointer;
    color: #dc3545;
    font-size: 14px;
}

.btn-remove-saved:hover {
    color: #c82333;
}

/* Modal Overlay */
.guest-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 999;
}

.guest-modal-overlay.show {
    display: block !important;
}

/* Modal Wrapper */
.guest-modal-wrapper {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    background: transparent;
}

.guest-modal-wrapper.show {
    display: flex !important;
}

/* Modal Box */
.guest-modal-box {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    max-width: 600px;
    width: 90%;
    display: flex;
    flex-direction: column;
    max-height: 90vh;
}

.guest-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.guest-modal-header h2 {
    margin: 0;
    font-size: 20px;
    color: #333;
}

.guest-modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 30px;
    height: 30px;
}

.guest-modal-close:hover {
    color: #333;
}

.guest-modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}

.guest-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid #eee;
    background: #f9f9f9;
    border-radius: 0 0 8px 8px;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
}

.btn-primary {
    background-color: #0066cc;
    color: white;
}

.btn-primary:hover {
    background-color: #0052a3;
}

.btn-secondary {
    background-color: #f0f0f0;
    color: #333;
}

.btn-secondary:hover {
    background-color: #e0e0e0;
}

@media (max-width: 768px) {
    .guest-modal-box {
        width: 95%;
        max-width: none;
    }
}
</style>
@endsection

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
                                <span class="step-num">1</span> Guest Details
                            </h2>

                            {{-- Primary Guest --}}
                            <div class="primary-guest-section">
                                @if(auth('traveler')->check())
                                    <div class="form-group" style="margin-bottom: 20px;">
                                        <label style="display: flex; gap: 20px;">
                                            <span>
                                                <input type="radio" name="guest_type" value="myself" checked>
                                                <strong>Myself</strong>
                                            </span>
                                            <span>
                                                <input type="radio" name="guest_type" value="someone_else">
                                                <strong>Someone Else</strong>
                                            </span>
                                        </label>
                                    </div>
                                @endif

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="guests_0_relation">Relationship <span class="req">*</span></label>
                                        <select id="guests_0_relation" name="guests[0][relation]" required class="form-input">
                                            <option value="self" {{ old('guests.0.relation') === 'self' ? 'selected' : '' }}>Self</option>
                                            <option value="spouse" {{ old('guests.0.relation') === 'spouse' ? 'selected' : '' }}>Spouse</option>
                                            <option value="child" {{ old('guests.0.relation') === 'child' ? 'selected' : '' }}>Child</option>
                                            <option value="friend" {{ old('guests.0.relation') === 'friend' ? 'selected' : '' }}>Friend</option>
                                            <option value="colleague" {{ old('guests.0.relation') === 'colleague' ? 'selected' : '' }}>Colleague</option>
                                            <option value="other" {{ old('guests.0.relation') === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="guests_0_gender">Gender</label>
                                        <select id="guests_0_gender" name="guests[0][gender]" class="form-input">
                                            <option value="">Select</option>
                                            <option value="male" {{ old('guests.0.gender') === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('guests.0.gender') === 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="non_binary" {{ old('guests.0.gender') === 'non_binary' ? 'selected' : '' }}>Non-binary</option>
                                            <option value="other" {{ old('guests.0.gender') === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="guests_0_first_name">First Name <span class="req">*</span></label>
                                        <input type="text" id="guests_0_first_name" name="guests[0][first_name]" value="{{ old('guests.0.first_name', $traveler?->profile->first_name ?? '') }}" required class="form-input">
                                    </div>

                                    <div class="form-group">
                                        <label for="guests_0_middle_name">Middle Name</label>
                                        <input type="text" id="guests_0_middle_name" name="guests[0][middle_name]" value="{{ old('guests.0.middle_name', $traveler?->profile->middle_name ?? '') }}" class="form-input">
                                    </div>

                                    <div class="form-group">
                                        <label for="guests_0_last_name">Last Name <span class="req">*</span></label>
                                        <input type="text" id="guests_0_last_name" name="guests[0][last_name]" value="{{ old('guests.0.last_name', $traveler?->profile->last_name ?? '') }}" required class="form-input">
                                    </div>
                                </div>

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="guests_0_dob">Date of Birth <span class="req">*</span></label>
                                        <input type="date" id="guests_0_dob" name="guests[0][dob]" value="{{ old('guests.0.dob') }}" required class="form-input">
                                    </div>

                                    <div class="form-group">
                                        <label for="guests_0_nationality">Nationality <span class="req">*</span></label>
                                        <select id="guests_0_nationality" name="guests[0][nationality]" required class="form-input">
                                            <option value="">Select nationality</option>
                                            @foreach($countries as $country)
                                                <option value="{{ $country }}" {{ old('guests.0.nationality', $traveler?->profile->country ?? '') === $country ? 'selected' : '' }}>{{ $country }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="guests_0_passport_number">Passport No.</label>
                                        <input type="text" id="guests_0_passport_number" name="guests[0][passport_number]" value="{{ old('guests.0.passport_number') }}" class="form-input">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="guests_0_notes">Notes</label>
                                    <textarea id="guests_0_notes" name="guests[0][notes]" class="form-input" rows="2">{{ old('guests.0.notes') }}</textarea>
                                </div>
                            </div>

                            {{-- Saved Guests --}}
                            <div class="saved-guests-section" style="margin-top: 30px;">
                                <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 15px;">Your Saved Guests</h3>
                                <div class="saved-guests-list">
                                    @forelse($savedGuests as $guest)
                                    <div class="saved-guest-item" data-guest-id="{{ $guest->id }}">
                                        <div class="saved-guest-info">
                                            <span class="saved-guest-name">{{ $guest->first_name }} {{ $guest->last_name }}</span>
                                            <span class="saved-guest-details">{{ $guest->nationality }} - {{ \Carbon\Carbon::parse($guest->dob)->age }} years</span>
                                        </div>
                                        <div class="saved-guest-actions">
                                            <button type="button" class="btn-add-to-booking" data-guest='@json($guest)'>
                                                <i class="fa-solid fa-plus"></i> Add to Booking
                                            </button>
                                            <button type="button" class="btn-remove-saved" data-guest-id="{{ $guest->id }}">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="saved-guest-empty" style="padding: 12px; border: 1px dashed #ccc; border-radius: 6px; color: #666;">
                                        No saved guests yet. Add one through the "Add Guest" button above.
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Additional Guests List --}}
                            <div class="additional-guests-section" style="margin-top: 30px;">
                                <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 15px;">Additional Guests</h3>
                                
                                <div id="guestsList" class="guests-list">
                                    {{-- Dynamically populated by JS --}}
                                </div>

                                <button type="button" id="addGuestBtn" class="btn-add-guest">
                                    <i class="fa-solid fa-plus"></i> Add Guest
                                </button>
                            </div>

                            {{-- Hidden inputs to store additional guests --}}
                            <input type="hidden" id="additionalGuestsData" name="additional_guests_json" value="">

                            <div class="form-grid" style="margin-top: 20px;">
                                <div class="form-group">
                                    <label for="guest_email">Email Address</label>
                                    <input type="email" id="guest_email" name="guest_email" value="{{ old('guest_email', $guestDefaults['guest_email'] ?? '') }}" placeholder="you@example.com" class="form-input">
                                    <p class="form-hint">Booking confirmation will be sent here.</p>
                                </div>

                                <div class="form-group">
                                    <label for="guest_phone">Phone Number</label>
                                    <input type="tel" id="guest_phone" name="guest_phone" value="{{ old('guest_phone', $guestDefaults['guest_phone'] ?? '') }}" placeholder="+230 5xxx xxxx" class="form-input">
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

        {{-- ════════ MODAL SECTION OUTSIDE MAIN CONTENT ════════ --}}
        <div id="modalOverlay" class="guest-modal-overlay"></div>
        <div id="addGuestModal" class="guest-modal-wrapper">
            <div class="guest-modal-box">
                <div class="guest-modal-header">
                    <h2>Add Guest</h2>
                    <button type="button" class="guest-modal-close" id="closeModalBtn">&times;</button>
                </div>
                <div class="guest-modal-body">
                    <form id="addGuestForm">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="modal_relation">Relationship <span class="req">*</span></label>
                                <select id="modal_relation" name="relation" required class="form-input">
                                    <option value="">Select</option>
                                    <option value="self">Self</option>
                                    <option value="spouse">Spouse</option>
                                    <option value="child">Child</option>
                                    <option value="friend">Friend</option>
                                    <option value="colleague">Colleague</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="modal_gender">Gender</label>
                                <select id="modal_gender" name="gender" class="form-input">
                                    <option value="">Select</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="non_binary">Non-binary</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="modal_first_name">First Name <span class="req">*</span></label>
                                <input type="text" id="modal_first_name" name="first_name" required class="form-input">
                            </div>

                            <div class="form-group">
                                <label for="modal_middle_name">Middle Name</label>
                                <input type="text" id="modal_middle_name" name="middle_name" class="form-input">
                            </div>

                            <div class="form-group">
                                <label for="modal_last_name">Last Name <span class="req">*</span></label>
                                <input type="text" id="modal_last_name" name="last_name" required class="form-input">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="modal_dob">Date of Birth <span class="req">*</span></label>
                                <input type="date" id="modal_dob" name="dob" required class="form-input">
                            </div>

                            <div class="form-group">
                                <label for="modal_nationality">Nationality <span class="req">*</span></label>
                                <select id="modal_nationality" name="nationality" required class="form-input">
                                    <option value="">Select nationality</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}">{{ $country }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="modal_passport_number">Passport No.</label>
                                <input type="text" id="modal_passport_number" name="passport_number" class="form-input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="modal_notes">Notes</label>
                            <textarea id="modal_notes" name="notes" class="form-input" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" id="modal_below_12" name="below_12">
                                <span>Below 12 years of age</span>
                            </label>
                        </div>
                    </form>
                </div>
                <div class="guest-modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelModalBtn">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveGuestBtn">Add to Saved Guests</button>
                </div>
            </div>
        </div>

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

.traveler-checkbox-row {
    margin-bottom: 16px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #1a1a2e;
}

.checkbox-label input[type="checkbox"] {
    width: 16px;
    height: 16px;
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const totalGuests = {{ $totalGuests }};
        const addGuestBtn = document.getElementById('addGuestBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelModalBtn = document.getElementById('cancelModalBtn');
        const saveGuestBtn = document.getElementById('saveGuestBtn');
        const addGuestModal = document.getElementById('addGuestModal');
        const modalOverlay = document.getElementById('modalOverlay');
        const addGuestForm = document.getElementById('addGuestForm');
        const guestsList = document.getElementById('guestsList');
        const additionalGuestsData = document.getElementById('additionalGuestsData');
        const checkoutForm = document.getElementById('checkoutForm');

        let additionalGuests = [];
        let editingIndex = null;

        // Load saved guests from localStorage or URL old data
        function loadGuestsFromForm() {
            const savedData = additionalGuestsData.value;
            if (savedData) {
                try {
                    additionalGuests = JSON.parse(savedData);
                } catch (e) {
                    additionalGuests = [];
                }
            }
        }

        // Save guests to hidden input
        function saveGuestsToForm() {
            additionalGuestsData.value = JSON.stringify(additionalGuests);
        }

        // Render guests list
        function renderGuestsList() {
            guestsList.innerHTML = '';
            additionalGuests.forEach((guest, index) => {
                const guestName = `${guest.first_name} ${guest.last_name}`;
                const ageLabel = guest.below_12 ? ' (Below 12 years)' : '';
                const guestItem = document.createElement('div');
                guestItem.className = 'guest-item';
                guestItem.innerHTML = `
                    <div class="guest-item-info">
                        <span class="guest-item-name">${guestName}</span>
                        <span class="guest-item-age">${guest.relation}${ageLabel}</span>
                    </div>
                    <div class="guest-item-actions">
                        <button type="button" class="btn-edit-guest" data-index="${index}">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                        <button type="button" class="btn-remove-guest" data-index="${index}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                `;
                guestsList.appendChild(guestItem);
            });

            // Attach event listeners using delegation
            document.querySelectorAll('.btn-edit-guest').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const index = parseInt(this.dataset.index);
                    editGuest(index);
                });
            });

            document.querySelectorAll('.btn-remove-guest').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const index = parseInt(this.dataset.index);
                    removeGuest(index);
                });
            });

            saveGuestsToForm();
        }

        // Open modal
        function openModal() {
            addGuestModal.classList.add('show');
            modalOverlay.classList.add('show');
            addGuestModal.style.display = 'flex';
            modalOverlay.style.display = 'block';
            addGuestForm.reset();
            editingIndex = null;
        }

        // Close modal
        function closeModal() {
            addGuestModal.classList.remove('show');
            modalOverlay.classList.remove('show');
            addGuestModal.style.display = 'none';
            modalOverlay.style.display = 'none';
            addGuestForm.reset();
            editingIndex = null;
        }

        // Add/Save guest
        function saveGuest() {
            if (!addGuestForm.checkValidity()) {
                addGuestForm.reportValidity();
                return;
            }

            const guestData = {
                relation: document.getElementById('modal_relation').value,
                gender: document.getElementById('modal_gender').value,
                first_name: document.getElementById('modal_first_name').value,
                middle_name: document.getElementById('modal_middle_name').value,
                last_name: document.getElementById('modal_last_name').value,
                dob: document.getElementById('modal_dob').value,
                nationality: document.getElementById('modal_nationality').value,
                passport_number: document.getElementById('modal_passport_number').value,
                notes: document.getElementById('modal_notes').value,
                below_12: document.getElementById('modal_below_12').checked
            };

            // Save to server
            fetch('{{ route("frontend.booking.save-guest") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    first_name: guestData.first_name,
                    middle_name: guestData.middle_name,
                    last_name: guestData.last_name,
                    dob: guestData.dob,
                    gender: guestData.gender,
                    nationality: guestData.nationality,
                    passport_number: guestData.passport_number,
                    notes: guestData.notes
                })
            }).then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    const validationErrors = data.errors ? Object.values(data.errors).flat().join('; ') : null;
                    const msg = validationErrors || data.error || 'Unknown error';
                    throw new Error(msg);
                }

                if (!data.success) {
                    throw new Error(data.error || 'Unknown error');
                }

                // Add the guest to the saved guests list in DOM
                const savedGuestsList = document.querySelector('.saved-guests-list');
                if (savedGuestsList) {
                    const guestItem = document.createElement('div');
                    guestItem.className = 'saved-guest-item';
                    guestItem.setAttribute('data-guest-id', data.guest.id);
                    guestItem.innerHTML = `
                        <div class="saved-guest-info">
                            <span class="saved-guest-name">${guestData.first_name} ${guestData.last_name}</span>
                            <span class="saved-guest-details">${guestData.nationality} - ${new Date().getFullYear() - new Date(guestData.dob).getFullYear()} years</span>
                        </div>
                        <div class="saved-guest-actions">
                            <button type="button" class="btn-add-to-booking" data-guest='${JSON.stringify(data.guest).replace(/'/g, "&apos;")}'>
                                <i class="fa-solid fa-plus"></i> Add to Booking
                            </button>
                            <button type="button" class="btn-remove-saved" data-guest-id="${data.guest.id}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    `;
                    savedGuestsList.appendChild(guestItem);
                    const section = document.querySelector('.saved-guests-section');
                    if (section) section.style.display = 'block';
                }
            }).catch(error => {
                console.error('Error saving guest:', error);
                alert('Failed to save guest: ' + (error.message || 'Please try again.'));
            });

            if (editingIndex !== null) {
                additionalGuests[editingIndex] = guestData;
            } else {
                additionalGuests.push(guestData);
            }

            renderGuestsList();
            closeModal();
        }

        // Edit guest
        function editGuest(index) {
            const guest = additionalGuests[index];
            document.getElementById('modal_relation').value = guest.relation;
            document.getElementById('modal_gender').value = guest.gender;
            document.getElementById('modal_first_name').value = guest.first_name;
            document.getElementById('modal_middle_name').value = guest.middle_name;
            document.getElementById('modal_last_name').value = guest.last_name;
            document.getElementById('modal_dob').value = guest.dob;
            document.getElementById('modal_nationality').value = guest.nationality;
            document.getElementById('modal_passport_number').value = guest.passport_number;
            document.getElementById('modal_notes').value = guest.notes;
            document.getElementById('modal_below_12').checked = guest.below_12;
            editingIndex = index;
            openModal();
        }

        // Remove guest
        function removeGuest(index) {
            additionalGuests.splice(index, 1);
            renderGuestsList();
        }

        // Form submission - inject all guests as array
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                // Build guests array inputs
                const guestsContainer = document.createElement('div');
                guestsContainer.style.display = 'none';

                // Add additional guests to form
                additionalGuests.forEach((guest, index) => {
                    const guestIndex = index + 1; // +1 because first guest starts at 0
                    Object.keys(guest).forEach(key => {
                        if (key !== 'below_12') { // Don't submit below_12 to backend
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = `guests[${guestIndex}][${key}]`;
                            input.value = guest[key];
                            guestsContainer.appendChild(input);
                        }
                    });
                });

                this.appendChild(guestsContainer);
            });
        }

        // Event listeners
        if (addGuestBtn) {
            addGuestBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openModal();
            });
        }
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeModal();
            });
        }
        if (cancelModalBtn) {
            cancelModalBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeModal();
            });
        }
        if (saveGuestBtn) {
            saveGuestBtn.addEventListener('click', function(e) {
                e.preventDefault();
                saveGuest();
            });
        }
        if (modalOverlay) {
            modalOverlay.addEventListener('click', closeModal);
        }

        // Event listeners for saved guests
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-add-to-booking')) {
                e.preventDefault();
                const btn = e.target.closest('.btn-add-to-booking');
                const guestData = JSON.parse(btn.dataset.guest);
                // Calculate age
                const dob = new Date(guestData.dob);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const monthDiff = today.getMonth() - dob.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }
                // Convert to the format used in additionalGuests
                const bookingGuest = {
                    relation: 'other', // default
                    gender: guestData.gender,
                    first_name: guestData.first_name,
                    middle_name: guestData.middle_name,
                    last_name: guestData.last_name,
                    dob: guestData.dob,
                    nationality: guestData.nationality,
                    passport_number: guestData.passport_number,
                    notes: guestData.notes,
                    below_12: age < 12
                };
                additionalGuests.push(bookingGuest);
                renderGuestsList();
            }

            if (e.target.closest('.btn-remove-saved')) {
                e.preventDefault();
                const btn = e.target.closest('.btn-remove-saved');
                const guestId = btn.dataset.guestId;
                if (confirm('Are you sure you want to remove this saved guest?')) {
                    fetch('{{ route("frontend.booking.remove-guest") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ guest_id: guestId })
                    }).then(async response => {
                        const data = await response.json();
                        if (!response.ok) {
                            const validationErrors = data.errors ? Object.values(data.errors).flat().join('; ') : null;
                            const msg = validationErrors || data.error || 'Unknown error';
                            throw new Error(msg);
                        }

                        if (!data.success) {
                            throw new Error(data.error || 'Unknown error');
                        }

                        // Remove the guest item from DOM
                        const guestItem = btn.closest('.saved-guest-item');
                        if (guestItem) {
                            guestItem.remove();
                            const list = document.querySelector('.saved-guests-list');
                            if (list && list.children.length === 0) {
                                const section = document.querySelector('.saved-guests-section');
                                if (section) section.style.display = 'none';
                            }
                        }
                    }).catch(error => {
                        console.error('Error removing guest:', error);
                        alert('Failed to remove guest: ' + (error.message || 'Please try again.'));
                    });
                }
            }
        });

        // Initialize
        loadGuestsFromForm();
        if (additionalGuests.length > 0) {
            renderGuestsList();
        }
    });
</script>
@endpush
