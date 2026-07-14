@extends('layouts.app')

@section('title', 'All Transport Bookings | Operator')

@section('content')
<div class="container mt-0">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @include('operator.registration._sidebar_main')
        </div>
        <div class="col-md-9 my-pro">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;margin-top:40px;">
                <h2 style="margin:0;font-weight:700;">All Transport Bookings</h2>
                <p style="margin:6px 0 0 0;color:#666;">Bookings across all your transport services.</p>
            </div>

            @if($bookings->isEmpty())
                <div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
                    <div class="alert" style="background:transparent;color:#666;margin:0;">No bookings found.</div>
                </div>
            @else
                <div style="background:#fff;border-radius:12px;padding:12px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>Service</th>
                                    <th>Guest</th>
                                    <th>Passengers</th>
                                    <th>Pickup</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Booked</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bookings as $booking)
                                    <tr>
                                        <td>{{ $booking->booking_reference }}</td>
                                        <td>{{ optional($booking->transport)->vehicle_name }}</td>
                                        <td>{{ $booking->guest_name ?? ($booking->traveler_first_name.' '.$booking->traveler_last_name) }}</td>
                                        <td>{{ $booking->total_passengers ?? $booking->adults }}</td>
                                        <td>{{ optional($booking->pickup_date)->format('M d, Y') }} {{ $booking->pickup_time }}</td>
                                        <td>{{ $booking->currency ?? 'USD' }} {{ number_format($booking->total_amount, 2) }}</td>
                                        <td>{{ ucfirst($booking->booking_status ?? 'pending') }}</td>
                                        <td>{{ optional($booking->booked_at)->format('M d, Y H:i') }}</td>
                                        <td><a href="{{ route('operator.transport.booking.details', [$booking->transport_id, $booking->id]) }}" class="btn btn-sm btn-primary">Details</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $bookings->links() }}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
