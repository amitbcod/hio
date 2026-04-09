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
                                    foreach ($trip->bookings as $booking) {
                                        foreach ($booking->lineItems as $item) {
                                            if (! empty($item->service_type)) {
                                                $serviceTypes->push($item->service_type);
                                            }
                                        }
                                    }
                                    $serviceTypes = $serviceTypes->unique()->map(fn($type) => ucfirst(strtolower($type)));
                                    $serviceTypeLabels = collect();
                                    if ($serviceTypes->contains(fn($type) => str_contains(strtolower($type), 'accommodation'))) {
                                        $serviceTypeLabels->push('Accommodation');
                                    }
                                    if ($serviceTypes->contains(fn($type) => str_contains(strtolower($type), 'activity'))) {
                                        $serviceTypeLabels->push('Activity');
                                    }
                                    if ($serviceTypeLabels->isEmpty()) {
                                        $serviceTypeLabels->push('Travel');
                                    }
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
                                            @foreach($serviceTypeLabels as $type)
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
                                    <td>{{ $trip->bookings->count() }}</td>
                                    <td class="trip-actions-cell">
                                        <a href="{{ route('traveler.trip.detail', $trip) }}" class="btn btn-primary">Details</a>
                                        <form method="POST" action="{{ route('traveler.trip.add-service', $trip) }}" style="display:inline-block; margin-left: 8px;">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary">Add Service</button>
                                        </form>
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

<style>
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
