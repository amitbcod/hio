<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransportVehicleType;
use Illuminate\Http\Request;

class TransportVehicleTypeController extends Controller
{
    public function index()
    {
        $vehicleTypes = TransportVehicleType::orderBy('seat_capacity', 'desc')
            ->orderBy('name')
            ->get();

        return view('admin.vehicle_types.index', compact('vehicleTypes'));
    }

    public function create()
    {
        return view('admin.vehicle_types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:transport_vehicle_types,name',
            'seat_capacity' => 'nullable|integer|min:1|max:200',
            'is_active' => 'nullable',
        ]);

        $data['is_active'] = $request->has('is_active');

        TransportVehicleType::create($data);

        return redirect()->route('admin.vehicle-types.index')->with('success', 'Vehicle type created.');
    }

    public function edit(TransportVehicleType $vehicleType)
    {
        return view('admin.vehicle_types.edit', compact('vehicleType'));
    }

    public function update(Request $request, TransportVehicleType $vehicleType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:transport_vehicle_types,name,' . $vehicleType->id,
            'seat_capacity' => 'nullable|integer|min:1|max:200',
            'is_active' => 'nullable',
        ]);

        $data['is_active'] = $request->has('is_active');

        $vehicleType->update($data);

        return redirect()->route('admin.vehicle-types.index')->with('success', 'Vehicle type updated.');
    }

    public function destroy(TransportVehicleType $vehicleType)
    {
        $vehicleType->delete();

        return redirect()->route('admin.vehicle-types.index')->with('success', 'Vehicle type deleted.');
    }
}
