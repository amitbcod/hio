@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Transport Booking Listings</h3>
    <p class="mb-3">Super admin view: all transport bookings.</p>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Ref</th>
                <th>Operator</th>
                <th>Vehicle</th>
                <th>Guest</th>
                <th>Passengers</th>
                <th>Pickup / Destination</th>
                <th>Pickup Date</th>
                <th>Status</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <td>{{ $booking->booking_reference }}</td>
                    <td>{{ optional($booking->transport->operator)->email ?? 'N/A' }}</td>
                    <td>{{ optional($booking->transport)->vehicle_name ?? 'N/A' }}</td>
                    <td>{{ $booking->guest_name ?? 'N/A' }}</td>
                    <td>{{ $booking->total_passengers ?? ($booking->adults + ($booking->children ?? 0)) }}</td>
                    <td>{{ $booking->route_from }} → {{ $booking->route_to }}</td>
                    <td>{{ optional($booking->pickup_date)->format('Y-m-d') }}</td>
                    <td>{{ $booking->booking_status }}</td>
                    <td>{{ $booking->currency }} {{ number_format($booking->total_amount ?? 0, 2) }}</td>
                    <td><a href="{{ route('admin.transport.booking.details', $booking->id) }}" class="btn btn-sm btn-primary">View</a></td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center">No bookings found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center mt-3 nav-pagination">{{ $bookings->links() }}</div>
</div>
@endsection
