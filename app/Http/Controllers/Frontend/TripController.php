<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index()
    {
        $traveler = auth('traveler')->user();
        $trips = Trip::where('traveler_account_id', $traveler->id)->with('bookings.lineItems')->orderBy('created_at', 'desc')->get();
        return view('frontend.traveler.trips', compact('trips'));
    }

    public function show(Trip $trip)
    {
        $traveler = auth('traveler')->user();
        if ($trip->traveler_account_id !== $traveler->id) {
            abort(403);
        }
        $trip->load('bookings.lineItems.travellers', 'travellers');
        
        // Load associated accommodation and activity bookings
        $accommodationBookings = \App\Models\AccommodationBooking::where('trip_id', $trip->id)
            ->with(['accommodation', 'room', 'guests'])
            ->orderBy('check_in_date', 'asc')
            ->get();
        
        $activityBookings = \App\Models\ActivityBooking::where('trip_id', $trip->id)
            ->with('activity')
            ->orderBy('activity_date', 'asc')
            ->get();
        
        // Calculate actual trip dates from all bookings
        $allDates = [];
        foreach ($accommodationBookings as $booking) {
            if ($booking->check_in_date) $allDates[] = $booking->check_in_date;
            if ($booking->check_out_date) $allDates[] = $booking->check_out_date;
        }
        foreach ($activityBookings as $booking) {
            if ($booking->activity_date) $allDates[] = $booking->activity_date;
        }
        
        $tripStartDate = !empty($allDates) ? min($allDates) : $trip->start_date;
        $tripEndDate = !empty($allDates) ? max($allDates) : $trip->end_date;
        
        return view('frontend.traveler.trip-detail', compact('trip', 'accommodationBookings', 'activityBookings', 'tripStartDate', 'tripEndDate'));
    }
}
