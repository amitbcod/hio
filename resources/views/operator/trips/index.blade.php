@extends('layouts.app')

@section('content')
<div class="container mt-0">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @include('operator.registration._sidebar_main')
        </div>
        <div class="col-md-9 my-pro">
            <div class="container-middle">
<div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow: 0 1px 2px rgba(15,23,42,.04);">
    <div style="padding:20px 24px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; gap:16px;">
        <div>
            <h4 style="margin:0; color:#0f172a; font-size:20px; font-weight:700;">Trip Listing</h4>
            <div style="margin-top:4px; color:#64748b; font-size:13px;">Trips containing at least one service owned by your business or operator.</div>
        </div>
    </div>

    <div style="padding:20px 24px;">
        <form method="GET" action="{{ route('operator.trips.index') }}" style="padding:16px; border:1px solid #e5e7eb; border-radius:8px; background:#f8fafc; margin-bottom:20px;">
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:14px;">
                <div>
                    <label for="operator-trip-from-date" style="display:block; margin-bottom:5px; font-size:13px; font-weight:600;">Trip From Date</label>
                    <input id="operator-trip-from-date" type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px;">
                </div>
                <div>
                    <label for="operator-trip-to-date" style="display:block; margin-bottom:5px; font-size:13px; font-weight:600;">Trip To Date</label>
                    <input id="operator-trip-to-date" type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px;">
                </div>
                <div>
                    <label for="operator-trip-payment-status" style="display:block; margin-bottom:5px; font-size:13px; font-weight:600;">Payment Status</label>
                    <select id="operator-trip-payment-status" name="payment_status" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px;">
                        <option value="">All Payment Statuses</option>
                        @foreach($paymentStatuses as $value => $label)
                            <option value="{{ $value }}" @selected(strtolower($filters['payment_status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="operator-trip-type" style="display:block; margin-bottom:5px; font-size:13px; font-weight:600;">Trip Type</label>
                    <select id="operator-trip-type" name="trip_type" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px;">
                        <option value="">All Trip Types</option>
                        @foreach($tripTypes as $tripType)
                            <option value="{{ $tripType }}" @selected(($filters['trip_type'] ?? '') === $tripType)>{{ $tripType }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="operator-trip-traveller-information" style="display:block; margin-bottom:5px; font-size:13px; font-weight:600;">Missing traveller information</label>
                    <select id="operator-trip-traveller-information" name="traveller_information" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px;">
                        <option value="" @selected(empty($filters['traveller_information']))>All</option>
                        <option value="missing" @selected(($filters['traveller_information'] ?? '') === 'missing')>Missing traveller information</option>
                        <option value="complete" @selected(($filters['traveller_information'] ?? '') === 'complete')>Complete traveller information</option>
                    </select>
                </div>
                <div>
                    <label for="operator-trip-traveller" style="display:block; margin-bottom:5px; font-size:13px; font-weight:600;">Traveller</label>
                    <input id="operator-trip-traveller" type="search" name="traveller" value="{{ $filters['traveller'] ?? '' }}" placeholder="Search traveller name" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px;">
                </div>
                <div>
                    <label for="operator-trip-booking-reference" style="display:block; margin-bottom:5px; font-size:13px; font-weight:600;">Booking References</label>
                    <input id="operator-trip-booking-reference" type="search" name="booking_reference" value="{{ $filters['booking_reference'] ?? '' }}" placeholder="Search booking reference" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px;">
                </div>
                <div>
                    <label for="operator-trip-search" style="display:block; margin-bottom:5px; font-size:13px; font-weight:600;">Trip</label>
                    <input id="operator-trip-search" type="search" name="trip" value="{{ $filters['trip'] ?? '' }}" placeholder="Search trip name or ID" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px;">
                </div>
            </div>
            <div style="display:flex; gap:8px; margin-top:14px;">
                <button type="submit" style="padding:8px 14px; border:0; border-radius:6px; background:#0f172a; color:#fff; font-weight:600; cursor:pointer;">Apply Filters</button>
                <a href="{{ route('operator.trips.index') }}" style="padding:8px 14px; border:1px solid #cbd5e1; border-radius:6px; background:#fff; color:#334155; text-decoration:none;">Reset Filters</a>
            </div>
        </form>
        @if($trips->isEmpty())
            <div style="padding:40px 20px; text-align:center; color:#64748b;">No trips found for your services.</div>
        @else
            <div style="overflow-x:auto;">
                <table class="operator-trip-listing" style="width:100%; border-collapse:collapse; table-layout:fixed; min-width:1320px;">
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
                        <tr style="background:#f8fafc; color:#475569; font-size:13px; text-transform:uppercase; letter-spacing:.04em;">
                            <th scope="col" aria-label="Expand trip details" style="padding:8px; border-bottom:1px solid #e5e7eb;"></th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Trip</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Travel / next service</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Authority and Travel Party</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Booking</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Payment</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Attention / Priority</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Next Action</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Due / Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trips as $trip)
                            @php
                                $tripRef = $trip->common_booking_reference ?? null;
                                $tripNextActions = $trip->next_actions ?? [];
                                $tripPendingActions = collect($trip->operations_overview['pending_actions'] ?? []);
                                $tripDueActions = collect($trip->due_actions ?? [])
                                    ->where('status', 'due')
                                    ->values();
                                $serviceBookings = collect()
                                    ->merge($trip->accommodationBookings->map(fn ($booking) => ['type' => 'accommodation', 'booking' => $booking]))
                                    ->merge($trip->activityBookings->map(fn ($booking) => ['type' => 'activity', 'booking' => $booking]))
                                    ->merge($trip->transportBookings->map(fn ($booking) => ['type' => 'transport', 'booking' => $booking]));
                                $tripParticipants = $serviceBookings
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
                            <tr style="border-bottom:1px solid #eef2f7;">
                                <td style="padding:10px 8px; vertical-align:top; text-align:center;">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-trip-toggle aria-expanded="false" aria-controls="operator-trip-{{ $trip->id }}" aria-label="Expand trip details" title="Expand trip details" style="width:30px; height:30px; padding:0; border:1px solid #cbd5e1; background:#fff; color:#334155; border-radius:4px; font-size:18px; line-height:1; font-weight:600; cursor:pointer;">+</button>
                                </td>
                                <td style="padding:14px; vertical-align:top;">
                                    @php
                                        $resolvedTripType = $trip->trip_type ?? 'Trip';
                                        $tripDisplayName = $resolvedTripType === 'Trip' ? null : ($trip->title ?: 'Trip #' . $trip->id);
                                        $travelDates = $trip->operations_overview['travel_dates'] ?? [];
                                        $tripStartDate = $trip->start_date?->format('d M Y') ?? ($travelDates['start'] ?? 'N/A');
                                        $tripEndDate = $trip->end_date?->format('d M Y') ?? ($travelDates['end'] ?? 'N/A');
                                        $tripDateRange = $tripStartDate . ' - ' . $tripEndDate;
                                    @endphp
                                    @if(in_array($resolvedTripType, ['Package Trip', 'Group Trip'], true))
                                        <div style="font-weight:700; color:#0f172a;">{{ $tripDisplayName ?: ($trip->title ?: 'Trip #' . $trip->id) }}</div>
                                        <div style="margin-top:6px; color:#64748b; font-size:12px;">Trip #{{ $trip->id }}</div>
                                        <div style="margin-top:6px; color:#334155; font-size:12px;">{{ $resolvedTripType }}</div>
                                        <div style="margin-top:6px; color:#64748b; font-size:12px;">{{ $tripDateRange }}</div>
                                    @else
                                        <div style="font-weight:700; color:#0f172a;">Trip #{{ $trip->id }}</div>
                                        <div style="margin-top:6px; color:#64748b; font-size:12px;">{{ $tripDateRange }}</div>
                                    @endif
                                    @forelse($trip->total_amounts as $totalAmount)
                                        <div style="margin-top:6px; color:#64748b; font-size:12px;">Total Amount {{ $totalAmount['currency'] }}{{ $totalAmount['amount'] }}</div>
                                    @empty
                                        <div style="margin-top:6px; color:#64748b; font-size:12px;">Total Amount USD0</div>
                                    @endforelse
                                </td>
                                <td style="padding:14px; vertical-align:top;">
                                    <div style="font-size:12px; color:#64748b;">{{ $tripStartDate }} – {{ $tripEndDate }}</div>
                                    @if(!empty($trip->operations_overview['next_service']))
                                        <div style="font-weight:600; margin-top:5px;">{{ $trip->operations_overview['next_service']['name'] }}</div>
                                        <div style="font-size:12px; color:#64748b;">{{ $trip->operations_overview['next_service']['when'] }}</div>
                                    @else
                                        <div style="font-size:12px; color:#64748b; margin-top:5px;">No upcoming service</div>
                                    @endif
                                </td>
                                <td style="padding:14px; vertical-align:top;">
                                    <div><strong>Booking Authority / Account Holder:</strong> {{ $trip->traveller_display_name }}</div>
                                    <div style="margin-top:6px; color:#64748b; font-size:12px;">Travel Party Size: {{ $trip->travel_party_size }}</div>
                                    <div style="margin-top:8px; font-size:12px;"><strong>Travellers:</strong></div>
                                    <div class="operator-trip-participants">
                                        @forelse($tripParticipantsForDisplay as $participantName)
                                            <div>{{ $participantName }}</div>
                                        @empty
                                            <span style="color:#64748b;">N/A</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td style="padding:14px; vertical-align:top;">
                                    <div style="font-size:12px; margin-bottom:4px;"><strong>Reference:</strong> {{ $tripRef ?: 'N/A' }}</div>
                                    <div style="font-size:12px; color:#64748b; margin-bottom:4px;">{{ $trip->common_booking_ref_count }} Booking Refs · {{ $trip->common_booking_bli_count }} BLIs</div>
                                    <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                        @forelse($trip->booking_status_counts as $statusCount)
                                            @include('operator.partials._status_badge', ['status' => $statusCount['status'], 'statusBadgeLabel' => $statusCount['status'] . ': ' . $statusCount['count']])
                                        @empty
                                            <span style="color:#64748b;">N/A</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="operator-trip-payment" style="padding:14px; vertical-align:top;">
                                    <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                        @forelse($trip->payment_status_badges as $statusBadge)
                                            @include('operator.partials._status_badge', ['status' => $statusBadge['status']])
                                        @empty
                                            <span style="color:#64748b;">N/A</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td style="padding:14px; vertical-align:top;">
                                    @php
                                        $priorityValue = strtolower((string) ($trip->priority ?? 'normal'));
                                        $priorityLabel = App\Models\Trip::priorityOptions()[$priorityValue] ?? ucfirst($priorityValue);
                                        $priorityTone = match ($priorityValue) {
                                            'low' => 'background:#e2e8f0; color:#334155;',
                                            'normal' => 'background:#dbeafe; color:#1d4ed8;',
                                            'high' => 'background:#fef3c7; color:#b45309;',
                                            'urgent' => 'background:#fee2e2; color:#b91c1c;',
                                            default => 'background:#e2e8f0; color:#334155;',
                                        };
                                    @endphp
                                    <span style="display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; font-weight:700; {{ $priorityTone }};">{{ $priorityLabel }}</span>
                                    <div>
                                        <button type="button" class="btn btn-link btn-sm px-0" data-pending-actions-trigger aria-haspopup="dialog" aria-controls="pending-actions-{{ $trip->id }}">
                                            View Pending Actions
                                        </button>
                                    </div>
                                    <dialog id="pending-actions-{{ $trip->id }}" aria-labelledby="pending-actions-title-{{ $trip->id }}" style="width:min(42rem, calc(100% - 2rem)); max-height:80vh; padding:0; border:1px solid #cbd5e1; border-radius:10px; box-shadow:0 20px 50px rgba(15,23,42,.25);">
                                        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:14px 16px; border-bottom:1px solid #e5e7eb;">
                                            <h2 id="pending-actions-title-{{ $trip->id }}" style="font-size:18px; margin:0;">Pending Actions — {{ $trip->title ?: 'Trip #' . $trip->id }}</h2>
                                            <form method="dialog">
                                                <button type="submit" class="btn-close" autofocus aria-label="Close pending actions"></button>
                                            </form>
                                        </div>
                                        <div style="padding:16px; overflow-y:auto; max-height:65vh;">
                                            @forelse($tripPendingActions as $pendingAction)
                                                <article style="border:1px solid #e5e7eb; border-radius:8px; padding:12px; margin-bottom:10px;">
                                                    <div style="font-weight:700;">{{ $pendingAction['label'] }}</div>
                                                    <div style="font-size:12px; color:#64748b; margin-top:5px;">
                                                        {{ ucfirst($pendingAction['service_type']) }} booking
                                                        {{ $pendingAction['booking_reference'] ?: '#' . $pendingAction['booking_id'] }}
                                                        @if($pendingAction['booking_status'])
                                                            · {{ $pendingAction['booking_status'] }}
                                                        @endif
                                                    </div>
                                                    @if($pendingAction['service_date'])
                                                        <div style="font-size:12px; margin-top:5px;">Service date: {{ $pendingAction['service_date'] }}</div>
                                                    @endif
                                                    @if($pendingAction['url'])
                                                        <a class="btn btn-sm btn-outline-primary mt-2" href="{{ $pendingAction['url'] }}">View booking</a>
                                                    @endif
                                                </article>
                                            @empty
                                                <p style="margin:0; color:#64748b;">No pending actions for this trip.</p>
                                            @endforelse
                                        </div>
                                    </dialog>
                                </td>
                                <td style="padding:14px; vertical-align:top;">
                                    @if(empty($tripNextActions))
                                        No Action Required
                                    @else
                                        @foreach($tripNextActions as $action)
                                            <div>{{ $action['label'] ?? 'No Action Required' }}</div>
                                        @endforeach
                                    @endif
                                </td>
                                <td style="padding:14px; vertical-align:top;">
                                    @if($tripDueActions->isEmpty())
                                        <span style="color:#64748b;">N/A</span>
                                    @else
                                        @php
                                            $firstDueAction = $tripDueActions->first();
                                            $additionalDueActions = $tripDueActions->skip(1);
                                        @endphp
                                        <div style="margin-bottom:8px;">
                                            <div><span style="display:inline-block;padding:3px 7px;border-radius:999px;font-size:11px;font-weight:700;background:#fef3c7;color:#92400e;border:1px solid #fde68a;">DUE</span> — {{ $firstDueAction['action_name'] }}</div>
                                            <div style="font-size:12px;">Deadline: {{ $firstDueAction['target_display'] }}</div>
                                            <div style="font-size:12px;">Due since: {{ $firstDueAction['due_since_display'] }}</div>
                                        </div>
                                        @if($additionalDueActions->isNotEmpty())
                                            <div id="additional-due-actions-{{ $trip->id }}" hidden>
                                                @foreach($additionalDueActions as $action)
                                                    <div style="margin-bottom:8px;">
                                                        <div><span style="display:inline-block;padding:3px 7px;border-radius:999px;font-size:11px;font-weight:700;background:#fef3c7;color:#92400e;border:1px solid #fde68a;">DUE</span> — {{ $action['action_name'] }}</div>
                                                        <div style="font-size:12px;">Deadline: {{ $action['target_display'] }}</div>
                                                        <div style="font-size:12px;">Due since: {{ $action['due_since_display'] }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" class="btn btn-link btn-sm p-0" data-due-actions-toggle aria-expanded="false" aria-controls="additional-due-actions-{{ $trip->id }}" style="font-size:12px;">
                                                + {{ $additionalDueActions->count() }} more
                                            </button>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="9" style="padding:0; border-bottom:1px solid #e5e7eb;">
                                    <div id="operator-trip-{{ $trip->id }}" hidden>
                                        <div style="padding:18px; background:#f8fafc;">
                                            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid #e5e7eb;">
                                                <div>
                                                    <strong style="color:#0f172a;">{{ $trip->title ?: 'Trip #' . $trip->id }}</strong>
                                                    <div style="font-size:12px; color:#64748b; margin-top:4px;">Common booking: {{ $tripRef ?: 'N/A' }}</div>
                                                </div>
                                                <div style="font-size:12px; color:#64748b;">{{ $trip->start_date ? $trip->start_date->format('d/m/Y') : 'N/A' }} – {{ $trip->end_date ? $trip->end_date->format('d/m/Y') : 'N/A' }}</div>
                                            </div>

                                            @if($serviceBookings->isEmpty())
                                                <div style="color:#64748b;">No service bookings available for your operator.</div>
                                            @else
                                                @foreach($serviceBookings as $serviceItem)
                                                    @php
                                                        $booking = $serviceItem['booking'];
                                                        $type = $serviceItem['type'];
                                                        $detailRoute = match ($type) {
                                                            'accommodation' => route('operator.accommodation.booking.details', $booking->id),
                                                            'activity' => route('operator.activity.booking.details', $booking->id),
                                                            'transport' => route('operator.transport.booking.details', ['transport' => $booking->transport_id, 'booking' => $booking->id]),
                                                            default => '#',
                                                        };
                                                        $serviceLabel = match ($type) {
                                                            'accommodation' => 'Accommodation',
                                                            'activity' => 'Activity',
                                                            'transport' => 'Transport',
                                                            default => 'Booking',
                                                        };
                                                        $nextStep = strtolower((string) $trip->payment_status) !== 'paid'
                                                            ? 'Awaiting Payment'
                                                            : ((string) ($booking->booking_status ?? '') !== 'Confirmed'
                                                                ? 'Mark as Confirmed'
                                                                : ($type === 'transport' && ! $booking->hasCompleteAssignment()
                                                                    ? 'Assign Driver & Vehicle'
                                                                    : 'No Action Required'));
                                                        $guestNames = implode(', ', $booking->participant_names ?? []);
                                                        $guestNamesDisplay = $guestNames !== '' ? $guestNames : 'Not provided';
                                                        $participantCounts = 'Adults: ' . (int) ($booking->adults ?? 0)
                                                            . ' · Children: ' . (int) ($booking->children ?? 0);
                                                        $serviceMeta = match ($type) {
                                                            'accommodation' => [
                                                                'Property' => optional($booking->accommodation)->property_name ?? 'N/A',
                                                                'Room' => optional($booking->room)->room_name ?? optional($booking->room)->name ?? 'N/A',
                                                                'Check-in' => $booking->check_in_date ? $booking->check_in_date->format('d/m/Y') : 'N/A',
                                                                'Check-out' => $booking->check_out_date ? $booking->check_out_date->format('d/m/Y') : 'N/A',
                                                                'Rooms' => (int) ($booking->rooms_booked ?? 0),
                                                                'Participants' => $participantCounts . ' · Total: ' . ((int) ($booking->adults ?? 0) + (int) ($booking->children ?? 0)),
                                                                'Participant Names' => $guestNamesDisplay,
                                                                'BLI Id' => $booking->booking_reference ?? $booking->bookingRef?->booking_ref_code ?? 'N/A',
                                                                'Status' => $booking->booking_status ?? 'N/A',
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
                                                                    'BLI Id' => $booking->booking_reference ?? $booking->bookingRef?->booking_ref_code ?? 'N/A',
                                                                    'Status' => $booking->booking_status ?? 'N/A',
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
                                                                'BLI Id' => $booking->booking_reference ?? $booking->bookingRef?->booking_ref_code ?? 'N/A',
                                                                'Status' => $booking->booking_status ?? 'N/A',
                                                            ],
                                                            default => [],
                                                        };
                                                    @endphp
                                                    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:14px; margin-bottom:12px;">
                                                        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:10px;">
                                                            <div>
                                                                <div style="font-size:11px; color:#64748b; text-transform:uppercase; letter-spacing:.05em;">Booking</div>
                                                                <div style="font-weight:700; color:#0f172a;">{{ $serviceLabel }}</div>
                                                            </div>
                                                            <a href="{{ $detailRoute }}" style="display:inline-block; background:#0f172a; color:#fff; border-radius:6px; padding:7px 12px; font-size:12px; font-weight:600; text-decoration:none;">View Details</a>
                                                        </div>
                                                        <div style="display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; font-size:13px;">
                                                            @foreach($serviceMeta as $label => $value)
                                                                <div>
                                                                    @if($label !== '')
                                                                        @if($label === 'Status')
                                                                            <strong style="color:#334155;">{{ $label }}:</strong> @include('operator.partials._status_badge', ['status' => $value, 'statusBadgeLabel' => $value])
                                                                        @else
                                                                            <strong style="color:#334155;">{{ $label }}:</strong> {{ $value }}
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div style="margin-top:8px; font-size:13px;">
                                                            <strong style="color:#334155;">Voucher:</strong>
                                                            @include('operator.partials._status_badge', ['status' => $booking->voucher_available ? 'paid' : 'pending', 'statusBadgeLabel' => $booking->voucher_available ? 'Voucher: Available' : 'Voucher: Not Available'])
                                                        </div>
                                                        <div style="margin-top:10px; font-size:13px;"><strong style="color:#334155;">Next Step:</strong> {{ $nextStep }}</div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px;">{{ $trips->links() }}</div>
        @endif
    </div>
</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .operator-trip-listing th,
    .operator-trip-listing td {
        overflow-wrap: anywhere;
    }

    .operator-trip-listing td:nth-child(2) {
        overflow-wrap: break-word;
    }

    .operator-trip-listing .operator-trip-participants {
        font-size: 12px;
        overflow-wrap: anywhere;
    }

    .operator-trip-listing .operator-trip-payment .operator-status-badge {
        max-width: 100%;
        white-space: normal;
        overflow-wrap: anywhere;
    }
</style>
@endpush

@push('scripts')
@include('operator.partials._sidebar_scripts')
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
        button.addEventListener('click', () => {
            const panel = document.getElementById(button.getAttribute('aria-controls'));
            const isExpanded = button.getAttribute('aria-expanded') === 'true';

            panel.hidden = isExpanded;
            button.setAttribute('aria-expanded', String(!isExpanded));
            button.textContent = isExpanded ? '+' : '-';
            button.setAttribute('aria-label', isExpanded ? 'Expand trip details' : 'Collapse trip details');
            button.title = isExpanded ? 'Expand trip details' : 'Collapse trip details';
        });
    });
</script>
@endpush
