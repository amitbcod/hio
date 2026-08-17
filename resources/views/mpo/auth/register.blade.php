@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="mb-4">MPO Registration</h1>

                    <form method="POST" action="{{ route('mpo.register.post') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label d-block">Are you the business owner?</label>
                            <div class="d-flex gap-3">
                                <label class="form-check-label">
                                    <input type="radio" name="is_owner" value="yes" checked> Yes, I am the owner
                                </label>
                                <label class="form-check-label">
                                    <input type="radio" name="is_owner" value="no"> No, I am registering on behalf of the owner
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Business Legal Name</label>
                            <input name="business_legal_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Country of Operation</label>
                            <input name="country_of_operation" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Your Full Name</label>
                            <input name="full_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Your Email</label>
                            <input name="email" type="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Your Phone</label>
                            <input name="phone" class="form-control" required>
                        </div>

                        <div class="mb-3 owner-extra-field" style="display:none;">
                            <label class="form-label">Owner Full Name</label>
                            <input name="owner_full_name" class="form-control">
                        </div>

                        <div class="mb-3 owner-extra-field" style="display:none;">
                            <label class="form-label">Owner Email</label>
                            <input name="owner_email" type="email" class="form-control">
                        </div>

                        <div class="mb-3 owner-extra-field" style="display:none;">
                            <label class="form-label">Owner Phone</label>
                            <input name="owner_phone" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input name="password" type="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input name="password_confirmation" type="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-check-label">
                                <input type="checkbox" name="terms" value="1"> Accept terms
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ownerFields = document.querySelectorAll('.owner-extra-field');
        const ownerRadios = document.querySelectorAll('input[name="is_owner"]');

        function toggleOwnerFields() {
            const isOwner = document.querySelector('input[name="is_owner"]:checked')?.value === 'yes';
            ownerFields.forEach(function (field) {
                field.style.display = isOwner ? 'none' : 'block';
                const input = field.querySelector('input');
                if (input) {
                    input.required = !isOwner;
                }
            });
        }

        ownerRadios.forEach(function (radio) {
            radio.addEventListener('change', toggleOwnerFields);
        });

        toggleOwnerFields();
    });
</script>
@endpush
@endsection
