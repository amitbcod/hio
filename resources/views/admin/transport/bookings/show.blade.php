@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Transport Booking Details</h3>
    <a href="{{ route('admin.transport.bookings') }}" class="btn btn-sm btn-secondary mb-3">← Back to Bookings</a>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ $booking->booking_reference }}</h5>
            <p class="card-text"><strong>Status:</strong> {{ $booking->booking_status }} | <strong>Channel:</strong> {{ $booking->source_channel }}</p>

            <div class="row">
                <div class="col-md-4"><strong>Operator:</strong><br>{{ optional($booking->transport->operator)->email ?? 'N/A' }}</div>
                <div class="col-md-4"><strong>Vehicle:</strong><br>{{ optional($booking->transport)->vehicle_name ?? 'N/A' }}</div>
                <div class="col-md-4"><strong>Passengers:</strong><br>{{ $booking->total_passengers ?? ($booking->adults + ($booking->children ?? 0)) }}</div>
            </div>

            <div class="row mt-2">
                <div class="col-md-4"><strong>Pickup:</strong><br>{{ $booking->route_from }}</div>
                <div class="col-md-4"><strong>Destination:</strong><br>{{ $booking->route_to }}</div>
                <div class="col-md-4"><strong>Pickup Date:</strong><br>{{ optional($booking->pickup_date)->format('Y-m-d') }} {{ $booking->pickup_time }}</div>
            </div>

            <div class="row mt-2">
                <div class="col-md-3"><strong>Total:</strong><br>{{ $booking->currency }} {{ number_format($booking->total_amount ?? 0, 2) }}</div>
                <div class="col-md-3"><strong>Booked at:</strong><br>{{ optional($booking->booked_at)->format('Y-m-d H:i') }}</div>
                <div class="col-md-6"><strong>Booked by:</strong><br>{{ $booking->guest_name }}@if($booking->guest_email) ({{ $booking->guest_email }})@endif</div>
            </div>

            @if($booking->traveler_first_name)
            <hr>
            <h5>Traveler</h5>
            <p>{{ $booking->traveler_first_name }} {{ $booking->traveler_middle_name }} {{ $booking->traveler_last_name }}</p>
            <p>Relation: {{ ucfirst($booking->traveler_relation ?? 'N/A') }}, Gender: {{ ucfirst($booking->traveler_gender ?? 'N/A') }}, Nationality: {{ $booking->traveler_nationality ?? 'N/A' }}</p>
            @endif

            @if($booking->guests->count())
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
