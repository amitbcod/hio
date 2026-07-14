<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransportBooking;

class TransportBookingController extends Controller
{
    public function index()
    {
        $bookings = TransportBooking::with(['transport', 'transport.operator'])
            ->orderBy('booked_at', 'desc')
            ->paginate(20);

        return view('admin.transport.bookings.index', compact('bookings'));
    }

    public function show(TransportBooking $booking)
    {
        $booking->load(['transport', 'guests']);
        return view('admin.transport.bookings.show', compact('booking'));
    }
}
