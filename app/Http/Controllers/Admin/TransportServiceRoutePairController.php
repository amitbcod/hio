<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Region;
use App\Models\TransportServiceRoutePair;
use App\Support\CountryAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransportServiceRoutePairController extends Controller
{
    public function index()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $countryId = CountryAccess::countryId()
            ?? (int) request()->query('country_id', Country::query()->where('iso_code', 'MU')->value('id'));
        $pairs = TransportServiceRoutePair::where('country_id', $countryId)
            ->orderBy('service_type')->orderBy('route_from')->orderBy('route_to')->get();
        $regions = Region::where('country_id', $countryId)->orderBy('name')->pluck('name')->toArray();
        $countries = Country::where('is_active', true)->orWhereKey($countryId)->orderBy('name')->get();
        $serviceTypes = [
            'airport_transfer' => 'Airport Transfer',
            'activity_transfer' => 'Activity Transfer',
            'hotel_transfer' => 'Hotel Transfer',
            'full_day_sightseeing' => 'Full Day Sightseeing',
            'half_day_sightseeing' => 'Half Day Sightseeing',
        ];

        return view('admin.transport-service-route-pairs.index', compact(
            'pairs',
            'regions',
            'countries',
            'countryId',
            'serviceTypes'
        ));
    }

    public function store(Request $request)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $countryId = CountryAccess::countryId() ?? (int) $request->input('country_id');
        abort_if($countryId <= 0, 422, 'A country must be selected.');

        $data = $request->validate([
            'service_type' => 'required|string|in:airport_transfer,activity_transfer,hotel_transfer,full_day_sightseeing,half_day_sightseeing',
            'route_from' => 'required|string',
            'route_to' => 'required|string',
            'country_id' => CountryAccess::isMpoAdmin()
                ? 'prohibited'
                : ['required', 'integer', Rule::exists('countries', 'id')->where('is_active', true)],
            'is_active' => 'nullable|boolean',
            'trip_time_minutes' => 'nullable|integer|min:0',
            'buffer_time_minutes' => 'nullable|integer|min:0',
        ]);
        $data['country_id'] = $countryId;

        $request->validate([
            'service_type' => [
                Rule::unique('transport_service_route_pairs')
                    ->where('country_id', $countryId)
                    ->where('route_from', $data['route_from'])
                    ->where('route_to', $data['route_to']),
            ],
        ]);
        TransportServiceRoutePair::create($data + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.transport-service-route-pairs.index', ['country_id' => $countryId])
            ->with('success', 'Route pair added successfully.');
    }

    public function update(Request $request, TransportServiceRoutePair $transportServiceRoutePair)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $countryId = CountryAccess::countryId() ?? (int) $request->input('country_id', $transportServiceRoutePair->country_id);
        abort_if($countryId <= 0, 422, 'A country must be selected.');

        $data = $request->validate([
            'service_type' => 'required|string|in:airport_transfer,activity_transfer,hotel_transfer,full_day_sightseeing,half_day_sightseeing',
            'route_from' => 'required|string',
            'route_to' => 'required|string',
            'country_id' => CountryAccess::isMpoAdmin()
                ? 'prohibited'
                : ['required', 'integer', Rule::exists('countries', 'id')->where('is_active', true)],
            'is_active' => 'nullable|boolean',
            'trip_time_minutes' => 'nullable|integer|min:0',
            'buffer_time_minutes' => 'nullable|integer|min:0',
        ]);
        $data['country_id'] = $countryId;
        $request->validate([
            'service_type' => [
                Rule::unique('transport_service_route_pairs')
                    ->ignore($transportServiceRoutePair->getKey())
                    ->where('country_id', $countryId)
                    ->where('route_from', $data['route_from'])
                    ->where('route_to', $data['route_to']),
            ],
        ]);

        $transportServiceRoutePair->update($data + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.transport-service-route-pairs.index', ['country_id' => $countryId])
            ->with('success', 'Route pair updated successfully.');
    }

    public function destroy(TransportServiceRoutePair $transportServiceRoutePair)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $transportServiceRoutePair->delete();

        return redirect()->route('admin.transport-service-route-pairs.index', [
            'country_id' => $transportServiceRoutePair->country_id,
        ])->with('success', 'Route pair deleted successfully.');
    }
}
