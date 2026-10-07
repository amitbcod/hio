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
        @if($trips->isEmpty())
            <div style="padding:40px 20px; text-align:center; color:#64748b;">No trips found for your services.</div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; min-width:1100px;">
                    <thead>
                        <tr style="background:#f8fafc; color:#475569; font-size:13px; text-transform:uppercase; letter-spacing:.04em;">
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Trip</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Trip Type</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Common Booking</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Traveller</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Dates</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Payment</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Next Action</th>
                            <th style="padding:12px 14px; border-bottom:1px solid #e5e7eb; text-align:left;">Action</th>
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
                                <td style="padding:14px; vertical-align:top;">
                                    <div style="font-weight:700; color:#0f172a;">{{ $trip->title ?: 'Trip #' . $trip->id }}</div>
                                    <div style="font-size:12px; color:#94a3b8; margin-top:3px;">#{{ $trip->id }}</div>
                                </td>
                                <td style="padding:14px; vertical-align:top;">{{ $trip->trip_type ?? 'Trip' }}</td>
                                <td style="padding:14px; vertical-align:top;">{{ $tripRef ?: 'N/A' }}</td>
                                <td style="padding:14px; vertical-align:top;">{{ optional($trip->traveler)->full_name ?? optional($trip->traveler)->email ?? 'N/A' }}</td>
                                <td style="padding:14px; vertical-align:top;">
                                    {{ $trip->start_date ? $trip->start_date->format('d/m/Y') : 'N/A' }}<br>
                                    <span style="color:#64748b;">to</span> {{ $trip->end_date ? $trip->end_date->format('d/m/Y') : 'N/A' }}
                                </td>
                                <td style="padding:14px; vertical-align:top;">
                                    <span style="display:inline-block; padding:4px 8px; border-radius:999px; background:{{ $trip->payment_status === 'paid' ? '#dcfce7' : '#fef3c7' }}; color:{{ $trip->payment_status === 'paid' ? '#166534' : '#92400e' }}; font-size:12px; font-weight:600; text-transform:capitalize;">
                                        {{ $trip->payment_status ?? 'pending' }}
                                    </span>
                                </td>
                                <td style="padding:14px; vertical-align:top;">
                                    @if(empty($tripNextActions))
                                        <span style="color:#64748b;">No Action Required</span>
                                    @else
                                        @foreach($tripNextActions as $action)
                                            @php $actionType = $action['type'] ?? 'status'; @endphp
                                            @if($actionType === 'confirm')
                                                <form method="POST" action="{{ $action['url'] ?? '#' }}" class="d-inline-block mb-1">
                                                    @csrf
                                                    <button type="submit" style="background:#16a34a; color:#fff; border:0; border-radius:6px; padding:7px 10px; font-size:12px; font-weight:700; cursor:pointer;">{{ $action['label'] }}</button>
                                                </form>
                                            @elseif($actionType === 'assign')
                                                <a href="{{ $action['url'] ?? '#' }}" style="display:inline-block; background:#f59e0b; color:#fff; border-radius:6px; padding:7px 10px; font-size:12px; font-weight:700; text-decoration:none; margin-bottom:4px;">{{ $action['label'] }}</a>
                                            @else
                                                <div style="font-size:12px; color:#64748b;">{{ $action['label'] }}</div>
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td style="padding:14px; vertical-align:top;">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-trip-toggle aria-expanded="false" aria-controls="operator-trip-{{ $trip->id }}" style="border:1px solid #cbd5e1; background:#fff; color:#334155; border-radius:6px; padding:7px 10px; font-weight:600; cursor:pointer;">
                                        Expand
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" style="padding:0; border-bottom:1px solid #e5e7eb;">
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
                                                        $serviceMeta = match ($type) {
                                                            'accommodation' => [
                                                                'Property' => optional($booking->accommodation)->property_name ?? 'N/A',
                                                                'Room' => optional($booking->room)->room_name ?? optional($booking->room)->name ?? 'N/A',
                                                                'Booking Ref' => $booking->booking_reference ?? $booking->bookingRef?->booking_ref_code ?? 'N/A',
                                                                'Status' => $booking->booking_status ?? 'N/A',
                                                            ],
                                                            'activity' => [
                                                                'Activity' => optional($booking->activity)->activity_name ?? 'N/A',
                                                                '' => '',
                                                                'Booking Ref' => $booking->booking_reference ?? $booking->bookingRef?->booking_ref_code ?? 'N/A',
                                                                'Status' => $booking->booking_status ?? 'N/A',
                                                            ],
                                                            'transport' => [
                                                                'Route' => trim(($booking->route_from ?? '') . ' → ' . ($booking->route_to ?? '')),
                                                                'Vehicle' => optional($booking->transport)->vehicle_display_name ?? 'N/A',
                                                                'Booking Ref' => $booking->booking_reference ?? $booking->bookingRef?->booking_ref_code ?? 'N/A',
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
            button.textContent = isExpanded ? 'Expand' : 'Collapse';
        });
    });
</script>
@endpush
