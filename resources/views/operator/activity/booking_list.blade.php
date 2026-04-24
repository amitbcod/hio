@extends('layouts.app')

@section('content')
    <div class="container mt-0">
        <div class="row">
            <div class="col-md-3 net-section">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9 my-pro">
                <div style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 40px;margin-top: 40px;">

                    {{-- Header --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                        <div>
                            <h2 style="font-weight: bold; margin-bottom: 8px;">Activity Bookings</h2>
                            <p style="color: #666; margin-bottom: 0;">View and manage all bookings for your activities</p>
                        </div>
                        <a href="{{ route('operator.activity.index') }}" class="btn" style="background: #19b5b5; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">
                            Back to Activities
                        </a>
                    </div>

                    {{-- Alerts --}}
                    @if(session('success'))
                        <div class="alert alert-success" style="margin-bottom: 20px;">{{ session('success') }}</div>
                    @endif

                    {{-- Bookings Table --}}
                    @if($bookings->isEmpty())
                        <div style="background: #f8f8f8; padding: 40px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 16px;">🎯</div>
                            <h5 style="font-weight: 600; margin-bottom: 8px;">No Bookings Yet</h5>
                            <p style="color: #666; margin-bottom: 16px;">Bookings for your activities will appear here once travelers make reservations.</p>
                        </div>
                    @else
                        <div style="overflow-x: auto;">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Activity</th>
                                        <th>Primary Name</th>
                                        <th>Participants</th>
                                        <!-- <th>Booking Date</th> -->
                                        <th>Activity Date</th>
                                        <th>Status</th>
                                        <th>Booked At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookings as $booking)
                                        <tr>
                                            <td>{{ $booking->booking_reference ?? $booking->id }}</td>
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
                                            <!-- <td>{{ $booking->booking_date ? $booking->booking_date->format('M d, Y') : 'N/A' }}</td> -->
                                            <td>{{ $booking->activity_date ? $booking->activity_date->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                <span class="badge" style="background: {{ $booking->booking_status === 'Confirmed' ? '#28a745' : ($booking->booking_status === 'Pending' ? '#ffc107' : '#dc3545') }}; color: #fff;">
                                                    {{ $booking->booking_status }}
                                                </span>
                                            </td>
                                            <td>{{ $booking->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('operator.activity.booking.details', $booking->id) }}" class="btn btn-sm" style="background: #19b5b5; color: #fff; border: none;">View Details</a>
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