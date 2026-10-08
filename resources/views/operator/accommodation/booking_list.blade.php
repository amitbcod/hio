@extends('layouts.app')

@section('content')
    <div class="container mt-0">
        <div class="row">
            <div class="col-md-3 net-section">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9 my-pro div-box">
                <div class="container-middle">
                    
                    {{-- Header --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                        <div>
                            <h2 style="font-weight: bold; margin-bottom: 8px;">Accommodation Bookings</h2>
                            <p style="color: #666; margin-bottom: 0;">View and manage all bookings for your properties</p>
                        </div>
                        <a href="{{ route('operator.accommodation.index') }}" class="btn btn-outline-blue" style="">
                            Back to Properties
                        </a>
                    </div>

                    {{-- Alerts --}}
                    @if(session('success'))
                        <div class="alert alert-success" style="margin-bottom: 20px;">{{ session('success') }}</div>
                    @endif

                    {{-- Bookings Table --}}
                    @if($bookings->isEmpty())
                        <div style="background: #f8f8f8; padding: 40px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 16px;">📅</div>
                            <h5 style="font-weight: 600; margin-bottom: 8px;">No Bookings Yet</h5>
                            <p style="color: #666; margin-bottom: 16px;">Bookings for your properties will appear here once travelers make reservations.</p>
                        </div>
                    @else
                        <div style="overflow-x: auto;">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Booking Reference</th>
                                        <th>Property</th>
                                        <th>Guest</th>
                                        <th>Guests</th>
                                        <th>Room</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Status</th>
                                        <th>Booked At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookings as $booking)
                                        <tr>
                                            <td>
                                                <div>{{ $booking->booking_reference ?? $booking->id }}</div>
                                                <small class="text-muted">Common Booking: {{ $booking->bookingRef?->booking_ref_code ?? 'N/A' }}</small>
                                            </td>
                                            <td>{{ $booking->accommodation->property_name }}</td>
                                            <td>{{ $booking->guest_name ?? 'N/A' }}</td>
                                            <td>
                                               {{ $booking->adults + $booking->children + ($booking->infants ?? 0) }}
                                                @if($booking->infants > 0)
                                                    ({{ $booking->adults }} A, {{ $booking->children }} C, {{ $booking->infants }} I)
                                                @elseif($booking->children > 0)
                                                    ({{ $booking->adults }} A, {{ $booking->children }} C)
                                                @else
                                                    ({{ $booking->adults }} A)
                                                @endif
                                            </td>
                                            <td>{{ $booking->room->room_name ?? 'N/A' }}</td>
                                            <td>{{ $booking->check_in_date->format('M d, Y') }}</td>
                                            <td>{{ $booking->check_out_date->format('M d, Y') }}</td>
                                            <td>
                                                @include('operator.partials._status_badge', ['status' => $booking->booking_status])
                                            </td>
                                            <td>{{ $booking->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('operator.accommodation.booking.details', $booking->id) }}" class="btn btn-sm btn-secondary" style="">View Details</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div style="margin-top: 20px;">
                            {{ $bookings->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection