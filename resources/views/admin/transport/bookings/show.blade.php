@extends('layouts.admin')

@section('content')
@php
    $transport = $booking->transport;
    $operator = $transport?->operator;
    $operatorName = $operator?->profile?->business_legal_name
        ?: ($operator?->business_name ?: ($operator?->email ?: 'N/A'));
    $routeLabel = trim((string) ($booking->route_from ?? '')) !== '' && trim((string) ($booking->route_to ?? '')) !== ''
        ? trim((string) $booking->route_from) . ' → ' . trim((string) $booking->route_to)
        : null;
@endphp

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Transport Booking Details</h3>
            <div class="text-muted">Booking Reference: {{ $booking->booking_reference ?: 'Transport booking #' . $booking->id }}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.transport.bookings') }}" class="btn btn-outline-secondary btn-sm">← Back to Bookings</a>
            <span class="badge rounded-pill text-bg-info px-3 py-2">{{ $booking->booking_status ?: \App\Models\TransportBooking::STATUS_PROCESSING }}</span>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title mb-3">Booking Information</h5>
            <div class="row g-3">
                <div class="col-md-3"><strong>Booking Reference:</strong><br>{{ $booking->booking_reference ?: 'N/A' }}</div>
                <div class="col-md-3"><strong>Booking Date:</strong><br>{{ optional($booking->created_at)->format('M d, Y H:i') }}</div>
                <div class="col-md-3"><strong>Source Channel:</strong><br>{{ $booking->source_channel ?? 'Direct' }}</div>
                <div class="col-md-3"><strong>Status:</strong><br><span class="badge text-bg-info">{{ $booking->booking_status ?: \App\Models\TransportBooking::STATUS_PROCESSING }}</span></div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title mb-3">Guest Information</h5>
            <div class="row g-3">
                <div class="col-md-6"><strong>Primary Guest:</strong><br>{{ $booking->guest_name ?? 'N/A' }}</div>
                <div class="col-md-6"><strong>Email:</strong><br>{{ $booking->guest_email ?? 'Not provided' }}</div>
            </div>
            @if($booking->traveler_first_name || $booking->traveler_last_name)
                <hr>
                <div class="row g-3">
                    <div class="col-md-4"><strong>Traveler Name:</strong><br>{{ trim(($booking->traveler_first_name ?? '') . ' ' . ($booking->traveler_middle_name ?? '') . ' ' . ($booking->traveler_last_name ?? '')) }}</div>
                    <div class="col-md-4"><strong>Relation:</strong><br>{{ ucfirst($booking->traveler_relation ?? 'self') }}</div>
                    <div class="col-md-4"><strong>Nationality:</strong><br>{{ $booking->traveler_nationality ?? 'Not specified' }}</div>
                </div>
                @if($booking->traveler_dob)
                    <div class="row g-3 mt-1">
                        <div class="col-md-6"><strong>Date of Birth:</strong><br>{{ $booking->traveler_dob->format('M d, Y') }}</div>
                        <div class="col-md-6"><strong>Gender:</strong><br>{{ ucfirst($booking->traveler_gender ?? 'not specified') }}</div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title mb-3">Service & Vehicle Details</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <strong>Operator:</strong><br>{{ $operatorName }}
                    @if($transport)
                        <br><small class="text-muted">{{ $transport->vehicle_display_name ?? 'N/A' }} • {{ $transport->vehicle_type ?? 'N/A' }}</small>
                    @endif
                </div>
                <div class="col-md-6">
                    <strong>Assigned Vehicle:</strong><br>
                    {{ $booking->vehicle?->license_number ?: ($booking->other_vehicle_license_number ? 'Other: ' . $booking->other_vehicle_license_number : 'Unassigned') }}
                    @if($booking->vehicle)
                        <br><small class="text-muted">{{ $booking->vehicle->registration_number ?? 'N/A' }}</small>
                    @elseif($booking->other_vehicle_name)
                        <br><small class="text-muted">Vehicle name: {{ $booking->other_vehicle_name }}</small>
                    @endif
                </div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-md-4"><strong>Pickup Driver:</strong><br>{{ $booking->pickupDriver?->driver_name ?: 'Unassigned' }}</div>
                <div class="col-md-4"><strong>Return Driver:</strong><br>{{ $booking->returnDriver?->driver_name ?: 'Unassigned' }}</div>
                <div class="col-md-4"><strong>Vehicle Type:</strong><br>{{ $transport?->vehicle_type ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title mb-3">Trip Details</h5>
            <div class="row g-3">
                @if($routeLabel)
                    <div class="col-md-8"><strong>Route:</strong><br>{{ $routeLabel }}</div>
                    <div class="col-md-4"><strong>Passengers:</strong><br>{{ $booking->total_passengers ?? ((int) ($booking->adults ?? 0) + (int) ($booking->children ?? 0)) }}</div>
                @else
                    <div class="col-md-4"><strong>Pickup:</strong><br>{{ $booking->route_from ?? 'N/A' }}</div>
                    <div class="col-md-4"><strong>Destination:</strong><br>{{ $booking->route_to ?? 'N/A' }}</div>
                    <div class="col-md-4"><strong>Passengers:</strong><br>{{ $booking->total_passengers ?? ((int) ($booking->adults ?? 0) + (int) ($booking->children ?? 0)) }}</div>
                @endif
            </div>
            <div class="row g-3 mt-1">
                <div class="col-md-4"><strong>Pickup:</strong><br>{{ optional($booking->pickup_date)->format('M d, Y') ?? 'N/A' }} {{ $booking->pickup_time ?? '' }}</div>
                <div class="col-md-4"><strong>Return:</strong><br>{{ optional($booking->return_date)->format('M d, Y') ?? 'N/A' }} {{ $booking->return_time ?? '' }}</div>
                <div class="col-md-4"><strong>Pickup / Drop-off:</strong><br>{{ $booking->pickup_address ?: 'N/A' }} / {{ $booking->dropoff_address ?: 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title mb-3">Financial Information</h5>
            <div class="row g-3">
                <div class="col-md-6"><strong>Total Amount:</strong><br>{{ $booking->currency ?? 'USD' }} {{ number_format((float) ($booking->total_amount ?? 0), 2) }}</div>
                <div class="col-md-6"><strong>Payment Method:</strong><br>{{ $booking->payment_method ?: 'N/A' }}</div>
            </div>
        </div>
    </div>

    @if($booking->guests && $booking->guests->count() > 0)
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title mb-3">Guest List</h5>
                @foreach($booking->guests as $guest)
                    <div class="border rounded p-3 mb-2">
                        <div class="row g-3">
                            <div class="col-md-4"><strong>{{ $guest->first_name }} {{ $guest->middle_name }} {{ $guest->last_name }}</strong><br><small class="text-muted">{{ ucfirst($guest->relation ?? 'guest') }}</small></div>
                            <div class="col-md-4"><strong>Nationality:</strong> {{ $guest->nationality ?? 'Not specified' }}@if($guest->dob)<br><small class="text-muted">DOB: {{ $guest->dob->format('M d, Y') }}</small>@endif</div>
                            <div class="col-md-4"><strong>Passport:</strong> {{ $guest->passport_number ?? 'Not provided' }}@if($guest->gender)<br><small class="text-muted">Gender: {{ ucfirst($guest->gender) }}</small>@endif</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

        @if(in_array($booking->booking_status, [\App\Models\TransportBooking::STATUS_CONFIRMED, \App\Models\TransportBooking::STATUS_SCHEDULED], true))
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                    <h5 class="card-title mb-3">Current Assignment</h5>
                    <form method="POST" action="{{ route('admin.transport.booking.assign-drivers', $booking->id) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Vehicle</strong></label>
                                <select id="adminVehicleSelect" name="vehicle_id" class="form-select">
                                    <option value="">Select vehicle</option>
                                    @foreach($assignmentVehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" @selected((int) $booking->transport_vehicle_id === (int) $vehicle->id)>{{ $vehicle->license_number }} / {{ $vehicle->registration_number }}</option>
                                    @endforeach
                                    <option value="other" @selected(!$booking->transport_vehicle_id && $booking->other_vehicle_license_number)>Other vehicle</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Driver</strong></label>
                                <select name="pickup_driver_id" class="form-select">
                                    <option value="">Select driver</option>
                                    @foreach($assignmentDrivers as $driver)
                                        <option value="{{ $driver->id }}" @selected((int) $booking->pickup_driver_id === (int) $driver->id)>{{ $driver->driver_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div id="adminOtherVehicleFields" class="row g-3 mt-1" style="display: {{ (!$booking->transport_vehicle_id && $booking->other_vehicle_license_number) || old('vehicle_id') === 'other' ? 'flex' : 'none' }};">
                            <div class="col-md-6">
                                <label class="form-label">Other vehicle name</label>
                                <input name="other_vehicle_name" class="form-control" value="{{ $booking->other_vehicle_name }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Other vehicle license number</label>
                                <input name="other_vehicle_license_number" class="form-control" value="{{ $booking->other_vehicle_license_number }}">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Reason for change</label>
                            <input name="reason" class="form-control" placeholder="Vehicle breakdown, driver emergency, operational change...">
                        </div>
                        <button type="submit" class="btn btn-info mt-3">Change Assignment</button>
                    </form>
                </div>
            </div>
        @endif

        @if($booking->assignments && $booking->assignments->isNotEmpty())
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">Assignment History</h5>
                    @foreach($booking->assignments->sortByDesc('assigned_at') as $assignment)
                        <div class="border-bottom py-2">
                            <strong>{{ $assignment->vehicle?->license_number ?: ($assignment->other_vehicle_license_number ? 'Other: ' . $assignment->other_vehicle_license_number : 'No vehicle') }}</strong>
                            <span> | {{ $assignment->driver?->driver_name ?: 'No driver' }}</span>
                            <span class="badge text-bg-secondary ms-2">{{ $assignment->status }}</span>
                            <div class="text-muted small mt-1">{{ optional($assignment->assigned_at)->format('d M Y H:i') }}@if($assignment->reason) | {{ $assignment->reason }}@endif @if($assignment->assignedBy) | {{ $assignment->assignedBy->name ?? $assignment->assignedBy->email ?? 'Operator' }}@endif</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-4 text-center">
            @if($booking->booking_status === \App\Models\TransportBooking::STATUS_PROCESSING)
                <form method="POST" action="{{ route('admin.transport.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Confirm this booking?');">
                    @csrf
                    <input type="hidden" name="booking_status" value="Confirmed">
                    <button type="submit" class="btn btn-success">✓ Confirm Booking</button>
                </form>
            @endif

            @if(in_array($booking->booking_status, [\App\Models\TransportBooking::STATUS_PROCESSING, \App\Models\TransportBooking::STATUS_CONFIRMED], true))
                <form method="POST" action="{{ route('admin.transport.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Cancel this booking?');">
                    @csrf
                    <input type="hidden" name="booking_status" value="Cancelled">
                    <button type="submit" class="btn btn-danger">✕ Cancel Booking</button>
                </form>
            @endif

            @if($booking->booking_status === \App\Models\TransportBooking::STATUS_SCHEDULED)
                <form method="POST" action="{{ route('admin.transport.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Mark this booking as completed?');">
                    @csrf
                    <input type="hidden" name="booking_status" value="{{ \App\Models\TransportBooking::STATUS_COMPLETED }}">
                    <button type="submit" class="btn btn-info">Mark Completed</button>
                </form>
            @endif
        </div>
    </div>
    <script>
        const adminVehicleSelect = document.getElementById('adminVehicleSelect');
        const adminOtherVehicleFields = document.getElementById('adminOtherVehicleFields');

        function toggleAdminOtherVehicleFields() {
            const isOtherVehicle = adminVehicleSelect && adminVehicleSelect.value === 'other';
            if (adminOtherVehicleFields) {
                adminOtherVehicleFields.style.display = isOtherVehicle ? 'flex' : 'none';
            }
        }

        adminVehicleSelect?.addEventListener('change', toggleAdminOtherVehicleFields);
        toggleAdminOtherVehicleFields();
    </script>
    @endsection
