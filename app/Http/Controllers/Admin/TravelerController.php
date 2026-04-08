<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\TravelerAccount;
use App\Models\TravelerProfile;
use App\Models\Trip;
use App\Models\Traveller;
use App\Models\Booking;
use App\Models\BookingLineItem;
use App\Services\TripService;

class TravelerController extends Controller
{
    public function index()
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $travellers = TravelerAccount::with('profile')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.travellers.index', compact('travellers'));
    }

    public function edit(TravelerAccount $traveler)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $traveler->load('profile');
        return view('admin.travellers.edit', compact('traveler'));
    }

    public function update(Request $request, TravelerAccount $traveler)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $request->validate([
            'email' => 'required|email|unique:traveler_accounts,email,' . $traveler->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:25',
            'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string|max:255',
            'account_suspended' => 'nullable|boolean',
        ]);

        $travelerUpdate = [
            'email' => $request->email,
        ];

        if (Schema::hasColumn('traveler_accounts', 'account_suspended')) {
            $travelerUpdate['account_suspended'] = $request->account_suspended ? true : false;
        }

        $traveler->update($travelerUpdate);

        $profileData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'nationality' => $request->nationality,
        ];

        if (Schema::hasColumn('traveler_profiles', 'phone')) {
            $profileData['phone'] = $request->phone;
        }

        if ($traveler->profile) {
            $traveler->profile->update($profileData);
        } else {
            $profileData['traveler_account_id'] = $traveler->id;
            TravelerProfile::create($profileData);
        }

        return redirect()->route('admin.travellers.index')->with('success', 'Traveller updated successfully.');
    }

    public function suspend(Request $request, TravelerAccount $traveler)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        if (!Schema::hasColumn('traveler_accounts', 'account_suspended')) {
            return redirect()->back()->with('error', 'Traveler suspension support is not available in the current database schema.');
        }

        $traveler->update(['account_suspended' => !$traveler->account_suspended]);

        $status = $traveler->account_suspended ? 'suspended' : 'activated';
        return redirect()->back()->with('success', 'Traveller account ' . $status . ' successfully.');
    }

    public function createBooking(Request $request, TravelerAccount $traveler)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        // Generate Trip ID
        $tripId = TripService::getOrCreateTripId($traveler, $request->all());

        // Create Booking
        $booking = Booking::create([
            'trip_id' => $tripId,
            'operator_id' => session('admin_id'), // Assuming admin is operator
            'total_amount' => $request->total_amount ?? 0,
            'status' => 'pending',
        ]);

        // Create BLIs and allocations as needed
        // This is simplified; in real implementation, handle service selection

        return redirect()->back()->with('success', 'Booking created with Trip ID: ' . $tripId);
    }
}