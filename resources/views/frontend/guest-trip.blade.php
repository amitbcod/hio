@extends('frontend.layout')

@section('title', 'My Bookings')

@section('meta_description', 'View and manage your guest bookings')

@section('content')

<section class="guest-trip-section">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fa-solid fa-ticket"></i>
                My Bookings
            </h1>
            <p class="page-subtitle">Guest Booking Details</p>
        </div>

        <!-- Flash Messages -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <!-- Guest Email Info -->
        <div class="guest-info-card">
            <i class="fa-solid fa-envelope"></i>
            <p>Viewing bookings for: <strong>{{ $otpToken->email }}</strong></p>
            <a href="{{ route('traveler.guest-trip.logout') }}" class="btn-logout">Sign Out</a>
        </div>

        <!-- Accommodations -->
        @if ($accommodationBookings->isNotEmpty())
            <div class="bookings-section">
                <h2 class="section-title">
                    <i class="fa-solid fa-bed"></i>
                    Accommodation Bookings
                </h2>

                <div class="bookings-grid">
                    @foreach ($accommodationBookings as $booking)
                        <div class="booking-card">
                            <div class="booking-header">
                                <div>
                                    <h3 class="booking-title">{{ $booking->accommodation->property_name ?? 'Accommodation' }}</h3>
                                    <p class="booking-ref">Booking Reference: <strong>{{ $booking->booking_reference }}</strong></p>
                                </div>
                                <span class="booking-status {{ strtolower($booking->booking_status) }}">
                                    {{ $booking->booking_status }}
                                </span>
                            </div>

                            <div class="booking-details">
                                <div class="detail-item">
                                    <span class="detail-label">Check-in:</span>
                                    <span class="detail-value">{{ $booking->check_in_date->format('F d, Y') }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Check-out:</span>
                                    <span class="detail-value">{{ $booking->check_out_date->format('F d, Y') }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Guests:</span>
                                    <span class="detail-value">{{ $booking->adults }} Adult(s), {{ $booking->children }} Child(ren)</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Total Amount:</span>
                                    <span class="detail-value font-bold">{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</span>
                                </div>
                            </div>

                            @php
                                $tripDetailUrl = $booking->trip_id ? route('traveler.guest-trip.detail', ['otp' => $otpToken->otp_code, 'trip' => $booking->trip_id]) : route('traveler.guest-trip.show', ['otp' => $otpToken->otp_code]);
                                $voucherUrl = $booking->trip_id ? route('traveler.guest-trip.trip.booking.download-voucher', ['otp' => $otpToken->otp_code, 'trip' => $booking->trip_id, 'booking' => $booking->id]) : '#';
                            @endphp

                            <div class="booking-actions">
                                <a href="{{ $tripDetailUrl }}" class="btn-secondary btn-sm">View Details</a>
                                <a href="{{ $voucherUrl }}" class="btn-secondary btn-sm">Download Voucher</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Activities -->
        @if ($activityBookings->isNotEmpty())
            <div class="bookings-section">
                <h2 class="section-title">
                    <i class="fa-solid fa-ticket"></i>
                    Activity Bookings
                </h2>

                <div class="bookings-grid">
                    @foreach ($activityBookings as $booking)
                        <div class="booking-card">
                            <div class="booking-header">
                                <div>
                                    <h3 class="booking-title">{{ $booking->activity->activity_name ?? 'Activity' }}</h3>
                                    <p class="booking-ref">Booking Reference: <strong>{{ $booking->booking_reference }}</strong></p>
                                </div>
                                <span class="booking-status {{ strtolower($booking->booking_status) }}">
                                    {{ $booking->booking_status }}
                                </span>
                            </div>

                            <div class="booking-details">
                                <div class="detail-item">
                                    <span class="detail-label">Activity Date:</span>
                                    <span class="detail-value">{{ $booking->activity_date->format('F d, Y') }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Participants:</span>
                                    <span class="detail-value">{{ $booking->participants ?? $booking->adults }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Total Amount:</span>
                                    <span class="detail-value font-bold">{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</span>
                                </div>
                            </div>

                            @php
                                $tripDetailUrl = $booking->trip_id ? route('traveler.guest-trip.detail', ['otp' => $otpToken->otp_code, 'trip' => $booking->trip_id]) : route('traveler.guest-trip.show', ['otp' => $otpToken->otp_code]);
                                $voucherUrl = $booking->trip_id ? route('traveler.guest-trip.trip.booking.download-voucher', ['otp' => $otpToken->otp_code, 'trip' => $booking->trip_id, 'booking' => $booking->id]) : '#';
                            @endphp

                            <div class="booking-actions">
                                <a href="{{ $tripDetailUrl }}" class="btn-secondary btn-sm">View Details</a>
                                <a href="{{ $voucherUrl }}" class="btn-secondary btn-sm">Download Voucher</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Empty State -->
        @if ($accommodationBookings->isEmpty() && $activityBookings->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <h3>No Bookings Found</h3>
                <p>You don't have any bookings yet.</p>
                <a href="{{ url('/') }}" class="btn-primary">Browse Accommodations & Activities</a>
            </div>
        @endif
    </div>
</section>

@endsection

@push('styles')
<style>
.guest-trip-section {
    padding: 40px 0;
    background: #f9f9f9;
    min-height: 60vh;
}

.page-header {
    margin-bottom: 36px;
    text-align: center;
}

.page-title {
    font-size: 28px;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.page-title i {
    font-size: 32px;
}

.page-subtitle {
    font-size: 15px;
    color: #666;
    margin: 0;
}

.guest-info-card {
    background: #fff;
    border: 2px solid #ddd;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.guest-info-card i {
    font-size: 24px;
    color: #1a1a2e;
}

.guest-info-card p {
    margin: 0;
    font-size: 15px;
    color: #333;
    flex: 1;
    min-width: 200px;
}

.btn-logout {
    background: #dc3545;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-logout:hover {
    background: #c82333;
}

.bookings-section {
    margin-bottom: 40px;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    font-size: 22px;
}

.bookings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

.booking-card {
    background: #fff;
    border: 1px solid #e8e8ef;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    transition: all 0.3s ease;
}

.booking-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.booking-header {
    background: #f9f9f9;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    border-bottom: 1px solid #e8e8ef;
}

.booking-title {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 4px;
}

.booking-ref {
    font-size: 12px;
    color: #666;
    margin: 0;
}

.booking-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    white-space: nowrap;
}

.booking-status.pending {
    background: #fff3cd;
    color: #856404;
}

.booking-status.confirmed {
    background: #d4edda;
    color: #155724;
}

.booking-status.cancelled {
    background: #f8d7da;
    color: #721c24;
}

.booking-details {
    padding: 16px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail-label {
    font-size: 12px;
    color: #999;
    font-weight: 600;
    text-transform: uppercase;
}

.detail-value {
    font-size: 14px;
    color: #1a1a2e;
}

.font-bold {
    font-weight: 700;
}

.booking-actions {
    display: flex;
    gap: 8px;
    padding: 12px 16px;
    border-top: 1px solid #e8e8ef;
    background: #fafafa;
}

.btn-secondary {
    flex: 1;
    display: inline-block;
    background: #e8e8ef;
    color: #1a1a2e;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: #ddd;
}

.btn-sm {
    padding: 8px 12px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e8e8ef;
}

.empty-state i {
    font-size: 64px;
    color: #ccc;
    margin-bottom: 16px;
}

.empty-state h3 {
    font-size: 22px;
    color: #1a1a2e;
    margin: 0 0 8px;
}

.empty-state p {
    color: #666;
    margin-bottom: 24px;
}

@media (max-width: 768px) {
    .guest-trip-section {
        padding: 20px 0;
    }

    .bookings-grid {
        grid-template-columns: 1fr;
    }

    .guest-info-card {
        flex-direction: column;
        text-align: center;
    }

    .booking-details {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

