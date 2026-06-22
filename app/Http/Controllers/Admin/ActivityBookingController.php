<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityBooking;

class ActivityBookingController extends Controller
{
    protected function ensureAdmin()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }
        return null;
    }

    public function index(Request $request)
    {
        if ($redirect = $this->ensureAdmin()) return $redirect;

        $bookings = ActivityBooking::with(['activity.operator.business', 'guests'])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('admin.activity.bookings.index', compact('bookings'));
    }

    public function show($bookingId)
    {
        if ($redirect = $this->ensureAdmin()) return $redirect;

        $booking = ActivityBooking::with(['activity.operator', 'activity.schedulingTimeSlots', 'guests'])
            ->findOrFail($bookingId);

        return view('admin.activity.bookings.show', compact('booking'));
    }
}