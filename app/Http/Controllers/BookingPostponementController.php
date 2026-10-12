<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
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

        $date = $request->validate(['date' => ['required', 'date_format:Y-m-d']])['date'];

        return response()->json([
            'slots' => $postponementService->activitySlotsForDate($booking, $date, $actor, false),
        ]);
    }
}
