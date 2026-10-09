@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Activity Booking Details</h3>
            <div class="text-muted">Booking Reference: {{ $booking->booking_reference }}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.activity.bookings') }}" class="btn btn-outline-secondary btn-sm">← Back to Bookings</a>
            @include('admin.partials._status_badge', ['status' => $booking->booking_status ?? 'Pending'])
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title mb-3">Booking Information</h5>
            <div class="row g-3">
                <div class="col-md-3"><strong>Booking Reference:</strong><br>{{ $booking->booking_reference }}</div>
                <div class="col-md-3"><strong>Booking Date:</strong><br>{{ optional($booking->created_at)->format('M d, Y H:i') }}</div>
                <div class="col-md-3"><strong>Source Channel:</strong><br>{{ $booking->source_channel ?? 'Direct' }}</div>
                <div class="col-md-3"><strong>Status:</strong><br>@include('admin.partials._status_badge', ['status' => $booking->booking_status ?? 'Pending'])</div>
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
            @if($booking->guest_phone)
                <div class="row g-3 mt-1">
                    <div class="col-md-6"><strong>Phone:</strong><br>{{ $booking->guest_phone }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title mb-3">Activity Details</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <strong>Activity:</strong><br>
                    {{ optional($booking->activity)->activity_name ?? 'N/A' }}
                    @if(optional($booking->activity)->city)
                        <br><small class="text-muted">{{ optional($booking->activity)->city }}, {{ optional($booking->activity)->country ?? '' }}</small>
                    @endif
                </div>
                <div class="col-md-6"><strong>Variant:</strong><br>{{ $booking->variant_name ?? 'Not specified' }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title mb-3">Activity Schedule</h5>
            <div class="row g-3">
                <div class="col-md-6"><strong>Activity Date:</strong><br>{{ $booking->activity_date ? $booking->activity_date->format('M d, Y') : 'Not specified' }}</div>
                <div class="col-md-6">
                    <strong>Participants:</strong><br>
                    {{ $booking->adults ?? 0 }} Adult{{ (($booking->adults ?? 0) > 1) ? 's' : '' }}
                    @if(($booking->children ?? 0) > 0)
                        , {{ $booking->children }} Child{{ $booking->children > 1 ? 'ren' : '' }}
                    @endif
                    @if(($booking->infants ?? 0) > 0)
                        , {{ $booking->infants }} Infant{{ $booking->infants > 1 ? 's' : '' }}
                    @endif
                    <br><strong>Total:</strong> {{ ($booking->adults ?? 0) + ($booking->children ?? 0) + ($booking->infants ?? 0) }}
                </div>
            </div>
            @if($booking->activity_time_slot_id && optional($booking->activity)->schedulingTimeSlots)
                @php $slot = $booking->activity->schedulingTimeSlots->firstWhere('timeslot_id', $booking->activity_time_slot_id); @endphp
                @if($slot)
                    <div class="mt-3 border-top pt-3">
                        <strong>Time Slot:</strong> {{ $slot->start_time }} - {{ $slot->end_time }} @if($slot->duration) ({{ $slot->duration }}) @endif
                    </div>
                @endif
            @endif
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title mb-3">Financial Information</h5>
            <div class="row g-3">
                <div class="col-md-6"><strong>Total Amount:</strong><br>{{ $booking->currency ?? 'USD' }} {{ number_format((float) ($booking->total_amount ?? 0), 2) }}</div>
                <div class="col-md-6"><strong>Payment Method:</strong><br>{{ $booking->payment_method ?? 'Not specified' }}</div>
            </div>
        </div>
    </div>

    @if($booking->guests && $booking->guests->count() > 0)
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title mb-3">Participant List</h5>
                @foreach($booking->guests as $guest)
                    <div class="border rounded p-3 mb-2">
                        <div class="row g-3">
                            <div class="col-md-4"><strong>{{ $guest->first_name }} {{ $guest->middle_name }} {{ $guest->last_name }}</strong><br><small class="text-muted">{{ ucfirst($guest->relation ?? 'participant') }}</small></div>
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

    @if($booking->special_requests)
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title mb-3">Special Requests</h5>
                {{ $booking->special_requests }}
            </div>
        </div>
    @endif

    <div class="text-center mt-4 pt-3 border-top">
        @if($booking->booking_status === 'Pending')
            <form method="POST" action="{{ route('admin.activity.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Confirm this booking?');">
                @csrf
                <input type="hidden" name="booking_status" value="Confirmed">
                <button type="submit" class="btn btn-success">✓ Confirm Booking</button>
            </form>
        @endif

        @if($booking->booking_status !== 'Cancelled')
            <form method="POST" action="{{ route('admin.activity.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Cancel this booking?');">
                @csrf
                <input type="hidden" name="booking_status" value="Cancelled">
                <button type="submit" class="btn btn-danger">✕ Cancel Booking</button>
            </form>
        @endif
    </div>
</div>
@endsection
