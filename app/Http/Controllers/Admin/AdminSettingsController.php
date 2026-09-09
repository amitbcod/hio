<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
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

        return view('admin.settings', compact('admin'));
    }

    public function defaultPackagePolicy()
    {
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

        $request->validate([
            'business_name' => 'nullable|string|max:191',
            'business_address' => 'nullable|string|max:2000',
            'email' => 'required|email|max:191|unique:admin_users,email,' . $admin->id,
            'phone_number' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:100',
            'brn_number' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $admin->business_name = $request->input('business_name');
        $admin->business_address = $request->input('business_address');
        $admin->email = $request->input('email');
        $admin->phone_number = $request->input('phone_number');
        $admin->vat_number = $request->input('vat_number');
        $admin->brn_number = $request->input('brn_number');

        if ($request->hasFile('logo')) {
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

        return redirect()->route('admin.settings.edit')->with('success', 'Settings saved successfully.');
    }
}
