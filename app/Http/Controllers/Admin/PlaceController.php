<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\Region;
use App\Models\Country;
use App\Support\CountryAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlaceController extends Controller
{
    public function index()
    {
        $places = Place::orderBy('route_region')->orderBy('place_name')->get();

        return view('admin.places.index', compact('places'));
    }

    public function create()
    {
        $selectedCountryId = CountryAccess::countryId()
            ?? (int) request()->query('country_id', Country::where('iso_code', 'MU')->value('id'));
        $regions = Region::where('country_id', $selectedCountryId)->orderBy('name')->pluck('name')->toArray();

        return view('admin.places.create', [
            'regions' => $regions,
            'countries' => Country::where('is_active', true)->orderBy('name')->get(),
            'selectedCountryId' => $selectedCountryId,
            'isMpo' => CountryAccess::isMpoAdmin(),
        ]);
    }

    public function store(Request $request)
    {
        $countryId = $this->countryId($request);
        $regionNames = Region::where('country_id', $countryId)->pluck('name')->all();

        $data = $request->validate([
            'place_name' => ['required', 'string', 'max:100', Rule::unique('places', 'place_name')->where('country_id', $countryId)],
            'route_region' => ['required', Rule::in($regionNames)],
            'is_active' => 'nullable',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['country_id'] = $countryId;

        Place::create($data);

        return redirect()->route('admin.places.index')->with('success', 'Hotel / City Mapping created.');
    }

    public function edit(Place $place)
    {
        $regions = Region::where('country_id', $place->country_id)->orderBy('name')->pluck('name')->toArray();

        return view('admin.places.edit', [
            'place' => $place,
            'regions' => $regions,
            'countries' => Country::where('is_active', true)->orWhereKey($place->country_id)->orderBy('name')->get(),
            'isMpo' => CountryAccess::isMpoAdmin(),
        ]);
    }

    public function update(Request $request, Place $place)
    {
        $countryId = $this->countryId($request, $place->country_id);
        $regionNames = Region::where('country_id', $countryId)->pluck('name')->all();

        $data = $request->validate([
            'place_name' => ['required', 'string', 'max:100', Rule::unique('places', 'place_name')->where('country_id', $countryId)->ignore($place->id)],
            'route_region' => ['required', Rule::in($regionNames)],
            'is_active' => 'nullable',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['country_id'] = $countryId;

        $place->update($data);

        return redirect()->route('admin.places.index')->with('success', 'Hotel / City Mapping updated.');
    }

    public function destroy(Place $place)
    {
        $place->delete();

        return redirect()->route('admin.places.index')->with('success', 'Hotel / City Mapping deleted.');
    }

    private function countryId(Request $request, ?int $fallback = null): int
    {
        if (CountryAccess::isMpoAdmin()) {
            return (int) CountryAccess::countryId();
        }

        $data = $request->validate([
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')->where('is_active', true)],
        ]);

        return (int) ($data['country_id'] ?? $fallback);
    }
}
