@extends('frontend.layout')

@section('title', 'Traveller Profile | Holidays.io')
@section('meta_description', 'Manage your traveler profile on Holidays.io.')

@section('content')
    <section class="page-section traveler-profile-section">
        <div class="wrap">
            <div class="traveler-profile-card">
                <div class="traveler-profile-head">
                    <h1>Traveller Profile</h1>
                    <p>Complete and maintain your customer profile details.</p>
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

                <form method="POST" action="{{ route('traveler.profile.update') }}" class="traveler-profile-grid">
                    @csrf

                    <div class="traveler-form-group">
                        <label>Account ID</label>
                        <input type="text" value="{{ $account->id }}" readonly>
                    </div>

                    <div class="traveler-form-group">
                        <label>PrivilegeTraveller ID</label>
                        <input type="text" value="{{ $account->traveler_id }}" readonly>
                    </div>

                    <div class="traveler-form-group">
                        <label for="gender">Gender / Title</label>
                        <select id="gender" name="gender">
                            <option value="">Select</option>
                            @foreach($titleOptions as $title)
                                <option value="{{ $title }}" {{ old('gender', $profile->gender) === $title ? 'selected' : '' }}>{{ $title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="traveler-form-group">
                        <label for="first_name">First Name *</label>
                        <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $profile->first_name) }}" required>
                    </div>

                    <div class="traveler-form-group">
                        <label for="middle_name">Middle Name</label>
                        <input id="middle_name" type="text" name="middle_name" value="{{ old('middle_name', $profile->middle_name) }}">
                    </div>

                    <div class="traveler-form-group">
                        <label for="last_name">Last Name *</label>
                        <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $profile->last_name) }}" required>
                    </div>

                    <div class="traveler-form-group">
                        <label for="date_of_birth">Date of Birth *</label>
                        <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($profile->date_of_birth)->format('Y-m-d')) }}" required>
                        <small>Traveller must be 18+ years.</small>
                    </div>

                    <div class="traveler-form-group">
                        <label for="country">Country *</label>
                        <select id="country" name="country" required>
                            <option value="">Select country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}" {{ old('country', $profile->country ?: $account->country) === $country ? 'selected' : '' }}>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="traveler-form-group">
                        <label for="nationality">Nationality *</label>
                        <select id="nationality" name="nationality" required>
                            <option value="">Select nationality</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}" {{ old('nationality', $profile->nationality) === $country ? 'selected' : '' }}>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="traveler-form-group traveler-form-group--full">
                        <label for="address_line_1">Address Line 1 *</label>
                        <input id="address_line_1" type="text" name="address_line_1" value="{{ old('address_line_1', $profile->address_line_1) }}" required>
                    </div>

                    <div class="traveler-form-group traveler-form-group--full">
                        <label for="address_line_2">Address Line 2</label>
                        <input id="address_line_2" type="text" name="address_line_2" value="{{ old('address_line_2', $profile->address_line_2) }}">
                    </div>

                    <div class="traveler-form-group traveler-form-group--full">
                        <label for="city_region">City / Region</label>
                        <input id="city_region" type="text" name="city_region" value="{{ old('city_region', $profile->city_region) }}">
                    </div>

                    <div class="traveler-form-group">
                        <label for="emergency_contact_name">Emergency Contact Name</label>
                        <input id="emergency_contact_name" type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $profile->emergency_contact_name) }}">
                    </div>

                    <div class="traveler-form-group">
                        <label for="emergency_contact_phone">Emergency Contact Phone</label>
                        <input id="emergency_contact_phone" type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $profile->emergency_contact_phone) }}" placeholder="+23052511153">
                    </div>

                    <div class="traveler-form-group traveler-form-group--full">
                        <label for="special_notes">Special Notes</label>
                        <textarea id="special_notes" name="special_notes" rows="4">{{ old('special_notes', $profile->special_notes) }}</textarea>
                    </div>

                    <div class="traveler-form-group">
                        <label for="preferred_language">Preferred Language *</label>
                        <select id="preferred_language" name="preferred_language" required>
                            @foreach($preferredLanguages as $code => $label)
                                <option value="{{ $code }}" {{ old('preferred_language', $profile->preferred_language) === $code ? 'selected' : '' }}>{{ $label }} ({{ $code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="traveler-form-group">
                        <label>Verification Status</label>
                        <input type="text" value="{{ $account->verification_status }}" readonly>
                    </div>

                    <div class="traveler-form-group traveler-form-group--full traveler-form-actions">
                        <button type="submit" class="btn-primary">Save Profile</button>
                    </div>
                </form>
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
            border-radius: 22px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
            padding: 28px;
        }

        .traveler-profile-head h1 {
            margin: 0;
            font-size: 34px;
            font-family: 'Roboto Slab', Georgia, serif;
        }

        .traveler-profile-head p {
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

        .traveler-profile-grid {
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

        .traveler-form-group textarea {
            min-height: 110px;
        }

        .traveler-form-group small {
            color: var(--muted);
            font-size: 12px;
        }

        .traveler-form-actions {
            margin-top: 4px;
            display: flex;
            justify-content: flex-end;
        }

        .traveler-form-actions .btn-primary {
            border: 0;
            cursor: pointer;
        }

        @media (max-width: 860px) {
            .traveler-profile-grid {
                grid-template-columns: 1fr;
            }

            .traveler-profile-card {
                padding: 22px;
            }

            .traveler-profile-head h1 {
                font-size: 28px;
            }
        }
    </style>
@endpush
