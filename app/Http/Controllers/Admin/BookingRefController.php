<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingRef;

class BookingRefController extends Controller
{
    public function index()
    {
        $bookingRefs = BookingRef::orderBy('created_at', 'desc')->paginate(50);
        return view('admin.booking_refs.index', compact('bookingRefs'));
    }

    public function show(BookingRef $bookingRef)
    {
        $bookingRef->load(['trip', 'paymentTransaction']);
        return view('admin.booking_refs.show', compact('bookingRef'));
    }
}
