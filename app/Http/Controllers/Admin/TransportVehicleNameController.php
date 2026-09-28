<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransportVehicleName;
use App\Models\TransportVehicleType;
use Illuminate\Http\Request;

class TransportVehicleNameController extends Controller
{
    public function index()
    {
        $vehicleNames = TransportVehicleName::with('vehicleType')
            ->orderBy('name')
            ->paginate(25);

        return view('admin.vehicle_names.index', compact('vehicleNames'));
    }

    public function create()
    {
        $vehicleTypes = TransportVehicleType::active()->orderBy('name')->get();
        return view('admin.vehicle_names.create', compact('vehicleTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'transport_vehicle_type_id' => 'required|integer|exists:transport_vehicle_types,id',
            'seat_capacity' => 'required|integer|min:1|max:200',
            'is_active' => 'nullable',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $duplicate = TransportVehicleName::where('name', $data['name'])
            ->where('transport_vehicle_type_id', $data['transport_vehicle_type_id'])
            ->exists();
        if ($duplicate) {
            return back()->withInput()->withErrors(['name' => 'This vehicle name already exists for the selected type.']);
        }

        TransportVehicleName::create($data);

        return redirect()->route('admin.vehicle-names.index')->with('success', 'Vehicle name created.');
    }

    public function edit(TransportVehicleName $vehicleName)
    {
        $vehicleTypes = TransportVehicleType::active()
            ->orWhereKey($vehicleName->transport_vehicle_type_id)
            ->orderBy('name')
            ->get();

        return view('admin.vehicle_names.edit', compact('vehicleName', 'vehicleTypes'));
    }

    public function update(Request $request, TransportVehicleName $vehicleName)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'transport_vehicle_type_id' => 'required|integer|exists:transport_vehicle_types,id',
            'seat_capacity' => 'required|integer|min:1|max:200',
            'is_active' => 'nullable',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $duplicate = TransportVehicleName::where('name', $data['name'])
            ->where('transport_vehicle_type_id', $data['transport_vehicle_type_id'])
            ->where('id', '!=', $vehicleName->id)
            ->exists();
        if ($duplicate) {
            return back()->withInput()->withErrors(['name' => 'This vehicle name already exists for the selected type.']);
        }

        $vehicleName->update($data);

        return redirect()->route('admin.vehicle-names.index')->with('success', 'Vehicle name updated.');
    }

    public function destroy(TransportVehicleName $vehicleName)
    {
        if ($vehicleName->transports()->exists()) {
            return back()->with('error', 'This vehicle name cannot be deleted while Transport listings use it.');
        }

        $vehicleName->delete();

        return redirect()->route('admin.vehicle-names.index')->with('success', 'Vehicle name deleted.');
    }
}