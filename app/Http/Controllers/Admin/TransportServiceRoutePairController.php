<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\TransportServiceRoutePair;
use Illuminate\Http\Request;

class TransportServiceRoutePairController extends Controller
{
    public function index()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $pairs = TransportServiceRoutePair::orderBy('service_type')->orderBy('route_from')->orderBy('route_to')->get();
        $regions = Region::orderBy('name')->pluck('name')->toArray();
        $serviceTypes = [
            'airport_transfer' => 'Airport Transfer',
            'activity_transfer' => 'Activity Transfer',
            'full_day_sightseeing' => 'Full Day Sightseeing',
        ];

        return view('admin.transport-service-route-pairs.index', compact('pairs', 'regions', 'serviceTypes'));
    }

    public function store(Request $request)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $data = $request->validate([
            'service_type' => 'required|string|in:airport_transfer,activity_transfer,full_day_sightseeing',
            'route_from' => 'required|string',
            'route_to' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        TransportServiceRoutePair::create($data + ['is_active' => (bool) ($request->boolean('is_active') ?? true)]);

        return redirect()->route('admin.transport-service-route-pairs.index')->with('success', 'Route pair added successfully.');
    }

    public function update(Request $request, TransportServiceRoutePair $transportServiceRoutePair)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $data = $request->validate([
            'service_type' => 'required|string|in:airport_transfer,activity_transfer,full_day_sightseeing',
            'route_from' => 'required|string',
            'route_to' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $transportServiceRoutePair->update($data + ['is_active' => (bool) ($request->boolean('is_active') ?? true)]);

        return redirect()->route('admin.transport-service-route-pairs.index')->with('success', 'Route pair updated successfully.');
    }

    public function destroy(TransportServiceRoutePair $transportServiceRoutePair)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $transportServiceRoutePair->delete();

        return redirect()->route('admin.transport-service-route-pairs.index')->with('success', 'Route pair deleted successfully.');
    }
}
