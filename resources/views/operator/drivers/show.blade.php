@extends('operator.layout')

@section('title', $driver->full_name . ' | Driver Details | Operator Dashboard')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>{{ $driver->full_name }}</h1>
            <p class="text-muted">Driver ID: {{ $driver->driver_id }}</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('operator.drivers.edit', $driver->id) }}" class="btn btn-warning">
                <i class="fa-solid fa-edit"></i> Edit
            </a>
            <a href="{{ route('operator.drivers.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (!empty($expiryWarnings))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h5>Document Expiry Warnings:</h5>
            <ul class="mb-0">
                @foreach ($expiryWarnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Status</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Status:</strong><br>
                        <span class="badge bg-{{ $driver->status === 'active' ? 'success' : ($driver->status === 'inactive' ? 'secondary' : 'danger') }}">
                            {{ ucfirst($driver->status) }}
                        </span>
                    </p>
                    <p class="mb-2">
                        <strong>Rating:</strong><br>
                        <span>{{ round($driver->average_rating, 1) }}/5 ({{ $driver->total_ratings }} ratings)</span>
                    </p>
                    <p class="mb-0">
                        <strong>Total Trips:</strong><br>
                        <span>{{ $driver->total_trips }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Name:</strong><br>
                            {{ $driver->driver_name ?? $driver->full_name ?? 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Driver ID:</strong><br>
                            {{ $driver->driver_id ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Email:</strong><br>
                            @if(!empty($driver->email))
                                <a href="mailto:{{ $driver->email }}">{{ $driver->email }}</a>
                            @else
                                N/A
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Phone:</strong><br>
                            @if(!empty($driver->phone))
                                <a href="tel:{{ $driver->phone }}">{{ $driver->phone }}</a>
                            @else
                                N/A
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Mobile:</strong><br>
                            @if(!empty($driver->driver_mobile_no) || !empty($driver->mobile))
                                <a href="tel:{{ $driver->driver_mobile_no ?? $driver->mobile }}">{{ $driver->driver_mobile_no ?? $driver->mobile }}</a>
                            @else
                                N/A
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Date of Birth:</strong><br>
                            @if(!empty($driver->date_of_birth))
                                @php $dob = \Carbon\Carbon::parse($driver->date_of_birth); @endphp
                                {{ $dob->format('M d, Y') }}
                            @else
                                N/A
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Address:</strong><br>
                        {{ $driver->address ?? 'N/A' }}<br>
                        {{ $driver->city ?? '' }}{{ $driver->city ? ',' : '' }} {{ $driver->state ?? '' }} {{ $driver->postal_code ?? '' }}<br>
                        {{ $driver->country ?? '' }}
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Operational Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong><br>
                            {{ $driver->driver_status ?? ucfirst($driver->status ?? 'N/A') }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Languages:</strong><br>
                            {{ $driver->languages ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <strong>Home Zone:</strong><br>
                            {{ $driver->home_zone ?? 'N/A' }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>Shift:</strong><br>
                            {{ $driver->shift_start_time ?? 'N/A' }} — {{ $driver->shift_end_time ?? 'N/A' }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>Break (min):</strong><br>
                            {{ $driver->driver_break_min ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Total Trips:</strong><br>
                            {{ $driver->total_trips ?? 0 }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Average Rating:</strong><br>
                            {{ number_format($driver->average_rating ?? 0, 1) }} / 5
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Remarks:</strong><br>
                        {{ $driver->remarks ?? 'N/A' }}
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Driver License</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>License Number:</strong><br>
                            {{ $driver->license_number }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>License Class:</strong><br>
                            {{ $driver->license_class ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Issue Date:</strong><br>
                            {{ $driver->license_issue_date ? $driver->license_issue_date->format('M d, Y') : 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Expiry Date:</strong><br>
                            @if ($driver->license_expiry)
                                @php $licExpiry = \Carbon\Carbon::parse($driver->license_expiry); @endphp
                                <span class="badge bg-{{ $licExpiry->diffInDays() < 30 ? 'warning' : 'success' }}">
                                    {{ $licExpiry->format('M d, Y') }}
                                </span>
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>License Status:</strong><br>
                        <span class="badge bg-{{ $driver->license_status === 'verified' ? 'success' : 'warning' }}">
                            {{ ucfirst($driver->license_status) }}
                        </span>
                        @if ($driver->license_document_path)
                            <br><a href="{{ asset('storage/' . $driver->license_document_path) }}" target="_blank" class="btn btn-sm btn-info mt-2">
                                <i class="fa-solid fa-file"></i> View Document
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Insurance Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Policy Number:</strong><br>
                            {{ $driver->insurance_policy_number ?? 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Provider:</strong><br>
                            {{ $driver->insurance_provider ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Expiry Date:</strong><br>
                        @if ($driver->insurance_expiry)
                            <span class="badge bg-{{ \Carbon\Carbon::parse($driver->insurance_expiry)->diffInDays() < 30 ? 'warning' : 'success' }}">
                                {{ $driver->insurance_expiry->format('M d, Y') }}
                            </span>
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="mb-3">
                        <strong>Insurance Status:</strong><br>
                        <span class="badge bg-{{ $driver->insurance_status === 'verified' ? 'success' : 'warning' }}">
                            {{ ucfirst($driver->insurance_status) }}
                        </span>
                        @if ($driver->insurance_document_path)
                            <br><a href="{{ asset('storage/' . $driver->insurance_document_path) }}" target="_blank" class="btn btn-sm btn-info mt-2">
                                <i class="fa-solid fa-file"></i> View Document
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Background Check</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Police Clearance Date:</strong><br>
                            {{ $driver->police_clearance_date ? $driver->police_clearance_date->format('M d, Y') : 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Expiry Date:</strong><br>
                            @if ($driver->police_clearance_expiry)
                                <span class="badge bg-{{ \Carbon\Carbon::parse($driver->police_clearance_expiry)->diffInDays() < 30 ? 'warning' : 'success' }}">
                                    {{ $driver->police_clearance_expiry->format('M d, Y') }}
                                </span>
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong><br>
                        <span class="badge bg-{{ $driver->police_clearance_status === 'verified' ? 'success' : 'warning' }}">
                            {{ ucfirst($driver->police_clearance_status) }}
                        </span>
                        @if ($driver->police_clearance_document_path)
                            <br><a href="{{ asset('storage/' . $driver->police_clearance_document_path) }}" target="_blank" class="btn btn-sm btn-info mt-2">
                                <i class="fa-solid fa-file"></i> View Document
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Bank Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Bank Name:</strong><br>
                            {{ $driver->bank_name ?? 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Beneficiary Name:</strong><br>
                            {{ $driver->beneficiary_name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Account Number:</strong><br>
                            {{ $driver->bank_account_number ? '****' . substr($driver->bank_account_number, -4) : 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Routing Number:</strong><br>
                            {{ $driver->bank_routing_number ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Emergency Contact</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Contact Name:</strong><br>
                            {{ $driver->emergency_contact_name ?? 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Relation:</strong><br>
                            {{ $driver->emergency_contact_relation ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Phone:</strong><br>
                        @if ($driver->emergency_contact_phone)
                            <a href="tel:{{ $driver->emergency_contact_phone }}">{{ $driver->emergency_contact_phone }}</a>
                        @else
                            N/A
                        @endif
                    </div>
                </div>
            </div>

            @if ($driver->notes)
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Notes</h5>
                    </div>
                    <div class="card-body">
                        {{ $driver->notes }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="mb-4">
                <a href="{{ route('operator.drivers.edit', $driver->id) }}" class="btn btn-warning btn-lg">
                    <i class="fa-solid fa-edit"></i> Edit Driver
                </a>
                <form action="{{ route('operator.drivers.destroy', $driver->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-lg" onclick="return confirm('Are you sure you want to delete this driver?')">
                        <i class="fa-solid fa-trash"></i> Delete Driver
                    </button>
                </form>
                <a href="{{ route('operator.drivers.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fa-solid fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .container {
        max-width: 1200px;
    }
    
    .card {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        border-radius: 0.375rem 0.375rem 0 0;
    }
    
    .badge {
        padding: 6px 12px;
        font-size: 12px;
    }
    
    h1 {
        color: #1f2937;
        margin-bottom: 8px;
    }
</style>
@endsection
