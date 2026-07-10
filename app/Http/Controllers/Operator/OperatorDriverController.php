<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\OperatorDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class OperatorDriverController extends Controller
{
    public function index()
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        // Get drivers for this operator
        if (!empty($operator->business_id)) {
            $drivers = OperatorDriver::where('business_id', $operator->business_id)
                ->orderBy('driver_name')
                ->paginate(20);
        } else {
            $drivers = OperatorDriver::where('operator_id', $operator->operator_id)
                ->orderBy('driver_name')
                ->paginate(20);
        }

        return view('operator.drivers.index', compact('drivers'));
    }

    public function create()
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        return view('operator.drivers.create');
    }

    public function store(Request $request)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        // Build validation rules dynamically in case the DB schema is not yet updated
        $rules = [
            'driver_name' => 'required|string|max:150',
            'driver_mobile_no' => 'nullable|string|max:30',
            'license_expiry_date' => 'required|date|after:today',
            'driver_status' => 'nullable|in:Active,Off Duty,Sick Leave,Suspended,Inactive',
            'shift_start_time' => 'nullable|date_format:H:i',
            'shift_end_time' => 'nullable|date_format:H:i',
            'driver_break_min' => 'nullable|integer|min:0',
            'languages' => 'nullable|string|max:200',
            'home_zone' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
        ];

        // Only validate driver_license_no uniqueness if the column exists in the database
        if (Schema::hasColumn('operator_drivers', 'driver_license_no')) {
            $rules['driver_license_no'] = 'required|string|unique:operator_drivers,driver_license_no|max:60';
        } else {
            // If column missing, still require the field input but skip unique DB check
            $rules['driver_license_no'] = 'required|string|max:60';
        }

        $validated = $request->validate($rules);

        $validated['driver_id'] = OperatorDriver::generateDriverId();
        $validated['operator_id'] = $operator->operator_id;
        if (!empty($operator->business_id)) {
            $validated['business_id'] = $operator->business_id;
        }

        // Backwards-compatibility: some installs may have legacy columns.
        if (Schema::hasColumn('operator_drivers', 'full_name') && empty($validated['full_name'])) {
            $validated['full_name'] = $validated['driver_name'] ?? null;
        }
        if (Schema::hasColumn('operator_drivers', 'license_number') && empty($validated['license_number'])) {
            $validated['license_number'] = $validated['driver_license_no'] ?? null;
        }
        if (Schema::hasColumn('operator_drivers', 'license_expiry') && empty($validated['license_expiry'])) {
            $validated['license_expiry'] = $validated['license_expiry_date'] ?? null;
        }
        if (Schema::hasColumn('operator_drivers', 'email')) {
            $validated['email'] = $request->filled('email') ? $request->input('email') : null;
        }

        $driver = OperatorDriver::create($validated + [
            'driver_id' => OperatorDriver::generateDriverId(),
            'operator_id' => $operator->operator_id,
            'business_id' => $operator->business_id ?? null,
        ]);

        return redirect()->route('operator.drivers.index')
            ->with('success', 'Driver created successfully. Please upload required documents.');
    }

    public function show(OperatorDriver $driver)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        // Check authorization
        if (!empty($operator->business_id)) {
            if ($driver->business_id !== $operator->business_id) {
                abort(403);
            }
        } else {
            if ($driver->operator_id !== $operator->operator_id) {
                abort(403);
            }
        }

        $expiryWarnings = $driver->getDocumentExpiryWarnings();

        return view('operator.drivers.show', compact('driver', 'expiryWarnings'));
    }

    public function edit(OperatorDriver $driver)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        // Check authorization
        if (!empty($operator->business_id)) {
            if ($driver->business_id !== $operator->business_id) {
                abort(403);
            }
        } else {
            if ($driver->operator_id !== $operator->operator_id) {
                abort(403);
            }
        }

        return view('operator.drivers.edit', compact('driver'));
    }

    public function update(Request $request, OperatorDriver $driver)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        // Check authorization
        if (!empty($operator->business_id)) {
            if ($driver->business_id !== $operator->business_id) {
                abort(403);
            }
        } else {
            if ($driver->operator_id !== $operator->operator_id) {
                abort(403);
            }
        }

        $rules = [
            'driver_name' => 'required|string|max:150',
            'driver_mobile_no' => 'nullable|string|max:30',
            'license_expiry_date' => 'required|date',
            'driver_status' => 'nullable|in:Active,Off Duty,Sick Leave,Suspended,Inactive',
            'shift_start_time' => 'nullable|date_format:H:i',
            'shift_end_time' => 'nullable|date_format:H:i',
            'driver_break_min' => 'nullable|integer|min:0',
            'languages' => 'nullable|string|max:200',
            'home_zone' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
        ];

        if (Schema::hasColumn('operator_drivers', 'driver_license_no')) {
            $rules['driver_license_no'] = 'required|string|unique:operator_drivers,driver_license_no,' . $driver->id . '|max:60';
        } else {
            $rules['driver_license_no'] = 'required|string|max:60';
        }

        $validated = $request->validate($rules);

        if (Schema::hasColumn('operator_drivers', 'full_name') && empty($validated['full_name'])) {
            $validated['full_name'] = $validated['driver_name'] ?? null;
        }

        $driver->update($validated);

        return redirect()->route('operator.drivers.edit', $driver->id)
            ->with('success', 'Driver information updated successfully.');
    }

    public function destroy(OperatorDriver $driver)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        // Check authorization
        if (!empty($operator->business_id)) {
            if ($driver->business_id !== $operator->business_id) {
                abort(403);
            }
        } else {
            if ($driver->operator_id !== $operator->operator_id) {
                abort(403);
            }
        }

        $driver->delete();

        return redirect()->route('operator.drivers.index')
            ->with('success', 'Driver deleted successfully.');
    }

    /**
     * Verify driver documents
     */
    public function verifyDocuments(Request $request, OperatorDriver $driver)
    {
        $operator = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        // Check authorization
        if (!empty($operator->business_id)) {
            if ($driver->business_id !== $operator->business_id) {
                abort(403);
            }
        } else {
            if ($driver->operator_id !== $operator->operator_id) {
                abort(403);
            }
        }

        $request->validate([
            'driver_status' => 'nullable|in:Active,Off Duty,Sick Leave,Suspended,Inactive',
        ]);

        if ($request->has('driver_status')) {
            $driver->driver_status = $request->driver_status;
            $driver->save();
        }

        return redirect()->route('operator.drivers.show', $driver->id)
            ->with('success', 'Driver documents status updated.');
    }
}
