<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccommodationBooking;

class AccommodationBookingController extends Controller
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

        $bookings = AccommodationBooking::with(['accommodation.operator', 'room'])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('admin.accommodation.bookings.index', compact('bookings'));
    }

    public function show($bookingId)
    {
        if ($redirect = $this->ensureAdmin()) return $redirect;

        $booking = AccommodationBooking::with(['accommodation.operator', 'room', 'guests'])
            ->findOrFail($bookingId);

        return view('admin.accommodation.bookings.show', compact('booking'));
    }
}
