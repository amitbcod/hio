<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $operator = auth()->user();

        // Only show roles for the operator's business (and global roles)
        // If Spatie not available, return empty list and notice
        if (!class_exists(\Spatie\Permission\Models\Role::class) || !\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            $roles = collect();
            return view('operator.roles.index', compact('roles'))->with('error', 'Role management is not available until permissions are installed.');
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('roles', 'business_id')) {
            $roles = Role::orderBy('name')->get();
        } else {
            $roles = Role::where(function($q) use ($operator) {
                $q->whereNull('business_id');
                if (!empty($operator->business_id)) {
                    $q->orWhere('business_id', $operator->business_id);
                }
            })->orderBy('name')->get();
        }

        return view('operator.roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $operator = auth()->user();

        // Owners are NOT allowed to create roles; only admins create new roles
        return redirect()->route('operator.roles.index')->with('error', 'Only admins can create roles.');
    }

    public function edit(Role $role)
    {
        $operator = auth()->user();
        // only operators belonging to the same business (or global roles) may edit
        if (empty($operator->business_id) || !((is_null($role->business_id) || ($role->business_id ?? null) == $operator->business_id))) {
            return redirect()->route('operator.roles.index')->with('error', 'Unauthorized action.');
        }

        return view('operator.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $operator = auth()->user();
        if (empty($operator->business_id) || !((is_null($role->business_id) || ($role->business_id ?? null) == $operator->business_id))) {
            return redirect()->route('operator.roles.index')->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:191',
        ]);

        $role->name = $request->name;
        $role->save();

        return redirect()->route('operator.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        $operator = auth()->user();
        if (empty($operator->business_id) || !((is_null($role->business_id) || ($role->business_id ?? null) == $operator->business_id))) {
            return redirect()->route('operator.roles.index')->with('error', 'Unauthorized action.');
        }

        $role->delete();
        return redirect()->route('operator.roles.index')->with('success', 'Role deleted.');
    }

    public function permissions(Role $role)
    {
        $operator = auth()->user();

        // Operators in the same business (or global roles) may manage permissions
        if (empty($operator->business_id) || !(is_null($role->business_id) || ($role->business_id ?? null) == $operator->business_id)) {
            return redirect()->route('operator.roles.index')->with('error', 'Unauthorized action.');
        }

        // Load modules and role-module permissions if available
        $modules = \App\Models\Module::orderBy('name')->get();

        // Build list of roles owner may pick (global + business-scoped)
        $roles = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            $query = Role::query();
            if (\Illuminate\Support\Facades\Schema::hasColumn('roles', 'business_id')) {
                $query->where(function($q) use ($operator) {
                    $q->whereNull('business_id');
                    if (!empty($operator->business_id)) {
                        $q->orWhere('business_id', $operator->business_id);
                    }
                });
            }
            $roles = $query->orderBy('name')->get();
        }

        $roleModulePermissions = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('role_module_permissions')) {
            $roleModulePermissions = \App\Models\RoleModulePermission::where('role_id', $role->id)
                ->get()
                ->keyBy(function($row){ return $row->module->slug ?? $row->module_id; });
        }

        // For backwards-compat: also provide Spatie permissions list if present
        $permissions = collect();
        $rolePerms = [];
        if (class_exists(\Spatie\Permission\Models\Permission::class) && \Illuminate\Support\Facades\Schema::hasTable('permissions')) {
            $permissions = Permission::orderBy('name')->get();
            $rolePerms = $role->permissions->pluck('name')->toArray();
        }

        return view('operator.roles.permissions', compact('role', 'roles', 'modules', 'roleModulePermissions', 'permissions', 'rolePerms'));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        \Log::debug('updatePermissions called', ['role_id' => $role->id, 'request_permissions' => $request->permissions]);

        $operator = auth()->user();

        if (empty($operator->business_id) || !(is_null($role->business_id) || ($role->business_id ?? null) == $operator->business_id)) {
            \Log::debug('unauthorized in updatePermissions', ['operator' => $operator ? $operator->id : null, 'operator_business' => $operator->business_id ?? null, 'role_business' => $role->business_id ?? null]);
            return redirect()->route('operator.roles.index')->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'permissions' => 'nullable|array',
        ]);

        $operator = auth()->user();
        // Only operators in the same business (or global roles) may update role module perms
        if (empty($operator->business_id) || !(is_null($role->business_id) || ($role->business_id ?? null) == $operator->business_id)) {
            \Log::debug('unauthorized (2) in updatePermissions', ['operator' => $operator->id ?? null, 'operator_business' => $operator->business_id ?? null, 'role_business' => $role->business_id ?? null]);
            return redirect()->route('operator.roles.index')->with('error', 'Unauthorized action.');
        }

        // If role-module-permissions table exists, save per-module flags
        if (\Illuminate\Support\Facades\Schema::hasTable('role_module_permissions')) {
            \Log::debug('role_module_permissions table exists');
            $payload = $request->input('permissions', []); // expected shape: ['account' => ['Read','Create']]
            \Log::debug('payload', ['payload' => $payload]);

            foreach ($payload as $moduleSlug => $actions) {
                $module = \App\Models\Module::where('slug', $moduleSlug)->orWhere('name', $moduleSlug)->first();
                if (!$module) {
                    \Log::debug('module not found for slug', ['slug' => $moduleSlug]);
                    continue;
                }

                $row = \App\Models\RoleModulePermission::firstOrNew([
                    'role_id' => $role->id,
                    'module_id' => $module->id,
                    'business_id' => $operator->business_id,
                ]);

                $row->can_read = in_array('Read', $actions);
                $row->can_create = in_array('Create', $actions);
                $row->can_update = in_array('Update', $actions);
                $row->can_approve = in_array('Approve', $actions);
                $row->can_publish = in_array('Publish', $actions);
                $row->save();
                \Log::info('RoleModulePermission saved', ['role_id' => $role->id, 'module' => $module->slug, 'flags' => [
                    'read' => $row->can_read,
                    'create' => $row->can_create,
                    'update' => $row->can_update,
                    'approve' => $row->can_approve,
                    'publish' => $row->can_publish,
                ]]);
            }

            return redirect()->route('operator.roles.index')->with('success', 'Role module permissions updated.');
        }

        \Log::debug('role_module_permissions table does not exist - falling back');

        // Fallback: sync Spatie permissions
        $perms = $request->permissions ?? [];
        $role->syncPermissions($perms);

        return redirect()->route('operator.roles.index')->with('success', 'Role permissions updated.');
    }
}
