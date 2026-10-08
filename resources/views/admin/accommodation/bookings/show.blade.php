@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Accommodation Booking Details</h3>
            <div class="text-muted">Booking Reference: {{ $booking->booking_reference }}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.accommodation.bookings') }}" class="btn btn-outline-secondary btn-sm">← Back to Bookings</a>
            <span class="badge rounded-pill text-bg-info px-3 py-2">{{ $booking->booking_status ?? 'Pending' }}</span>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title mb-3">Booking Information</h5>
            <div class="row g-3">
                <div class="col-md-3"><strong>Booking Reference:</strong><br>{{ $booking->booking_reference }}</div>
                <div class="col-md-3"><strong>Booking Date:</strong><br>{{ optional($booking->created_at)->format('M d, Y H:i') }}</div>
                <div class="col-md-3"><strong>Source Channel:</strong><br>{{ $booking->source_channel ?? 'Direct' }}</div>
                <div class="col-md-3"><strong>Status:</strong><br><span class="badge text-bg-info">{{ $booking->booking_status ?? 'Pending' }}</span></div>
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
            <h5 class="card-title mb-3">Property & Room Details</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <strong>Property:</strong><br>
                    {{ optional($booking->accommodation)->property_name ?? 'N/A' }}
                    @if(optional($booking->accommodation)->address)
                        <br><small class="text-muted">{{ optional($booking->accommodation)->address }}, {{ optional($booking->accommodation)->city }}, {{ optional($booking->accommodation)->country }}</small>
                    @endif
                </div>
                <div class="col-md-6">
                    <strong>Room:</strong><br>
                    {{ optional($booking->room)->room_name ?? 'Not specified' }}
                    @if($booking->room)
                        <br><small class="text-muted">{{ $booking->room->room_type }} • {{ $booking->room->capacity }} guests max</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title mb-3">Stay Details</h5>
            <div class="row g-3">
                <div class="col-md-3"><strong>Check-in:</strong><br>{{ optional($booking->check_in_date)->format('Y-m-d') ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Check-out:</strong><br>{{ optional($booking->check_out_date)->format('Y-m-d') ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Adults:</strong><br>{{ $booking->adults ?? 0 }}</div>
                <div class="col-md-3"><strong>Children:</strong><br>{{ $booking->children ?? 0 }}</div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-md-6"><strong>Total Amount:</strong><br>{{ $booking->currency ?? 'USD' }} {{ number_format((float) ($booking->total_amount ?? 0), 2) }}</div>
                <div class="col-md-6"><strong>Booked By:</strong><br>{{ $booking->guest_name ?? 'N/A' }}@if($booking->guest_email) ({{ $booking->guest_email }})@endif</div>
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
                        @if($guest->notes)
                            <div class="mt-2 border-top pt-2"><strong>Notes:</strong> {{ $guest->notes }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($booking->traveler_notes)
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title mb-3">Special Requests & Notes</h5>
                {{ $booking->traveler_notes }}
            </div>
        </div>
    @endif

    <div class="text-center mt-4 pt-3 border-top">
        @if($booking->booking_status === 'Pending')
            <form method="POST" action="{{ route('admin.accommodation.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Confirm this booking?');">
                @csrf
                <input type="hidden" name="booking_status" value="Confirmed">
                <button type="submit" class="btn btn-success">✓ Confirm Booking</button>
            </form>
        @endif

        @if($booking->booking_status !== 'Cancelled')
            <form method="POST" action="{{ route('admin.accommodation.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Cancel this booking?');">
                @csrf
                <input type="hidden" name="booking_status" value="Cancelled">
                <button type="submit" class="btn btn-danger">✕ Cancel Booking</button>
            </form>
        @endif
    </div>
</div>
@endsection
