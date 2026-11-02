<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\MpoStaffUser;
use App\Models\TransportBooking;
use App\Services\BookingPostponementService;
use App\Services\MpoStaffPermissionService;
use App\Services\TransportAvailabilityService;
use App\Support\CountryAccess;
use Illuminate\Http\Request;

class ServiceBookingController extends Controller
{
    private const BOOKING_MODULES = [
        'accommodation' => ['module' => 'accommodation-bookings', 'model' => AccommodationBooking::class],
        'activity' => ['module' => 'activity-bookings', 'model' => ActivityBooking::class],
        'transport' => ['module' => 'transport-bookings', 'model' => TransportBooking::class],
    ];

    public function __construct(private readonly MpoStaffPermissionService $permissions) {}

    public function show(Request $request, string $serviceType, int $booking, BookingPostponementService $postponements)
    {
        [$staff, $owner, $module] = $this->context($request, $serviceType, 'read');
        $record = $this->booking($serviceType, $booking);
        $this->assertCountryOwnership($record, $owner->country_id);
        $record = $postponements->loadForDetails($serviceType, $booking);
        $postponementActions = $postponements->details($record, $serviceType, false);

        return view('mpo.operations.booking-show', [
            'booking' => $record,
            'serviceType' => $serviceType,
            'module' => ['module' => $module, ...config("mpo_staff.registry.{$module}")],
            'postponementActions' => $postponementActions,
            'staff' => $staff,
            'permissions' => $this->permissions,
            ...$this->transportAssignmentOptions($record, $serviceType),
        ]);
    }

    public function update(Request $request, string $serviceType, int $booking, BookingPostponementService $postponements)
    {
        [$staff, $owner] = $this->context($request, $serviceType, 'update');
        $record = $this->booking($serviceType, $booking);
        $this->assertCountryOwnership($record, $owner->country_id);
        $postponements->postpone($request, $serviceType, $booking, $staff, false, true);

        return back()->with('success', __('booking_postponement.success'));
    }

    public function updateStatus(Request $request, string $serviceType, int $booking)
    {
        $allowedStatuses = $serviceType === 'transport'
            ? TransportBooking::STATUSES
            : ['Confirmed', 'Cancelled'];
        $status = $request->validate([
            'booking_status' => ['required', 'string', \Illuminate\Validation\Rule::in($allowedStatuses)],
        ])['booking_status'];
        $action = match ($status) {
            'Confirmed' => 'confirm',
            'Cancelled' => 'cancel',
            default => 'process',
        };
        [$staff, $owner, $module] = $this->context($request, $serviceType, $action);
        $record = $this->booking($serviceType, $booking);
        $this->assertCountryOwnership($record, $owner->country_id);

        if ($serviceType === 'transport') {
            return app(\App\Http\Controllers\Operator\TransportController::class)
                ->updateBookingStatusForActor($request, $record, $staff, true);
        }

        if ($record->booking_status === 'Cancelled') {
            return back()->with('error', 'Cancelled bookings cannot be updated.');
        }

        $record->booking_status = $status;
        $record->save();
        app(\App\Services\OperatorBookingNotificationService::class)
            ->notifyBookingStatusChanged($record, $serviceType, $status);

        return back()->with('success', 'Booking status updated to '.$status.'.');
    }

    public function activitySlots(Request $request, int $booking, BookingPostponementService $postponements)
    {
        [$staff, $owner] = $this->context($request, 'activity', 'update');
        $record = $this->booking('activity', $booking);
        $this->assertCountryOwnership($record, $owner->country_id);
        $date = $request->validate(['date' => ['required', 'date_format:Y-m-d']])['date'];

        return response()->json([
            'slots' => $postponements->activitySlotsForDate($booking, $date, $staff, false, true),
        ]);
    }

    public function assignTransport(Request $request, int $booking)
    {
        [$staff, $owner, $module] = $this->context($request, 'transport', null);
        abort_unless(
            $this->permissions->can($staff, $module, 'assign')
                || $this->permissions->can($staff, $module, 'reassign'),
            403,
        );
        $record = $this->booking('transport', $booking);
        $this->assertCountryOwnership($record, $owner->country_id);
        $assigned = $record->transport_vehicle_id
            || filled($record->other_vehicle_license_number)
            || $record->pickup_driver_id
            || $record->return_driver_id;
        $action = $assigned ? 'reassign' : 'assign';
        abort_unless($this->permissions->can($staff, $module, $action), 403);

        return app(\App\Http\Controllers\Operator\TransportController::class)
            ->assignDriversForActor($request, $record, $staff, true);
    }

    private function context(Request $request, string $serviceType, ?string $action): array
    {
        $staff = $request->attributes->get('mpo_staff_user');
        $owner = $request->attributes->get('mpo_admin');
        abort_unless(
            $staff instanceof MpoStaffUser
                && $owner instanceof AdminUser
                && $owner->admin_type === 'mpo'
                && (int) $staff->admin_user_id === (int) $owner->id,
            403,
        );
        abort_unless(isset(self::BOOKING_MODULES[$serviceType]), 404);
        $module = self::BOOKING_MODULES[$serviceType]['module'];
        if ($action !== null) {
            abort_unless($this->permissions->can($staff, $module, $action), 403);
        }

        return [$staff, $owner, $module];
    }

    private function booking(string $serviceType, int $booking): AccommodationBooking|ActivityBooking|TransportBooking
    {
        $model = self::BOOKING_MODULES[$serviceType]['model'] ?? abort(404);

        return $model::query()->findOrFail($booking);
    }

    private function assertCountryOwnership(
        AccommodationBooking|ActivityBooking|TransportBooking $booking,
        int|string|null $countryId,
    ): void {
        abort_unless(
            $countryId
                && CountryAccess::countryId() === (int) $countryId
                && (int) $booking->country_id === (int) $countryId,
            404,
        );
    }

    private function transportAssignmentOptions(
        AccommodationBooking|ActivityBooking|TransportBooking $booking,
        string $serviceType,
    ): array {
        if ($serviceType !== 'transport'
            || ! in_array($booking->booking_status, [TransportBooking::STATUS_CONFIRMED, TransportBooking::STATUS_SCHEDULED], true)) {
            return ['assignmentDrivers' => collect(), 'assignmentVehicles' => collect()];
        }

        $transport = $booking->transport;
        $operator = $transport?->operator;
        if (! $transport || ! $operator) {
            return ['assignmentDrivers' => collect(), 'assignmentVehicles' => collect()];
        }

        $availability = new TransportAvailabilityService();
        $drivers = $availability->availableDrivers($operator, $booking);
        $vehicles = $availability->availableVehicleModelsForBooking($transport, $booking);
        if ($booking->pickupDriver && ! $drivers->contains('id', $booking->pickup_driver_id)) {
            $drivers->prepend($booking->pickupDriver);
        }
        if ($booking->vehicle && ! $vehicles->contains('id', $booking->transport_vehicle_id)) {
            $vehicles->prepend($booking->vehicle);
        }

        return ['assignmentDrivers' => $drivers, 'assignmentVehicles' => $vehicles];
    }
}
