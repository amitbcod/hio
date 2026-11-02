<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Module;
use App\Models\MpoRoleDelegation;
use App\Models\MpoRoleModulePermission;
use App\Models\MpoRoleModuleSetting;
use App\Models\MpoStaffUser;
use App\Services\MpoStaffPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RolePermissionController extends Controller
{
    public function __construct(private readonly MpoStaffPermissionService $permissions) {}

    public function index(Request $request)
    {
        [$owner, $actor] = $this->context($request);
        abort_unless($this->permissions->can($actor, 'capacity-permissions', 'read'), 403);

        $roles = collect(config('mpo_staff.roles', []))
            ->map(function (string $role) use ($owner, $actor): array {
                $aliases = $role === 'Finance' ? ['Finance', 'Accountant'] : [$role];

                return [
                    'name' => $role,
                    'description' => config("mpo_staff.role_descriptions.{$role}", ''),
                    'staff_count' => $owner->staffUsers()->whereIn('role', $aliases)->count(),
                    'configurable' => $this->permissions->canConfigureRole($actor, $role),
                    'permissions' => $this->permissions->roleMatrix((int) $owner->id, $role),
                ];
            });

        $modules = Module::query()
            ->whereIn('slug', array_keys(config('mpo_staff.modules', [])))
            ->orderBy('name')
            ->get()
            ->each(function (Module $module): void {
                $definition = config("mpo_staff.registry.{$module->slug}", []);
                $module->setAttribute('category', $definition['category'] ?? 'MPO Onboarding');
                $module->setAttribute('display_name', $definition['name'] ?? $module->name);
                $module->setAttribute('display_description', $definition['description'] ?? $module->description);
            })
            ->sortBy(fn (Module $module): string => $module->category.'|'.$module->display_name)
            ->values();
        $selectedRole = $request->query('role');
        if (! in_array($selectedRole, config('mpo_staff.roles', []), true)) {
            $selectedRole = $roles->firstWhere('configurable', true)['name'] ?? $roles->first()['name'];
        }
        abort_unless(
            $this->permissions->canConfigureRole($actor, $selectedRole),
            403,
            'You are not authorized to view this role configuration.',
        );

        $settings = [];
        foreach ($modules as $module) {
            $settings[$module->slug] = $this->permissions->roleSettings(
                (int) $owner->id,
                $selectedRole,
                (int) $module->id,
            );
        }

        $delegatedRoles = MpoRoleDelegation::query()
            ->where('admin_user_id', $owner->id)
            ->whereIn('assigner_role', $selectedRole === 'Finance' ? ['Finance', 'Accountant'] : [$selectedRole])
            ->pluck('assignable_role')
            ->map(fn (string $role): string => $this->permissions->canonicalRole($role))
            ->unique()
            ->values()
            ->all();

        return view('mpo.profile.capacity-permissions', [
            'mpoAdmin' => $owner,
            'actor' => $actor,
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'modules' => $modules,
            'settings' => $settings,
            'roleMatrix' => $this->permissions->roleMatrix((int) $owner->id, $selectedRole),
            'delegatableActions' => $this->permissions->delegatedActionMatrix($actor, $modules->all()),
            'canConfigureSelectedRole' => $this->permissions->canConfigureRole($actor, $selectedRole),
            'canChangeRoleSettings' => $actor instanceof AdminUser,
            'canChangeDelegations' => $actor instanceof AdminUser
                || ($actor instanceof MpoStaffUser && $this->permissions->can($actor, 'users', 'update')),
            'delegatedRoles' => $delegatedRoles,
            'supportedActions' => config('mpo_staff.modules', []),
        ]);
    }

    public function update(Request $request)
    {
        [$owner, $actor] = $this->context($request);
        abort_unless($this->permissions->can($actor, 'capacity-permissions', 'update'), 403);

        $data = $request->validate([
            'role' => ['required', Rule::in(config('mpo_staff.roles', []))],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*.allowed' => ['required', 'boolean'],
            'permissions.*.*.can_delegate' => ['nullable', 'boolean'],
            'settings' => ['sometimes', 'array'],
            'settings.*' => ['array'],
            'settings.*.capacity_level' => ['nullable', Rule::in(array_keys(config('mpo_staff.capacity_levels', [])))],
            'settings.*.data_scope' => ['required', Rule::in(array_keys(config('mpo_staff.data_scopes', [])))],
            'settings.*.notes' => ['nullable', 'string', 'max:2000'],
            'assignable_roles' => ['sometimes', 'array'],
            'assignable_roles.*' => ['string', Rule::in(config('mpo_staff.roles', []))],
        ]);

        $role = $data['role'];
        abort_unless($this->permissions->canConfigureRole($actor, $role), 403, 'You are not authorized to configure this role.');
        $this->validatePermissionChanges($data['permissions'] ?? [], $actor);
        $this->validateSettingsChanges($data['settings'] ?? [], $actor, $owner, $role);
        $this->validateDelegationChanges($data['assignable_roles'] ?? [], $actor, $owner, $role);

        DB::transaction(function () use ($owner, $actor, $role, $data): void {
            $modules = Module::query()
                ->whereIn('slug', array_keys(config('mpo_staff.modules', [])))
                ->get()
                ->keyBy('slug');

            $previousCapacityLevels = [];
            foreach ($data['settings'] ?? [] as $slug => $values) {
                $module = $modules->get($slug);
                if (! $module) {
                    continue;
                }

                $previousCapacityLevels[$slug] = $this->permissions->roleSettings(
                    (int) $owner->id,
                    $role,
                    (int) $module->id,
                )['capacity_level'];
            }

            if ($actor instanceof AdminUser) {
                foreach ($previousCapacityLevels as $slug => $previousLevel) {
                    if ($previousLevel !== 'Section'
                        || ($data['settings'][$slug]['capacity_level'] ?? null) !== 'Tick Function') {
                        continue;
                    }

                    $module = $modules->get($slug);
                    MpoRoleModulePermission::query()
                        ->where('admin_user_id', $owner->id)
                        ->where('role', $role)
                        ->where('module_id', $module->id)
                        ->delete();
                }
            }

            foreach ($data['permissions'] ?? [] as $slug => $actions) {
                $module = $modules->get($slug);
                $sectionAccess = ($data['settings'][$slug]['capacity_level'] ?? null) === 'Section';
                foreach ($actions as $action => $settings) {
                    if (! $module || ! $this->permissions->canDelegate($actor, $slug, $action)) {
                        continue;
                    }

                    if (! $sectionAccess && ! $settings['allowed']) {
                        MpoRoleModulePermission::query()
                            ->where('admin_user_id', $owner->id)
                            ->where('role', $role)
                            ->where('module_id', $module->id)
                            ->where('action', $action)
                            ->delete();

                        continue;
                    }

                    MpoRoleModulePermission::query()->updateOrCreate(
                        [
                            'admin_user_id' => $owner->id,
                            'role' => $role,
                            'module_id' => $module->id,
                            'action' => $action,
                        ],
                        [
                            'is_allowed' => ! $sectionAccess,
                            'can_delegate' => (bool) ($settings['can_delegate'] ?? false),
                            'approval_limit' => null,
                        ],
                    );
                }
            }

            if ($actor instanceof AdminUser) {
                foreach ($data['settings'] ?? [] as $slug => $values) {
                    $module = $modules->get($slug);
                    if (! $module) {
                        continue;
                    }

                    MpoRoleModuleSetting::query()->updateOrCreate(
                        [
                            'admin_user_id' => $owner->id,
                            'role' => $role,
                            'module_id' => $module->id,
                        ],
                        [
                            'capacity_level' => $values['capacity_level'] ?? null,
                            'data_scope' => $values['data_scope'],
                            'notes' => $values['notes'] ?? null,
                        ],
                    );
                }
            }

            if (array_key_exists('assignable_roles', $data)
                && ($actor instanceof AdminUser || $this->permissions->can($actor, 'users', 'update'))) {
                $assignerAliases = $role === 'Finance' ? ['Finance', 'Accountant'] : [$role];
                MpoRoleDelegation::query()
                    ->where('admin_user_id', $owner->id)
                    ->whereIn('assigner_role', $assignerAliases)
                    ->delete();

                foreach (array_unique($data['assignable_roles']) as $assignableRole) {
                    MpoRoleDelegation::query()->create([
                        'admin_user_id' => $owner->id,
                        'assigner_role' => $role,
                        'assignable_role' => $assignableRole,
                    ]);
                }
            }
        });

        Log::info('MPO role permissions updated.', [
            'mpo_admin_id' => $owner->id,
            'role' => $role,
            'actor_type' => $actor instanceof MpoStaffUser ? 'staff' : 'mpo_admin',
            'actor_id' => $actor->id,
        ]);

        return redirect()->route('mpo.capacity-permissions', ['role' => $role])
            ->with('success', 'Role permissions and delegation settings have been saved.');
    }

    private function validatePermissionChanges(array $permissions, AdminUser|MpoStaffUser $actor): void
    {
        $supported = config('mpo_staff.modules', []);
        foreach ($permissions as $slug => $actions) {
            abort_unless(isset($supported[$slug]) && is_array($actions), 422, 'This module is unavailable for MPO staff permissions.');
            foreach ($actions as $action => $values) {
                abort_unless(
                    in_array($action, $supported[$slug], true) && is_array($values),
                    422,
                    'This module action is not supported.',
                );
                if ($values['allowed']) {
                    abort_unless($this->permissions->canDelegate($actor, $slug, $action), 403, 'You cannot delegate this module action.');
                }
                abort_if(
                    ($values['can_delegate'] ?? false) && ! ($values['allowed'] ?? false),
                    422,
                    'A permission must be allowed before it can be delegated.',
                );
            }
        }
    }

    private function validateSettingsChanges(array $settings, AdminUser|MpoStaffUser $actor, AdminUser $owner, string $role): void
    {
        if ($actor instanceof AdminUser) {
            $supportedSlugs = array_keys(config('mpo_staff.modules', []));
            abort_if(array_diff(array_keys($settings), $supportedSlugs) !== [], 422, 'Unsupported module settings were submitted.');

            return;
        }

        foreach ($settings as $slug => $values) {
            $module = Module::query()->where('slug', $slug)->first();
            abort_unless($module, 422, 'Unknown module.');
            $current = $this->permissions->roleSettings((int) $owner->id, $role, (int) $module->id);
            abort_unless(
                ($values['capacity_level'] ?? null) === $current['capacity_level']
                && ($values['data_scope'] ?? null) === $current['data_scope']
                && ($values['notes'] ?? null) === $current['notes'],
                403,
                'Only the MPO owner can change capacity, data scope, or role notes.',
            );
        }
    }

    private function validateDelegationChanges(
        array $roles,
        AdminUser|MpoStaffUser $actor,
        AdminUser $owner,
        string $assignerRole,
    ): void {
        $existing = MpoRoleDelegation::query()
            ->where('admin_user_id', $owner->id)
            ->whereIn(
                'assigner_role',
                $assignerRole === 'Finance' ? ['Finance', 'Accountant'] : [$assignerRole],
            )
            ->pluck('assignable_role')
            ->map(fn (string $role): string => $this->permissions->canonicalRole($role))
            ->unique()
            ->all();

        foreach (array_diff(array_unique($roles), $existing) as $role) {
            abort_unless($this->permissions->roleAssignable($actor, $role), 403, 'You cannot delegate this role.');
        }
    }

    private function context(Request $request): array
    {
        $owner = $request->attributes->get('mpo_admin');
        $actor = $request->attributes->get('mpo_staff_user', $owner);
        abort_unless($owner instanceof AdminUser && $owner->admin_type === 'mpo', 403);
        abort_unless($actor instanceof AdminUser || $actor instanceof MpoStaffUser, 403);

        return [$owner, $actor];
    }
}
