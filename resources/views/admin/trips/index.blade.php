@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Trips Management</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Trip ID</th>
                                    <th>Trip</th>
                                    <th>Trip Type</th>
                                    <th>Traveller</th>
                                    <th>Dates</th>
                                    <th>Status</th>
                                    <th>Booking References</th>
                                    <th>Next Action</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trips as $trip)
                                    @php
                                        $tripLabel = $trip->title ?: 'Trip #' . $trip->id;
                                        $allServiceBookings = collect()
                                            ->merge($trip->accommodationBookings->map(fn ($booking) => ['type' => 'accommodation', 'booking' => $booking]))
                                            ->merge($trip->activityBookings->map(fn ($booking) => ['type' => 'activity', 'booking' => $booking]))
                                            ->merge($trip->transportBookings->map(fn ($booking) => ['type' => 'transport', 'booking' => $booking]));

                                        $groupedServiceBookings = $allServiceBookings->groupBy(function ($item) {
                                            $booking = $item['booking'];
                                            $refCode = $booking->booking_ref_id
                                                ? ($booking->bookingRef?->booking_ref_code ?? null)
                                                : null;

                                            return $refCode ?: 'NO_COMMON_BOOKING_REFERENCE';
                                        });

                                        $bookingReferenceCodes = $trip->bookingRefs
                                            ->pluck('booking_ref_code')
                                            ->filter()
                                            ->values()
                                            ->all();

                                        $tripNextActions = $trip->next_actions ?? [];
                                    @endphp
                                    <tr>
                                        <td>{{ $trip->id }}</td>
                                        <td>{{ $tripLabel }}</td>
                                        <td>{{ $trip->trip_type ?? 'Trip' }}</td>
                                        <td>{{ optional($trip->traveler)->full_name ?? optional($trip->traveler)->email ?? 'N/A' }}</td>
                                        <td>{{ $trip->start_date ? $trip->start_date->format('d/m/Y') : 'N/A' }} - {{ $trip->end_date ? $trip->end_date->format('d/m/Y') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $trip->status ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ !empty($bookingReferenceCodes) ? implode(', ', $bookingReferenceCodes) : ($allServiceBookings->count() ? $allServiceBookings->count() : 0) }}</td>
                                        <td>
                                            @if(empty($tripNextActions))
                                                <span class="text-muted">No Action Required</span>
                                            @else
                                                @foreach($tripNextActions as $action)
                                                    @php $actionType = $action['type'] ?? 'status'; @endphp
                                                    @if($actionType === 'confirm')
                                                        <form method="POST" action="{{ $action['url'] ?? '#' }}" class="d-inline-block mb-1">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success">{{ $action['label'] }}</button>
                                                        </form>
                                                    @elseif($actionType === 'assign')
                                                        <a href="{{ $action['url'] ?? '#' }}" class="btn btn-sm btn-warning mb-1">{{ $action['label'] }}</a>
                                                    @else
                                                        <div class="small {{ $actionType === 'payment' ? 'text-warning' : 'text-muted' }}">{{ $action['label'] }}</div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#trip-{{ $trip->id }}" aria-expanded="false" aria-controls="trip-{{ $trip->id }}">
                                                Expand
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="p-0 border-top-0">
                                            <div id="trip-{{ $trip->id }}" class="collapse">
                                                <div class="p-3 bg-light">
                                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                                        <div>
                                                            <strong>{{ $tripLabel }}</strong>
                                                            <div class="small text-muted">Traveller: {{ optional($trip->traveler)->full_name ?? optional($trip->traveler)->email ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="small text-muted">{{ $trip->start_date ? $trip->start_date->format('d/m/Y') : 'N/A' }} - {{ $trip->end_date ? $trip->end_date->format('d/m/Y') : 'N/A' }}</div>
                                                    </div>

                                                    <div class="row g-3 small mb-3">
                                                        <div class="col-md-3"><strong>Trip Type:</strong> {{ $trip->trip_type ?? 'Trip' }}</div>
                                                        <div class="col-md-3"><strong>Payment:</strong> {{ ucfirst($trip->payment_status ?? 'pending') }}</div>
                                                        <div class="col-md-3"><strong>Next Action:</strong> {{ empty($tripNextActions) ? 'No Action Required' : ($tripNextActions[0]['label'] ?? 'No Action Required') }}</div>
                                                        <div class="col-md-3"><strong>Authority:</strong> {{ optional($trip->traveler)->full_name ?? optional($trip->traveler)->email ?? 'N/A' }}</div>
                                                    </div>

                                                    @if($groupedServiceBookings->isEmpty())
                                                        <div class="text-muted">No service bookings found for this trip.</div>
                                                    @else
                                                        @foreach($groupedServiceBookings as $groupKey => $groupItems)
                                                            @php
                                                                $groupLabel = $groupKey === 'NO_COMMON_BOOKING_REFERENCE' ? 'No common booking reference' : $groupKey;
                                                            @endphp
                                                            <div class="mb-3 border rounded bg-white">
                                                                <div class="px-3 py-2 border-bottom bg-light">
                                                                    <strong>Booking Reference:</strong> {{ $groupLabel }}
                                                                </div>
                                                                <div class="p-3">
                                                                    @foreach($groupItems as $item)
                                                                        @php
                                                                            $booking = $item['booking'];
                                                                            $bookingType = $item['type'];
                                                                            $bookingRefValue = $booking->booking_reference ?? $booking->bookingRef?->booking_ref_code ?? 'N/A';
                                                                            $bookingStatus = $booking->booking_status ?? 'N/A';
                                                                            $detailRoute = match ($bookingType) {
                                                                                'accommodation' => route('admin.accommodation.booking.details', $booking->id),
                                                                                'activity' => route('admin.activity.booking.details', $booking->id),
                                                                                'transport' => route('admin.transport.booking.details', $booking->id),
                                                                                default => '#',
                                                                            };
                                                                            $serviceLabel = match ($bookingType) {
                                                                                'accommodation' => 'Accommodation',
                                                                                'activity' => 'Activity',
                                                                                'transport' => 'Transport',
                                                                                default => 'Booking',
                                                                            };
                                                                            $serviceMeta = match ($bookingType) {
                                                                                'accommodation' => [
                                                                                    'Property' => optional($booking->accommodation)->property_name ?? 'N/A',
                                                                                    'Room' => optional($booking->room)->name ?? 'N/A',
                                                                                    'Check-in' => $booking->check_in_date ? $booking->check_in_date->format('d/m/Y') : 'N/A',
                                                                                    'Check-out' => $booking->check_out_date ? $booking->check_out_date->format('d/m/Y') : 'N/A',
                                                                                ],
                                                                                'activity' => [
                                                                                    'Activity' => optional($booking->activity)->activity_name ?? 'N/A',
                                                                                    'Date' => $booking->activity_date ? $booking->activity_date->format('d/m/Y') : 'N/A',
                                                                                    'Participants' => $booking->adults + $booking->children,
                                                                                ],
                                                                                'transport' => [
                                                                                    'Route' => trim(($booking->route_from ?? '') . ' → ' . ($booking->route_to ?? '')),
                                                                                    'Vehicle' => optional($booking->transport)->vehicle_display_name ?? 'N/A',
                                                                                    'Pickup' => $booking->pickup_date ? $booking->pickup_date->format('d/m/Y') : 'N/A',
                                                                                    'Passengers' => (int) ($booking->total_passengers ?? (($booking->adults ?? 0) + ($booking->children ?? 0))),
                                                                                ],
                                                                                default => [],
                                                                            };
                                                                        @endphp
                                                                        <div class="border rounded p-3 mb-3">
                                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                                <div>
                                                                                    <div class="small text-uppercase text-muted">BLI / Booking</div>
                                                                                    <div class="fw-semibold">{{ $serviceLabel }}</div>
                                                                                </div>
                                                                                <a href="{{ $detailRoute }}" class="btn btn-sm btn-primary">View Details</a>
                                                                            </div>
                                                                            <div class="row g-3 small">
                                                                                <div class="col-md-6"><strong>Booking Ref:</strong> {{ $bookingRefValue }}</div>
                                                                                <div class="col-md-6"><strong>Status:</strong> {{ $bookingStatus }}</div>
                                                                                @foreach($serviceMeta as $label => $value)
                                                                                    <div class="col-md-6"><strong>{{ $label }}:</strong> {{ $value }}</div>
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No trips found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $trips->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
