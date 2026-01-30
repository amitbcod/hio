@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Step 3: Legal Compliance</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ url('operator/register/step3-legal') }}">
        @csrf
        <div class="form-group">
            <label for="business_license_number">Business License Number</label>
            <input type="text" name="business_license_number" class="form-control" required value="{{ old('business_license_number', $legal->business_license_number ?? '') }}">
        </div>
        <div class="form-group">
            <label for="license_type">License Type</label>
            <select name="license_type" class="form-control" required>
                <option value="Accommodation" {{ (old('license_type', $legal->license_type ?? '') == 'Accommodation') ? 'selected' : '' }}>Accommodation</option>
                <option value="Tour Operator" {{ (old('license_type', $legal->license_type ?? '') == 'Tour Operator') ? 'selected' : '' }}>Tour Operator</option>
                <option value="Car Rental" {{ (old('license_type', $legal->license_type ?? '') == 'Car Rental') ? 'selected' : '' }}>Car Rental</option>
                <option value="Guide" {{ (old('license_type', $legal->license_type ?? '') == 'Guide') ? 'selected' : '' }}>Guide</option>
                <option value="Other" {{ (old('license_type', $legal->license_type ?? '') == 'Other') ? 'selected' : '' }}>Other</option>
            </select>
        </div>
        <!-- Add more fields as needed -->
        <button type="submit" class="btn btn-primary">Save & Next</button>
    </form>
</div>
@endsection
