<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\Module;
use App\Models\MpoRoleDelegation;
use App\Models\MpoRoleModulePermission;
use App\Models\MpoRoleModuleSetting;
use App\Models\MpoStaffUser;
use Illuminate\Support\Collection;

class MpoStaffPermissionService
{
    public function canManage(AdminUser|MpoStaffUser $actor, string $action): bool
    {
        return $this->can($actor, 'users', $action);
    }

    public function can(AdminUser|MpoStaffUser $actor, string $moduleSlug, string $action): bool
    {
        if ($actor instanceof AdminUser && $actor->admin_type === 'mpo') {
            return true;
        }
        if (! $actor instanceof MpoStaffUser) {
            return false;
        }
        if (! $this->supports($moduleSlug, $action)) {
            return false;
        }

        $module = $this->module($moduleSlug);
        if (! $module || ! $this->supportsScope($actor, $module)) {
            return false;
        }

        return (bool) ($this->effectiveMatrix($actor)[$moduleSlug][$action]['allowed'] ?? false);
    }

    public function canDelegate(AdminUser|MpoStaffUser $actor, string $moduleSlug, string $action): bool
    {
        if ($actor instanceof AdminUser && $actor->admin_type === 'mpo') {
            return true;
        }
        if (! $actor instanceof MpoStaffUser) {
            return false;
        }
        if (! $this->supports($moduleSlug, $action)) {
            return false;
        }

        if (! $this->can($actor, $moduleSlug, $action)) {
            return false;
        }

        $permission = $this->effectiveMatrix($actor)[$moduleSlug][$action] ?? null;

        return (bool) ($permission && $permission['allowed'] && $permission['can_delegate']);
    }

    public function roleAssignable(AdminUser|MpoStaffUser $actor, string $role): bool
    {
        $role = $this->canonicalRole($role);
        if (! in_array($role, config('mpo_staff.roles', []), true)) {
            return false;
        }

        if ($actor instanceof AdminUser && $actor->admin_type === 'mpo') {
            return true;
        }
        if (! $actor instanceof MpoStaffUser) {
            return false;
        }

        $canAssign = MpoRoleDelegation::query()
            ->where('admin_user_id', $actor->admin_user_id)
            ->whereIn('assigner_role', $this->roleAliases($actor->role))
            ->whereIn('assignable_role', $this->roleAliases($role))
            ->exists();

        if (! $canAssign) {
            return false;
        }

        $rolePermissions = collect($this->roleMatrix((int) $actor->admin_user_id, $role))
            ->flatMap(fn (array $actions, string $module): array => collect($actions)
                ->map(fn (array $permission, string $action): array => [
                    'module' => $module,
                    'action' => $action,
                    ...$permission,
                ])->values()->all())
            ->contains(function (array $permission) use ($actor): bool {
                if (! $permission['allowed'] && $permission['can_delegate']) {
                    return true;
                }

                return $permission['allowed']
                    && ! $this->canDelegate($actor, $permission['module'], $permission['action']);
            });
        if ($rolePermissions) {
            return false;
        }

        $actorAssignableRoles = MpoRoleDelegation::query()
            ->where('admin_user_id', $actor->admin_user_id)
            ->whereIn('assigner_role', $this->roleAliases($actor->role))
            ->pluck('assignable_role')
            ->map(fn (string $assignableRole): string => $this->canonicalRole($assignableRole))
            ->unique()
            ->all();
        $targetAssignableRoles = MpoRoleDelegation::query()
            ->where('admin_user_id', $actor->admin_user_id)
            ->whereIn('assigner_role', $this->roleAliases($role))
            ->pluck('assignable_role')
            ->map(fn (string $assignableRole): string => $this->canonicalRole($assignableRole));

        return $targetAssignableRoles->every(
            fn (string $assignableRole): bool => in_array($assignableRole, $actorAssignableRoles, true),
        );
    }

    public function assignableRoles(AdminUser|MpoStaffUser $actor): array
    {
        return array_values(array_filter(
            config('mpo_staff.roles', []),
            fn (string $role): bool => $this->roleAssignable($actor, $role),
        ));
    }

    public function roleMatrix(int $ownerId, string $role): array
    {
        $role = $this->canonicalRole($role);
        $matrix = MpoRoleModulePermission::query()
            ->with('module')
            ->where('admin_user_id', $ownerId)
            ->whereIn('role', $this->roleAliases($role))
            ->orderBy('role')
            ->get()
            ->filter(fn (MpoRoleModulePermission $permission): bool => $permission->module !== null)
            ->filter(fn (MpoRoleModulePermission $permission): bool => $this->supports($permission->module->slug, $permission->action))
            ->groupBy(fn (MpoRoleModulePermission $permission): string => $permission->module->slug)
            ->map(fn (Collection $permissions): array => $permissions->keyBy('action')
                ->map(fn (MpoRoleModulePermission $permission): array => [
                    'allowed' => $permission->is_allowed,
                    'can_delegate' => $permission->can_delegate,
                    'approval_limit' => $permission->approval_limit,
                ])->all())
            ->all();

        $modules = Module::query()
            ->whereIn('slug', array_keys(config('mpo_staff.modules', [])))
            ->get(['id', 'slug']);
        foreach ($modules as $module) {
            $settings = $this->roleSettings($ownerId, $role, (int) $module->id);
            if ($settings['capacity_level'] !== 'Section') {
                continue;
            }

            foreach (config("mpo_staff.modules.{$module->slug}", []) as $action) {
                $existing = $matrix[$module->slug][$action] ?? [];
                $matrix[$module->slug][$action] = [
                    'allowed' => true,
                    'can_delegate' => (bool) ($existing['can_delegate'] ?? false),
                    'approval_limit' => $existing['approval_limit'] ?? null,
                    'source' => 'section',
                ];
            }
        }

        return $matrix;
    }

    public function roleSettings(int $ownerId, string $role, int $moduleId): array
    {
        $role = $this->canonicalRole($role);
        $settings = MpoRoleModuleSetting::query()
            ->where('admin_user_id', $ownerId)
            ->whereIn('role', $this->roleAliases($role))
            ->where('module_id', $moduleId)
            ->orderByRaw('CASE WHEN role = ? THEN 0 ELSE 1 END', [$role])
            ->first();

        return [
            'capacity_level' => $settings?->capacity_level ?? 'Tick Function',
            'data_scope' => $settings?->data_scope ?? 'all',
            'notes' => $settings?->notes,
        ];
    }

    public function canConfigureRole(AdminUser|MpoStaffUser $actor, string $role): bool
    {
        $role = $this->canonicalRole($role);
        if ($actor instanceof AdminUser && $actor->admin_type === 'mpo') {
            return true;
        }

        return $actor instanceof MpoStaffUser
            && $this->canonicalRole($actor->role) !== $role
            && $this->can($actor, 'capacity-permissions', 'update')
            && $this->roleAssignable($actor, $role);
    }

    public function canApproveAmount(MpoStaffUser $user, string $moduleSlug, string $action, string|int|float $amount): bool
    {
        if (! $this->can($user, $moduleSlug, $action)) {
            return false;
        }

        $permission = MpoRoleModulePermission::query()
            ->with('module')
            ->where('admin_user_id', $user->admin_user_id)
            ->whereIn('role', $this->roleAliases($user->role))
            ->whereHas('module', fn ($query) => $query->where('slug', $moduleSlug))
            ->where('action', $action)
            ->first();

        if ($permission === null || $permission->approval_limit === null) {
            return false;
        }

        $amountMinor = $this->amountInMinorUnits((string) $amount);
        $limitMinor = $this->amountInMinorUnits((string) $permission->approval_limit);

        return $amountMinor !== null
            && $limitMinor !== null
            && strlen($amountMinor) === strlen($limitMinor)
            && strcmp($amountMinor, $limitMinor) <= 0;
    }

    public function effectiveMatrix(MpoStaffUser $user): array
    {
        return $this->calculateEffectiveMatrix($user, []);
    }

    private function calculateEffectiveMatrix(MpoStaffUser $user, array $delegationPath): array
    {
        $userId = (int) $user->getKey();
        if (in_array($userId, $delegationPath, true)) {
            return [];
        }

        $delegationPath[] = $userId;
        $matrix = $this->roleMatrix((int) $user->admin_user_id, $user->role);
        $overrides = $user->relationLoaded('permissionOverrides')
            ? $user->permissionOverrides
            : $user->permissionOverrides()->with('module')->get();

        foreach ($overrides as $override) {
            $slug = $override->module?->slug;
            if (! $slug || ! $this->supports($slug, $override->action)) {
                continue;
            }
            if ($override->effect === 'allow' && $override->grantor_role) {
                if ($override->granted_by_staff_user_id) {
                    $grantor = MpoStaffUser::query()
                        ->where('admin_user_id', $user->admin_user_id)
                        ->find($override->granted_by_staff_user_id);
                    $grantorPermission = $grantor
                        ? ($this->calculateEffectiveMatrix($grantor, $delegationPath)[$slug][$override->action] ?? null)
                        : null;
                } else {
                    $grantorPermission = $this->roleMatrix(
                        (int) $user->admin_user_id,
                        $override->grantor_role,
                    )[$slug][$override->action] ?? null;
                }
                if (! ($grantorPermission['allowed'] ?? false) || ! ($grantorPermission['can_delegate'] ?? false)) {
                    continue;
                }
            }

            $canDelegateOverride = $override->effect === 'allow' && $override->can_delegate;
            if ($canDelegateOverride && $override->grantor_role) {
                if ($override->granted_by_staff_user_id) {
                    $grantor = MpoStaffUser::query()
                        ->where('admin_user_id', $user->admin_user_id)
                        ->find($override->granted_by_staff_user_id);
                    $grantorPermission = $grantor
                        ? ($this->calculateEffectiveMatrix($grantor, $delegationPath)[$slug][$override->action] ?? null)
                        : null;
                } else {
                    $grantorPermission = $this->roleMatrix(
                        (int) $user->admin_user_id,
                        $override->grantor_role,
                    )[$slug][$override->action] ?? null;
                }
                $canDelegateOverride = (bool) ($grantorPermission['allowed'] ?? false)
                    && (bool) ($grantorPermission['can_delegate'] ?? false);
            }

            $matrix[$slug][$override->action] = [
                'allowed' => $override->effect === 'allow',
                'can_delegate' => $canDelegateOverride,
                'source' => 'explicit',
            ];
        }

        foreach ($matrix as $slug => $actions) {
            foreach ($actions as $action => $permission) {
                $matrix[$slug][$action] = [
                    ...$permission,
                    'source' => $permission['source'] ?? 'role',
                ];
            }
        }

        if ($delegationPath === [$userId]) {
            return $matrix;
        }

        return $matrix;
    }

    public function delegatedActionMatrix(AdminUser|MpoStaffUser $actor, array $modules): array
    {
        $matrix = [];
        foreach ($modules as $module) {
            foreach (array_keys(config('mpo_staff.actions', [])) as $action) {
                if ($this->canDelegate($actor, $module->slug, $action)) {
                    $matrix[$module->slug][$action] = true;
                }
            }
        }

        return $matrix;
    }

    private function module(string $slug): ?Module
    {
        return Module::query()->where('slug', $slug)->first();
    }

    public function canonicalRole(string $role): string
    {
        return $role === 'Accountant' ? 'Finance' : $role;
    }

    private function roleAliases(string $role): array
    {
        $canonical = $this->canonicalRole($role);

        return $canonical === 'Finance' ? ['Finance', 'Accountant'] : [$canonical];
    }

    private function supports(string $moduleSlug, string $action): bool
    {
        return in_array($action, config("mpo_staff.modules.{$moduleSlug}", []), true);
    }

    private function supportsScope(MpoStaffUser $user, Module $module): bool
    {
        $settings = $this->roleSettings(
            (int) $user->admin_user_id,
            $user->role,
            (int) $module->id,
        );

        return $settings['data_scope'] === 'all';
    }

    private function amountInMinorUnits(string $amount): ?string
    {
        if (! preg_match('/^\d{1,12}(?:\.\d{1,2})?$/', $amount)) {
            return null;
        }

        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return str_pad(ltrim($whole.$fraction.str_repeat('0', 2 - strlen($fraction)), '0') ?: '0', 14, '0', STR_PAD_LEFT);
    }
}
