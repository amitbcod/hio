<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use App\Models\OperatorProfile;
use App\Models\TransportBooking;
use Illuminate\Http\Request;

class TransportBookingController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'booking_reference' => trim((string) $request->query('booking_reference', '')),
            'operator_id' => trim((string) $request->query('operator_id', '')),
            'status' => trim((string) $request->query('status', '')),
            'pickup_date' => trim((string) $request->query('pickup_date', '')),
            'trip_type' => trim((string) $request->query('trip_type', '')),
        ];

        $bookings = TransportBooking::query()
            ->with([
                'transport.operator.profile',
                'transport.vehicleName.vehicleType',
                'travelerAccount',
                'vehicle',
                'pickupDriver',
                'returnDriver',
                'currentAssignment.vehicle',
                'currentAssignment.driver',
            ])
            ->when($filters['booking_reference'] !== '', fn ($query) => $query->where('booking_reference', 'like', '%' . $filters['booking_reference'] . '%'))
            ->when($filters['operator_id'] !== '' && ctype_digit($filters['operator_id']), fn ($query) => $query->whereHas('transport', fn ($transportQuery) => $transportQuery->where('operator_id', (int) $filters['operator_id'])))
            ->when(in_array($filters['status'], TransportBooking::STATUSES, true), fn ($query) => $query->where('booking_status', $filters['status']))
            ->when($filters['pickup_date'] !== '', fn ($query) => $query->whereDate('pickup_date', $filters['pickup_date']))
            ->when(in_array($filters['trip_type'], ['ONE_WAY', 'OUTBOUND', 'RETURN'], true), fn ($query) => $query->where('trip_type', $filters['trip_type']))
            ->orderByDesc('booked_at')
            ->orderByDesc('id')
            ->paginate(20);

        $operators = Operator::query()
            ->with('profile')
            ->whereHas('transports.bookings')
                ->orderBy(OperatorProfile::query()
                    ->select('business_legal_name')
                    ->whereColumn('operator_profiles.operator_id', 'operators.operator_id'))
                ->orderBy('operators.operator_id')
            ->get();

        return view('admin.transport.bookings.index', compact('bookings', 'filters', 'operators'));
    }

    public function show(TransportBooking $booking)
    {
        $booking->load([
            'transport.operator.profile',
            'transport.vehicleName.vehicleType',
            'travelerAccount',
            'vehicle',
            'pickupDriver',
            'returnDriver',
            'drivers',
            'currentAssignment.vehicle',
            'currentAssignment.driver',
            'guests',
        ]);
        return view('admin.transport.bookings.show', compact('booking'));
    }
}
