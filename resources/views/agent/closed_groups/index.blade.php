@extends('layouts.agent')

@section('title', 'Group Bookings | Holidays.io')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Group Bookings</h1>
        <p class="text-muted mb-0">Bookings created by {{ $agent->agency_name }}.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('agent.closed-groups.book') }}">Create Group Booking</a>
</div>

<div class="card">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Group / Trip</th>
                <th>Traveller</th>
                <th>Booking Reference</th>
                <th>Admin Review</th>
                <th>Payment</th>
                <th>Booking Status</th>
                <th class="text-end">Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
                @php($lead = $booking->trip?->travellers?->firstWhere('relationship', 'lead'))
                <tr>
                    <td>{{ $booking->trip?->title ?? 'Group booking' }}</td>
                    <td>{{ $lead?->name ?? 'Traveller details unavailable' }}</td>
                    <td>{{ $booking->bookingRef?->booking_ref_code ?? 'Pending reference' }}</td>
                    <td>
                        @if($booking->agent_verification_status === 'verified')
                            <span class="badge bg-success">Approved</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending Admin Review</span>
                        @endif
                    </td>
                    <td>
                        @php($payment = $booking->payments->firstWhere('booking_ref_id', $booking->booking_ref_id))
                        @if($payment?->settlement_status === 'verified_settled')
                            <span class="badge bg-success">Settled</span>
                        @elseif($payment?->bank_transfer_receipt_path)
                            <span class="badge bg-info text-dark">Proof Submitted</span>
                        @else
                            <span class="badge bg-warning text-dark">Awaiting Proof</span>
                        @endif
                    </td>
                    <td>{{ ucfirst($booking->status ?? 'pending') }}</td>
                    <td class="text-end">USD {{ number_format((float) $booking->total_amount, 2) }}</td>
                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('agent.closed-groups.show', $booking->id) }}">View</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No group bookings yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $bookings->links('pagination::bootstrap-5') }}</div>
@endsection
