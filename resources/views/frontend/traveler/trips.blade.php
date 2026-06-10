@extends('frontend.layout')

@section('title', 'My Trips | Holidays.io')
@section('meta_description', 'Manage your travel trips on Holidays.io.')

@section('content')
<section class="page-section traveler-trips-section">
    <div class="wrap">
        <div class="traveler-trips-card">
            <div class="traveler-trips-head">
                <h1>My Trips</h1>
                <!-- <p>View and manage your holiday trips in a simple table format.</p> -->
            </div>

            @if($trips->count() > 0)
                <div class="traveler-trips-table-wrapper">
                    <table class="traveler-trips-table">
                        <thead>
                            <tr>
                                <th>Trip</th>
                                <th>Service Type</th>
                                <th>Dates</th>
                                <th>Status</th>
                                <th>Bookings</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trips as $trip)
                                @php
                                    $serviceTypes = collect();
                                    
                                    // Collect booking types from accommodation bookings
                                    if ($trip->accommodationBookings && $trip->accommodationBookings->isNotEmpty()) {
                                        $serviceTypes->push('Accommodation');
                                    }
                                    
                                    // Collect booking types from activity bookings
                                    if ($trip->activityBookings && $trip->activityBookings->isNotEmpty()) {
                                        $serviceTypes->push('Activity');
                                    }
                                    
                                    if ($serviceTypes->isEmpty()) {
                                        $serviceTypes->push('Travel');
                                    }
                                    
                                    $serviceTypes = $serviceTypes->unique();
                                @endphp
                                <tr>
                                    <td>
                                        <div class="trip-name-cell">
                                            <strong>Trip #100{{ $trip->id }}</strong>
                                            <span>Trip</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="service-type-badges">
                                            @foreach($serviceTypes as $type)
                                                <span class="service-badge">{{ $type }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        {{ $trip->start_date ? $trip->start_date->format('d M Y') : 'N/A' }}
                                        -
                                        {{ $trip->end_date ? $trip->end_date->format('d M Y') : 'N/A' }}
                                    </td>
                                    <td>
                                        <span class="trip-status trip-status--{{ $trip->status }}">{{ ucfirst($trip->status) }}</span>
                                    </td>
                                    <td>{{ ($trip->accommodationBookings ? $trip->accommodationBookings->count() : 0) + ($trip->activityBookings ? $trip->activityBookings->count() : 0) }}</td>
                                    <td class="trip-actions-cell">
                                        <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.detail', ['otp' => $otp, 'trip' => $trip->id]) : route('traveler.trip.detail', $trip) }}" class="btn btn-primary">Details</a>
                                        @if(!isset($guestMode) || !$guestMode)
                                            <form method="POST" action="{{ route('traveler.trip.add-service', $trip) }}" style="display:inline-block; margin-left: 8px;">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary">Add Service</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="traveler-empty-state">
                    <p>No trips found. Start planning your holiday!</p>
                    <a href="/" class="btn btn-primary">Browse Accommodations</a>
                </div>
            @endif
        </div>
    </div>
</section>

<section class="page-section traveler-trips-help-section">
    <div class="wrap">
        <div class="traveler-trips-help-card">
            <p>If you have any questions, concerns, or require assistance, please contact us at <a href="mailto:info@holidays.io">info@holidays.io</a>.</p>
        </div>
    </div>
</section>

<style>
    .traveler-trips-help-card {
        max-width: 880px;
        margin: 0 auto 30px;
        padding: 18px 22px;
        background: #f9fbff;
        border: 1px solid #dce7f5;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(17, 74, 128, 0.04);
        color: #1f3f66;
        font-size: 0.98rem;
        line-height: 1.75;
        text-align: center;
    }

    .traveler-trips-help-card a {
        color: #1659c2;
        text-decoration: none;
        font-weight: 700;
    }

    .traveler-trips-help-card a:hover {
        text-decoration: underline;
    }

    .traveler-trips-table-wrapper {
        overflow-x: auto;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 20px;
    }

    .traveler-trips-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 720px;
    }

    .traveler-trips-table th,
    .traveler-trips-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f0f0f0;
        text-align: left;
        vertical-align: middle;
    }

    .traveler-trips-table th {
        background: #faf7f2;
        color: #333;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        font-size: 0.85rem;
    }

    .trip-name-cell {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .trip-name-cell span {
        color: #777;
        font-size: 0.95rem;
    }

    .service-type-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .service-badge {
        display: inline-block;
        padding: 6px 10px;
        background: #fff4e5;
        color: #bf6d14;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .trip-status {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .trip-status--planned {
        background: #e8f4ff;
        color: #1565c0;
    }

    .trip-status--active {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .trip-status--completed {
        background: #f1f8e9;
        color: #558b2f;
    }

    .trip-status--cancelled {
        background: #ffebee;
        color: #c62828;
    }

    .trip-actions-cell {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    @media (max-width: 900px) {
        .traveler-trips-table {
            min-width: 600px;
        }
    }

    @media (max-width: 700px) {
        .traveler-trips-table,
        .traveler-trips-table thead,
        .traveler-trips-table tbody,
        .traveler-trips-table th,
        .traveler-trips-table td,
        .traveler-trips-table tr {
            display: block;
        }

        .traveler-trips-table thead {
            float: left;
        }

        .traveler-trips-table tbody {
            width: auto;
            position: relative;
            overflow-x: auto;
        }

        .traveler-trips-table tr {
            margin-bottom: 16px;
        }

        .traveler-trips-table th {
            display: none;
        }

        .traveler-trips-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 10px;
            border: none;
            border-bottom: 1px solid #f0f0f0;
        }

        .traveler-trips-table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #555;
            margin-right: 8px;
        }

        .trip-actions-cell {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endsection
