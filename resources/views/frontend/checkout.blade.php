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
    flex-direction: column;
    align-items: stretch;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    margin-bottom: 10px;
    background: #f9f9f9;
}

.guest-item-info {
    display: flex;
    justify-content: space-between;
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

.guest-item-timeslot {
    margin-top: 10px;
    width: 100%;
}

.guest-item-timeslot label {
    display: block;
    font-size: 12px;
    color: #444;
    margin-bottom: 6px;
}

.guest-item-timeslot .form-input {
    width: 100%;
    max-width: 100%;
}

.guest-item-actions {
    display: flex;
    justify-content: flex-end;
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

.item-saved-guests-panel {
    border: 1px solid #e8e8ef;
    background: #f9fbff;
    border-radius: 14px;
    padding: 14px;
}

.item-saved-guests-title {
    margin: 0 0 10px;
    font-size: 13px;
    color: #333;
}

.item-saved-guests-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.saved-guest-checkbox {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid #dfe4ed;
    background: #fff;
    cursor: pointer;
    width: 100%;
}

.saved-guest-checkbox input {
    flex-shrink: 0;
}

.saved-guest-checkbox .saved-guest-info {
    width: 100%;
}

.saved-guest-checkbox .saved-guest-name,
.saved-guest-checkbox .saved-guest-details {
    display: block;
    width: 100%;
}

.saved-guest-checkbox .saved-guest-details {
    margin-top: 4px;
    color: #555;
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
                    <a href="{{ url('/') }}">{{ __('site.home') }}</a>
                    <span>/</span>
                    <span>{{ __('checkout.title') }}</span>
                </div>
                <h1>{{ __('checkout.complete_booking') }}</h1>
                <p class="checkout-subtitle">{{ __('checkout.subtitle') }}</p>
                    @if(!auth('traveler')->check())
                        <p class="checkout-login-link">
                            {{ __('checkout.already_have_account') }} <a href="{{ route('traveler.login') . '?redirect=' . urlencode(route('frontend.booking.checkout')) }}">{{ __('checkout.login') }}</a>
                        </p>
                    @endif

                <form method="POST" action="{{ route('frontend.booking.place-order') }}" class="checkout-form" id="checkoutForm">
                    @csrf

                    @if(session('error'))
                        <div class="form-errors">
                            <ul>
                                <li>{{ session('error') }}</li>
                            </ul>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="form-errors">
                            <ul>
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-card accordion-card">
                            <button type="button" class="accordion-header" aria-expanded="true">
                                <h2 class="form-section-title">
                                    <span class="step-num">1</span> {{ __('checkout.step.guest_details') }}
                                </h2>
                                <span class="accordion-toggle">−</span>
                            </button>
                            <div class="accordion-panel">
                                {{-- Primary Guest --}}
                                <div class="primary-guest-section">
                                    @if(auth('traveler')->check())
                                        <div class="form-group" style="margin-bottom: 20px;">
                                            <label style="display: flex; gap: 20px;">
                                                <span>
                                                    <input type="radio" name="guest_type" value="myself" checked>
                                                    <strong>{{ __('checkout.choice.myself') }}</strong>
                                                </span>
                                                <span>
                                                    <input type="radio" name="guest_type" value="someone_else">
                                                    <strong>{{ __('checkout.choice.someone_else') }}</strong>
                                                </span>
                                            </label>
                                        </div>
                                    @endif

                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="guests_0_relation">Relationship <span class="req">*</span></label>
                                            <select id="guests_0_relation" name="guests[0][relation]" class="form-input">
                                                <option value="">{{ __('checkout.relation.choose') }}</option>
                                                <option value="self" {{ old('guests.0.relation') === 'self' ? 'selected' : '' }}>{{ __('checkout.relation.self') }}</option>
                                                <option value="spouse" {{ old('guests.0.relation') === 'spouse' ? 'selected' : '' }}>{{ __('checkout.relation.spouse') }}</option>
                                                <option value="child" {{ old('guests.0.relation') === 'child' ? 'selected' : '' }}>{{ __('checkout.relation.child') }}</option>
                                                <option value="friend" {{ old('guests.0.relation') === 'friend' ? 'selected' : '' }}>{{ __('checkout.relation.friend') }}</option>
                                                <option value="colleague" {{ old('guests.0.relation') === 'colleague' ? 'selected' : '' }}>{{ __('checkout.relation.colleague') }}</option>
                                                <option value="other" {{ old('guests.0.relation') === 'other' ? 'selected' : '' }}>{{ __('checkout.relation.other') }}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="guests_0_gender">Gender</label>
                                            <select id="guests_0_gender" name="guests[0][gender]" class="form-input">
                                                <option value="">{{ __('checkout.gender.select') }}</option>
                                                <option value="male" {{ old('guests.0.gender') === 'male' ? 'selected' : '' }}>{{ __('checkout.gender.male') }}</option>
                                                <option value="female" {{ old('guests.0.gender') === 'female' ? 'selected' : '' }}>{{ __('checkout.gender.female') }}</option>
                                                <option value="non_binary" {{ old('guests.0.gender') === 'non_binary' ? 'selected' : '' }}>{{ __('checkout.gender.non_binary') }}</option>
                                                <option value="other" {{ old('guests.0.gender') === 'other' ? 'selected' : '' }}>{{ __('checkout.gender.other') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-grid col-3">
                                        <div class="form-group">
                                            <label for="guests_0_first_name">{{ __('profile.first_name') }} <span class="req">*</span></label>
                                            <input type="text" id="guests_0_first_name" name="guests[0][first_name]" value="{{ old('guests.0.first_name', $traveler?->profile->first_name ?? '') }}" class="form-input">
                                        </div>

                                        <div class="form-group">
                                            <label for="guests_0_middle_name">{{ __('profile.middle_name') }}</label>
                                            <input type="text" id="guests_0_middle_name" name="guests[0][middle_name]" value="{{ old('guests.0.middle_name', $traveler?->profile->middle_name ?? '') }}" class="form-input">
                                        </div>

                                        <div class="form-group">
                                            <label for="guests_0_last_name">{{ __('profile.last_name') }} <span class="req">*</span></label>
                                            <input type="text" id="guests_0_last_name" name="guests[0][last_name]" value="{{ old('guests.0.last_name', $traveler?->profile->last_name ?? '') }}" class="form-input">
                                        </div>
                                    </div>

                                    <div class="form-grid col-3">
                                        <div class="form-group">
                                            <label for="guests_0_dob">{{ __('profile.date_of_birth') }} <span class="req">*</span></label>
                                            <input type="date" id="guests_0_dob" name="guests[0][dob]" value="{{ old('guests.0.dob', $guestDefaults['dob'] ?? '') }}" class="form-input">
                                        </div>

                                        <div class="form-group">
                                            <label for="guests_0_nationality">{{ __('profile.nationality') }} <span class="req">*</span></label>
                                            <select id="guests_0_nationality" name="guests[0][nationality]" class="form-input">
                                                <option value="">{{ __('checkout.select_nationality') }}</option>
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

                                <div class="form-grid" style="margin-top: 20px;">
                                    <div class="form-group">
                                        <label for="guest_email">Email Address <span class="req">*</span></label>
                                        <input type="email" id="guest_email" name="guest_email" value="{{ old('guest_email', $guestDefaults['guest_email'] ?? $traveler?->email ?? '') }}" placeholder="{{ __('checkout.email_placeholder') }}" class="form-input" required>
                                        <p class="form-hint">Booking confirmation will be sent here.</p>
                                    </div>

                                    <div class="form-group">
                                        <label for="guest_phone">{{ __('checkout.phone_number') }}</label>
                                        <input type="tel" id="guest_phone" name="guest_phone" value="{{ old('guest_phone', $guestDefaults['guest_phone'] ?? $traveler?->mobile_phone ?? '') }}" placeholder="{{ __('checkout.phone_placeholder') }}" class="form-input">
                                    </div>
                                </div>

                                {{-- Create Account Checkbox --}}
                                @if(!auth('traveler')->check())
                                <div class="form-group" style="margin-top: 20px;">
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                        <input type="checkbox" id="createAccountCheckbox" name="create_account" value="1" class="form-input" style="width: auto; margin: 0;">
                                        <span style="font-size: 14px; color: #333;">{{ __('checkout.create_account_label') }}</span>
                                    </label>
                                </div>

                                {{-- Account Creation Fields (Hidden by Default) --}}
                                <div id="accountCreationSection" style="display: none; margin-top: 20px; padding: 16px; background: #f8f9fa; border-radius: 10px; border: 1px solid #e8e8ef;">
                                    <div class="form-group">
                                        <label for="account_password">{{ __('checkout.password') }} <span class="req">*</span></label>
                                        <input type="password" id="account_password" name="account_password" class="form-input" placeholder="{{ __('checkout.password') }}">
                                        <p class="form-hint" style="color: #666; margin-top: 6px;">
                                            <i class="fa-solid fa-info-circle"></i> {{ __('checkout.password_hint') }}
                                        </p>
                                    </div>

                                    <div class="form-group">
                                        <label for="account_password_confirm">{{ __('checkout.password_confirm') }} <span class="req">*</span></label>
                                        <input type="password" id="account_password_confirm" name="account_password_confirm" class="form-input" placeholder="{{ __('checkout.password_confirm') }}">
                                    </div>

                                    <button type="button" id="createAccountBtn" class="btn-create-account" onclick="createGuestAccount()">
                                        <i class="fa-solid fa-user-plus"></i> {{ __('checkout.create_account_and_proceed') }}
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-card accordion-card">
                            <button type="button" class="accordion-header" aria-expanded="true">
                                <h2 class="form-section-title">
                                    <span class="step-num">2</span> {{ __('checkout.step.special_requests') }}
                                </h2>
                                <span class="accordion-toggle">−</span>
                            </button>
                            <div class="accordion-panel">
                                <div class="form-group form-group--full">
                                    <label for="special_requests">{{ __('checkout.special_requests_optional') }}</label>
                                    <textarea id="special_requests" name="special_requests"
                                            rows="3" placeholder="{{ __('checkout.special_request_placeholder') }}"
                                            class="form-input form-textarea">{{ old('special_requests') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-card accordion-card">
                            <button type="button" class="accordion-header" aria-expanded="true">
                                <h2 class="form-section-title">
                                    <span class="step-num">3</span> {{ __('checkout.step.payment_method') }}
                                </h2>
                                <span class="accordion-toggle">−</span>
                            </button>
                            <div class="accordion-panel">
                                <div class="payment-options">
                                    <!-- <label class="payment-option {{ old('payment_method', 'cod') === 'cod' ? 'selected' : '' }}">
                                        <input type="radio" name="payment_method" value="cod" hidden {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}>
                                        <div class="payment-option-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                                        <div>
                                            <strong>Testing Payment</strong>
                                       
                                        </div>
                                        <i class="fa-solid fa-circle-check payment-tick"></i>
                                    </label> -->
                                    <label class="payment-option {{ old('payment_method') === 'againgency' ? 'selected' : '' }}">
                                        <input type="radio" name="payment_method" value="againgency" hidden checked>
                                        <div class="payment-option-icon"><i class="fa-solid fa-credit-card"></i></div>
                                        <div>
                                            <strong>{{ __('checkout.online_payment') }}</strong>
                                            <p>{{ __('checkout.pay_securely') }}</p>
                                        </div>
                                        <i class="fa-solid fa-circle-check payment-tick"></i>
                                    </label>
                                </div>
                                <p class="form-hint" style="margin-top:10px;">
                                    {{ __('checkout.payment_method_hint') }}
                                </p>
                            </div>
                        </div>

                        {{-- Items review mini list --}}
                        <div class="form-card accordion-card">
                            <button type="button" class="accordion-header" aria-expanded="true">
                                <h2 class="form-section-title">
                                    <span class="step-num">4</span> {{ __('checkout.step.your_items') }}
                                </h2>
                                <span class="accordion-toggle">−</span>
                            </button>
                            <div class="accordion-panel">
                                <div class="item-accordion">
                                @foreach($cart as $key => $item)
                                    <div class="item-panel open">
                                        <button type="button" class="item-panel-header">
                                            <div class="item-panel-title">
                                                <strong>{{ $item['title'] }}</strong>
                                                @if($item['type'] === 'accommodation' && !empty($item['plan_label']))
                                                    <span style="background: #f0f7f7; color: #19b5b5; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; margin-left: 8px;">{{ $item['plan_label'] }}</span>
                                                @endif
                                                <span class="item-panel-meta">
                                                    {{ $item['check_in_display'] }}
                                                    @if($item['check_in'] !== $item['check_out'])
                                                        → {{ $item['check_out_display'] }}
                                                    @endif
                                                </span>
                                            </div>
                                            <span class="accordion-toggle">−</span>
                                        </button>
                                        <div class="item-panel-content">
                                            <div class="item-panel-summary">
                                                @if($item['type'] === 'accommodation')
                                                    <span><i class="fa-solid fa-user"></i> {{ $item['adults'] }} adult{{ $item['adults'] != 1 ? 's' : '' }}</span>
                                                    <span><i class="fa-solid fa-child"></i> {{ $item['children'] }} child{{ $item['children'] != 1 ? 'ren' : '' }}</span>
                                                    @if(!empty($item['infants']))
                                                        <span><i class="fa-solid fa-baby-carriage"></i> {{ $item['infants'] }} infant{{ $item['infants'] != 1 ? 's' : '' }}</span>
                                                    @endif
                                                    <span><i class="fa-solid fa-bed"></i> {{ $item['room_name'] }} · {{ $item['nights'] }} night{{ $item['nights'] != 1 ? 's' : '' }}</span>
                                                @else
                                                    @php $participantCount = ($item['adults'] ?? 0) + ($item['children'] ?? 0); @endphp
                                                    <span><i class="fa-solid fa-user"></i> {{ $participantCount }} participant{{ $participantCount != 1 ? 's' : '' }}</span>
                                                    <span><i class="fa-solid fa-person-hiking"></i> {{ $item['variant_name'] ?: 'Standard' }}</span>
                                                @endif
                                                <span><i class="fa-solid fa-money-bill-wave"></i> {{ $item['currency'] }} {{ number_format($item['net_amount'], 2) }}</span>
                                            </div>
                                            <div class="item-panel-actions">
                                                <button type="button" class="btn toggle-guest-form" data-item="{{ $key }}">
                                                    <i class="fa-solid fa-plus"></i> {{ __('checkout.add_guest_details', ['type' => $item['type'] === 'accommodation' ? __('checkout.guest') : __('checkout.participant')]) }}
                                                </button>
                                            </div>
                                            <div class="item-guest-form" id="guest-form-{{ $key }}" style="display: none; margin-top: 15px;">
                                                <h4 style="font-size: 14px; font-weight: bold; margin-bottom: 10px;">{{ $item['type'] === 'accommodation' ? 'Guest' : 'Participant' }} Details for {{ $item['title'] }}</h4>
                                                <div class="item-saved-guests-panel" id="item-saved-guests-panel-{{ $key }}" style="margin-bottom: 14px;">
                                                    <p class="item-saved-guests-title">{{ __('checkout.select_saved_guests') }}</p>
                                                    <div class="item-saved-guests-list" id="item-saved-guests-list-{{ $key }}"></div>
                                                    <button type="button" class="btn btn-secondary btn-apply-saved-guests" data-item="{{ $key }}" style="margin-top: 10px;">
                                                        <i class="fa-solid fa-check"></i> {{ __('checkout.add_selected_saved_guests') }}
                                                    </button>
                                                </div>
                                                <div class="item-guests-list" id="item-guests-{{ $key }}">
                                                    {{-- {{ __('checkout.guests_added_here') }} --}}
                                                </div>
                                                <button type="button" class="btn-add-item-guest" data-item="{{ $key }}" style="margin-top: 10px;">
                                                    <i class="fa-solid fa-plus"></i> {{ __('checkout.add_new', ['type' => $item['type'] === 'accommodation' ? __('checkout.guest') : __('checkout.participant')]) }}
                                                </button>
                                            </div>
                                            <p style="margin-top: 12px; font-size: 13px; color: #555;">
                                                {{ __('checkout.optional_details_text', ['type' => $item['type'] === 'accommodation' ? __('checkout.guest_names') : __('checkout.participant_details')]) }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                            </div>
                            <input type="hidden" id="additionalGuestsData" name="additional_guests_json" value="">
                        </div>
                    </form>
                </div>

                {{-- ════════ RIGHT — Summary Sidebar ════════ --}}
                <aside class="checkout-summary">
                    <div class="summary-card">
                            <h2 class="summary-heading">{{ __('checkout.order_summary') }}</h2>
                        <div class="fare-rows">
                            @foreach($cart as $item)
                                @php
                                    $nights = (int) ($item['nights'] ?? 1);
                                    $rooms  = (int) ($item['rooms'] ?? 1);
                                    $lbl = $item['type'] === 'accommodation'
                                        ? $rooms . ' Room' . ($rooms !== 1 ? 's' : '') . ' · ' . $nights . ' Night' . ($nights !== 1 ? 's' : '')
                                        : 'Activity: ' . ($item['variant_name'] ?: $item['title']);
                                @endphp
                                <div class="fare-row">
                                    <span>{{ $lbl }}</span>
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
                            <span>{{ __('cart.net_amount_payable') }}</span>
                            <span>{{ $summary['currency'] }} {{ number_format($summary['net_payable'], 2) }}</span>
                        </div>

                        <button type="submit" form="checkoutForm" class="btn-checkout">
                            <i class="fa-solid fa-lock"></i>
                            {{ __('checkout.confirm_booking_cod') }}
                        </button>

                        <p class="summary-note">
                            <i class="fa-solid fa-shield-halved"></i>
                            {{ __('checkout.secure_booking') }}                        </p>

                        <a href="{{ url('/') }}" class="back-to-cart">
                            <i class="fa-solid fa-arrow-left"></i> {{ __('checkout.back_to_search') }}
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
                    <h2>{{ __('checkout.add_guest') }}</h2>
                    <button type="button" class="guest-modal-close" id="closeModalBtn">&times;</button>
                </div>
                <div class="guest-modal-body">
                    <form id="addGuestForm">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="modal_relation">{{ __('checkout.relation.choose') }} <span class="req">*</span></label>
                                <select id="modal_relation" name="relation" required class="form-input">
                                    <option value="">{{ __('checkout.relation.choose') }}</option>
                                    <option value="self">{{ __('checkout.relation.self') }}</option>
                                    <option value="spouse">{{ __('checkout.relation.spouse') }}</option>
                                    <option value="child">{{ __('checkout.relation.child') }}</option>
                                    <option value="friend">{{ __('checkout.relation.friend') }}</option>
                                    <option value="colleague">{{ __('checkout.relation.colleague') }}</option>
                                    <option value="other">{{ __('checkout.relation.other') }}</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="modal_gender">Gender</label>
                                <select id="modal_gender" name="gender" class="form-input">
                                    <option value="">{{ __('checkout.gender.select') }}</option>
                                    <option value="male">{{ __('checkout.gender.male') }}</option>
                                    <option value="female">{{ __('checkout.gender.female') }}</option>
                                    <option value="non_binary">{{ __('checkout.gender.non_binary') }}</option>
                                    <option value="other">{{ __('checkout.gender.other') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="modal_first_name">{{ __('profile.first_name') }} <span class="req">*</span></label>
                                <input type="text" id="modal_first_name" name="first_name" required class="form-input">
                            </div>

                            <div class="form-group">
                                <label for="modal_middle_name">{{ __('profile.middle_name') }}</label>
                                <input type="text" id="modal_middle_name" name="middle_name" class="form-input">
                            </div>

                            <div class="form-group">
                                <label for="modal_last_name">{{ __('profile.last_name') }} <span class="req">*</span></label>
                                <input type="text" id="modal_last_name" name="last_name" required class="form-input">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="modal_dob">{{ __('profile.date_of_birth') }} <span class="req">*</span></label>
                                <input type="date" id="modal_dob" name="dob" required class="form-input">
                            </div>

                            <div class="form-group">
                                <label for="modal_nationality">{{ __('profile.nationality') }} <span class="req">*</span></label>
                                <select id="modal_nationality" name="nationality" required class="form-input">
                                    <option value="">{{ __('checkout.select_nationality') }}</option>
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

.checkout-section > .wrap {display: flex; gap: 20px;}

.checkout-page-header { margin-bottom: 0; flex: 1;}
.checkout-page-header h1 { font-size: 32px; font-weight: 700; color: 
var(--blue-darker); margin: 0 0 5px; letter-spacing: -0.5px; }
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
    border: 1px solid #e1e1e1;
    border-radius: 10px;
    margin-bottom: 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}

.accordion-card {
    overflow: hidden;
}

.accordion-header {
    width: 100%;
    background: transparent;
    border: none;
    padding: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
}

.accordion-header:hover {
    background: #f8f8ff;
}

.accordion-header h2 {
    margin: 0;
}

.accordion-toggle {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    /* background: #1a1a2e; */
    color: var(--blue-dark);
    font-size: 28px;
}

.accordion-panel {
        padding: 24px;
    display: block;
    border-top: 1px solid #ededed;
}

.accordion-card.collapsed .accordion-panel {
    display: none;
}

.item-accordion {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.item-panel {
    border: 1px solid #e8e8ef;
    border-radius: 14px;
    overflow: hidden;
    background: #fbfbfd;
}

.item-panel-header {
    width: 100%;
    background: #fff;
    border: none;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
}

.item-panel-header:hover {
    background: #f5f7ff;
}

.item-panel-content {
    padding: 18px;
    display: none;
    background: #fbfbfd;
}

.item-panel.open .item-panel-content {
    display: block;
}

.item-panel-title {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
    text-align: left;
}

.item-panel-title strong {
    font-size: 14px;
    color: #1a1a2e;
}

.item-panel-meta {
    font-size: 12px;
    color: #666;
}

.item-panel-summary {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin: 14px 0 0;
    font-size: 13px;
    color: #555;
}

.item-panel-summary span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e8e8ef;
}

.item-panel-actions {
    margin-top: 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.item-panel-actions button {
    border: 1px solid #1a1a2e;
    background: #fff;
    color: #1a1a2e;
    padding: 10px 14px;
    border-radius: 10px;
    cursor: pointer;
    transition: background .2s, color .2s;
}

.item-panel-actions button:hover {
    background: #1a1a2e;
    color: #fff;
}

.form-card .form-group,
.form-card .form-grid,
.form-card .saved-guests-section,
.form-card .additional-guests-section {
    margin-bottom: 0;
}

.form-card .form-group {
    margin-bottom: 16px;
}

.form-card .form-grid {
    padding-top: 12px;
}

.form-card .form-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 600;
    color: #1a1a2e;
    margin: 0 0 0;
}
.step-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px; height: 26px;
    background: var(--brand-color);
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
    color: var(--darker);
    outline: none;
    transition: border-color .2s;
    box-sizing: border-box;
    background: #f2f1f6;
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
    border: 2px solid rgba(26, 127, 55, 0.25);
    border-radius: 12px;
    padding: 14px 16px;
    background: #f8f8ff;
    cursor: pointer;
    transition: border-color .2s, background .2s;
}
.payment-option.selected {
    border-color: #1a7f37;
    background: #ecf9ed;
}
.payment-option-icon {
    font-size: 22px;
    color: #1a1a2e;
    flex-shrink: 0;
}
.payment-option strong { display: block; font-size: 14px; color: #1a1a2e; }
.payment-option p { font-size: 12px; color: #666; margin: 2px 0 0; }
.payment-tick {
    margin-left: auto;
    color: #1a7f37;
    font-size: 20px;
    opacity: 0;
    transition: opacity .2s;
}
.payment-option.selected .payment-tick {
    opacity: 1;
}

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
.checkout-summary { position: sticky; top: 120px;     flex-basis: 35%;
    align-self: flex-start; }
.summary-card {
    background: var(--grey-light);
    border: 0px solid #e8e8ef;
    border-radius: 10px;
    padding: 24px 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,.06);
}
.summary-heading { font-size: 18px; font-weight: 700; color: #1a1a2e; margin: 0 0 18px; }
.summary-divider { height: 1px; background: #eee; margin: 16px 0; }
.fare-rows { display: flex; flex-direction: column; gap: 10px; }
.fare-row { display: flex; justify-content: space-between; font-size: 14px; color: #333; font-weight: 600;}
.fare-row span:first-child { display: flex; align-items: center; gap: 5px; font-weight: 500;}
.fare-row--discount { color: #1a7f37; font-weight: 600; }
.fare-row--total { font-size: 16px; font-weight: 700; color: #1a1a2e; margin-top: 4px; }

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

.btn-create-account {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    margin-top: 16px;
    padding: 12px 18px;
    background: #1a7f37;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    transition: background .2s;
}
.btn-create-account:hover { background: #0f5a23; }
.btn-create-account:disabled { background: #ccc; cursor: not-allowed; }

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

button.btn-add-item-guest {
    border: 1px solid #1a1a2e;
    background: #fff;
    color: #1a1a2e;
    padding: 10px 14px;
    border-radius: 10px;
    cursor: pointer;
    transition: background .2s, color .2s;
}

button.btn-add-item-guest:hover {
    background: #1a1a2e;
    color: #fff;
}

label.saved-guest-checkbox {
    display: flex;
    align-items: self-start;
    gap: 10px;
        margin-bottom: 10px;
}

.saved-guest-info .saved-guest-name {
    font-weight: 600;
}
.saved-guest-info .saved-guest-details {
    font-size: 14px;
}

.payment-options {
    display: flex;
    gap: 10px;
}

.payment-options > label {
    flex: 1;
}
 
@media (max-width: 860px) {
    .checkout-layout { grid-template-columns: 1fr; }
    .checkout-summary { position: static; }
    .form-grid { grid-template-columns: 1fr; }

    .checkout-section > .wrap {display: block;}

    .payment-options {
    flex-direction: column;
}
}

</style>
@endpush
@endsection

@push('scripts')
<script>
    const checkoutLocale = {
        emailRequired: @json(__('checkout.error.email_required')),
        passwordMismatch: @json(__('checkout.error.password_mismatch')),
        passwordStrength: @json(__('checkout.error.password_strength')),
        accountCreationFailed: @json(__('checkout.error.create_account_failed')),
        genericRetry: @json(__('checkout.error.generic_retry')),
        createAccountAndProceed: @json(__('checkout.create_account_and_proceed')),
        payNow: @json(__('checkout.pay_now')),
        confirmBookingCod: @json(__('checkout.confirm_booking_cod')),
    };

    // Account Creation Helper Functions
    function validatePassword(password) {
        const regex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*#?&^()_+\-=\[\]{};:"\\|,.<>\/?]).{8,}$/;
        return regex.test(password);
    }

    function toggleAccountCreationSection() {
        const checkbox = document.getElementById('createAccountCheckbox');
        const section = document.getElementById('accountCreationSection');
        const passwordInput = document.getElementById('account_password');
        const confirmInput = document.getElementById('account_password_confirm');
        
        if (checkbox && section) {
            if (checkbox.checked) {
                section.style.display = 'block';
                passwordInput.setAttribute('required', 'required');
                confirmInput.setAttribute('required', 'required');
            } else {
                section.style.display = 'none';
                passwordInput.removeAttribute('required');
                confirmInput.removeAttribute('required');
                passwordInput.value = '';
                confirmInput.value = '';
                // Clear any validation error messages
                const errorContainer = document.getElementById('accountCreationErrors');
                if (errorContainer) {
                    errorContainer.innerHTML = '';
                    errorContainer.style.display = 'none';
                }
            }
        }
    }

    function showAccountCreationError(message) {
        let errorContainer = document.getElementById('accountCreationErrors');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.id = 'accountCreationErrors';
            errorContainer.style.cssText = 'background: #fff5f5; border: 1px solid #fc8181; border-radius: 8px; padding: 12px 16px; margin-bottom: 12px;';
            const section = document.getElementById('accountCreationSection');
            section.parentNode.insertBefore(errorContainer, section);
        }
        errorContainer.innerHTML = '<ul style="margin: 0; padding-left: 18px;"><li style="font-size: 13px; color: #c53030;">' + message + '</li></ul>';
        errorContainer.style.display = 'block';
    }

    function clearAccountCreationErrors() {
        const errorContainer = document.getElementById('accountCreationErrors');
        if (errorContainer) {
            errorContainer.innerHTML = '';
            errorContainer.style.display = 'none';
        }
    }

    function createGuestAccount() {
        clearAccountCreationErrors();
        
        const email = document.getElementById('guest_email').value.trim();
        const password = document.getElementById('account_password').value;
        const confirmPassword = document.getElementById('account_password_confirm').value;
        const firstName = document.getElementById('guests_0_first_name')?.value.trim() || '';
        const middleName = document.getElementById('guests_0_middle_name')?.value.trim() || '';
        const lastName = document.getElementById('guests_0_last_name')?.value.trim() || '';
        const dob = document.getElementById('guests_0_dob')?.value || '';
        const gender = document.getElementById('guests_0_gender')?.value || '';
        const nationality = document.getElementById('guests_0_nationality')?.value || '';
        const phone = document.getElementById('guest_phone')?.value.trim() || '';
        const btn = document.getElementById('createAccountBtn');

        // Validate email
        if (!email) {
            showAccountCreationError(checkoutLocale.emailRequired);
            return;
        }

        // Validate passwords match
        if (password !== confirmPassword) {
            showAccountCreationError(checkoutLocale.passwordMismatch);
            return;
        }

        // Validate password strength
        if (!validatePassword(password)) {
            showAccountCreationError(checkoutLocale.passwordStrength);
            return;
        }

        // Disable button and show loading state
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating Account...';

        // Send account creation request
        fetch('{{ route("frontend.booking.create-guest-account") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                email: email,
                password: password,
                password_confirmation: confirmPassword,
                first_name: firstName,
                middle_name: middleName,
                last_name: lastName,
                dob: dob,
                gender: gender,
                nationality: nationality,
                guest_phone: phone,
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Account created and user logged in
                // Redirect to checkout page for logged-in customer
                window.location.href = '{{ route("frontend.booking.checkout") }}';
            } else {
                // Show error message
                showAccountCreationError(data.error || checkoutLocale.accountCreationFailed);
                btn.disabled = false;
                btn.innerHTML = `<i class="fa-solid fa-user-plus"></i> ${checkoutLocale.createAccountAndProceed}`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAccountCreationError(checkoutLocale.genericRetry);
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-user-plus"></i> ${checkoutLocale.createAccountAndProceed}`;
        });
    }

    // Setup checkbox toggle listener
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('createAccountCheckbox');
        if (checkbox) {
            checkbox.addEventListener('change', toggleAccountCreationSection);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Define route URLs
        const saveGuestUrl = '{{ route("frontend.booking.save-guest") }}';
        const removeGuestUrl = '{{ route("frontend.booking.remove-guest") }}';
        @php
            $savedGuestsArray = $savedGuests->map(function ($guest) {
                return [
                    'id' => $guest->id,
                    'relation' => $guest->relation ?? 'other',
                    'gender' => $guest->gender,
                    'first_name' => $guest->first_name,
                    'middle_name' => $guest->middle_name,
                    'last_name' => $guest->last_name,
                    'dob' => $guest->dob,
                    'nationality' => $guest->nationality,
                    'passport_number' => $guest->passport_number,
                    'notes' => $guest->notes,
                ];
            })->all();
        @endphp
        const savedGuestsData = @json($savedGuestsArray);
        const cartItems = @json($cart);
        const isTraveler = {{ $traveler ? 'true' : 'false' }};

        function normalizeBookingGender(value) {
            if (!value) {
                return null;
            }

            const map = {
                mr: 'male',
                mrs: 'female',
                miss: 'female',
                ms: 'female',
                mx: 'non_binary',
                other: 'other',
            };

            const normalized = value.toString().trim().toLowerCase();
            if (map[normalized]) {
                return map[normalized];
            }

            return ['male', 'female', 'non_binary', 'other'].includes(normalized) ? normalized : null;
        }

        @php
            $selfGuest = null;
            if ($traveler) {
                $selfGuest = [
                    'id' => 'self',
                    'relation' => 'self',
                    'gender' => $traveler->profile->gender ?? 'other',
                    'first_name' => $traveler->profile->first_name ?? '',
                    'middle_name' => $traveler->profile->middle_name ?? '',
                    'last_name' => $traveler->profile->last_name ?? '',
                    'dob' => $traveler->profile->dob ?? $guestDefaults['dob'] ?? '',
                    'nationality' => $traveler->profile->nationality ?? '',
                    'passport_number' => $traveler->profile->passport_number ?? '',
                    'notes' => '',
                ];
            }
        @endphp
        const selfGuest = @json($selfGuest);
        if (selfGuest) {
            selfGuest.gender = normalizeBookingGender(selfGuest.gender);
        }
        const itemKeys = [@foreach($cart as $key => $item) '{{ $key }}', @endforeach];
        const totalGuests = {{ $totalGuests }};
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelModalBtn = document.getElementById('cancelModalBtn');
        const saveGuestBtn = document.getElementById('saveGuestBtn');
        const addGuestModal = document.getElementById('addGuestModal');
        const modalOverlay = document.getElementById('modalOverlay');
        const addGuestForm = document.getElementById('addGuestForm');
        const additionalGuestsData = document.getElementById('additionalGuestsData');
        const checkoutForm = document.getElementById('checkoutForm');

        let additionalGuests = {}; // { itemKey: [] }
        let currentItem = null; // For per item modal

        // Load saved guests from localStorage or URL old data
        function loadGuestsFromForm() {
            const savedData = additionalGuestsData.value;
            if (savedData) {
                try {
                    additionalGuests = JSON.parse(savedData);
                } catch (e) {
                    additionalGuests = {};
                }
            } else {
                additionalGuests = {};
            }
            // Ensure all cart items have arrays
            @foreach($cart as $key => $item)
                if (!additionalGuests['{{ $key }}']) {
                    additionalGuests['{{ $key }}'] = [];
                }
            @endforeach
        }

        // Save guests to hidden input
        function saveGuestsToForm() {
            additionalGuestsData.value = JSON.stringify(additionalGuests);
        }

        function renderSavedGuestsForItem(itemKey) {
            const container = document.getElementById('item-saved-guests-list-' + itemKey);
            if (!container) return;
            container.innerHTML = '';

            if (!savedGuestsData || savedGuestsData.length === 0) {
                container.innerHTML = '<div class="form-hint">No saved guest profiles available.</div>';
                return;
            }

            const selectedGuests = additionalGuests[itemKey] || [];

            savedGuestsData.forEach((guest, index) => {
                const item = document.createElement('label');
                item.className = 'saved-guest-checkbox';

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'saved-guest-checkbox-input';
                checkbox.dataset.index = index;
                checkbox.dataset.id = guest.id ?? '';
                checkbox.dataset.relation = guest.relation || 'other';
                checkbox.dataset.firstName = guest.first_name || '';
                checkbox.dataset.middleName = guest.middle_name || '';
                checkbox.dataset.lastName = guest.last_name || '';
                checkbox.dataset.dob = guest.dob || '';
                checkbox.dataset.gender = guest.gender || '';
                checkbox.dataset.nationality = guest.nationality || '';
                checkbox.dataset.passport = guest.passport_number || '';
                checkbox.dataset.notes = guest.notes || '';

                const isSelected = selectedGuests.some(selected => {
                    if (checkbox.dataset.id && selected.id) {
                        return checkbox.dataset.id === selected.id;
                    }
                    return (
                        selected.first_name === checkbox.dataset.firstName &&
                        selected.last_name === checkbox.dataset.lastName &&
                        selected.dob === checkbox.dataset.dob &&
                        selected.nationality === checkbox.dataset.nationality
                    );
                });
                checkbox.checked = isSelected;

                const info = document.createElement('div');
                info.className = 'saved-guest-info';

                const name = document.createElement('div');
                name.className = 'saved-guest-name';
                name.textContent = `${guest.first_name || ''} ${guest.last_name || ''}`.trim();
                if (guest.id === 'self' || guest.relation === 'self') {
                    name.textContent += ' (You)';
                }

                const formattedDob = guest.dob ? (new Date(guest.dob).toLocaleDateString('en-GB') || guest.dob) : 'No DOB';
                const details = document.createElement('div');
                details.className = 'saved-guest-details';
                details.textContent = `${guest.nationality || 'Unknown'} · ${formattedDob}`;

                info.appendChild(name);
                info.appendChild(details);
                item.appendChild(checkbox);
                item.appendChild(info);
                container.appendChild(item);
            });
        }

        function addSavedGuestsToItem(itemKey) {
            const container = document.getElementById('item-saved-guests-list-' + itemKey);
            if (!container) return;
            const checked = Array.from(container.querySelectorAll('input.saved-guest-checkbox-input:checked'));
            if (!checked.length) {
                alert('Please select at least one saved guest to add.');
                return;
            }
            if (!additionalGuests[itemKey]) {
                additionalGuests[itemKey] = [];
            }
            checked.forEach(input => {
                const guest = {
                    id: input.dataset.id || null,
                    relation: input.dataset.relation || 'other',
                    gender: input.dataset.gender,
                    first_name: input.dataset.firstName,
                    middle_name: input.dataset.middleName,
                    last_name: input.dataset.lastName,
                    dob: input.dataset.dob,
                    nationality: input.dataset.nationality,
                    passport_number: input.dataset.passport,
                    notes: input.dataset.notes,
                    below_12: (() => {
                        if (!input.dataset.dob) return false;
                        const dob = new Date(input.dataset.dob);
                        const today = new Date();
                        let age = today.getFullYear() - dob.getFullYear();
                        const monthDiff = today.getMonth() - dob.getMonth();
                        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                            age--;
                        }
                        return age < 12;
                    })(),
                };

                const alreadyAdded = additionalGuests[itemKey].some(existing => {
                    if (guest.id && existing.id) {
                        return guest.id === existing.id;
                    }
                    return (
                        existing.first_name === guest.first_name &&
                        existing.last_name === guest.last_name &&
                        existing.dob === guest.dob &&
                        existing.nationality === guest.nationality
                    );
                });
                if (!alreadyAdded) {
                    additionalGuests[itemKey].push(guest);
                }
            });
            renderItemGuestsList(itemKey, document.getElementById('item-guests-' + itemKey));
            saveGuestsToForm();
            renderSavedGuestsForItem(itemKey);
        }

        // Render guests list for a specific item
        function renderItemGuestsList(itemKey, container) {
            container.innerHTML = '';
            const guests = additionalGuests[itemKey] || [];
            const item = cartItems[itemKey];
            
            guests.forEach((guest, index) => {
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
                        <button type="button" class="btn-edit-guest" data-item="${itemKey}" data-index="${index}">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                        <button type="button" class="btn-remove-guest" data-item="${itemKey}" data-index="${index}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                `;
                container.appendChild(guestItem);
            });

            // Attach event listeners using delegation
            container.querySelectorAll('.btn-edit-guest').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const item = this.dataset.item;
                    const index = parseInt(this.dataset.index);
                    editItemGuest(item, index);
                });
            });

            container.querySelectorAll('.btn-remove-guest').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const item = this.dataset.item;
                    const index = parseInt(this.dataset.index);
                    removeItemGuest(item, index);
                });
            });

            saveGuestsToForm();
        }

        // Open modal for global or item
        function openModal(itemKey = null) {
            currentItem = itemKey;
            addGuestModal.classList.add('show');
            modalOverlay.classList.add('show');
            addGuestModal.style.display = 'flex';
            modalOverlay.style.display = 'block';
            addGuestForm.reset();
            editingIndex = null;

            // Per-guest time slot entry is no longer used in checkout.
            const timeslotGroup = document.getElementById('modal_timeslot_group');
            const timeslotSelect = document.getElementById('modal_time_slot');
            if (timeslotGroup) {
                timeslotGroup.style.display = 'none';
            }
            if (timeslotSelect) {
                timeslotSelect.value = '';
            }
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
                below_12: document.getElementById('modal_below_12').checked,
            };

            if (isTraveler) {
                // Save to server for logged-in travelers only
                fetch(saveGuestUrl, {
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

                    savedGuestsData.push({
                        id: data.guest.id,
                        relation: data.guest.relation || 'other',
                        gender: data.guest.gender,
                        first_name: data.guest.first_name,
                        middle_name: data.guest.middle_name,
                        last_name: data.guest.last_name,
                        dob: data.guest.dob,
                        nationality: data.guest.nationality,
                        passport_number: data.guest.passport_number,
                        notes: data.guest.notes,
                    });
                    itemKeys.forEach(key => renderSavedGuestsForItem(key));
                }).catch(error => {
                    console.error('Error saving guest:', error);
                    alert('Failed to save guest: ' + (error.message || 'Please try again.'));
                });
            }

            if (editingIndex !== null && currentItem) {
                additionalGuests[currentItem][editingIndex] = guestData;
            } else if (currentItem) {
                if (!additionalGuests[currentItem]) {
                    additionalGuests[currentItem] = [];
                }
                additionalGuests[currentItem].push(guestData);
            }

            if (currentItem) {
                renderItemGuestsList(currentItem, document.getElementById('item-guests-' + currentItem));
            }
            closeModal();
        }

        // Edit guest
        function editItemGuest(itemKey, index) {
            currentItem = itemKey;
            editingIndex = index;
            const guest = additionalGuests[itemKey][index];
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
            openModal(itemKey);
        }

        // Remove guest
        function removeItemGuest(itemKey, index) {
            additionalGuests[itemKey].splice(index, 1);
            renderItemGuestsList(itemKey, document.getElementById('item-guests-' + itemKey));
        }

        // Form submission - inject all guests as array
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                // Build guests array inputs
                const guestsContainer = document.createElement('div');
                guestsContainer.style.display = 'none';

                // Get primary guest data from form
                const primaryGuestData = {
                    relation: document.getElementById('guests_0_relation')?.value || 'self',
                    gender: document.getElementById('guests_0_gender')?.value || '',
                    first_name: document.getElementById('guests_0_first_name')?.value || '',
                    middle_name: document.getElementById('guests_0_middle_name')?.value || '',
                    last_name: document.getElementById('guests_0_last_name')?.value || '',
                    dob: document.getElementById('guests_0_dob')?.value || '',
                    nationality: document.getElementById('guests_0_nationality')?.value || '',
                    passport_number: document.getElementById('guests_0_passport_number')?.value || '',
                    notes: document.getElementById('guests_0_notes')?.value || '',
                };

                // Add per item guests
                Object.keys(additionalGuests).forEach(itemKey => {
                    const item = cartItems[itemKey];
                    let itemGuestIndex = 0;
                    
                    // Add additional guests for this item
                    additionalGuests[itemKey].forEach((guest, index) => {
                        if (typeof guest === 'object' && guest !== null) {
                            const guestIndex = itemGuestIndex + index + 1;
                            
                            Object.keys(guest).forEach(key => {
                                if (key !== 'below_12' && key !== '_primary_guest_time_slot') {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = `guests[${itemKey}][${guestIndex}][${key}]`;
                                    input.value = guest[key];
                                    guestsContainer.appendChild(input);
                                }
                            });
                        }
                    });
                });

                this.appendChild(guestsContainer);
            });

            function refreshPaymentSelection() {
                const selectedInput = document.querySelector('input[name="payment_method"]:checked');
                const selectedMethod = selectedInput ? selectedInput.value : 'againgency';
                document.querySelectorAll('.payment-option').forEach(option => {
                    const optionInput = option.querySelector('input[name="payment_method"]');
                    option.classList.toggle('selected', optionInput ? optionInput.checked : false);
                });

                const checkoutButton = document.querySelector('.btn-checkout');
                if (checkoutButton) {
                    checkoutButton.innerHTML = selectedMethod === 'againgency'
                        ? `<i class="fa-solid fa-credit-card"></i> ${checkoutLocale.payNow}`
                        : `<i class="fa-solid fa-lock"></i> ${checkoutLocale.confirmBookingCod}`;
                }
            }

            document.querySelectorAll('input[name="payment_method"]').forEach(input => {
                input.addEventListener('change', refreshPaymentSelection);
            });
            refreshPaymentSelection();
        }

        // Event listeners
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
            if (e.target.closest('.btn-remove-saved')) {
                e.preventDefault();
                const btn = e.target.closest('.btn-remove-saved');
                const guestId = btn.dataset.guestId;
                if (confirm('Are you sure you want to remove this saved guest?')) {
                    fetch(removeGuestUrl, {
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
        @foreach($cart as $key => $item)
        renderItemGuestsList('{{ $key }}', document.getElementById('item-guests-{{ $key }}'));
        renderSavedGuestsForItem('{{ $key }}');
        @endforeach

        // Handle Myself/Someone Else radio buttons and auto-fill
        const myselfRadio = document.querySelector('input[name="guest_type"][value="myself"]');
        const someoneElseRadio = document.querySelector('input[name="guest_type"][value="someone_else"]');
        const relationSelectEl = document.getElementById('guests_0_relation');
        const genderSelect = document.getElementById('guests_0_gender');
        const firstNameInput = document.getElementById('guests_0_first_name');
        const middleNameInput = document.getElementById('guests_0_middle_name');
        const lastNameInput = document.getElementById('guests_0_last_name');
        const dobInputEl = document.getElementById('guests_0_dob');
        const nationalitySelect = document.getElementById('guests_0_nationality');
        const passportInput = document.getElementById('guests_0_passport_number');
        const notesTextarea = document.getElementById('guests_0_notes');

        // Traveler data from PHP
        const travelerData = {
            first_name: '{{ $traveler?->profile->first_name ?? $traveler?->first_name ?? "" }}',
            middle_name: '{{ $traveler?->profile->middle_name ?? "" }}',
            last_name: '{{ $traveler?->profile->last_name ?? $traveler?->last_name ?? "" }}',
            dob: '{{ $traveler?->profile->date_of_birth ? \Carbon\Carbon::parse($traveler->profile->date_of_birth)->format('Y-m-d') : ($guestDefaults['dob'] ?? '') }}',
            gender: '{{ $traveler?->profile->gender ?? "" }}',
            nationality: '{{ $traveler?->profile->nationality ?? $traveler?->profile->country ?? "" }}',
            passport_number: '{{ $traveler?->profile->passport_number ?? "" }}',
        };

        // Normalize traveler gender
        travelerData.gender = normalizeBookingGender(travelerData.gender);

        function ensureHiddenRelationInput(value) {
            let hiddenInput = document.getElementById('guests_0_relation_hidden');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = 'guests_0_relation_hidden';
                hiddenInput.name = 'guests[0][relation]';
                relationSelectEl.parentNode?.insertBefore(hiddenInput, relationSelectEl.nextSibling);
            }
            hiddenInput.value = value;
        }

        function removeHiddenRelationInput() {
            const hiddenInput = document.getElementById('guests_0_relation_hidden');
            if (hiddenInput) {
                hiddenInput.remove();
            }
        }

        function handleGuestTypeChange() {
            if (myselfRadio && myselfRadio.checked) {
                // Auto-fill for Myself
                relationSelectEl.value = 'self';
                relationSelectEl.disabled = true;
                ensureHiddenRelationInput('self');
                if (genderSelect) genderSelect.value = travelerData.gender || '';
                if (firstNameInput) firstNameInput.value = travelerData.first_name || '';
                if (middleNameInput) middleNameInput.value = travelerData.middle_name || '';
                if (lastNameInput) lastNameInput.value = travelerData.last_name || '';
                if (dobInputEl) dobInputEl.value = travelerData.dob || '';
                if (nationalitySelect) nationalitySelect.value = travelerData.nationality || '';
                if (passportInput) passportInput.value = travelerData.passport_number || '';
                if (notesTextarea) notesTextarea.value = '';
            } else {
                // Enable for Someone Else
                relationSelectEl.disabled = false;
                removeHiddenRelationInput();
            }
        }

        // Auto-fill DOB when relation is "self" (for Someone Else mode)
        function handleRelationChange() {
            if (relationSelectEl.value === 'self') {
                if (dobInputEl) dobInputEl.value = travelerData.dob || '';
            } else {
                if (dobInputEl) dobInputEl.value = '';
            }
        }

        if (myselfRadio && someoneElseRadio) {
            myselfRadio.addEventListener('change', handleGuestTypeChange);
            someoneElseRadio.addEventListener('change', handleGuestTypeChange);
            // Run on page load
            handleGuestTypeChange();
        }

        if (relationSelectEl) {
            relationSelectEl.addEventListener('change', handleRelationChange);
        }



        // Accordion toggles for checkout sections
        document.querySelectorAll('.accordion-header').forEach(header => {
            header.addEventListener('click', function() {
                const card = this.closest('.accordion-card');
                if (!card) return;
                const expanded = card.classList.toggle('collapsed');
                this.setAttribute('aria-expanded', String(!expanded));
                const toggle = this.querySelector('.accordion-toggle');
                if (toggle) {
                    toggle.textContent = expanded ? '+' : '−';
                }
            });
        });

        document.querySelectorAll('.item-panel-header').forEach(header => {
            header.addEventListener('click', function() {
                const panel = this.closest('.item-panel');
                if (!panel) return;
                panel.classList.toggle('open');
                const toggle = this.querySelector('.accordion-toggle');
                if (toggle) {
                    toggle.textContent = panel.classList.contains('open') ? '−' : '+';
                }
            });
        });

        // Toggle guest form per item
        document.querySelectorAll('.toggle-guest-form').forEach(btn => {
            btn.addEventListener('click', function() {
                const item = this.dataset.item;
                const form = document.getElementById('guest-form-' + item);
                if (!form) return;
                const isVisible = form.style.display !== 'none';
                form.style.display = isVisible ? 'none' : 'block';
                if (!isVisible) {
                    renderSavedGuestsForItem(item);
                }
            });
        });

        // Add guest per item
        document.querySelectorAll('.btn-add-item-guest').forEach(btn => {
            btn.addEventListener('click', function() {
                const item = this.dataset.item;
                openModal(item);
            });
        });

        // Add saved guests to item
        document.querySelectorAll('.btn-apply-saved-guests').forEach(btn => {
            btn.addEventListener('click', function() {
                const item = this.dataset.item;
                addSavedGuestsToItem(item);
            });
        });

        // Global event delegation for timeslot changes

    });
</script>
@endpush
