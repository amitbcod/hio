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
                        <p style="color: #666; margin-bottom: 0;">Booking Reference: {{ $booking->booking_reference }}</p>
                    </div>
                    <div style="display: flex; gap: 12px; align-items:center;">
                        <a href="{{ route('operator.transport.bookings') }}" class="btn" style="background: #6c757d; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">
                            ← Back to Bookings
                        </a>
                        <span class="badge" style="background: {{ $booking->booking_status === 'Confirmed' ? '#28a745' : ($booking->booking_status === 'Pending' ? '#ffc107' : '#dc3545') }}; color: #fff; font-size: 14px; padding: 8px 16px;">
                            {{ $booking->booking_status ?? 'Pending' }}
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
                                <span class="badge" style="background: {{ $booking->booking_status === 'Confirmed' ? '#28a745' : ($booking->booking_status === 'Pending' ? '#ffc107' : '#dc3545') }}; color:#fff;">{{ $booking->booking_status ?? 'Pending' }}</span>
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
                <div style="margin-bottom: 32px;">
                    <h4 style="font-weight: 600; margin-bottom: 20px; color: #333;">🚗 Service & Vehicle Details</h4>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Vehicle:</strong><br>
                                {{ optional($transport)->vehicle_name }}
                                <br><small style="color: #666;">{{ optional($transport)->vehicle_type }} • Seating: {{ optional($transport)->seating_capacity ?? 'N/A' }}</small>
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
                                    $bookingStatus = $booking->booking_status ?? 'Pending';
                                    $statusColor = $bookingStatus === 'Confirmed' ? '#28a745' : ($bookingStatus === 'Cancelled' ? '#dc3545' : '#17a2b8');
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
                        <a href="{{ route('operator.transport.bookings') }}" class="btn" style="background: #6c757d; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">← Back to All Bookings</a>

                        @if($booking->booking_status === 'Pending')
                            <form method="POST" action="{{ route('operator.transport.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Confirm this booking?');">
                                @csrf
                                <input type="hidden" name="booking_status" value="Confirmed">
                                <button type="submit" class="btn" style="background: #28a745; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">✓ Confirm Booking</button>
                            </form>
                        @endif

                        @if($booking->booking_status !== 'Cancelled')
                            <form method="POST" action="{{ route('operator.transport.booking.status', $booking->id) }}" style="display:inline;" onsubmit="return confirm('Cancel this booking?');">
                                @csrf
                                <input type="hidden" name="booking_status" value="Cancelled">
                                <button type="submit" class="btn" style="background: #dc3545; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">✕ Cancel Booking</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                    @endsection
