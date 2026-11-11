<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'scope' => ['required', Rule::in(['global', 'country'])],
            'country_id' => ['nullable', 'required_if:scope,country', 'exists:countries,id'],
        ]);

        $roleAttributes = [
            'name' => $data['name'],
            'guard_name' => 'web',
            'scope' => $data['scope'],
            'country_id' => $data['scope'] === 'country' ? $data['country_id'] : null,
            'role_scope_key' => $data['scope'] === 'country' ? 'country:'.$data['country_id'] : 'global:0',
        ];
        if (Schema::hasColumn('roles', 'business_id')) {
            $roleAttributes['business_id'] = null;
        }
        Role::forceCreate($roleAttributes);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function update(Request $request, Role $role)
    {
        if (! session('admin_id')) {
            return redirect()->route('admin.login');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('roles', 'name')
                ->where('guard_name', 'web')->ignore($role->id)],
            'scope' => ['required', Rule::in(['global', 'country'])],
            'country_id' => ['nullable', 'required_if:scope,country', 'exists:countries,id'],
        ]);
        $scopeChanged = $role->scope !== $data['scope']
            || (int) $role->country_id !== (int) ($data['country_id'] ?? 0);
        abort_if(
            $scopeChanged
            && ($role->users()->exists()
                || (\Illuminate\Support\Facades\Schema::hasTable('operator_users')
                    && \App\Models\OperatorUser::query()->where('role', $role->name)->exists())),
            422,
            'A role scope cannot be changed while users are assigned to it.',
        );

        DB::transaction(function () use ($role, $data): void {
            $oldName = $role->name;
            $roleAttributes = [
                'name' => $data['name'],
                'scope' => $data['scope'],
                'country_id' => $data['scope'] === 'country' ? $data['country_id'] : null,
                'role_scope_key' => $data['scope'] === 'country' ? 'country:'.$data['country_id'] : 'global:0',
            ];
            if (Schema::hasColumn('roles', 'business_id')) {
                $roleAttributes['business_id'] = null;
            }
            $role->forceFill($roleAttributes)->save();
            if ($oldName !== $role->name && \Illuminate\Support\Facades\Schema::hasTable('operator_users')) {
                \App\Models\OperatorUser::query()->where('role', $oldName)->update(['role' => $role->name]);
                if (\Illuminate\Support\Facades\Schema::hasTable('operator_role_module_permissions')) {
                    DB::table('operator_role_module_permissions')
                        ->where('role_name', $oldName)
                        ->update(['role_name' => $role->name]);
                }
            }
        });

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if (! session('admin_id')) {
            return redirect()->route('admin.login');
        }

        abort_if(
            $role->users()->exists()
            || (\Illuminate\Support\Facades\Schema::hasTable('operator_users')
                && \App\Models\OperatorUser::query()->where('role', $role->name)->exists()),
            422,
            'This role is assigned to users and cannot be deleted.',
        );
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}