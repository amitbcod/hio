@extends('layouts.app')

@section('content')
    <div class="container mt-0">
        <div class="row">
            <div class="col-md-3 net-section">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9 my-pro">
                <div style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 40px;margin-top: 40px;">
                    
                    {{-- Header --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                        <div>
                            <h2 style="font-weight: bold; margin-bottom: 8px;">Booking Details</h2>
                            <p style="color: #666; margin-bottom: 0;">Booking Reference: {{ $booking->booking_reference }}</p>
                        </div>
                        <div style="display: flex; gap: 12px;">
                            <a href="{{ route('operator.accommodation.bookings') }}" class="btn" style="background: #6c757d; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">
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

                    {{-- Property & Room Details --}}
                    <div style="margin-bottom: 32px;">
                        <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">🏨 Property & Room Details</h4>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Property:</strong><br>
                                    {{ $booking->accommodation->property_name }}
                                    <br><small style="color: #666;">{{ $booking->accommodation->address }}, {{ $booking->accommodation->city }}, {{ $booking->accommodation->country }}</small>
                                </div>
                                <div class="col-md-6">
                                    <strong>Room:</strong><br>
                                    {{ $booking->room->room_name ?? 'Not specified' }}
                                    @if($booking->room)
                                        <br><small style="color: #666;">{{ $booking->room->room_type }} • {{ $booking->room->capacity }} guests max</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Stay Details --}}
                    <div style="margin-bottom: 32px;">
                        <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">📅 Stay Details</h4>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Check-in:</strong><br>
                                    {{ $booking->check_in_date->format('M d, Y') }}
                                    @if($booking->accommodation->checkin_time)
                                        <br><small style="color: #666;">From {{ $booking->accommodation->checkin_time }}</small>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <strong>Check-out:</strong><br>
                                    {{ $booking->check_out_date->format('M d, Y') }}
                                    @if($booking->accommodation->checkout_time)
                                        <br><small style="color: #666;">Until {{ $booking->accommodation->checkout_time }}</small>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <strong>Nights:</strong><br>
                                    {{ $booking->check_in_date->diffInDays($booking->check_out_date) }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Rooms Booked:</strong><br>
                                    {{ $booking->rooms_booked }}
                                </div>
                            </div>
                            <hr style="margin: 20px 0;">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Guests:</strong><br>
                                    {{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }}
                                    @if($booking->children > 0)
                                        , {{ $booking->children }} Child{{ $booking->children > 1 ? 'ren' : '' }}
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <strong>Total Guests:</strong><br>
                                    {{ $booking->adults + $booking->children }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Guest List --}}
                    @if($booking->guests->count() > 0)
                    <div style="margin-bottom: 32px;">
                        <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">👥 Guest List</h4>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                            @foreach($booking->guests as $guest)
                            <div style="background: #fff; padding: 15px; border-radius: 6px; margin-bottom: 10px; border-left: 4px solid #19b5b5;">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>{{ $guest->first_name }} {{ $guest->middle_name }} {{ $guest->last_name }}</strong>
                                        <br><small style="color: #666;">{{ ucfirst($guest->relation) }}</small>
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
                                    <strong>Payment Status:</strong><br>
                                    <span class="badge" style="background: #17a2b8;">Pending Payment</span>
                                    <br><small style="color: #666;">Payment processing details not available</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Additional Information --}}
                    @if($booking->traveler_notes)
                    <div style="margin-bottom: 32px;">
                        <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">📝 Special Requests & Notes</h4>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                            {{ $booking->traveler_notes }}
                        </div>
                    </div>
                    @endif

                    {{-- Cancellation Policy --}}
                    @if($booking->accommodation->cancellation_policy)
                    <div style="margin-bottom: 32px;">
                        <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">⚠️ Cancellation Policy</h4>
                        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 8px;">
                            @if($booking->accommodation->cancellation_policy_type === 'template')
                                <p><strong>Template:</strong> {{ $booking->accommodation->cancellation_policy_template_id }}</p>
                            @else
                                {!! $booking->accommodation->cancellation_policy !!}
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                            <a href="{{ route('operator.accommodation.bookings') }}" class="btn" style="background: #6c757d; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">
                                ← Back to All Bookings
                            </a>

                            @if($booking->booking_status === 'Pending')
                                <form method="POST" action="{{ route('operator.accommodation.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Confirm this booking?');">
                                    @csrf
                                    <input type="hidden" name="booking_status" value="Confirmed">
                                    <button type="submit" class="btn" style="background: #28a745; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">
                                        ✓ Confirm Booking
                                    </button>
                                </form>
                            @endif

                            @if($booking->booking_status !== 'Cancelled')
                                <form method="POST" action="{{ route('operator.accommodation.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Cancel this booking?');">
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