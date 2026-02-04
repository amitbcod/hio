@extends('layouts.app')
@section('content')
<style>
    body {
        background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
        min-height: 100vh;
    }
    .register-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
    }
    .register-card {
        display: flex;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        overflow: hidden;
        max-width: 1000px;
        width: 100%;
    }
    .register-form-section {
        flex: 1.2;
        padding: 40px 32px;
    }
    .register-brand-section {
        flex: 1;
        background: #fff;
        color: #222;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 40px 32px;
    }
    .register-form-section h2 {
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
    .password-req {
        font-size: 0.9em;
        color: #888;
        margin-bottom: 8px;
    }
</style>
<div class="register-container">
    <div class="register-card">
        <div class="register-form-section">
            <h2>Create Account</h2>
            <form method="POST" action="{{ route('operator.register') }}">
                @csrf
                <div class="form-group mb-3">
                    <label>Account Type *</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="user_type" value="Operator" {{ old('user_type', 'Operator') == 'Operator' ? 'checked' : '' }} required>
                        <label class="form-check-label">Operator</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="user_type" value="MPO" {{ old('user_type') == 'MPO' ? 'checked' : '' }}>
                        <label class="form-check-label">MPO</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="user_type" value="Agent" {{ old('user_type') == 'Agent' ? 'checked' : '' }}>
                        <label class="form-check-label">Agent</label>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label>Business Legal Name *</label>
                    <input type="text" name="business_legal_name" class="form-control" required value="{{ old('business_legal_name') }}">
                </div>
                <div class="form-group mb-3">
                    <label>Country of Operation *</label>
                    <select name="country_of_operation" class="form-control" required>
                        <option value="">-- Select Country --</option>
                        <option value="Mauritius" {{ old('country_of_operation') == 'Mauritius' ? 'selected' : '' }}>Mauritius</option>
                        <option value="India" {{ old('country_of_operation') == 'India' ? 'selected' : '' }}>India</option>
                        <option value="Other" {{ old('country_of_operation') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label>Are you the owner of this business? *</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_owner" value="yes" {{ old('is_owner', 'yes') == 'yes' ? 'checked' : '' }} required onclick="toggleOwnerFields()">
                        <label class="form-check-label">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_owner" value="no" {{ old('is_owner') == 'no' ? 'checked' : '' }} onclick="toggleOwnerFields()">
                        <label class="form-check-label">No</label>
                    </div>
                    <small class="text-muted d-block mt-1">Selecting "Yes" will create a business record linked to your account. If "No", we will attempt to link you to an existing business (by name/country) or create a pending business record to be verified by the owner.</small>
                </div>

                <div id="agreement-owner-field" class="form-group mb-3" style="display: {{ old('is_owner', 'yes') == 'yes' ? 'block' : 'none' }};">
                    <label>Agreement Type *</label>
                    <select name="agreement_type" class="form-control" {{ old('is_owner', 'yes') == 'yes' ? 'required' : '' }}>
                        <option value="">-- Select Agreement Type --</option>
                        <option value="Listing Only" {{ old('agreement_type') == 'Listing Only' ? 'selected' : '' }}>Listing Only</option>
                        <option value="OTO" {{ old('agreement_type') == 'OTO' ? 'selected' : '' }}>OTO</option>
                        <option value="Widget Only" {{ old('agreement_type') == 'Widget Only' ? 'selected' : '' }}>Widget Only</option>
                        <option value="OTO + Widget" {{ old('agreement_type') == 'OTO + Widget' ? 'selected' : '' }}>OTO + Widget</option>
                        <option value="Full Service" {{ old('agreement_type') == 'Full Service' ? 'selected' : '' }}>Full Service</option>
                    </select>
                </div>
                <h5 class="mt-4">Your Information</h5>
                <div class="form-group mb-3">
                    <label>User ID / Email Address *</label>
                    <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                </div>
                <div class="form-group mb-3">
                    <label>Mobile Number / WhatsApp *</label>
                    <input type="text" name="phone" class="form-control" required placeholder="+230 5xxxxxxx" value="{{ old('phone') }}">
                    <small class="text-muted">E164 format (e.g., +230 57011234)</small>
                </div>
                <div class="form-group mb-3">
                    <label>Your Full Name *</label>
                    <input type="text" name="full_name" class="form-control" required value="{{ old('full_name') }}">
                </div>
                <div class="form-group mb-3">
                    <label>User Role *</label>
                    <select name="role" class="form-control" required>
                        <option value="">-- Select Role --</option>
                        <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                        <option value="Head of Department" {{ old('role') == 'Head of Department' ? 'selected' : '' }}>Head of Department</option>
                        <option value="Reservation Manager" {{ old('role') == 'Reservation Manager' ? 'selected' : '' }}>Reservation Manager</option>
                        <option value="Operational Manager" {{ old('role') == 'Operational Manager' ? 'selected' : '' }}>Operational Manager</option>
                        <option value="Finance Manager" {{ old('role') == 'Finance Manager' ? 'selected' : '' }}>Finance Manager</option>
                        <option value="Marketing Manager" {{ old('role') == 'Marketing Manager' ? 'selected' : '' }}>Marketing Manager</option>
                        <option value="Support Manager" {{ old('role') == 'Support Manager' ? 'selected' : '' }}>Support Manager</option>
                        <option value="Content Manager" {{ old('role') == 'Content Manager' ? 'selected' : '' }}>Content Manager</option>
                    </select>
                </div>
                <div id="owner-fields" style="display: {{ old('is_owner', 'yes') == 'no' ? 'block' : 'none' }};">
                    <h5 class="mt-4">Owner Information</h5>
                    <div class="form-group mb-3">
                        <label>Owner's Full Name *</label>
                        <input type="text" name="owner_full_name" class="form-control" value="{{ old('owner_full_name') }}" {{ old('is_owner', 'yes') == 'no' ? 'required' : '' }}>
                    </div>
                    <div class="form-group mb-3">
                        <label>Owner's Email Address *</label>
                        <input type="email" name="owner_email" class="form-control" value="{{ old('owner_email') }}" {{ old('is_owner', 'yes') == 'no' ? 'required' : '' }}>
                    </div>
                    <div class="form-group mb-3">
                        <label>Owner's Mobile Number *</label>
                        <input type="text" name="owner_phone" class="form-control" value="{{ old('owner_phone') }}" {{ old('is_owner', 'yes') == 'no' ? 'required' : '' }}>
                        <small class="text-muted">E164 format (e.g., +230 57011234)</small>
                    </div>
                </div>
                <div class="form-group mb-3 position-relative">
                    <label>Set Password *</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                    <span class="show-password" onclick="togglePassword()">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </span>
                    <div class="password-req mt-1">
                        Requirement:<br>
                        • At least 8 characters<br>
                        • One uppercase letter (A-Z)<br>
                        • One lowercase letter (a-z)<br>
                        • One number (0-9)<br>
                        • One special character (@#%!*^$&)
                    </div>
                </div>
                <div class="form-group mb-3 position-relative">
                    <label>Confirm Password *</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="terms" class="form-check-input" id="terms" required {{ old('terms') ? 'checked' : '' }}>
                    <label class="form-check-label" for="terms">I agree to the <a href="#" target="_blank">terms and conditions</a> *</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-2">Create Account</button>
                @if($errors->any())
                    <div class="alert alert-danger mt-2">{{ $errors->first() }}</div>
                @endif
            </form>
            <div class="text-center mt-2">
                Already have an account? <a href="{{ route('operator.login') }}">Sign in here</a>
            </div>
        </div>
        <div class="register-brand-section">
            <h2>Welcome to Holidays.io</h2>
            <p>Sign Up to Access your Account</p>
            <h5>Why Register?</h5>
            <ul class="list-unstyled">
                <li>✔ Manage your business profile</li>
                <li>✔ Upload legal documents</li>
                <li>✔ Configure payment settings</li>
                <li>✔ Manage staff users</li>
                <li>✔ Monitor compliance status</li>
                <li>✔ View payout history</li>
            </ul>
        </div>
    </div>
</div>
<script>
function toggleOwnerFields() {
    var isOwner = document.querySelector('input[name="is_owner"]:checked').value;
    document.getElementById('owner-fields').style.display = (isOwner === 'no') ? 'block' : 'none';
    var agreementEl = document.getElementById('agreement-owner-field');
    if (agreementEl) {
        agreementEl.style.display = (isOwner === 'yes') ? 'block' : 'none';
    }
}
document.addEventListener('DOMContentLoaded', function() {
    toggleOwnerFields();
});
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
