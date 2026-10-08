<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccommodationBooking;
use App\Models\OperatorBookingNotificationService;
use Illuminate\Http\Request;

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

    public function updateBookingStatus(Request $request, AccommodationBooking $booking)
    {
        if ($redirect = $this->ensureAdmin()) return $redirect;

        $request->validate([
            'booking_status' => 'required|in:Confirmed,Cancelled',
        ]);

        if ($booking->booking_status === 'Cancelled') {
            return back()->with('error', 'Cancelled bookings cannot be updated.');
        }

        $booking->booking_status = $request->input('booking_status');
        $booking->save();

        (new \App\Services\OperatorBookingNotificationService())->notifyBookingStatusChanged(
            $booking,
            'accommodation',
            $booking->booking_status
        );

        return back()->with('success', 'Booking status updated to ' . $booking->booking_status . '.');
    }
}
