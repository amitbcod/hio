@extends('layouts.app')

@section('title', 'Transport Booking Details | Operator')

@section('content')

<div class="container mt-0">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @include('operator.registration._sidebar_main')
        </div>
        <div class="col-md-9 my-pro">
            <div class="container-middle">

                {{-- Header --}}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                    <div>
                        <h2 style="font-weight: bold; margin-bottom: 8px;">Booking Details</h2>
                        <p style="color: #666; margin-bottom: 0;">Booking Reference: {{ $booking->booking_reference }} @if($booking->trip_type) | {{ ucfirst(strtolower($booking->trip_type)) }} trip @endif</p>
                    </div>
                    <div style="display: flex; gap: 12px; align-items:center;">
                        <a href="{{ route('operator.transport.bookings') }}" class="btn btn-outline-blue" style="">
                            ← Back to Bookings
                        </a>
                        <span class="badge" style="background: {{ match($booking->booking_status) { 'Confirmed' => '#28a745', 'Scheduled' => '#17a2b8', 'Processing' => '#ffc107', default => '#dc3545' } }}; color: #fff; font-size: 15px; padding: 8px 16px; line-height: 21px; font-weight: 500;">
                            {{ $booking->booking_status ?? \App\Models\TransportBooking::STATUS_PROCESSING }}
                        </span>
                    </div>
                </div>

                @if(session('success') || session('error'))
                    <div style="margin-bottom: 24px;">
                        @if(session('success'))
                            <div style="background:#e8f5e9;border:1px solid #66bb6a;color:#2e7d32;border-radius:8px;padding:16px;">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if(session('error'))
                            <div style="background:#ffebee;border:1px solid #ef5350;color:#c62828;border-radius:8px;padding:16px;">
                                {{ session('error') }}
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Booking Information --}}
                <div style="margin-bottom: 32px;">
                    <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">📋 Booking Information</h4>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Booking Reference:</strong><br>
                                {{ $booking->booking_reference }}
                            </div>
                            <div class="col-md-3">
                                <strong>Booking Date:</strong><br>
                                {{ optional($booking->created_at)->format('M d, Y H:i') }}
                            </div>
                            <div class="col-md-3">
                                <strong>Source Channel:</strong><br>
                                {{ $booking->source_channel ?? 'Direct' }}
                            </div>
                            <div class="col-md-3">
                                <strong>Status:</strong><br>
                                <span class="badge" style="background: {{ match($booking->booking_status) { 'Confirmed' => '#28a745', 'Scheduled' => '#17a2b8', 'Processing' => '#ffc107', default => '#dc3545' } }}; color:#fff;">{{ $booking->booking_status ?? \App\Models\TransportBooking::STATUS_PROCESSING }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Guest Information --}}
                <div style="margin-bottom: 32px;">
                    <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">👤 Guest Information</h4>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Primary Guest:</strong><br>
                                {{ $booking->guest_name }}
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong><br>
                                {{ $booking->guest_email ?? 'Not provided' }}
                            </div>
                        </div>
                        @if($booking->traveler_first_name || $booking->traveler_last_name)
                        <hr style="margin: 20px 0;">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Traveler Name:</strong><br>
                                {{ trim(($booking->traveler_first_name ?? '') . ' ' . ($booking->traveler_middle_name ?? '') . ' ' . ($booking->traveler_last_name ?? '')) }}
                            </div>
                            <div class="col-md-4">
                                <strong>Relation:</strong><br>
                                {{ ucfirst($booking->traveler_relation ?? 'self') }}
                            </div>
                            <div class="col-md-4">
                                <strong>Nationality:</strong><br>
                                {{ $booking->traveler_nationality ?? 'Not specified' }}
                            </div>
                        </div>
                        @if($booking->traveler_dob)
                        <div class="row" style="margin-top: 10px;">
                            <div class="col-md-6">
                                <strong>Date of Birth:</strong><br>
                                {{ $booking->traveler_dob->format('M d, Y') }}
                            </div>
                            <div class="col-md-6">
                                <strong>Gender:</strong><br>
                                {{ ucfirst($booking->traveler_gender ?? 'not specified') }}
                            </div>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>

                {{-- Service & Vehicle Details --}}
                @php
                    $hasAssignedVehicle = $booking->transport_vehicle_id || $booking->other_vehicle_license_number;
                    $hasAssignedDriver = $booking->pickup_driver_id || $booking->return_driver_id;
                    $hasExistingAssignment = $hasAssignedVehicle || $hasAssignedDriver;
                @endphp
                <div style="margin-bottom: 32px;">
                    <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">🚗 Service & Vehicle Details</h4>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Vehicle:</strong><br>
                                {{ optional($transport)->vehicle_name }}
                                <br><small style="color: #666;">{{ optional($transport)->vehicle_type }} • Seating: {{ optional($transport)->seating_capacity ?? 'N/A' }}</small>
                                @if($booking->vehicle)
                                    <br><small style="color: #666;">Assigned unit: {{ $booking->vehicle->license_number }} / {{ $booking->vehicle->registration_number }}</small>
                                @elseif($booking->other_vehicle_license_number)
                                    <br><small style="color: #666;">Assigned vehicle: Other</small>
                                    <br><small style="color: #666;">Vehicle name: {{ $booking->other_vehicle_name }}</small>
                                    <br><small style="color: #666;">License number: {{ $booking->other_vehicle_license_number }}</small>
                                @else
                                    <br><small style="color: #666;">Assigned vehicle: Unassigned <a href="#current-assignment" class="assignment-scroll-link">[Assign]</a></small>
                                @endif
                                <br><small style="color: #666;">Driver: {{ $booking->pickupDriver?->driver_name ?: 'Unassigned' }}@unless($hasAssignedDriver) <a href="#current-assignment" class="assignment-scroll-link">[Assign]</a>@endunless</small>
                            </div>
                            <div class="col-md-6">
                                <strong>Operator:</strong><br>
                                {{ optional($transport->operator)->business->name ?? optional($transport->operator)->name ?? 'Operator' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Trip Details --}}
                <div style="margin-bottom: 32px;">
                    <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">📅 Trip Details</h4>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                        <div class="row">
                            @if(!empty($packageRouteLabel))
                                <div class="col-md-8">
                                    <strong>Route:</strong><br>
                                    {{ $packageRouteLabel }}
                                    <br><small style="color:#666;">Pickup: {{ optional($booking->pickup_date)->format('M d, Y') }} {{ $booking->pickup_time }}@if($booking->return_date) • Return: {{ optional($booking->return_date)->format('M d, Y') }} {{ $booking->return_time ?? '' }}@endif</small>
                                </div>
                                <div class="col-md-4">
                                    <strong>Passengers:</strong><br>
                                    {{ $booking->total_passengers ?? $booking->adults }}
                                </div>
                            @else
                                <div class="col-md-4">
                                    <strong>Pickup:</strong><br>
                                    {{ $booking->route_from }}
                                    <br><small style="color:#666;">{{ optional($booking->pickup_date)->format('M d, Y') }} {{ $booking->pickup_time }}</small>
                                </div>
                                <div class="col-md-4">
                                    <strong>Destination:</strong><br>
                                    {{ $booking->route_to }}
                                    <br><small style="color:#666;">Return: {{ optional($booking->return_date)? optional($booking->return_date)->format('M d, Y') : '—' }} {{ $booking->return_time ?? '' }}</small>
                                </div>
                                <div class="col-md-4">
                                    <strong>Passengers:</strong><br>
                                    {{ $booking->total_passengers ?? $booking->adults }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Financial Information --}}
                <div style="margin-bottom: 32px;">
                    <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">💰 Financial Information</h4>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Total Amount:</strong><br>
                                @if($booking->total_amount)
                                    <span style="font-size: 18px; font-weight: bold; color: #19b5b5;">{{ $booking->currency ?? 'USD' }} {{ number_format($booking->total_amount, 2) }}</span>
                                @else
                                    <span style="color: #666;">Amount not available</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>Payment Status:</strong><br>
                                @php
                                    $bookingStatus = $booking->booking_status ?? \App\Models\TransportBooking::STATUS_PROCESSING;
                                    $statusColor = match($bookingStatus) { 'Confirmed' => '#28a745', 'Scheduled' => '#17a2b8', 'Processing' => '#ffc107', 'Cancelled' => '#dc3545', default => '#6c757d' };
                                @endphp
                                <span class="badge" style="background: {{ $statusColor }};">{{ $bookingStatus }}</span>
                                <br><small style="color: #666;">{{ $bookingStatus === 'Confirmed' ? 'Payment completed successfully' : ($bookingStatus === 'Cancelled' ? 'Booking has been cancelled' : 'Payment processing details not available') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Additional Information --}}
                @if($booking->traveler_notes)
                <div style="margin-bottom: 32px;">
                    <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">📝 Special Requests & Notes</h4>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">{{ $booking->traveler_notes }}</div>
                </div>
                @endif

                @if(in_array($booking->booking_status, [\App\Models\TransportBooking::STATUS_CONFIRMED, \App\Models\TransportBooking::STATUS_SCHEDULED], true))
                <div style="margin-bottom: 32px;">
                    <h4 id="current-assignment" style="font-weight: 600; margin-bottom: 20px; color: #333;">Current Assignment</h4>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                        <form id="detailsAssignmentForm" method="POST" action="{{ route('operator.transport.booking.assign-drivers', $booking->id) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="detailsVehicleSelect"><strong>Vehicle</strong></label>
                                    <select id="detailsVehicleSelect" name="vehicle_id" class="form-control">
                                        <option value="">Select vehicle</option>
                                        @foreach($assignmentVehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}" @selected((int) $booking->transport_vehicle_id === (int) $vehicle->id)>{{ $vehicle->license_number }} / {{ $vehicle->registration_number }}</option>
                                        @endforeach
                                        <option value="other" @selected(!$booking->transport_vehicle_id && $booking->other_vehicle_license_number)>Other vehicle</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="detailsDriverSelect"><strong>Driver</strong></label>
                                    <select id="detailsDriverSelect" name="pickup_driver_id" class="form-control">
                                        <option value="">Select driver</option>
                                        @foreach($assignmentDrivers as $driver)
                                            <option value="{{ $driver->id }}" @selected((int) $booking->pickup_driver_id === (int) $driver->id)>{{ $driver->driver_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label>Other vehicle name</label>
                                    <input name="other_vehicle_name" class="form-control" value="{{ $booking->other_vehicle_name }}">
                                </div>
                                <div class="col-md-6">
                                    <label>Other vehicle license number</label>
                                    <input name="other_vehicle_license_number" class="form-control" value="{{ $booking->other_vehicle_license_number }}">
                                </div>
                            </div>
                            <div id="detailsAssignmentReason" class="mt-3" style="display: {{ $hasExistingAssignment ? 'block' : 'none' }};">
                                <label>Reason for change</label>
                                <input name="reason" class="form-control" placeholder="Vehicle breakdown, driver emergency, operational change...">
                            </div>
                            <button type="submit" class="btn btn-info mt-3">Change Assignment</button>
                        </form>
                    </div>
                </div>
                @endif

                <div style="margin-bottom: 32px;">
                    <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">Assignment History</h4>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                        @forelse($booking->assignments->sortByDesc('assigned_at') as $assignment)
                            <div style="padding: 12px 0; border-bottom: 1px solid #ddd;">
                                <strong>{{ $assignment->vehicle?->license_number ?: ($assignment->other_vehicle_license_number ? 'Other: '.$assignment->other_vehicle_license_number : 'No vehicle') }}</strong>
                                <span> | {{ $assignment->driver?->driver_name ?: 'No driver' }}</span>
                                <span class="badge" style="background: {{ $assignment->status === 'Current' ? '#17a2b8' : '#6c757d' }};">{{ $assignment->status }}</span><br>
                                <small style="color:#666;">{{ optional($assignment->assigned_at)->format('d M Y H:i') }}@if($assignment->reason) | {{ $assignment->reason }}@endif @if($assignment->assignedBy) | {{ $assignment->assignedBy->name ?? $assignment->assignedBy->email ?? 'Operator' }}@endif</small>
                            </div>
                        @empty
                            <span style="color:#666;">No assignment history yet.</span>
                        @endforelse
                    </div>
                </div>

                {{-- Cancellation Policy --}}
                @if(optional($transport)->cancellation_policy)
                <div style="margin-bottom: 32px;">
                    <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">⚠️ Cancellation Policy</h4>
                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 8px;">{!! optional($transport)->cancellation_policy !!}</div>
                </div>
                @endif

                {{-- Action Buttons --}}
                <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                        <a href="{{ route('operator.transport.bookings') }}" class="btn btn-outline-blue" style="">← Back to All Bookings</a>

                        @if($booking->booking_status === \App\Models\TransportBooking::STATUS_PROCESSING)
                            <form method="POST" action="{{ route('operator.transport.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Confirm this booking?');">
                                @csrf
                                <input type="hidden" name="booking_status" value="Confirmed">
                                <button type="submit" class="btn" style="background: #28a745; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">✓ Confirm Booking</button>
                            </form>
                        @endif

                        @if(in_array($booking->booking_status, [\App\Models\TransportBooking::STATUS_PROCESSING, \App\Models\TransportBooking::STATUS_CONFIRMED], true))
                            <form method="POST" action="{{ route('operator.transport.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Cancel this booking?');">
                                @csrf
                                <input type="hidden" name="booking_status" value="Cancelled">
                                <button type="submit" class="btn" style="background: #dc3545; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">✕ Cancel Booking</button>
                            </form>
                        @endif

                        @if($booking->booking_status === \App\Models\TransportBooking::STATUS_SCHEDULED)
                            <form method="POST" action="{{ route('operator.transport.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Mark this booking as completed?');">
                                @csrf
                                <input type="hidden" name="booking_status" value="{{ \App\Models\TransportBooking::STATUS_COMPLETED }}">
                                <button type="submit" class="btn" style="background: #17a2b8; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">Mark Completed</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const detailsVehicleSelect = document.getElementById('detailsVehicleSelect');
const detailsOtherVehicleFields = document.querySelector('#detailsAssignmentForm .row.mt-3');

function toggleDetailsOtherVehicleFields() {
    const isOther = detailsVehicleSelect?.value === 'other';
    if (detailsOtherVehicleFields) {
        detailsOtherVehicleFields.style.display = isOther ? 'flex' : 'none';
    }
}

detailsVehicleSelect?.addEventListener('change', toggleDetailsOtherVehicleFields);
toggleDetailsOtherVehicleFields();

document.querySelectorAll('.assignment-scroll-link').forEach(function (link) {
    link.addEventListener('click', function (event) {
        event.preventDefault();
        document.getElementById('current-assignment')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

document.getElementById('detailsAssignmentForm')?.addEventListener('submit', function () {
    const vehicle = document.getElementById('detailsVehicleSelect');
    if (vehicle.value === 'other') {
        vehicle.value = '';
    }
});
</script>
                    @endsection
