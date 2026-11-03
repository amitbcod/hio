<?php

namespace App\Http\Controllers;

use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\AdminUser;
use App\Models\TransportBooking;
use App\Services\BookingPostponementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingPostponementController extends Controller
{
    public function admin(
        Request $request,
        string $serviceType,
        int $booking,
        BookingPostponementService $postponementService
    ) {
        $actor = AdminUser::find(session('admin_id'));
        if (! $actor) {
            abort(403);
        }

        $postponementService->postpone($request, $serviceType, $booking, $actor, true);

        return back()->with('success', __('booking_postponement.success'));
    }

    public function operator(
        Request $request,
        string $serviceType,
        int $booking,
        BookingPostponementService $postponementService
    ) {
        $actor = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (! $actor) {
            abort(403);
        }

        $this->ensurePaymentComplete($serviceType, $booking);
        $postponementService->postpone($request, $serviceType, $booking, $actor, false);

        return back()->with('success', __('booking_postponement.success'));
    }

    public function adminActivitySlots(
        Request $request,
        int $booking,
        BookingPostponementService $postponementService
    ) {
        $actor = AdminUser::find(session('admin_id'));
        if (! $actor) {
            abort(403);
        }

        $date = $request->validate(['date' => ['required', 'date_format:Y-m-d']])['date'];

        return response()->json([
            'slots' => $postponementService->activitySlotsForDate($booking, $date, $actor, true),
        ]);
    }

    public function operatorActivitySlots(
        Request $request,
        int $booking,
        BookingPostponementService $postponementService
    ) {
        $actor = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (! $actor) {
            abort(403);
        }

        abort_unless(ActivityBooking::paymentComplete()->whereKey($booking)->exists(), 404);
        $date = $request->validate(['date' => ['required', 'date_format:Y-m-d']])['date'];

        return response()->json([
            'slots' => $postponementService->activitySlotsForDate($booking, $date, $actor, false),
        ]);
    }

    private function ensurePaymentComplete(string $serviceType, int $bookingId): void
    {
        $model = match (strtolower(trim($serviceType))) {
            'accommodation' => AccommodationBooking::class,
            'activity' => ActivityBooking::class,
            'transport' => TransportBooking::class,
            default => abort(404),
        };

        abort_unless($model::paymentComplete()->whereKey($bookingId)->exists(), 404);
    }
}
