<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccommodationBooking;

class TestController extends Controller
{
    public function test()
    {
        try {
            $booking = new AccommodationBooking();
            $bookings = AccommodationBooking::all();
            return 'Model loaded successfully. Found ' . $bookings->count() . ' bookings.';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage() . ' on line ' . $e->getLine();
        }
    }
}
