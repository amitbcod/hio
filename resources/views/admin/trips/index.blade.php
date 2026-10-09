@extends('layouts.admin')

@section('content')
<div class="trips-management-page">
    <div class="">
        <div class="">
            <div class="">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="mt-4">Trips Management</h3>
                </div>
                <div class="">
                    <form method="GET" action="{{ route('admin.trips.index') }}" class="border rounded bg-light p-3 mb-3">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6 col-lg-3">
                                <label for="admin-trip-from-date" class="form-label">Trip From Date</label>
                                <input id="admin-trip-from-date" class="form-control" type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
                            </div>
                            <div class="col-12 col-sm-6 col-lg-3">
                                <label for="admin-trip-to-date" class="form-label">Trip To Date</label>
                                <input id="admin-trip-to-date" class="form-control" type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}">
                            </div>
                            <div class="col-12 col-sm-6 col-lg-3">
                                <label for="admin-trip-payment-status" class="form-label">Payment Status</label>
                                <select id="admin-trip-payment-status" class="form-select" name="payment_status">
                                    <option value="">All Payment Statuses</option>
                                    @foreach($paymentStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected(strtolower($filters['payment_status'] ?? '') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-3">
                                <label for="admin-trip-type" class="form-label">Trip Type</label>
                                <select id="admin-trip-type" class="form-select" name="trip_type">
                                    <option value="">All Trip Types</option>
                                    @foreach($tripTypes as $tripType)
                                        <option value="{{ $tripType }}" @selected(($filters['trip_type'] ?? '') === $tripType)>{{ $tripType }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-3">
                                <label for="admin-trip-traveller-information" class="form-label">Missing traveller information</label>
                                <select id="admin-trip-traveller-information" class="form-select" name="traveller_information">
                                    <option value="" @selected(empty($filters['traveller_information']))>All</option>
                                    <option value="missing" @selected(($filters['traveller_information'] ?? '') === 'missing')>Missing traveller information</option>
                                    <option value="complete" @selected(($filters['traveller_information'] ?? '') === 'complete')>Complete traveller information</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label for="admin-trip-traveller" class="form-label">Traveller</label>
                                <input id="admin-trip-traveller" class="form-control" type="search" name="traveller" value="{{ $filters['traveller'] ?? '' }}" placeholder="Search traveller name">
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label for="admin-trip-booking-reference" class="form-label">Booking References</label>
                                <input id="admin-trip-booking-reference" class="form-control" type="search" name="booking_reference" value="{{ $filters['booking_reference'] ?? '' }}" placeholder="Search booking reference">
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label for="admin-trip-search" class="form-label">Trip</label>
                                <input id="admin-trip-search" class="form-control" type="search" name="trip" value="{{ $filters['trip'] ?? '' }}" placeholder="Search trip name or ID">
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="{{ route('admin.trips.index') }}" class="btn btn-outline-secondary">Reset Filters</a>
                        </div>
                    </form>
                    <div class="table-responsive trips-table">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" style="width:48px;"><span class="visually-hidden">Expand trip details</span></th>
                                    <th>Trip</th>
                                    <th>Traveller</th>
                                    <th>Booking Status</th>
                                    <th>Payment Status</th>
                                    <th>Booking References</th>
                                    <th>Attention / Priority</th>
                                    <th>Next Action</th>
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
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#trip-{{ $trip->id }}" data-trip-toggle aria-expanded="false" aria-controls="trip-{{ $trip->id }}" aria-label="Expand trip details" title="Expand trip details" style="width:30px; height:30px; padding:0; font-size:18px; line-height:1;">+</button>
                                        </td>
                                        <td>
                                            @php
                                                $resolvedTripType = $trip->trip_type ?? 'Trip';
                                                $tripDateRange = ($trip->start_date ? $trip->start_date->format('d M Y') : 'N/A') . ' - ' . ($trip->end_date ? $trip->end_date->format('d M Y') : 'N/A');
                                            @endphp
                                            @if(in_array($resolvedTripType, ['Package Trip', 'Group Trip'], true))
                                                <div><strong>{{ $trip->title ?: 'Trip #' . $trip->id }}</strong></div>
                                                <div class="small text-muted mt-1">{{ $resolvedTripType }}</div>
                                                <div class="small text-muted mt-1">{{ $tripDateRange }}</div>
                                            @else
                                                <div><strong>Trip #{{ $trip->id }}</strong></div>
                                                <div class="small text-muted mt-1">{{ $tripDateRange }}</div>
                                            @endif
                                            <div class="small text-muted mt-1">{{ $trip->common_booking_ref_count }} Booking Refs · {{ $trip->common_booking_bli_count }} BLIs</div>
                                            @forelse($trip->total_amounts as $totalAmount)
                                                <div class="small text-muted mt-1">Total Amount {{ $totalAmount['currency'] }}{{ $totalAmount['amount'] }}</div>
                                            @empty
                                                <div class="small text-muted mt-1">Total Amount USD0</div>
                                            @endforelse
                                        </td>
                                        <td>
                                            <div>{{ $trip->traveller_display_name }}</div>
                                            <div class="small text-muted mt-1">Travel Party Size: {{ $trip->travel_party_size }}</div>
                                        </td>
                                        <td>
                                            <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                                @forelse($trip->booking_status_counts as $statusCount)
                                                    @include('admin.partials._status_badge', ['status' => $statusCount['status'], 'badgeLabel' => $statusCount['status'] . ': ' . $statusCount['count']])
                                                @empty
                                                    <span class="text-muted">N/A</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                                @forelse($trip->payment_status_badges as $statusBadge)
                                                    @include('admin.partials._status_badge', ['status' => $statusBadge['status']])
                                                @empty
                                                    <span class="text-muted">N/A</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td>{{ !empty($bookingReferenceCodes) ? implode(', ', $bookingReferenceCodes) : ($allServiceBookings->count() ? $allServiceBookings->count() : 0) }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.trips.update-priority', $trip) }}" class="d-inline">
                                                @csrf
                                                <div class="input-group input-group-sm" style="max-width: 180px;">
                                                    <select name="priority" class="form-select form-select-sm" aria-label="Trip priority" onchange="this.form.requestSubmit()">
                                                        @foreach(App\Models\Trip::priorityOptions() as $value => $label)
                                                            <option value="{{ $value }}" @selected(strtolower((string) ($trip->priority ?? 'normal')) === $value)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            @if(empty($tripNextActions))
                                                No Action Required
                                            @else
                                                @foreach($tripNextActions as $action)
                                                    <div>{{ $action['label'] ?? 'No Action Required' }}</div>
                                                @endforeach
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="p-0 border-top-0">
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
                                                        <div class="col-md-3"><strong>Payment:</strong> @include('admin.partials._status_badge', ['status' => $trip->payment_status ?? 'pending', 'badgeLabel' => ucfirst($trip->payment_status ?? 'pending')])</div>
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
                                                                            $nextStep = strtolower((string) $trip->payment_status) !== 'paid'
                                                                                ? 'Awaiting Payment'
                                                                                : ((string) ($booking->booking_status ?? '') !== 'Confirmed'
                                                                                    ? 'Mark as Confirmed'
                                                                                    : ($bookingType === 'transport' && ! $booking->hasCompleteAssignment()
                                                                                        ? 'Assign Driver & Vehicle'
                                                                                        : 'No Action Required'));
                                                                            $participantNameList = $booking->guests
                                                                                ->map(fn ($guest) => trim(implode(' ', array_filter([
                                                                                    $guest->first_name,
                                                                                    $guest->middle_name,
                                                                                    $guest->last_name,
                                                                                ]))))
                                                                                ->push($booking->guest_name);
                                                                            if ($bookingType === 'transport') {
                                                                                $participantNameList->push(trim(implode(' ', array_filter([
                                                                                    $booking->traveler_first_name,
                                                                                    $booking->traveler_middle_name,
                                                                                    $booking->traveler_last_name,
                                                                                ]))));
                                                                                foreach ($trip->bookings as $tripBooking) {
                                                                                    foreach ($tripBooking->lineItems as $lineItem) {
                                                                                        if ((int) ($lineItem->transport_booking_id ?? 0) === (int) $booking->id
                                                                                            && $lineItem->relationLoaded('travellers')) {
                                                                                            $participantNameList = $participantNameList->concat($lineItem->travellers->pluck('name'));
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                            $guestNames = $participantNameList
                                                                                ->filter(fn ($name) => is_string($name) && trim($name) !== '')
                                                                                ->map(fn ($name) => trim($name))
                                                                                ->unique()
                                                                                ->implode(', ');
                                                                            $participantCounts = 'Adults: ' . (int) ($booking->adults ?? 0)
                                                                                . ' · Children: ' . (int) ($booking->children ?? 0);
                                                                            $serviceMeta = match ($bookingType) {
                                                                                'accommodation' => [
                                                                                    'Property' => optional($booking->accommodation)->property_name ?? 'N/A',
                                                                                    'Room' => optional($booking->room)->room_name ?? optional($booking->room)->name ?? 'N/A',
                                                                                    'Check-in' => $booking->check_in_date ? $booking->check_in_date->format('d/m/Y') : 'N/A',
                                                                                    'Check-out' => $booking->check_out_date ? $booking->check_out_date->format('d/m/Y') : 'N/A',
                                                                                    'Rooms' => (int) ($booking->rooms_booked ?? 0),
                                                                                    'Participants' => $participantCounts . ' · Total: ' . ((int) ($booking->adults ?? 0) + (int) ($booking->children ?? 0)),
                                                                                    'Participant Names' => $guestNames !== '' ? $guestNames : ($booking->guest_name ?? 'N/A'),
                                                                                ],
                                                                                'activity' => (function () use ($booking, $guestNames, $participantCounts) {
                                                                                    $slot = optional($booking->activity)->schedulingTimeSlots
                                                                                        ?->firstWhere('timeslot_id', $booking->activity_time_slot_id);
                                                                                    $infants = (int) ($booking->infants ?? 0);
                                                                                    $counts = $participantCounts . ($infants > 0 ? ' · Infants: ' . $infants : '');

                                                                                    return [
                                                                                        'Activity' => optional($booking->activity)->activity_name ?? 'N/A',
                                                                                        'Date' => $booking->activity_date ? $booking->activity_date->format('d/m/Y') : 'N/A',
                                                                                        'Time' => $slot ? trim(($slot->start_time ?? '') . ' - ' . ($slot->end_time ?? '')) : 'N/A',
                                                                                        'Participants' => $counts . ' · Total: ' . ((int) ($booking->adults ?? 0) + (int) ($booking->children ?? 0) + $infants),
                                                                                        'Participant Names' => $guestNames !== '' ? $guestNames : ($booking->guest_name ?? 'N/A'),
                                                                                    ];
                                                                                })(),
                                                                                'transport' => [
                                                                                    'Route' => trim(($booking->route_from ?? '') . ' → ' . ($booking->route_to ?? '')),
                                                                                    'Vehicle' => optional($booking->transport)->vehicle_display_name ?? 'N/A',
                                                                                    'Pickup' => trim(($booking->pickup_date ? $booking->pickup_date->format('d/m/Y') : 'N/A') . ' ' . ($booking->pickup_time ?? '')),
                                                                                    'Passengers' => (int) ($booking->total_passengers ?? (($booking->adults ?? 0) + ($booking->children ?? 0))),
                                                                                    'Passenger Categories' => $participantCounts,
                                                                                    'Passenger Names' => $guestNames !== '' ? $guestNames : (trim(($booking->traveler_first_name ?? '') . ' ' . ($booking->traveler_middle_name ?? '') . ' ' . ($booking->traveler_last_name ?? '')) ?: ($booking->guest_name ?? 'N/A')),
                                                                                    ...($booking->hasVehicleAssignment() ? ['Vehicles' => 1] : []),
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
                                                                                <div class="col-md-6"><strong>BLI Id:</strong> {{ $bookingRefValue }}</div>
                                                                                <div class="col-md-6"><strong>Status:</strong> @include('admin.partials._status_badge', ['status' => $bookingStatus, 'badgeLabel' => $bookingStatus])</div>
                                                                                @foreach($serviceMeta as $label => $value)
                                                                                    <div class="col-md-6"><strong>{{ $label }}:</strong> {{ $value }}</div>
                                                                                @endforeach
                                                                                <div class="col-12"><strong>Next Step:</strong> {{ $nextStep }}</div>
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
                                        <td colspan="11" class="text-center text-muted">No trips found.</td>
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

@push('scripts')
<script>
    document.querySelectorAll('[data-trip-toggle]').forEach((button) => {
        const panel = document.getElementById(button.getAttribute('aria-controls'));

        panel.addEventListener('shown.bs.collapse', () => {
            button.textContent = '-';
            button.setAttribute('aria-label', 'Collapse trip details');
            button.title = 'Collapse trip details';
        });

        panel.addEventListener('hidden.bs.collapse', () => {
            button.textContent = '+';
            button.setAttribute('aria-label', 'Expand trip details');
            button.title = 'Expand trip details';
        });
    });
</script>
@endpush
