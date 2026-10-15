<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\Country;
use App\Support\CountryAccess;
use Illuminate\Validation\Rule;

class RegionController extends Controller
{
    public function index()
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $regions = Region::with('country')->orderBy('name')->get();
        return view('admin.regions.index', compact('regions'));
    }

    public function create()
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        return view('admin.regions.create', [
            'countries' => Country::where('is_active', true)->orderBy('name')->get(),
            'isMpo' => CountryAccess::isMpoAdmin(),
        ]);
    }

    public function store(Request $request)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $countryId = $this->countryId($request);
        $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('regions', 'name')->where('country_id', $countryId)],
        ]);

        Region::create([
            'name' => $request->name,
            'country_id' => $countryId,
        ]);

        return redirect()->route('admin.regions.index')->with('success', 'Region created successfully.');
    }

    public function edit(Region $region)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        return view('admin.regions.edit', [
            'region' => $region,
            'countries' => Country::where('is_active', true)->orWhereKey($region->country_id)->orderBy('name')->get(),
            'isMpo' => CountryAccess::isMpoAdmin(),
        ]);
    }

    public function update(Request $request, Region $region)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $countryId = $this->countryId($request, $region->country_id);
        $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('regions', 'name')->where('country_id', $countryId)->ignore($region->id)],
        ]);

        $region->name = $request->name;
        $region->country_id = $countryId;
        $region->save();

        return redirect()->route('admin.regions.index')->with('success', 'Region updated successfully.');
    }

    public function destroy(Region $region)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $region->delete();
        return redirect()->route('admin.regions.index')->with('success', 'Region deleted successfully.');
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
