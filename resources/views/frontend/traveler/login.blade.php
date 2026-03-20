@extends('frontend.layout')

@section('title', 'Traveller Login | Holidays.io')
@section('meta_description', 'Sign in to your traveller account on Holidays.io.')

@section('content')
    <section class="page-section traveler-auth-section">
        <div class="wrap">
            <div class="traveler-login-card">
                <div class="traveler-auth-head">
                    <h1>Traveller Login</h1>
                    <p>Access your customer profile and manage your bookings.</p>
                </div>

                @if(session('success'))
                    <div class="traveler-alert traveler-alert--success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="traveler-alert traveler-alert--error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('traveler.login.store') }}" class="traveler-login-form">
                    @csrf

                    <div class="traveler-form-group">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="traveler-form-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required>
                    </div>

                    <label class="traveler-check-row">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember me</span>
                    </label>

                    <button type="submit" class="btn-primary traveler-login-btn">Sign In</button>
                </form>

                <p class="traveler-inline-note">
                    New traveller? <a href="{{ route('traveler.register') }}">Create account</a>
                </p>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .traveler-auth-section {
            padding-top: 44px;
        }

        .traveler-login-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 22px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
            padding: 28px;
            max-width: 520px;
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

        .traveler-alert--success {
            border: 1px solid rgba(65, 175, 170, 0.35);
            background: rgba(65, 175, 170, 0.12);
            color: #175f5b;
        }

        .traveler-login-form {
            margin-top: 18px;
            display: grid;
            gap: 12px;
        }

        .traveler-form-group {
            display: grid;
            gap: 7px;
        }

        .traveler-form-group label {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
        }

        .traveler-form-group input {
            width: 100%;
            min-height: 44px;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 10px 12px;
            font: inherit;
            color: var(--ink);
            background: #fff;
        }

        .traveler-check-row {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #374151;
            line-height: 1.6;
        }

        .traveler-check-row input {
            width: 16px;
            height: 16px;
            min-height: 16px;
            accent-color: var(--brand);
        }

        .traveler-login-btn {
            border: 0;
            cursor: pointer;
            margin-top: 4px;
        }

        .traveler-inline-note {
            margin: 16px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .traveler-inline-note a {
            color: var(--brand-dark);
            font-weight: 700;
        }
    </style>
@endpush
