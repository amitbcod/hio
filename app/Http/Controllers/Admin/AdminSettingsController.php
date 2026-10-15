<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\TripActionDeadlineSetting;
use App\Models\Country;
use App\Support\CountryAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminSettingsController extends Controller
{
    public function edit()
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = AdminUser::find(session('admin_id'));
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $countries = Country::query()->where('is_active', true)->orderBy('name')->get();
        $defaultCountryId = Country::query()->where('iso_code', 'MU')->value('id');
        $selectedCountryId = CountryAccess::countryId()
            ?? (int) request()->query('country_id', $defaultCountryId);
        abort_unless($countries->contains('id', $selectedCountryId), 404);
        $tripActionDeadlineIntervals = TripActionDeadlineSetting::intervals($selectedCountryId);
        $isMpo = CountryAccess::isMpoAdmin();

        return view('admin.settings', compact(
            'admin',
            'tripActionDeadlineIntervals',
            'countries',
            'selectedCountryId',
            'isMpo'
        ));
    }

    public function defaultPackagePolicy()
    {
        abort_unless(CountryAccess::isGlobalAdmin(), 403);
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = AdminUser::find(session('admin_id')); 
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $packagePolicy = is_array($admin->package_policy ?? null) ? $admin->package_policy : [];

        return view('admin.package_default_policy', compact('admin', 'packagePolicy'));
    }

    public function saveDefaultPackagePolicy(Request $request)
    {
        abort_unless(CountryAccess::isGlobalAdmin(), 403);
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = AdminUser::find(session('admin_id'));
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $data = $request->input('package_policy');
        $admin->package_policy = is_array($data) ? $data : [];
        $admin->save();

        return redirect()->route('admin.policy.package-default-policy')->with('success', 'Default package policy saved successfully.');
    }

    public function defaultGroupPolicy()
    {
        abort_unless(CountryAccess::isGlobalAdmin(), 403);
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = AdminUser::find(session('admin_id'));
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $groupPolicy = is_array($admin->group_policy ?? null) ? $admin->group_policy : [];

        return view('admin.group_default_policy', compact('admin', 'groupPolicy'));
    }

    public function saveDefaultGroupPolicy(Request $request)
    {
        abort_unless(CountryAccess::isGlobalAdmin(), 403);
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = AdminUser::find(session('admin_id'));
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $data = $request->input('group_policy');
        $admin->group_policy = is_array($data) ? $data : [];
        $admin->save();

        return redirect()->route('admin.policy.group-default-policy')->with('success', 'Default group policy saved successfully.');
    }

    public function update(Request $request)
    {
        if (!session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $admin = AdminUser::find(session('admin_id'));
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $isMpo = CountryAccess::isMpoAdmin();
        $rules = [
            'trip_action_deadlines' => 'sometimes|array',
            'trip_action_deadlines.guest_details' => 'required_with:trip_action_deadlines|integer|min:0|max:8760',
            'trip_action_deadlines.driver_license_expire' => 'required_with:trip_action_deadlines|integer|min:0|max:8760',
            'trip_action_deadlines.driver_assign' => 'required_with:trip_action_deadlines|integer|min:0|max:8760',
            'trip_action_deadlines.vehicle_assign' => 'required_with:trip_action_deadlines|integer|min:0|max:8760',
        ];

        if ($isMpo) {
            $rules['country_id'] = 'prohibited';
        } else {
            $rules += [
                'country_id' => 'required|integer|exists:countries,id',
                'business_name' => 'nullable|string|max:191',
                'business_address' => 'nullable|string|max:2000',
                'email' => 'required|email|max:191|unique:admin_users,email,' . $admin->id,
                'phone_number' => 'nullable|string|max:100',
                'vat_number' => 'nullable|string|max:100',
                'brn_number' => 'nullable|string|max:100',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            ];
        }
        $request->validate($rules);

        $countryId = $isMpo ? CountryAccess::countryId() : (int) $request->input('country_id');
        abort_unless(Country::query()->whereKey($countryId)->where('is_active', true)->exists(), 422);

        if (! $isMpo) {
            $admin->business_name = $request->input('business_name');
            $admin->business_address = $request->input('business_address');
            $admin->email = $request->input('email');
            $admin->phone_number = $request->input('phone_number');
            $admin->vat_number = $request->input('vat_number');
            $admin->brn_number = $request->input('brn_number');
        }

        if ($request->has('trip_action_deadlines')) {
            TripActionDeadlineSetting::saveIntervals(
                $request->input('trip_action_deadlines'),
                (int) $admin->id,
                $countryId
            );
        }

        if (! $isMpo && $request->hasFile('logo')) {
            $uploadDir = public_path('uploads/admin-logos');
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            $logo = $request->file('logo');
            $filename = Str::slug($admin->business_name ?: $admin->name ?: 'admin') . '-' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->move($uploadDir, $filename);
            $admin->logo_path = 'uploads/admin-logos/' . $filename;
        }

        $admin->save();

        return redirect()->route('admin.settings.edit', ['country_id' => $countryId])
            ->with('success', 'Settings saved successfully.');
    }
}
