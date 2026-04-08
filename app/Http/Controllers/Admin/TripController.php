<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TravelerAccount;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index()
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $trips = Trip::with('traveler')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.trips.index', compact('trips'));
    }

    public function show(Trip $trip)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $trip->load('bookings.lineItems.travellers', 'travellers');
        return view('admin.trips.show', compact('trip'));
    }

    public function create()
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $travellers = TravelerAccount::all();
        return view('admin.trips.create', compact('travellers'));
    }

    public function store(Request $request)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $request->validate([
            'traveler_account_id' => 'required|exists:traveler_accounts,id',
            'title' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        Trip::create($request->all());

        return redirect()->route('admin.trips.index')->with('success', 'Trip created successfully.');
    }

    public function edit(Trip $trip)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $travellers = TravelerAccount::all();
        return view('admin.trips.edit', compact('trip', 'travellers'));
    }

    public function update(Request $request, Trip $trip)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $request->validate([
            'traveler_account_id' => 'required|exists:traveler_accounts,id',
            'title' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:planned,active,completed,cancelled',
        ]);

        $trip->update($request->all());

        return redirect()->route('admin.trips.index')->with('success', 'Trip updated successfully.');
    }
}
