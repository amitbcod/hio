@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Accommodation Booking Listings</h3>
    <p class="mb-3">Super admin view: all operator bookings.</p>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Ref</th>
                <th>Operator</th>
                <th>Property</th>
                <th>Guest</th>
                <th>Guests</th>
                <th>Room</th>
                <th>Dates</th>
                <th>Status</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <td>{{ $booking->booking_reference }}</td>
                    <td>{{ optional($booking->accommodation->operator)->full_name ?? 'N/A' }} {{ optional($booking->accommodation->operator)->email ?? 'N/A' }}</td>
                    <td>{{ optional($booking->accommodation)->property_name ?? 'N/A' }}</td>
                    <td>{{ $booking->guest_name ?? 'N/A' }}</td>
                    <td>
                        Total Guests: {{ $booking->adults + $booking->children }}
                        @if($booking->children > 0)
                            ({{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }}, {{ $booking->children }} Child{{ $booking->children > 1 ? 'ren' : '' }})
                        @else
                            ({{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }})
                        @endif
                    </td>
                    <td>{{ optional($booking->room)->name ?? 'N/A' }}</td>
                    <td>{{ optional($booking->check_in_date)->format('Y-m-d') }} - {{ optional($booking->check_out_date)->format('Y-m-d') }}</td>
                    <td>{{ $booking->booking_status }}</td>
                    <td>{{ $booking->currency }} {{ number_format($booking->total_amount ?? 0, 2) }}</td>
                    <td><a href="{{ route('admin.accommodation.booking.details', $booking->id) }}" class="btn btn-sm btn-primary">View</a></td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center">No bookings found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center mt-3 nav-pagination">{{ $bookings->links() }}</div>
</div>
@endsection
