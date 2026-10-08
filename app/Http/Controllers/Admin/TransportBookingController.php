<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Operator;
use App\Models\OperatorProfile;
use App\Models\TransportBooking;
use App\Models\TransportVehicle;
use App\Services\TransportAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransportBookingController extends Controller
{
    protected function admin(): ?AdminUser
    {
        $adminId = session('admin_id');
        return $adminId ? AdminUser::find($adminId) : null;
    }

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
            'assignments.vehicle',
            'assignments.driver',
            'assignments.assignedBy',
            'guests',
        ]);

        $assignmentDrivers = collect();
        $assignmentVehicles = collect();
        if (in_array($booking->booking_status, [TransportBooking::STATUS_CONFIRMED, TransportBooking::STATUS_SCHEDULED], true)) {
            $operator = $booking->transport?->operator;
            $transport = $booking->transport;
            $availability = new TransportAvailabilityService();
            if ($operator && $transport) {
                $assignmentDrivers = $availability->availableDrivers($operator, $booking);
                $assignmentVehicles = $availability->availableVehicleModelsForBooking($transport, $booking);
                if ($booking->pickupDriver && !$assignmentDrivers->contains('id', $booking->pickup_driver_id)) {
                    $assignmentDrivers->prepend($booking->pickupDriver);
                }
                if ($booking->vehicle && !$assignmentVehicles->contains('id', $booking->transport_vehicle_id)) {
                    $assignmentVehicles->prepend($booking->vehicle);
                }
            }
        }

        return view('admin.transport.bookings.show', compact('booking', 'assignmentDrivers', 'assignmentVehicles'));
    }

    public function updateBookingStatus(Request $request, TransportBooking $booking)
    {
        $admin = $this->admin();
        if (!$admin) {
            abort(403);
        }

        return app(\App\Http\Controllers\Operator\TransportController::class)
            ->updateBookingStatusForActor($request, $booking, $admin, true);
    }

    public function assignDrivers(Request $request, TransportBooking $booking)
    {
        $admin = $this->admin();
        if (!$admin) {
            abort(403);
        }

        return app(\App\Http\Controllers\Operator\TransportController::class)
            ->assignDriversForActor($request, $booking, $admin, true);
    }
}
