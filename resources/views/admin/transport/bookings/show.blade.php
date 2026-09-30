@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Transport Booking Details</h3>
    <a href="{{ route('admin.transport.bookings') }}" class="btn btn-sm btn-secondary mb-3">← Back to Bookings</a>

    @php
        $transport = $booking->transport;
        $operator = $transport?->operator;
        $operatorName = $operator?->profile?->business_legal_name
            ?: ($operator?->business_name ?: ($operator?->email ?: 'N/A'));
        $vehicleName = $transport?->vehicle_display_name
            ?: ($booking->other_vehicle_name ?: 'N/A');
        $assignedVehicle = $booking->vehicle?->license_number
            ?: ($booking->other_vehicle_license_number ? 'Other: ' . $booking->other_vehicle_license_number : 'Unassigned');
        $passengerCount = $booking->total_passengers
            ?? ((int) ($booking->adults ?? 0) + (int) ($booking->children ?? 0));
        $journeyLabel = match ($booking->trip_type) {
            'RETURN' => 'Return',
            'OUTBOUND' => 'Outbound',
            default => 'One-way',
        };
    @endphp

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ $booking->booking_reference ?: 'Transport booking #' . $booking->id }}</h5>
            <p class="card-text">
                <strong>Status:</strong> {{ $booking->booking_status ?: \App\Models\TransportBooking::STATUS_PROCESSING }}
                | <strong>Journey:</strong> {{ $journeyLabel }}
                | <strong>Channel:</strong> {{ $booking->source_channel ?: 'N/A' }}
            </p>

            <div class="row">
                <div class="col-md-4"><strong>Operator:</strong><br>{{ $operatorName }}</div>
                <div class="col-md-4"><strong>Vehicle Name:</strong><br>{{ $vehicleName }}</div>
                <div class="col-md-4"><strong>Vehicle Type:</strong><br>{{ $transport?->vehicle_type ?: 'N/A' }}</div>
            </div>

            <div class="row mt-2">
                <div class="col-md-4"><strong>Assigned Vehicle:</strong><br>{{ $assignedVehicle }}</div>
                <div class="col-md-4"><strong>Pickup Driver:</strong><br>{{ $booking->pickupDriver?->driver_name ?: ($booking->driver?->driver_name ?: 'Unassigned') }}</div>
                <div class="col-md-4"><strong>Return Driver:</strong><br>{{ $booking->returnDriver?->driver_name ?: 'Unassigned' }}</div>
            </div>

            <div class="row mt-2">
                <div class="col-md-4"><strong>Route:</strong><br>{{ $booking->route_from ?: 'N/A' }} → {{ $booking->route_to ?: 'N/A' }}</div>
                <div class="col-md-4"><strong>Pickup:</strong><br>{{ $booking->pickup_date?->format('Y-m-d') ?: 'N/A' }} {{ $booking->pickup_time }}</div>
                <div class="col-md-4"><strong>Return:</strong><br>{{ $booking->return_date?->format('Y-m-d') ?: 'N/A' }} {{ $booking->return_time }}</div>
            </div>

            <div class="row mt-2">
                <div class="col-md-4"><strong>Passengers:</strong><br>{{ $passengerCount }} ({{ (int) ($booking->adults ?? 0) }} adults, {{ (int) ($booking->children ?? 0) }} children)</div>
                <div class="col-md-4"><strong>Pickup / Drop-off:</strong><br>{{ $booking->pickup_address ?: 'N/A' }} / {{ $booking->dropoff_address ?: 'N/A' }}</div>
                <div class="col-md-4"><strong>Booked at:</strong><br>{{ $booking->booked_at?->format('Y-m-d H:i') ?: 'N/A' }}</div>
            </div>

            <div class="row mt-2">
                <div class="col-md-4"><strong>Total:</strong><br>{{ $booking->currency ?: 'USD' }} {{ number_format((float) ($booking->total_amount ?? 0), 2) }}</div>
                <div class="col-md-4"><strong>Payment Method:</strong><br>{{ $booking->payment_method ?: 'N/A' }}</div>
                <div class="col-md-4"><strong>Booked by:</strong><br>{{ $booking->guest_name ?: trim(($booking->traveler_first_name ?? '') . ' ' . ($booking->traveler_last_name ?? '')) ?: 'N/A' }}@if($booking->guest_email) ({{ $booking->guest_email }})@endif</div>
            </div>

            @if($booking->traveler_first_name)
            <hr>
            <h5>Traveler</h5>
            <p>{{ $booking->traveler_first_name }} {{ $booking->traveler_middle_name }} {{ $booking->traveler_last_name }}</p>
            <p>Relation: {{ ucfirst($booking->traveler_relation ?? 'N/A') }}, Gender: {{ ucfirst($booking->traveler_gender ?? 'N/A') }}, Nationality: {{ $booking->traveler_nationality ?? 'N/A' }}</p>
            @endif

            @if($booking->guests && $booking->guests->isNotEmpty())
            <hr>
            <h5>Guest List</h5>
            <ul>
                @foreach($booking->guests as $g)
                    <li>{{ $g->first_name }} {{ $g->last_name }} ({{ ucfirst($g->relation ?? 'guest') }}) - {{ $g->nationality }} @if($g->dob) - {{ $g->dob->format('Y-m-d') }}@endif</li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
</div>
@endsection
