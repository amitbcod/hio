@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Activity Booking Listings</h3>
    <p class="mb-3">Super admin view: all operator activity bookings.</p>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Ref</th>
                <th>Operator Email</th>
                <th>Company</th>
                <th>Activity</th>
                <th>Guest</th>
                <th>Participants</th>
                <!-- <th>Booking Date</th> -->
                <th>Activity Date</th>
                <th>Status</th>
                <th>Booked At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <td>{{ $booking->booking_reference }}</td>
                    <td>{{ optional($booking->activity->operator)->email ?? 'N/A' }}</td>
                    <td>{{ optional(optional($booking->activity->operator)->business)->legal_name ?? 'N/A' }}</td>
                    <td>{{ optional($booking->activity)->activity_name ?? 'N/A' }}</td>
                    <td>{{ $booking->guest_name ?? 'N/A' }}</td>
                    <td>
                        Total Participants: {{ $booking->adults + $booking->children }}
                        @if($booking->children > 0)
                            ({{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }}, {{ $booking->children }} Child{{ $booking->children > 1 ? 'ren' : '' }})
                        @else
                            ({{ $booking->adults }} Adult{{ $booking->adults > 1 ? 's' : '' }})
                        @endif
                    </td>
                    <!-- <td>{{ optional($booking->booking_date)->format('Y-m-d') }}</td> -->
                    <td>{{ optional($booking->activity_date)->format('Y-m-d') }}</td>
                    <td>{{ $booking->booking_status }}</td>
                    <td>{{ optional($booking->created_at)->format('Y-m-d H:i') }}</td>
                    <td><a href="{{ route('admin.activity.booking.details', $booking->id) }}" class="btn btn-sm btn-primary">View</a></td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center">No bookings found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center mt-3">{{ $bookings->links() }}</div>
</div>
@endsection