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
                        <table class="table table-bordered align-middle mb-0 admin-trip-listing">
                            <colgroup>
                                <col style="width:4%">
                                <col style="width:17%">
                                <col style="width:11%">
                                <col style="width:14%">
                                <col style="width:11%">
                                <col style="width:7%">
                                <col style="width:10%">
                                <col style="width:10%">
                                <col style="width:16%">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th scope="col"><span class="visually-hidden">Expand trip details</span></th>
                                    <th scope="col">Trip</th>
                                    <th scope="col">Travel / next service</th>
                                    <th scope="col">Authority and Travel Party</th>
                                    <th scope="col">Booking</th>
                                    <th scope="col">Payment</th>
                                    <th scope="col">Attention / Priority</th>
                                    <th scope="col">Next Action</th>
                                    <th scope="col">Due / Overdue</th>
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
                                        $tripPendingActions = collect($trip->operations_overview['pending_actions'] ?? []);
                                        $tripDueActions = collect($trip->due_actions ?? [])
                                            ->where('status', 'due')
                                            ->values();
                                        $tripParticipants = $allServiceBookings
                                            ->flatMap(fn ($item) => $item['booking']->participant_names ?? [])
                                            ->filter()
                                            ->unique()
                                            ->values();
                                        $accountHolderName = trim((string) $trip->traveller_display_name);
                                        $tripParticipantsForDisplay = $tripParticipants
                                            ->reject(fn ($participantName) => $accountHolderName !== ''
                                                && $accountHolderName !== 'Unknown Customer'
                                                && strcasecmp(trim((string) $participantName), $accountHolderName) === 0)
                                            ->values();
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#trip-{{ $trip->id }}" data-trip-toggle aria-expanded="false" aria-controls="trip-{{ $trip->id }}" aria-label="Expand trip details" title="Expand trip details" style="width:30px; height:30px; padding:0; font-size:18px; line-height:1;">+</button>
                                        </td>
                                        <td>
                                            @php
                                                $resolvedTripType = $trip->trip_type ?? 'Trip';
                                                $travelDates = $trip->operations_overview['travel_dates'] ?? [];
                                                $tripStartDate = $trip->start_date?->format('d M Y') ?? ($travelDates['start'] ?? 'N/A');
                                                $tripEndDate = $trip->end_date?->format('d M Y') ?? ($travelDates['end'] ?? 'N/A');
                                                $tripDateRange = $tripStartDate . ' - ' . $tripEndDate;
                                            @endphp
                                            @if(in_array($resolvedTripType, ['Package Trip', 'Group Trip'], true))
                                                <div><strong>{{ $trip->title ?: 'Trip #' . $trip->id }}</strong></div>
                                                <div class="small text-muted mt-1">Trip #{{ $trip->id }}</div>
                                                <div class="small text-muted mt-1">{{ $resolvedTripType }}</div>
                                                <div class="small text-muted mt-1">{{ $tripDateRange }}</div>
                                            @else
                                                <div><strong>Trip #{{ $trip->id }}</strong></div>
                                                <div class="small text-muted mt-1">{{ $tripDateRange }}</div>
                                            @endif
                                            @forelse($trip->total_amounts as $totalAmount)
                                                <div class="small text-muted mt-1">Total Amount {{ $totalAmount['currency'] }}{{ $totalAmount['amount'] }}</div>
                                            @empty
                                                <div class="small text-muted mt-1">Total Amount USD0</div>
                                            @endforelse
                                        </td>
                                        <td>
                                            <div class="small text-muted">{{ $tripStartDate }} – {{ $tripEndDate }}</div>
                                            @if(!empty($trip->operations_overview['next_service']))
                                                <div class="fw-semibold mt-1">{{ $trip->operations_overview['next_service']['name'] }}</div>
                                                <div class="small text-muted">{{ $trip->operations_overview['next_service']['when'] }}</div>
                                            @else
                                                <div class="small text-muted mt-1">No upcoming service</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div><strong>Booking Authority / Account Holder:</strong> {{ $trip->traveller_display_name }}</div>
                                            <div class="small text-muted mt-1">Travel Party Size: {{ $trip->travel_party_size }}</div>
                                            <div class="small mt-2"><strong>Travellers:</strong></div>
                                            <div class="admin-trip-participants">
                                                @forelse($tripParticipantsForDisplay as $participantName)
                                                    <div>{{ $participantName }}</div>
                                                @empty
                                                    <span class="text-muted">N/A</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small mb-1">
                                                <strong>References:</strong>
                                                {{ !empty($bookingReferenceCodes) ? implode(', ', $bookingReferenceCodes) : ($allServiceBookings->count() ? $allServiceBookings->count() : 0) }}
                                            </div>
                                            @if($trip->listing_booking_reference?->created_at)
                                                <div class="small text-muted mb-1">{{ $trip->listing_booking_reference->created_at->timezone(config('app.timezone'))->format('d M Y, H:i') }}</div>
                                            @endif
                                            <div class="small text-muted mb-1">{{ $trip->common_booking_ref_count }} Booking Refs · {{ $trip->common_booking_bli_count }} BLIs</div>
                                            <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                                @forelse($trip->booking_status_counts as $statusCount)
                                                    @include('admin.partials._status_badge', ['status' => $statusCount['status'], 'badgeLabel' => $statusCount['status'] . ': ' . $statusCount['count']])
                                                @empty
                                                    <span class="text-muted">N/A</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="admin-trip-payment">
                                            <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                                @forelse($trip->payment_status_badges as $statusBadge)
                                                    @include('admin.partials._status_badge', ['status' => $statusBadge['status']])
                                                @empty
                                                    <span class="text-muted">N/A</span>
                                                @endforelse
                                            </div>
                                        </td>
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
                                            <button type="button" class="btn btn-link btn-sm px-0 mt-1" data-pending-actions-trigger aria-haspopup="dialog" aria-controls="pending-actions-{{ $trip->id }}">
                                                View Pending Actions
                                            </button>
                                            <dialog id="pending-actions-{{ $trip->id }}" aria-labelledby="pending-actions-title-{{ $trip->id }}" style="width:min(42rem, calc(100% - 2rem)); max-height:80vh; padding:0; border:1px solid #cbd5e1; border-radius:10px; box-shadow:0 20px 50px rgba(15,23,42,.25);">
                                                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                                    <h2 id="pending-actions-title-{{ $trip->id }}" class="h5 mb-0">Pending Actions — {{ $tripLabel }}</h2>
                                                    <form method="dialog">
                                                        <button type="submit" class="btn-close" autofocus aria-label="Close pending actions"></button>
                                                    </form>
                                                </div>
                                                <div class="p-3" style="overflow-y:auto; max-height:65vh;">
                                                    @forelse($tripPendingActions as $pendingAction)
                                                        <article class="border rounded p-3 mb-2">
                                                            <div class="fw-semibold">{{ $pendingAction['label'] }}</div>
                                                            <div class="small text-muted mt-1">
                                                                {{ ucfirst($pendingAction['service_type']) }} booking
                                                                {{ $pendingAction['booking_reference'] ?: '#' . $pendingAction['booking_id'] }}
                                                                @if($pendingAction['booking_status'])
                                                                    · {{ $pendingAction['booking_status'] }}
                                                                @endif
                                                            </div>
                                                            @if($pendingAction['service_date'])
                                                                <div class="small mt-1">Service date: {{ $pendingAction['service_date'] }}</div>
                                                            @endif
                                                            @if($pendingAction['url'])
                                                                <a class="btn btn-sm btn-outline-primary mt-2" href="{{ $pendingAction['url'] }}">View booking</a>
                                                            @endif
                                                        </article>
                                                    @empty
                                                        <p class="mb-0 text-muted">No pending actions for this trip.</p>
                                                    @endforelse
                                                </div>
                                            </dialog>
                                        </td>
                                        <td>
                                            @if(empty($tripNextActions))
                                                No Action Required
                                            @else
                                                @foreach($tripNextActions as $action)
                                                    <div>{{ $action['label'] ?? 'No Action Required' }}</div>
                                                    @if(!empty($action['record_id']))
                                                        <!-- <form method="POST" action="{{ route('admin.trips.action-deadline.update', ['trip' => $trip->id, 'actionDeadline' => $action['record_id']]) }}" class="d-flex gap-1 align-items-center my-1">
                                                            @csrf
                                                            <label class="visually-hidden" for="deadline-override-{{ $action['record_id'] }}">Hours before target</label>
                                                            <input id="deadline-override-{{ $action['record_id'] }}" class="form-control form-control-sm" style="max-width:100px;" type="number" min="0" max="8760" name="override_hours" value="{{ $action['override_hours'] ?? '' }}" placeholder="Default" aria-label="Override hours before target">
                                                            <button class="btn btn-sm btn-outline-primary" type="submit">Save deadline</button>
                                                        </form> -->
                                                    @endif
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @if($tripDueActions->isEmpty())
                                                <span class="text-muted">N/A</span>
                                            @else
                                                @php
                                                    $firstDueAction = $tripDueActions->first();
                                                    $additionalDueActions = $tripDueActions->skip(1);
                                                @endphp
                                                <div class="mb-2">
                                                    <div><span class="badge rounded-pill" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;">DUE</span> — {{ $firstDueAction['action_name'] }}</div>
                                                    <div class="small">Deadline: {{ $firstDueAction['target_display'] }}</div>
                                                    <div class="small">Due since: {{ $firstDueAction['due_since_display'] }}</div>
                                                </div>
                                                @if($additionalDueActions->isNotEmpty())
                                                    <div id="additional-due-actions-{{ $trip->id }}" hidden>
                                                        @foreach($additionalDueActions as $action)
                                                            <div class="mb-2">
                                                                <div><span class="badge rounded-pill" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;">DUE</span> — {{ $action['action_name'] }}</div>
                                                                <div class="small">Deadline: {{ $action['target_display'] }}</div>
                                                                <div class="small">Due since: {{ $action['due_since_display'] }}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <button type="button" class="btn btn-link btn-sm p-0" data-due-actions-toggle aria-expanded="false" aria-controls="additional-due-actions-{{ $trip->id }}">
                                                        + {{ $additionalDueActions->count() }} more
                                                    </button>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="p-0 border-top-0">
                                            <div id="trip-{{ $trip->id }}" class="collapse">
                                                <div class="p-3 bg-light">
                                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                                        <div>
                                                            <strong>{{ $tripLabel }}</strong>
                                                            <div class="small text-muted">Trip ID: {{ $trip->getKey() }}</div>
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
                                                                            $guestNames = implode(', ', $booking->participant_names ?? []);
                                                                            $guestNamesDisplay = $guestNames !== '' ? $guestNames : 'Not provided';
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
                                                                                    'Participant Names' => $guestNamesDisplay,
                                                                                ],
                                                                                'activity' => (function () use ($booking, $guestNamesDisplay, $participantCounts) {
                                                                                    $slot = optional($booking->activity)->schedulingTimeSlots
                                                                                        ?->firstWhere('timeslot_id', $booking->activity_time_slot_id);
                                                                                    $infants = (int) ($booking->infants ?? 0);
                                                                                    $counts = $participantCounts . ($infants > 0 ? ' · Infants: ' . $infants : '');

                                                                                    return [
                                                                                        'Activity' => optional($booking->activity)->activity_name ?? 'N/A',
                                                                                        'Date' => $booking->activity_date ? $booking->activity_date->format('d/m/Y') : 'N/A',
                                                                                        'Time' => $slot ? trim(($slot->start_time ?? '') . ' - ' . ($slot->end_time ?? '')) : 'N/A',
                                                                                        'Participants' => $counts . ' · Total: ' . ((int) ($booking->adults ?? 0) + (int) ($booking->children ?? 0) + $infants),
                                                                                        'Participant Names' => $guestNamesDisplay,
                                                                                    ];
                                                                                })(),
                                                                                'transport' => [
                                                                                    'Route' => trim(($booking->route_from ?? '') . ' → ' . ($booking->route_to ?? '')),
                                                                                    'Vehicle' => optional($booking->transport)->vehicle_display_name ?? 'N/A',
                                                                                    'Pickup' => trim(($booking->pickup_date ? $booking->pickup_date->format('d/m/Y') : 'N/A') . ' ' . ($booking->pickup_time ?? '')),
                                                                                    'Passengers' => (int) ($booking->total_passengers ?? (($booking->adults ?? 0) + ($booking->children ?? 0))),
                                                                                    'Passenger Categories' => $participantCounts,
                                                                                    'Passenger Names' => $guestNamesDisplay,
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
                                                                                <div class="col-md-6"><strong>Voucher:</strong> @include('admin.partials._status_badge', ['status' => $booking->voucher_available ? 'paid' : 'pending', 'badgeLabel' => $booking->voucher_available ? 'Voucher: Available' : 'Voucher: Not Available'])</div>
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
                                        <td colspan="9" class="text-center text-muted">No trips found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $trips->links('pagination::bootstrap-5') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('click', (event) => {
        const dueToggle = event.target.closest('[data-due-actions-toggle]');
        if (dueToggle) {
            event.preventDefault();
            event.stopPropagation();
            const panel = document.getElementById(dueToggle.getAttribute('aria-controls'));
            const expanded = dueToggle.getAttribute('aria-expanded') === 'true';
            panel.hidden = expanded;
            dueToggle.setAttribute('aria-expanded', String(!expanded));
            dueToggle.textContent = expanded
                ? `+ ${panel.children.length} more`
                : 'Show less';
            return;
        }

        const trigger = event.target.closest('[data-pending-actions-trigger]');
        if (!trigger) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        const dialog = document.getElementById(trigger.getAttribute('aria-controls'));
        if (dialog && !dialog.open) {
            dialog.showModal();
        }
    });

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
