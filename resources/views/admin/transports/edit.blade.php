@extends('layouts.admin')

@section('title', 'Edit Transport Listing')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Transport Listing</h1>
            <p class="text-muted mb-0">{{ $transport->service_id ?: 'Service ID: '.$transport->id }}</p>
        </div>
        <a href="{{ route($showRoute, $transport) }}" class="btn btn-outline-secondary">Cancel</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route($updateRoute, $transport) }}">
        @csrf
        @method('PUT')
        <div class="card mb-3">
            <div class="card-header">Listing Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="vehicle-name" class="form-label">Vehicle Name</label>
                    <select id="vehicle-name" name="vehicle_name_id" class="form-select" required>
                        <option value="">Select a vehicle name</option>
                        @foreach($vehicleNames as $vehicleName)
                            <option value="{{ $vehicleName->id }}" @selected((string) old('vehicle_name_id', $transport->vehicle_name_id) === (string) $vehicleName->id)>
                                {{ $vehicleName->name }} ({{ $vehicleName->vehicleType?->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="registration-number" class="form-label">Registration Number</label>
                    <input id="registration-number" name="registration_number" class="form-control" maxlength="50" value="{{ old('registration_number', $transport->registration_number) }}">
                </div>
                <div class="mb-3">
                    <label for="service-description" class="form-label">Service Description</label>
                    <textarea id="service-description" name="service_description" class="form-control" rows="5">{{ old('service_description', $transport->service_description) }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="overview" class="form-label">Overview</label>
                    <textarea id="overview" name="overview" class="form-control" rows="5">{{ old('overview', $transport->overview) }}</textarea>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">Contact Details</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="contact-person" class="form-label">Contact Person</label>
                        <input id="contact-person" name="contact_person" class="form-control" maxlength="100" value="{{ old('contact_person', $transport->contact_person) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="contact-phone" class="form-label">Contact Phone</label>
                        <input id="contact-phone" name="contact_phone" class="form-control" maxlength="25" value="{{ old('contact_phone', $transport->contact_phone) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="contact-email" class="form-label">Contact Email</label>
                        <input id="contact-email" name="contact_email" type="email" class="form-control" maxlength="191" value="{{ old('contact_email', $transport->contact_email) }}">
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>
@endsection
