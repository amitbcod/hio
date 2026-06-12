@extends('frontend.layout')

@section('title', 'Trip Details | Holidays.io')

@section('meta_description', 'View your trip details on Holidays.io.')

@section('content')
<section class="page-section traveler-trip-detail-section">
    <div class="wrap">
        <!-- Header -->
        <div class="trip-detail-header-section" style="margin-bottom: 40px;">
            <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.show', ['otp' => $otp]) : route('traveler.trips') }}" class="btn btn-secondary-outline">&larr; Back to Trips</a>
            <div style="margin-top: 20px;">
                <h1 style="margin: 10px 0; font-size: 2.5rem;">Trip ID: <strong>#00{{ $trip->id }}</h1>
                <!-- <p style="color: #666; font-size: 1rem; margin: 5px 0; padding: 12px 16px; background: #fff3e0; border-left: 4px solid #ff9500; display: inline-block; border-radius: 4px;">Trip ID: <strong>#{{ $trip->id }}</strong></p> -->
                
                @php
                    $tripHasEnded = $tripEndDate && \Carbon\Carbon::parse($tripEndDate)->isPast();
                    $traveler = auth('traveler')->user();
                    $canLeaveFeedback = $traveler && $trip->traveler_account_id === $traveler->id && $tripHasEnded;
                @endphp
                
                @if($canLeaveFeedback)
                <div style="margin-top: 15px;">
                    <a href="{{ route('frontend.feedback.show', ['trip' => $trip->id]) }}" class="btn btn-primary" style="background: #ff9500; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block; font-weight: 600;">
                        ⭐ Share Your Feedback
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Trip Summary Cards -->
        <div class="trip-summary-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <div class="summary-card" style="background: #f5f5f5; padding: 20px; border-radius: 8px; border-left: 4px solid #ff9500;">
                <label style="font-size: 0.85rem; color: #999; text-transform: uppercase;">Status</label>
                <p style="font-size: 1.5rem; margin: 10px 0; font-weight: 600;">
                    <span class="trip-status trip-status--{{ $trip->status }}" style="display: inline-block; padding: 6px 12px; border-radius: 4px; background: {{ $trip->status === 'planned' ? '#e3f2fd' : ($trip->status === 'active' ? '#e8f5e9' : '#f3e5f5') }}; color: {{ $trip->status === 'planned' ? '#1976d2' : ($trip->status === 'active' ? '#388e3c' : '#7b1fa2') }};">
                        {{ ucfirst($trip->status) }}
                    </span>
                </p>
            </div>

            <div class="summary-card" style="background: #f5f5f5; padding: 20px; border-radius: 8px; border-left: 4px solid #ff9500;">
                <label style="font-size: 0.85rem; color: #999; text-transform: uppercase;">Start Date</label>
                <p style="font-size: 1.5rem; margin: 10px 0; font-weight: 600;">
                    {{ $tripStartDate ? \Carbon\Carbon::parse($tripStartDate)->format('d M Y') : 'Not set' }}
                </p>
            </div>

            <div class="summary-card" style="background: #f5f5f5; padding: 20px; border-radius: 8px; border-left: 4px solid #ff9500;">
                <label style="font-size: 0.85rem; color: #999; text-transform: uppercase;">End Date</label>
                <p style="font-size: 1.5rem; margin: 10px 0; font-weight: 600;">
                    {{ $tripEndDate ? \Carbon\Carbon::parse($tripEndDate)->format('d M Y') : 'Not set' }}
                </p>
            </div>
        </div>

        <!-- Accommodation Bookings Section -->
        @if($accommodationBookings->count() > 0)
        <div class="bookings-section" style="margin-bottom: 40px; background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 25px;">
            <h3 style="font-size: 1.3rem; margin-bottom: 20px; border-bottom: 2px solid #ff9500; padding-bottom: 10px;">Accommodation Bookings</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
                    <thead>
                        <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Booking Ref</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Accommodation</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Room</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Check-in</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Check-out</th>
                            <th style="padding: 12px; text-align: center; font-weight: 600;">Guests</th>
                            <th style="padding: 12px; text-align: right; font-weight: 600;">Amount</th>
                            <th style="padding: 12px; text-align: center; font-weight: 600;">Status</th>
                            <th style="padding: 12px; text-align: center; font-weight: 600;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accommodationBookings as $booking)
                        <tr style="border-bottom: 1px solid #e0e0e0; transition: background 0.2s;">
                            <td style="padding: 12px; font-weight: 600; color: #ff9500;">{{ $booking->booking_reference }}</td>
                            <td style="padding: 12px;">
                                {{ $booking->accommodation ? $booking->accommodation->property_name : 'N/A' }}
                            </td>
                            <td style="padding: 12px;">
                                {{ $booking->room ? $booking->room->room_name : 'N/A' }}
                            </td>
                            <td style="padding: 12px;">
                                {{ $booking->check_in_date->format('d M Y') }}
                            </td>
                            <td style="padding: 12px;">
                                {{ $booking->check_out_date->format('d M Y') }}
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                @php
                                    $bookedCount = ($booking->adults ?? 0) + ($booking->children ?? 0);
                                    $addedCount = $booking->guests->count();
                                @endphp
                                <div style="font-weight: 600;">Booked: {{ $bookedCount }}</div>
                                <div style="margin-bottom: 8px;">Added: {{ $addedCount }}</div>
                                
                            </td>
                            <td style="padding: 12px; text-align: right; font-weight: 600;">
                                {{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <span style="display: inline-block; padding: 4px 10px; background: {{ $booking->booking_status === 'Confirmed' ? '#e8f5e9' : ($booking->booking_status === 'Pending' ? '#fff3e0' : '#ffebee') }}; color: {{ $booking->booking_status === 'Confirmed' ? '#2e7d32' : ($booking->booking_status === 'Pending' ? '#e65100' : '#c62828') }}; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                                    {{ $booking->booking_status }}
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.trip.booking.manage-guests', ['otp' => $otp, 'trip' => $trip->id, 'booking' => $booking->id]) : route('traveler.trip.booking.manage-guests', ['trip' => $trip->id, 'booking' => $booking->id]) }}" class="btn btn-sm btn-outline-primary" style="margin-top: 5px;font-weight: 600; color: #ff9500;">Manage</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Activity Bookings Section -->
        @if($activityBookings->count() > 0)
        <div class="bookings-section" style="margin-bottom: 40px; background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 25px;">
            <h3 style="font-size: 1.3rem; margin-bottom: 20px; border-bottom: 2px solid #ff9500; padding-bottom: 10px;">Activity Bookings</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
                    <thead>
                        <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Booking Ref</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Activity</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Variant</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Activity Date</th>
                            <th style="padding: 12px; text-align: center; font-weight: 600;">Participants</th>
                            <th style="padding: 12px; text-align: right; font-weight: 600;">Amount</th>
                            <th style="padding: 12px; text-align: center; font-weight: 600;">Status</th>
                             <th style="padding: 12px; text-align: center; font-weight: 600;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activityBookings as $booking)
                        <tr style="border-bottom: 1px solid #e0e0e0; transition: background 0.2s;">
                            <td style="padding: 12px; font-weight: 600; color: #ff9500;">{{ $booking->booking_reference }}</td>
                            <td style="padding: 12px;">
                                {{ $booking->activity ? $booking->activity->activity_name : 'N/A' }}
                            </td>
                            <td style="padding: 12px;">
                                {{ $booking->variant_name ?? 'Standard' }}
                            </td>
                            <td style="padding: 12px;">
                                {{ $booking->activity_date->format('d M Y') }}
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                @php
                                    $bookedCount = ($booking->adults ?? 0) + ($booking->children ?? 0);
                                    $addedCount = $booking->guests->count();
                                @endphp
                                <div style="font-weight: 600;">Booked: {{ $bookedCount }}</div>
                                <div style="margin-bottom: 8px;">Added: {{ $addedCount }}</div>
                                @if($booking->participant_time_slots)
                                    @php
                                        $timeSlotsCount = count(array_filter($booking->participant_time_slots));
                                    @endphp
                                    <div style="font-size: 0.8rem; color: #666;">Time slots: {{ $timeSlotsCount }}/{{ $addedCount }}</div>
                                @endif
                            </td>
                            <td style="padding: 12px; text-align: right; font-weight: 600;">
                                {{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <span style="display: inline-block; padding: 4px 10px; background: {{ $booking->booking_status === 'Confirmed' ? '#e8f5e9' : ($booking->booking_status === 'Pending' ? '#fff3e0' : '#ffebee') }}; color: {{ $booking->booking_status === 'Confirmed' ? '#2e7d32' : ($booking->booking_status === 'Pending' ? '#e65100' : '#c62828') }}; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                                    {{ $booking->booking_status }}
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                             <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.trip.booking.manage-guests', ['otp' => $otp, 'trip' => $trip->id, 'booking' => $booking->id]) : route('traveler.trip.booking.manage-guests', ['trip' => $trip->id, 'booking' => $booking->id]) }}" class="btn btn-sm btn-outline-primary" style="margin-top: 5px;font-weight: 600; color: #ff9500;">Manage</a>
                            </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- No Bookings Message -->
        @if($accommodationBookings->count() === 0 && $activityBookings->count() === 0)
        <div style="background: #f9f9f9; padding: 30px; border-radius: 8px; text-align: center; margin-bottom: 30px;">
            <p style="color: #999; font-size: 1.1rem; margin: 0;">No bookings added to this trip yet.</p>
        </div>
        @endif

        @if((!isset($guestMode) || !$guestMode) && !in_array($trip->status, ['completed', 'cancelled']))
        <!-- Add Services Section -->
        <div class="trip-actions-section" style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 25px; margin-top: 30px;">
            <h3 style="font-size: 1.3rem; margin-bottom: 20px; border-bottom: 2px solid #ff9500; padding-bottom: 10px;">Add More Services</h3>
            <p style="color: #666; margin-bottom: 20px;">Expand your trip by adding more accommodations or activities.</p>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <form method="POST" action="{{ route('traveler.trip.add-service', $trip) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary" name="service_type" value="accommodation" style="padding: 12px 24px; background: #ff9500; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.3s;">
                        + Add Accommodation
                    </button>
                </form>
                <form method="POST" action="{{ route('traveler.trip.add-service', $trip) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary" name="service_type" value="activity" style="padding: 12px 24px; background: #2196F3; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.3s;">
                        + Add Activity
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- Back to Trips -->
        <div style="margin-top: 30px; text-align: center;">
            <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.show', ['otp' => $otp]) : route('traveler.trips') }}" class="btn btn-secondary" style="padding: 12px 30px; background: #f5f5f5; color: #333; border: 1px solid #ddd; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block; transition: background 0.3s;">
                &larr; Back to All Trips
            </a>
        </div>
    </div>
</section>

<style>
    .wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .page-section {
        padding: 40px 0;
        background: #f9f9f9;
    }
    
    @media (max-width: 768px) {
        .trip-summary-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        
        table {
            font-size: 0.85rem !important;
        }
        
        table th, table td {
            padding: 8px !important;
        }
    }
    
    @media (max-width: 480px) {
        .trip-summary-grid {
            grid-template-columns: 1fr !important;
        }
        
        .trip-detail-header-section h1 {
            font-size: 1.8rem !important;
        }
    }
</style>
@endsection
