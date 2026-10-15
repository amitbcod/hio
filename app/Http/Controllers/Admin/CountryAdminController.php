<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CountryAdminController extends Controller
{
    public function index()
    {
        $administrators = AdminUser::query()
            ->with('country')
            ->where('admin_type', 'mpo')
            ->orderBy('name')
            ->paginate(25);

        return view('admin.country-admins.index', compact('administrators'));
    }

    public function create()
    {
        $countries = Country::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.country-admins.form', [
            'administrator' => new AdminUser(),
            'countries' => $countries,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $administrator = new AdminUser();
        $administrator->name = $data['name'];
        $administrator->email = $data['email'];
        $administrator->password_hash = Hash::make($data['password']);
        $administrator->role = 'admin';
        $administrator->admin_type = 'mpo';
        $administrator->country_id = $data['country_id'];
        $administrator->status = $data['status'];
        $administrator->save();

        return redirect()->route('admin.country-admins.index')->with('success', 'MPO administrator created.');
    }

    public function edit(AdminUser $administrator)
    {
        abort_unless($administrator->admin_type === 'mpo', 404);
        $countries = Country::query()->where('is_active', true)
            ->orWhereKey($administrator->country_id)
            ->orderBy('name')
            ->get();

        return view('admin.country-admins.form', compact('administrator', 'countries'));
    }

    public function update(Request $request, AdminUser $administrator)
    {
        abort_unless($administrator->admin_type === 'mpo', 404);
        $data = $this->validated($request, $administrator);
        $administrator->name = $data['name'];
        $administrator->email = $data['email'];
        $administrator->country_id = $data['country_id'];
        $administrator->status = $data['status'];
        if (filled($data['password'] ?? null)) {
            $administrator->password_hash = Hash::make($data['password']);
        }
        $administrator->save();

        return redirect()->route('admin.country-admins.index')->with('success', 'MPO administrator updated.');
    }

    private function validated(Request $request, ?AdminUser $administrator = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('admin_users', 'email')->ignore($administrator?->id)],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')->where('is_active', true)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => [$administrator ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }
}
