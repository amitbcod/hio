@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Activity Booking Details</h3>
    <a href="{{ route('admin.activity.bookings') }}" class="btn btn-sm btn-secondary mb-3">← Back to Bookings</a>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ $booking->booking_reference }}</h5>
            <p class="card-text"><strong>Status:</strong> {{ $booking->booking_status }} | <strong>Channel:</strong> {{ $booking->source_channel }}</p>

            <div class="row">
                <div class="col-md-4"><strong>Operator:</strong><br>{{ optional($booking->activity->operator)->email ?? 'N/A' }}</div>
                <div class="col-md-4"><strong>Activity:</strong><br>{{ optional($booking->activity)->activity_name ?? 'N/A' }}</div>
                <div class="col-md-4"><strong>Variant:</strong><br>{{ $booking->variant_name ?? 'N/A' }}</div>
            </div>

            <div class="row mt-2">
                <div class="col-md-3"><strong>Activity Date:</strong><br>{{ optional($booking->activity_date)->format('Y-m-d') }}</div>
                <div class="col-md-3"><strong>Adults:</strong><br>{{ $booking->adults }}</div>
                <div class="col-md-3"><strong>Children:</strong><br>{{ $booking->children }}</div>
                <div class="col-md-3"><strong>Payment:</strong><br>{{ $booking->payment_method }}</div>
            </div>

            <div class="row mt-2">
                <div class="col-md-3"><strong>Total:</strong><br>{{ $booking->currency }} {{ number_format($booking->total_amount ?? 0, 2) }}</div>
                <div class="col-md-3"><strong>Booked at:</strong><br>{{ optional($booking->booked_at)->format('Y-m-d H:i') }}</div>
                <div class="col-md-6">
                    <strong>Booked by:</strong><br>
                    {{ $booking->guest_name }}
                    @if($booking->guest_email)
                        ({{ $booking->guest_email }})
                    @endif
                    @if($booking->guest_phone)
                        - {{ $booking->guest_phone }}
                    @endif
                </div>
            </div>

            @if($booking->guests->count())
            <hr>
            <h5>Participant List</h5>
            <ul>
                @foreach($booking->guests as $g)
                    <li>{{ $g->first_name }} {{ $g->last_name }} ({{ ucfirst($g->relation ?? 'participant') }}) - {{ $g->nationality }} @if($g->dob) - {{ $g->dob->format('Y-m-d') }}@endif</li>
                @endforeach
            </ul>
            @endif

            @if($booking->activity_time_slot_id && optional($booking->activity)->schedulingTimeSlots)
                @php
                    $slot = $booking->activity->schedulingTimeSlots->firstWhere('timeslot_id', $booking->activity_time_slot_id);
                @endphp
                @if($slot)
                <hr>
                <h5>Activity Schedule</h5>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8"><strong>Time Slot:</strong></div>
                            <div class="col-md-4">{{ $slot->start_time }} - {{ $slot->end_time }} @if($slot->duration) ({{ $slot->duration }}) @endif</div>
                        </div>
                    </div>
                </div>
                @endif
            @endif

            @if($booking->special_requests)
            <hr>
            <h5>Special Requests</h5>
            <p>{{ $booking->special_requests }}</p>
            @endif
        </div>
    </div>
</div>
@endsection