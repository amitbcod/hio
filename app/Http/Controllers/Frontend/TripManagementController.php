<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripManagementController extends Controller
{
    /**
     * Show add-to-trip modal or page
     */
    public function showAddServiceForm(Request $request, Trip $trip)
    {
        $traveler = auth('traveler')->user();
        
        if ($trip->traveler_account_id !== $traveler->id) {
            abort(403);
        }

        $serviceType = $request->query('service_type', 'accommodation'); // accommodation or activity
        
        return view('frontend.traveler.add-service-to-trip', compact('trip', 'serviceType'));
    }

    /**
     * Confirm adding service to trip - redirect to checkout with trip_id
     */
    public function confirmAddService(Request $request, Trip $trip)
    {
        $traveler = auth('traveler')->user();
        
        if ($trip->traveler_account_id !== $traveler->id) {
            abort(403);
        }

        $request->session()->put('add_to_trip_id', $trip->id);

        return redirect()->route('frontend.booking.cart')
            ->with('success', 'Services will be added to Trip ID: ' . $trip->id);
    }
}
