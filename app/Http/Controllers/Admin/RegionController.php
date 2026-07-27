<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;

class RegionController extends Controller
{
    public function index()
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $regions = Region::orderBy('name')->get();
        return view('admin.regions.index', compact('regions'));
    }

    public function create()
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        return view('admin.regions.create');
    }

    public function store(Request $request)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $request->validate([
            'name' => 'required|string|max:191|unique:regions,name',
        ]);

        Region::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.regions.index')->with('success', 'Region created successfully.');
    }

    public function edit(Region $region)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        return view('admin.regions.edit', compact('region'));
    }

    public function update(Request $request, Region $region)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $request->validate([
            'name' => 'required|string|max:191|unique:regions,name,' . $region->id,
        ]);

        $region->name = $request->name;
        $region->save();

        return redirect()->route('admin.regions.index')->with('success', 'Region updated successfully.');
    }

    public function destroy(Region $region)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $region->delete();
        return redirect()->route('admin.regions.index')->with('success', 'Region deleted successfully.');
    }
}
