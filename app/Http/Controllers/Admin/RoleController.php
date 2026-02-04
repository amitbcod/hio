<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        if (!class_exists(Role::class) || !\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            return view('admin.roles.index', ['roles' => collect()])->with('error', 'Roles not available until permissions are installed and migrated.');
        }

        $query = Role::query();
        if (\Illuminate\Support\Facades\Schema::hasColumn('roles', 'business_id')) {
            $query->orderBy('business_id');
        }
        $roles = $query->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        if (!class_exists(Role::class) || !\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            return redirect()->route('admin.roles.index')->with('error', 'Roles not available until permissions are installed and migrated.');
        }

        $request->validate([
            'name' => 'required|string|max:191',
            'business_id' => 'nullable|exists:businesses,id'
        ]);

        Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
            'business_id' => $request->business_id ?: null,
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }
}