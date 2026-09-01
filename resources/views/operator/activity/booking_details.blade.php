@extends('layouts.app')

@section('content')
    <div class="container mt-0">
        <div class="row">
            <div class="col-md-3 net-section">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9 my-pro">
                <div class="container-middle">

                    {{-- Header --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                        <div>
                            <h2 style="font-weight: bold; margin-bottom: 8px;">Activity Booking Details</h2>
                            <p style="color: #666; margin-bottom: 0;">Booking Reference: {{ $booking->booking_reference }}</p>
                        </div>
                        <div style="display: flex; gap: 12px;">
                            <a href="{{ route('operator.activity.bookings') }}" class="btn" style="background: #6c757d; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">
                                ← Back to Bookings
                            </a>
                            <span class="badge" style="background: {{ $booking->booking_status === 'Confirmed' ? '#28a745' : ($booking->booking_status === 'Pending' ? '#ffc107' : '#dc3545') }}; color: #fff; font-size: 14px; padding: 8px 16px;">
                                {{ $booking->booking_status }}
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
                                    {{ $booking->created_at->format('M d, Y H:i') }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Source Channel:</strong><br>
                                    {{ $booking->source_channel }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Status:</strong><br>
                                    <span class="badge" style="background: {{ $booking->booking_status === 'Confirmed' ? '#28a745' : ($booking->booking_status === 'Pending' ? '#ffc107' : '#dc3545') }};">
                                        {{ $booking->booking_status }}
                                    </span>
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
                            @if($booking->guest_phone)
                            <div class="row" style="margin-top: 10px;">
                                <div class="col-md-6">
                                    <strong>Phone:</strong><br>
                                    {{ $booking->guest_phone }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Activity Details --}}
                    <div style="margin-bottom: 32px;">
                        <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">🎯 Activity Details</h4>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Activity:</strong><br>
                                    {{ optional($booking->activity)->activity_name ?? 'N/A' }}
                                    @if(optional($booking->activity)->city)
                                        <br><small style="color: #666;">{{ optional($booking->activity)->city }}, {{ optional($booking->activity)->country ?? '' }}</small>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <strong>Variant:</strong><br>
                                    {{ $booking->variant_name ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Activity Schedule --}}
                    <div style="margin-bottom: 32px;">
                        <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">📅 Activity Schedule</h4>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Activity Date:</strong><br>
                                    {{ $booking->activity_date ? $booking->activity_date->format('M d, Y') : 'Not specified' }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Participants:</strong><br>
                                    {{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }}
                                    @if($booking->children > 0)
                                        , {{ $booking->children }} Child{{ $booking->children > 1 ? 'ren' : '' }}
                                    @endif
                                    @if($booking->infants > 0)
                                        , {{ $booking->infants }} Infant{{ $booking->infants > 1 ? 's' : '' }}
                                    @endif
                                    <br>
                                    <strong>Total:</strong> {{ $booking->adults + $booking->children + ($booking->infants ?? 0) }}
                                </div>
                            </div>
                            @if($booking->activity_time_slot_id && optional($booking->activity)->schedulingTimeSlots)
                                @php
                                    $slot = $booking->activity->schedulingTimeSlots->firstWhere('timeslot_id', $booking->activity_time_slot_id);
                                @endphp
                                @if($slot)
                                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                                    <strong>Time Slot:</strong><br>
                                    {{ $slot->start_time }} - {{ $slot->end_time }} @if($slot->duration) ({{ $slot->duration }}) @endif
                                </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Guest List --}}
                    @if($booking->guests->count() > 0)
                    <div style="margin-bottom: 32px;">
                        <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">👥 Participant List</h4>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                            @foreach($booking->guests as $guest)
                            <div style="background: #fff; padding: 15px; border-radius: 6px; margin-bottom: 10px; border-left: 4px solid #19b5b5;">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>{{ $guest->first_name }} {{ $guest->middle_name }} {{ $guest->last_name }}</strong>
                                        <br><small style="color: #666;">{{ ucfirst($guest->relation ?? 'participant') }}</small>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Nationality:</strong> {{ $guest->nationality ?? 'Not specified' }}
                                        @if($guest->dob)
                                            <br><small style="color: #666;">DOB: {{ $guest->dob->format('M d, Y') }}</small>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Passport:</strong> {{ $guest->passport_number ?? 'Not provided' }}
                                        @if($guest->gender)
                                            <br><small style="color: #666;">Gender: {{ ucfirst($guest->gender) }}</small>
                                        @endif
                                    </div>
                                </div>
                                @if($guest->notes)
                                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee;">
                                    <strong>Notes:</strong> {{ $guest->notes }}
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Financial Information --}}
                    <div style="margin-bottom: 32px;">
                        <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">💰 Financial Information</h4>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Total Amount:</strong><br>
                                    @if($booking->total_amount)
                                        <span style="font-size: 18px; font-weight: bold; color: #19b5b5;">
                                            {{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}
                                        </span>
                                    @else
                                        <span style="color: #666;">Amount not available</span>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <strong>Payment Method:</strong><br>
                                    {{ $booking->payment_method ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Special Requests --}}
                    @if($booking->special_requests)
                    <div style="margin-bottom: 32px;">
                        <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">📝 Special Requests & Notes</h4>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                            {{ $booking->special_requests }}
                        </div>
                    </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                            <a href="{{ route('operator.activity.bookings') }}" class="btn" style="background: #6c757d; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">
                                ← Back to All Bookings
                            </a>

                            @if($booking->booking_status === 'Pending')
                                <form method="POST" action="{{ route('operator.activity.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Confirm this booking?');">
                                    @csrf
                                    <input type="hidden" name="booking_status" value="Confirmed">
                                    <button type="submit" class="btn" style="background: #28a745; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">
                                        ✓ Confirm Booking
                                    </button>
                                </form>
                            @endif

                            @if($booking->booking_status !== 'Cancelled')
                                <form method="POST" action="{{ route('operator.activity.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Cancel this booking?');">
                                    @csrf
                                    <input type="hidden" name="booking_status" value="Cancelled">
                                    <button type="submit" class="btn" style="background: #dc3545; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">
                                        ✕ Cancel Booking
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection