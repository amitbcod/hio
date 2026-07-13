<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function index()
    {
        $places = Place::orderBy('route_region')->orderBy('place_name')->get();

        return view('admin.places.index', compact('places'));
    }

    public function create()
    {
        $regions = ['Airport', 'South', 'North'];

        return view('admin.places.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'place_name' => 'required|string|max:100|unique:places,place_name',
            'route_region' => 'required|in:Airport,South,North',
            'is_active' => 'nullable',
        ]);

        $data['is_active'] = $request->has('is_active');

        Place::create($data);

        return redirect()->route('admin.places.index')->with('success', 'Hotel / City Mapping created.');
    }

    public function edit(Place $place)
    {
        $regions = ['Airport', 'South', 'North'];

        return view('admin.places.edit', compact('place', 'regions'));
    }

    public function update(Request $request, Place $place)
    {
        $data = $request->validate([
            'place_name' => 'required|string|max:100|unique:places,place_name,' . $place->id,
            'route_region' => 'required|in:Airport,South,North',
            'is_active' => 'nullable',
        ]);

        $data['is_active'] = $request->has('is_active');

        $place->update($data);

        return redirect()->route('admin.places.index')->with('success', 'Hotel / City Mapping updated.');
    }

    public function destroy(Place $place)
    {
        $place->delete();

        return redirect()->route('admin.places.index')->with('success', 'Hotel / City Mapping deleted.');
    }
}
