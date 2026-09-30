@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Transport Booking Listings</h3>
    <p class="mb-3">Super admin view: all transport bookings.</p>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <form method="GET" action="{{ route('admin.transport.bookings') }}" class="row g-2 mb-3 align-items-end">
        <div class="col-md-3">
            <label for="filter-booking-reference" class="form-label">Booking Reference</label>
            <input id="filter-booking-reference" type="search" name="booking_reference" value="{{ $filters['booking_reference'] }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label for="filter-operator" class="form-label">Operator</label>
            <select id="filter-operator" name="operator_id" class="form-select">
                <option value="">All operators</option>
                @foreach($operators as $operator)
                    <option value="{{ $operator->id }}" @selected($filters['operator_id'] === (string) $operator->id)>
                        {{ $operator->profile?->business_legal_name ?: ($operator->email ?: 'Operator #' . $operator->id) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="filter-status" class="form-label">Status</label>
            <select id="filter-status" name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach(\App\Models\TransportBooking::STATUSES as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="filter-pickup-date" class="form-label">Pickup Date</label>
            <input id="filter-pickup-date" type="date" name="pickup_date" value="{{ $filters['pickup_date'] }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label for="filter-trip-type" class="form-label">Journey</label>
            <select id="filter-trip-type" name="trip_type" class="form-select">
                <option value="">All journeys</option>
                @foreach(['ONE_WAY' => 'One-way', 'OUTBOUND' => 'Outbound', 'RETURN' => 'Return'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['trip_type'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.transport.bookings') }}" class="btn btn-outline-secondary">Clear</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead>
                <tr>
                    <th>Ref</th>
                    <th>Journey</th>
                    <th>Operator</th>
                    <th>Vehicle</th>
                    <th>Assigned Vehicle</th>
                    <th>Driver</th>
                    <th>Guest</th>
                    <th>Passengers</th>
                    <th>Route</th>
                    <th>Pickup</th>
                    <th>Return</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Booked</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    @php
                        $transport = $booking->transport;
                        $operator = $transport?->operator;
                        $operatorName = $operator?->profile?->business_legal_name
                            ?: ($operator?->business_name ?: ($operator?->email ?: 'N/A'));
                        $vehicleName = $transport?->vehicle_display_name
                            ?: ($booking->other_vehicle_name ?: 'N/A');
                        $assignedVehicle = $booking->vehicle?->license_number
                            ?: ($booking->other_vehicle_license_number ? 'Other: ' . $booking->other_vehicle_license_number : 'Unassigned');
                        $driver = $booking->pickupDriver?->driver_name
                            ?: ($booking->driver?->driver_name ?: ($booking->currentAssignment?->driver?->driver_name ?: 'Unassigned'));
                        $guestName = $booking->guest_name
                            ?: trim(($booking->traveler_first_name ?? '') . ' ' . ($booking->traveler_last_name ?? ''));
                        $passengerCount = $booking->total_passengers
                            ?? ((int) ($booking->adults ?? 0) + (int) ($booking->children ?? 0));
                        $journeyLabel = match ($booking->trip_type) {
                            'RETURN' => 'Return',
                            'OUTBOUND' => 'Outbound',
                            default => 'One-way',
                        };
                    @endphp
                    <tr>
                        <td>{{ $booking->booking_reference ?: 'N/A' }}</td>
                        <td>{{ $journeyLabel }}</td>
                        <td>{{ $operatorName }}</td>
                        <td>
                            {{ $vehicleName }}
                            @if($transport?->vehicle_type)
                                <small class="text-muted d-block">{{ $transport->vehicle_type }}</small>
                            @endif
                        </td>
                        <td>{{ $assignedVehicle }}</td>
                        <td>{{ $driver }}</td>
                        <td>{{ $guestName !== '' ? $guestName : 'N/A' }}</td>
                        <td>{{ $passengerCount }}</td>
                        <td>{{ $booking->route_from ?: 'N/A' }} → {{ $booking->route_to ?: 'N/A' }}</td>
                        <td>{{ $booking->pickup_date?->format('M d, Y') ?: 'N/A' }} {{ $booking->pickup_time }}</td>
                        <td>{{ $booking->return_date?->format('M d, Y') ?: 'N/A' }} {{ $booking->return_time }}</td>
                        <td>{{ $booking->currency ?: 'USD' }} {{ number_format((float) ($booking->total_amount ?? 0), 2) }}</td>
                        <td>{{ $booking->booking_status ?: \App\Models\TransportBooking::STATUS_PROCESSING }}</td>
                        <td>{{ $booking->booked_at?->format('M d, Y H:i') ?: 'N/A' }}</td>
                        <td><a href="{{ route('admin.transport.booking.details', $booking->id) }}" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="15" class="text-center">No bookings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3 nav-pagination">{{ $bookings->links() }}</div>
</div>
@endsection
