@extends('frontend.layout')

@section('title', 'Traveller Settings | Holidays.io')
@section('meta_description', 'Manage your traveler account settings on Holidays.io.')

@section('content')
    <section class="page-section traveler-profile-section">
        <div class="wrap">
            <div class="traveler-profile-card">
                <div class="traveler-profile-head">
                    <h1>Traveller Account</h1>
                    <p>Manage your account settings and preferences.</p>
                </div>

                <!-- Submenu Navigation -->
                <div class="traveler-submenu">
                    <a href="{{ route('traveler.profile') }}" class="traveler-submenu-link">
                        <i class="fa-solid fa-user"></i> Profile
                    </a>
                    <a href="{{ route('traveler.settings') }}" class="traveler-submenu-link is-active">
                        <i class="fa-solid fa-gear"></i> Settings
                    </a>
                </div>

                @if(session('success'))
                    <div class="traveler-alert traveler-alert--success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="traveler-alert traveler-alert--error">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('traveler.settings.update') }}" class="traveler-settings-grid">
                    @csrf

                    <!-- 2FA Section -->
                    <div class="traveler-form-section">
                        <h3>Two-Factor Authentication (2FA)</h3>
                        <p class="section-note">Add an extra layer of security to your account.</p>

                        <div class="traveler-form-group traveler-form-group--toggle">
                            <label for="2fa_enabled" class="toggle-label">
                                <input 
                                    id="2fa_enabled" 
                                    type="checkbox" 
                                    name="2fa_enabled" 
                                    value="1"
                                    {{ old('2fa_enabled', $account->{'2fa_enabled'}) ? 'checked' : '' }}
                                    class="toggle-checkbox"
                                > 
                                <span>Enable Two-Factor Authentication</span>
                            </label>
                        </div>

                        <div id="2fa-method-group" class="traveler-form-group traveler-form-group--hidden" style="display: {{ old('2fa_enabled', $account->{'2fa_enabled'}) ? 'block' : 'none' }};">
                            <label for="2fa_method">2FA Method *</label>
                            <select id="2fa_method" name="2fa_method" class="traveler-form-control">
                                <option value="">Select method</option>
                                <option value="email" {{ old('2fa_method', $account->{'2fa_method'}) === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="sms" {{ old('2fa_method', $account->{'2fa_method'}) === 'sms' ? 'selected' : '' }}>SMS</option>
                                <option value="auth_app" {{ old('2fa_method', $account->{'2fa_method'}) === 'auth_app' ? 'selected' : '' }}>Authenticator App</option>
                            </select>
                            <small>Choose how you'd like to verify your login.</small>
                        </div>
                    </div>

                    <!-- Communication Preferences Section -->
                    <div class="traveler-form-section">
                        <h3>Communication Preferences</h3>
                        <p class="section-note">Choose how you'd like to receive notifications from us.</p>

                        <div class="traveler-form-group">
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input 
                                        type="checkbox" 
                                        name="communication_preference[]" 
                                        value="email"
                                        {{ in_array('email', old('communication_preference', $account->communication_preference ?? [])) ? 'checked' : '' }}
                                    >
                                    <span><i class="fa-solid fa-envelope"></i> Email</span>
                                </label>
                                <label class="checkbox-label">
                                    <input 
                                        type="checkbox" 
                                        name="communication_preference[]" 
                                        value="sms"
                                        {{ in_array('sms', old('communication_preference', $account->communication_preference ?? [])) ? 'checked' : '' }}
                                    >
                                    <span><i class="fa-solid fa-message"></i> SMS</span>
                                </label>
                                <label class="checkbox-label">
                                    <input 
                                        type="checkbox" 
                                        name="communication_preference[]" 
                                        value="whatsapp"
                                        {{ in_array('whatsapp', old('communication_preference', $account->communication_preference ?? [])) ? 'checked' : '' }}
                                    >
                                    <span><i class="fa-brands fa-whatsapp"></i> WhatsApp</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="traveler-form-actions">
                        <button type="submit" class="btn-primary">Save Settings</button>
                    </div>
                </form>

                <!-- Account Suspension Section (separate form to avoid nested forms) -->
                <div class="traveler-form-section traveler-form-section--danger" style="margin-top: 18px;">
                    <h3>Account Status</h3>
                    <p class="section-note">@if($account->account_suspended)Your account is currently suspended. You cannot login or make bookings.@else You can suspend your account at any time. While suspended, you won't be able to login or make bookings.@endif</p>

                    <div class="traveler-form-group traveler-form-group--full">
                        <form method="POST" action="{{ route('traveler.settings.toggle-suspension') }}" style="display: inline;">
                            @csrf
                            @if($account->account_suspended)
                                <button type="submit" class="btn-primary">
                                    <i class="fa-solid fa-check-circle"></i> Reactivate Account
                                </button>
                            @else
                                <button type="submit" class="btn-danger" onclick="return confirm('Are you sure you want to suspend your account? You will not be able to login until you reactivate it.');">
                                    <i class="fa-solid fa-pause-circle"></i> Suspend Account
                                </button>
                            @endif
                        </form>
                    </div>

                    @if($account->account_suspended)
                        <div class="traveler-status-badge traveler-status-badge--suspended">
                            <i class="fa-solid fa-circle"></i> Suspended
                        </div>
                    @else
                        <div class="traveler-status-badge traveler-status-badge--active">
                            <i class="fa-solid fa-circle"></i> Active
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .traveler-profile-section {
            padding-top: 40px;
        }

        .traveler-profile-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 10px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
            padding: 28px;
        }

        .traveler-profile-head h1 {
            margin: 0;
            font-size: 34px;
            /* font-family: 'Roboto Slab', Georgia, serif; */
        }

        .traveler-profile-head p {
            margin: 10px 0 0;
            color: var(--muted);
            line-height: 1.8;
        }

        /* Submenu Navigation */
        .traveler-submenu {
            display: flex;
            gap: 8px;
            margin-top: 24px;
            border-bottom: 2px solid var(--line);
            padding-bottom: 0;
        }

        .traveler-submenu-link {
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 600;
            color: var(--muted);
            text-decoration: none;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all 200ms ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .traveler-submenu-link:hover {
            color: #0f6cb6;
        }

        .traveler-submenu-link.is-active {
            color: #0f6cb6;
            border-bottom-color: #0f6cb6;
        }

        /* Alerts */
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

        .traveler-alert--success {
            border: 1px solid rgba(65, 175, 170, 0.35);
            background: rgba(65, 175, 170, 0.12);
            color: #175f5b;
        }

        .traveler-alert ul {
            margin: 0;
            padding-left: 18px;
            display: grid;
            gap: 4px;
        }

        /* Settings Grid */
        .traveler-settings-grid {
            margin-top: 20px;
            display: grid;
            gap: 28px;
        }

        .traveler-form-section {
            padding: 20px;
            background: #f9fafb;
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        .traveler-form-section--danger {
            background: #fff5f5;
            border-color: rgba(217, 83, 79, 0.35);
        }

        .traveler-form-section h3 {
            margin: 0 0 6px;
            font-size: 16px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .section-note {
            margin: 0 0 16px;
            font-size: 13px;
            color: var(--muted);
            line-height: 1.6;
        }

        .section-helper {
            margin: 0 0 12px;
            font-size: 13px;
            color: var(--muted);
            line-height: 1.6;
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

        .traveler-form-control {
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

        /* Toggle Switch */
        .traveler-form-group--toggle {
            display: block;
        }

        .toggle-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: var(--ink);
            cursor: pointer;
            user-select: none;
        }

        .toggle-checkbox {
            width: 48px;
            height: 24px;
            appearance: none;
            background: #e5e7eb;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            position: relative;
            transition: background 200ms ease;
            margin: 0;
            flex-shrink: 0;
        }

        .toggle-checkbox::before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            background: #fff;
            border-radius: 50%;
            top: 3px;
            left: 3px;
            transition: left 200ms ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .toggle-checkbox:checked {
            background: #0f6cb6;
        }

        .toggle-checkbox:checked::before {
            left: 27px;
        }

        /* Checkboxes */
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
            font-size: 14px;
            color: var(--ink);
        }

        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            margin: 0;
            accent-color: #0f6cb6;
        }

        .checkbox-label span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Info Row */
        .traveler-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            font-size: 14px;
            border-top: 1px solid var(--line);
            margin-top: 12px;
            padding-top: 12px;
        }

        .info-label {
            font-weight: 600;
            color: #374151;
        }

        .info-value {
            color: var(--muted);
        }

        /* Status Badge */
        .traveler-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 12px;
        }

        .traveler-status-badge--active {
            background: rgba(65, 175, 170, 0.1);
            color: #175f5b;
        }

        .traveler-status-badge--suspended {
            background: rgba(217, 83, 79, 0.1);
            color: #8b1c1a;
        }

        .traveler-status-badge i {
            width: 6px;
            height: 6px;
        }

        /* Buttons */
        .btn-primary,
        .btn-secondary,
        .btn-danger {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 200ms ease;
            text-decoration: none;
        }

        /* .btn-primary {
            background: #0f6cb6;
            color: white;
        }

        .btn-primary:hover {
            background: #0a5a9e;
        } */

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .traveler-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        @media (max-width: 860px) {
            .traveler-profile-card {
                padding: 10px;
            }

            .traveler-profile-head h1 {
                font-size: 28px;
            }

            .traveler-submenu {
                flex-wrap: wrap;
            }

            .checkbox-group {
                grid-template-columns: 1fr;
            }

            .traveler-info-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-value {
                margin-top: 4px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const twoFaToggle = document.getElementById('2fa_enabled');
            const twoFaMethodGroup = document.getElementById('2fa-method-group');
            const twoFaMethodSelect = document.getElementById('2fa_method');

            if (twoFaToggle) {
                function updateMethodVisibility() {
                    if (twoFaToggle.checked) {
                        twoFaMethodGroup.style.display = 'block';
                        twoFaMethodSelect.required = true;
                    } else {
                        twoFaMethodGroup.style.display = 'none';
                        twoFaMethodSelect.required = false;
                        twoFaMethodSelect.value = '';
                    }
                }

                twoFaToggle.addEventListener('change', updateMethodVisibility);
            }
        });
    </script>
@endpush
