@extends('operator.layout')

@section('title', 'Trip Listing')

@section('content')
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
                <table style="width:100%; border-collapse:collapse; min-width:1100px;">
                    <thead>
                        <tr style="background:#f8fafc; color:#475569; font-size:13px; text-transform:uppercase; letter-spacing:.04em;">
                            <th scope="col" aria-label="Expand trip details" style="width:48px; padding:8px; border-bottom:1px solid #e5e7eb;"></th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Trip</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Common Booking</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Traveller</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Booking Status</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Payment Status</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Attention / Priority</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Next Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trips as $trip)
                            @php
                                $tripRef = $trip->common_booking_reference ?? null;
                                $tripNextActions = $trip->next_actions ?? [];
                                $serviceBookings = collect()
                                    ->merge($trip->accommodationBookings->map(fn ($booking) => ['type' => 'accommodation', 'booking' => $booking]))
                                    ->merge($trip->activityBookings->map(fn ($booking) => ['type' => 'activity', 'booking' => $booking]))
                                    ->merge($trip->transportBookings->map(fn ($booking) => ['type' => 'transport', 'booking' => $booking]));
                            @endphp
                            <tr style="border-bottom:1px solid #eef2f7;">
                                <td style="padding:10px 8px; vertical-align:top; text-align:center;">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-trip-toggle aria-expanded="false" aria-controls="operator-trip-{{ $trip->id }}" aria-label="Expand trip details" title="Expand trip details" style="width:30px; height:30px; padding:0; border:1px solid #cbd5e1; background:#fff; color:#334155; border-radius:4px; font-size:18px; line-height:1; font-weight:600; cursor:pointer;">+</button>
                                </td>
                                <td style="padding:14px; vertical-align:top;">
                                    @php
                                        $resolvedTripType = $trip->trip_type ?? 'Trip';
                                        $tripDisplayName = $resolvedTripType === 'Trip' ? null : ($trip->title ?: 'Trip #' . $trip->id);
                                        $tripDateRange = ($trip->start_date ? $trip->start_date->format('d M Y') : 'N/A') . ' - ' . ($trip->end_date ? $trip->end_date->format('d M Y') : 'N/A');
                                    @endphp
                                    @if(in_array($resolvedTripType, ['Package Trip', 'Group Trip'], true))
                                        <div style="font-weight:700; color:#0f172a;">{{ $tripDisplayName ?: ($trip->title ?: 'Trip #' . $trip->id) }}</div>
                                        <div style="margin-top:6px; color:#334155; font-size:12px;">{{ $resolvedTripType }}</div>
                                        <div style="margin-top:6px; color:#64748b; font-size:12px;">{{ $tripDateRange }}</div>
                                    @else
                                        <div style="font-weight:700; color:#0f172a;">Trip #{{ $trip->id }}</div>
                                        <div style="margin-top:6px; color:#64748b; font-size:12px;">{{ $tripDateRange }}</div>
                                    @endif
                                    <div style="margin-top:6px; color:#64748b; font-size:12px;">{{ $trip->common_booking_ref_count }} Booking Refs · {{ $trip->common_booking_bli_count }} BLIs</div>
                                    @forelse($trip->total_amounts as $totalAmount)
                                        <div style="margin-top:6px; color:#64748b; font-size:12px;">Total Amount {{ $totalAmount['currency'] }}{{ $totalAmount['amount'] }}</div>
                                    @empty
                                        <div style="margin-top:6px; color:#64748b; font-size:12px;">Total Amount USD0</div>
                                    @endforelse
                                </td>
                                <td style="padding:14px; vertical-align:top;">{{ $tripRef ?: 'N/A' }}</td>
                                <td style="padding:14px; vertical-align:top;">
                                    <div>{{ $trip->traveller_display_name }}</div>
                                    <div style="margin-top:6px; color:#64748b; font-size:12px;">Travel Party Size: {{ $trip->travel_party_size }}</div>
                                </td>
                                <td style="padding:14px; vertical-align:top;">
                                    <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                        @forelse($trip->booking_status_counts as $statusCount)
                                            <span class="trip-booking-status-badge" style="display:inline-block; padding:4px 7px; border-radius:999px; font-size:12px; font-weight:600; white-space:nowrap; {{ $statusCount['style'] }}">{{ $statusCount['status'] }}: {{ $statusCount['count'] }}</span>
                                        @empty
                                            <span style="color:#64748b;">N/A</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td style="padding:14px; vertical-align:top;">
                                    <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                        @forelse($trip->payment_status_badges as $statusBadge)
                                            <span class="trip-booking-status-badge" style="display:inline-block; padding:4px 7px; border-radius:999px; font-size:12px; font-weight:600; white-space:nowrap; {{ $statusBadge['style'] }}">{{ $statusBadge['status'] }}</span>
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
                                                        $participantNameList = $booking->guests
                                                            ->map(fn ($guest) => trim(implode(' ', array_filter([
                                                                $guest->first_name,
                                                                $guest->middle_name,
                                                                $guest->last_name,
                                                            ]))))
                                                            ->push($booking->guest_name);
                                                        if ($type === 'transport') {
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
                                                        $serviceMeta = match ($type) {
                                                            'accommodation' => [
                                                                'Property' => optional($booking->accommodation)->property_name ?? 'N/A',
                                                                'Room' => optional($booking->room)->room_name ?? optional($booking->room)->name ?? 'N/A',
                                                                'Check-in' => $booking->check_in_date ? $booking->check_in_date->format('d/m/Y') : 'N/A',
                                                                'Check-out' => $booking->check_out_date ? $booking->check_out_date->format('d/m/Y') : 'N/A',
                                                                'Rooms' => (int) ($booking->rooms_booked ?? 0),
                                                                'Participants' => $participantCounts . ' · Total: ' . ((int) ($booking->adults ?? 0) + (int) ($booking->children ?? 0)),
                                                                'Participant Names' => $guestNames !== '' ? $guestNames : ($booking->guest_name ?? 'N/A'),
                                                                'BLI Id' => $booking->booking_reference ?? $booking->bookingRef?->booking_ref_code ?? 'N/A',
                                                                'Status' => $booking->booking_status ?? 'N/A',
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
                                                                'Passenger Names' => $guestNames !== '' ? $guestNames : (trim(($booking->traveler_first_name ?? '') . ' ' . ($booking->traveler_middle_name ?? '') . ' ' . ($booking->traveler_last_name ?? '')) ?: ($booking->guest_name ?? 'N/A')),
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
                                                                        <strong style="color:#334155;">{{ $label }}:</strong> {{ $value }}
                                                                    @endif
                                                                </div>
                                                            @endforeach
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
@endsection

@push('scripts')
<script>
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
