@extends('layouts.app')
@section('content')
<style>
    body {
        background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
        min-height: 100vh;
    }
    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
    }
    .login-card {
        display: flex;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        overflow: hidden;
        max-width: 800px;
        width: 100%;
    }
    .login-form-section {
        flex: 1;
        padding: 40px 32px;
    }
    .login-brand-section {
        flex: 1;
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 40px 32px;
    }
    .login-form-section h2 {
        font-weight: bold;
        margin-bottom: 24px;
    }
    .form-group label {
        font-weight: 500;
    }
    .btn-primary {
        background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
        border: none;
    }
    .show-password {
        cursor: pointer;
        position: absolute;
        right: 16px;
        top: 38px;
        color: #888;
    }
</style>
<div class="login-container">
    <div class="login-card">
        <div class="login-form-section">
            <h2>Login to Your Account</h2>
            <form method="POST" action="{{ route('operator.login') }}">
                @csrf
                {{-- Carry accept_token through the login form if present (owner redirected from public verify page) --}}
                @if(request('accept_token') || session('accept_token'))
                    <input type="hidden" name="accept_token" value="{{ request('accept_token') ?? session('accept_token') }}">
                @endif
                <div class="form-group mb-3">
                    <label for="email">Email Address *</label>
                    <input type="email" name="email" class="form-control" required autofocus value="{{ old('email') }}">
                </div>
                <div class="form-group mb-3 position-relative">
                    <label for="password">Password *</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                    <span class="show-password" onclick="togglePassword()">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </span>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me for 30 days</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-2">&#xf090; Sign In</button>
                @if($errors->any())
                    <div class="alert alert-danger mt-2">{{ $errors->first() }}</div>
                @endif
                <div class="d-flex justify-content-between mt-2">
                    <a href="#">Forgot your password?</a>
                </div>
            </form>
            <hr>
            <div class="text-center">
                Don't have an account? <a href="{{ route('operator.register') }}">Sign up here</a>
            </div>
        </div>
        <div class="login-brand-section">
            <h2>Holidays.io</h2>
            <h5 class="mb-3">Operator Management System</h5>
            <ul class="list-unstyled">
                <li>Real-time business analytics</li>
                <li>Secure account management</li>
                <li>24/7 customer support</li>
                <li>Mobile-friendly interface</li>
                <li>Instant updates & notifications</li>
            </ul>
        </div>
    </div>
</div>
<script>
function togglePassword() {
    var x = document.getElementById("password");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}
</script>
@endsection
