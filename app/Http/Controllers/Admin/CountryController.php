<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::query()->orderBy('name')->paginate(25);

        return view('admin.countries.index', compact('countries'));
    }

    public function create()
    {
        return view('admin.countries.form', ['country' => new Country()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        Country::query()->create($data);

        return redirect()->route('admin.countries.index')->with('success', 'Country created.');
    }

    public function edit(Country $country)
    {
        return view('admin.countries.form', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $data = $this->validated($request, $country);
        $data['is_active'] = $request->boolean('is_active');
        $country->update($data);

        return redirect()->route('admin.countries.index')->with('success', 'Country updated.');
    }

    private function validated(Request $request, ?Country $country = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('countries', 'name')->ignore($country?->id)],
            'iso_code' => ['required', 'string', 'size:2', Rule::unique('countries', 'iso_code')->ignore($country?->id)],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'time_zone' => ['nullable', 'timezone'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
