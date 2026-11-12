<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class OperatorRoleController extends Controller
{
    public function index(Request $request)
    {
        $owner = $this->owner();
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->where(function ($query) use ($owner): void {
                $query->where('scope', 'global')
                    ->orWhere(fn ($country) => $country
                        ->where('scope', 'country')
                        ->where('country_id', $owner->country_id));
            })
            ->orderBy('scope')
            ->orderBy('name')
            ->get();

        return view('mpo.operator-roles.index', compact('roles', 'owner'));
    }

    public function store(Request $request)
    {
        $owner = $this->owner();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('roles', 'name')->where('guard_name', 'web')],
        ]);

        $roleAttributes = [
            'name' => $data['name'],
            'guard_name' => 'web',
        ];
        if (Schema::hasColumn('roles', 'scope')) {
            $roleAttributes['scope'] = 'country';
        }
        if (Schema::hasColumn('roles', 'country_id')) {
            $roleAttributes['country_id'] = $owner->country_id;
        }
        if (Schema::hasColumn('roles', 'role_scope_key')) {
            $roleAttributes['role_scope_key'] = 'country:'.$owner->country_id;
        }
        if (Schema::hasColumn('roles', 'business_id')) {
            $roleAttributes['business_id'] = null;
        }
        Role::forceCreate($roleAttributes);

        return redirect()->route('mpo.operator-roles.index')->with('success', 'Country role created.');
    }

    public function update(Request $request, Role $role)
    {
        $owner = $this->owner();
        $this->assertCountryRole($role, $owner);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('roles', 'name')
                ->where('guard_name', 'web')->ignore($role->id)],
        ]);

        DB::transaction(function () use ($role, $data): void {
            $oldName = $role->name;
            $attributes = ['name' => $data['name']];
            if (Schema::hasColumn('roles', 'scope')) {
                $attributes['scope'] = $role->scope;
            }
            if (Schema::hasColumn('roles', 'country_id')) {
                $attributes['country_id'] = $role->country_id;
            }
            if (Schema::hasColumn('roles', 'role_scope_key')) {
                $attributes['role_scope_key'] = $role->role_scope_key;
            }
            if (Schema::hasColumn('roles', 'business_id')) {
                $attributes['business_id'] = $role->business_id;
            }
            $role->forceFill($attributes)->save();
            if ($oldName !== $role->name && \Illuminate\Support\Facades\Schema::hasTable('operator_users')) {
                \App\Models\OperatorUser::query()->where('role', $oldName)->update(['role' => $role->name]);
                if (\Illuminate\Support\Facades\Schema::hasTable('operator_role_module_permissions')) {
                    DB::table('operator_role_module_permissions')
                        ->where('role_name', $oldName)
                        ->update(['role_name' => $role->name]);
                }
            }
        });

        return redirect()->route('mpo.operator-roles.index')->with('success', 'Country role updated.');
    }

    public function destroy(Role $role)
    {
        $owner = $this->owner();
        $this->assertCountryRole($role, $owner);
        abort_if(
            $role->users()->exists()
            || (\Illuminate\Support\Facades\Schema::hasTable('operator_users')
                && \App\Models\OperatorUser::query()->where('role', $role->name)->exists()),
            422,
            'This role is assigned to users and cannot be deleted.',
        );
        $role->delete();

        return redirect()->route('mpo.operator-roles.index')->with('success', 'Country role deleted.');
    }

    private function owner(): AdminUser
    {
        $owner = auth('mpo')->user();
        abort_unless(
            $owner instanceof AdminUser
            && $owner->admin_type === 'mpo'
            && $owner->status === 'active'
            && $owner->country_id,
            403,
        );

        return $owner;
    }

    private function assertCountryRole(Role $role, AdminUser $owner): void
    {
        abort_unless(
            $role->guard_name === 'web'
            && $role->scope === 'country'
            && (int) $role->country_id === (int) $owner->country_id,
            403,
            'Only roles in your country can be modified.',
        );
    }
}
