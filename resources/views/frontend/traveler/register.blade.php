@extends('frontend.layout')

@section('title', __('auth.traveler_register_page_title'))
@section('meta_description', __('auth.traveler_register_meta_description'))

@section('content')
    <section class="page-section traveler-auth-section">
        <div class="wrap">
            <div class="traveler-auth-card">
                <div class="traveler-auth-head">
                    <h1>{{ __('auth.traveler_register_heading') }}</h1>
                    <p>{{ __('auth.traveler_register_subtitle') }}</p>
                </div>

                @if($errors->any())
                    <div class="traveler-alert traveler-alert--error">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('traveler.register.store') }}" class="traveler-form-grid">
                    @csrf

                    <div class="traveler-form-group traveler-form-group--full">
                        <label for="full_name">{{ __('auth.traveler_register_full_name') }}</label>
                        <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required>
                    </div>

                    <div class="traveler-form-group">
                        <label for="country">{{ __('auth.traveler_register_country_label') }}</label>
                        <select id="country" name="country">
                            <option value="">{{ __('auth.traveler_register_country_select') }}</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}" {{ old('country') === $country ? 'selected' : '' }}>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="traveler-form-group">
                        <label for="email">{{ __('auth.traveler_register_email_label') }}</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="traveler-form-group">
                        <label for="mobile_phone">{{ __('auth.traveler_register_mobile_label') }}</label>
                        <input id="mobile_phone" type="text" name="mobile_phone" value="{{ old('mobile_phone') }}" placeholder="+23052511153" required>
                        <small>{{ __('auth.traveler_register_mobile_hint') }}</small>
                    </div>

                    <div class="traveler-form-group position-relative">
                        <label for="password">{{ __('auth.traveler_register_password_label') }}</label>
                        <input id="password" type="password" name="password" required>
                        <span class="show-password" data-target="#password" aria-label="Toggle password visibility">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </span>
                        <small>{{ __('auth.traveler_register_password_hint') }}</small>
                    </div>

                    <div class="traveler-form-group">
                        <label for="password_confirmation">{{ __('auth.traveler_register_confirm_password_label') }}</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required>
                    </div>

                    <div class="traveler-form-group traveler-form-group--full traveler-consent-block">
                        <label class="traveler-check-row">
                            <input type="checkbox" name="consent_terms" value="1" {{ old('consent_terms') ? 'checked' : '' }} required>
                            <span>{{ __('auth.traveler_register_consent_terms') }}</span>
                        </label>
                        <label class="traveler-check-row">
                            <input type="checkbox" name="consent_privacy" value="1" {{ old('consent_privacy') ? 'checked' : '' }} required>
                            <span>{{ __('auth.traveler_register_consent_privacy') }}</span>
                        </label>
                        <label class="traveler-check-row">
                            <input type="checkbox" name="marketing_opt_in" value="1" {{ old('marketing_opt_in') ? 'checked' : '' }}>
                            <span>{{ __('auth.traveler_register_marketing_opt_in') }}</span>
                        </label>
                    </div>

                    <div class="traveler-form-group traveler-form-group--full traveler-form-actions">
                        <button type="submit" class="btn-primary">{{ __('auth.traveler_register_button') }}</button>
                        <a href="{{ route('traveler.login') }}" class="traveler-inline-link">{{ __('auth.traveler_register_have_account') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .traveler-auth-section {
            padding-top: 44px;
        }

        .traveler-auth-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 22px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
            padding: 28px;
            max-width: 840px;
            margin: 0 auto;
        }

        .traveler-auth-head h1 {
            margin: 0;
            font-size: 34px;
            font-family: 'Roboto Slab', Georgia, serif;
        }

        .traveler-auth-head p {
            margin: 10px 0 0;
            color: var(--muted);
            line-height: 1.8;
        }

        .traveler-alert {
            margin-top: 16px;
            border-radius: 14px;
            padding: 12px 14px;
            font-size: 14px;
        }

        .traveler-alert--error {
            border: 1px solid rgba(217, 83, 79, 0.35);
            background: rgba(217, 83, 79, 0.1);
            color: #8b1c1a;
        }

        .traveler-alert ul {
            margin: 0;
            padding-left: 18px;
            display: grid;
            gap: 4px;
        }

        .traveler-form-grid {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .traveler-form-group {
            display: grid;
            gap: 7px;
        }

        .traveler-form-group--full {
            grid-column: 1 / -1;
        }

        .traveler-form-group label {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
        }

        .traveler-form-group input,
        .traveler-form-group select,
        .traveler-form-group textarea {
            width: 100%;
            min-height: 44px;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 10px 12px;
            font: inherit;
            color: var(--ink);
            background: #fff;
        }

        .traveler-form-group small {
            color: var(--muted);
            font-size: 12px;
        }

        .traveler-consent-block {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 12px;
            gap: 10px;
            background: rgba(255, 255, 255, 0.76);
        }

        .traveler-check-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-weight: 600;
            color: #374151;
            line-height: 1.6;
        }

        .traveler-check-row input {
            width: 16px;
            height: 16px;
            min-height: 16px;
            margin-top: 3px;
            accent-color: var(--brand);
        }

        .traveler-form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 2px;
        }

        .traveler-form-actions .btn-primary {
            border: 0;
            cursor: pointer;
        }

        .traveler-inline-link {
            color: var(--brand-dark);
            font-weight: 700;
            font-size: 14px;
        }

        .traveler-form-group.position-relative {
            position: relative;
        }

        .traveler-form-group.position-relative .show-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #6b7280;
            opacity: 0.85;
        }

        .traveler-form-group.position-relative .show-password:hover {
            opacity: 1;
        }

        .traveler-form-group.position-relative input {
            padding-right: 44px;
        }

        @media (max-width: 760px) {
            .traveler-form-grid {
                grid-template-columns: 1fr;
            }

            .traveler-auth-card {
                padding: 22px;
            }

            .traveler-auth-head h1 {
                font-size: 28px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.show-password').forEach(function (button) {
                button.addEventListener('click', function () {
                    var target = button.dataset.target;
                    var input = target ? document.querySelector(target) : button.closest('.traveler-form-group').querySelector('input');
                    if (!input) return;
                    input.type = input.type === 'password' ? 'text' : 'password';
                });
            });
        });
    </script>

