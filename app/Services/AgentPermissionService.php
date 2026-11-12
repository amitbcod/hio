<?php

namespace App\Services;

use App\Models\AgentAccount;
use App\Models\AgentStaffUser;
use App\Models\Module;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AgentPermissionService
{
    private const MODULE_ACTIONS = [
        'profile' => ['read', 'update'],
        'compliance' => ['read', 'update'],
        'users' => ['read', 'create', 'update'],
        'operations' => ['read', 'update'],
        'reservation' => ['read', 'create', 'update'],
    ];

    private const ROLE_ACTIONS = [
        'Manager' => '*',
        'Consultant' => [
            'profile' => ['read', 'update'],
            'compliance' => ['read', 'update'],
            'reservation' => ['read', 'create', 'update'],
        ],
        'Accountant' => [
            'profile' => ['read'],
            'reservation' => ['read'],
        ],
        'Support' => [
            'profile' => ['read', 'update'],
            'reservation' => ['read'],
        ],
    ];

    private const ACTIONS = ['read', 'create', 'update', 'approve', 'publish'];

    public function modules(): Collection
    {
        return Module::query()
            ->whereIn('slug', array_keys(self::MODULE_ACTIONS))
            ->orderBy('name')
            ->get()
            ->each(function (Module $module): void {
                $module->setAttribute('name', match ($module->slug) {
                    'profile' => 'Accounts',
                    'reservation' => 'Closed Group Bookings',
                    default => $module->name,
                });
            });
    }

    public function supportedActions(string $module): array
    {
        return self::MODULE_ACTIONS[$this->canonicalModule($module)] ?? [];
    }

    public function roleAllows(string $role, string $module, string $action): bool
    {
        $module = $this->canonicalModule($module);
        if (! in_array($action, self::ACTIONS, true)
            || ! in_array($action, self::MODULE_ACTIONS[$module] ?? [], true)) {
            return false;
        }

        $roleActions = self::ROLE_ACTIONS[$role] ?? [];
        if ($roleActions === '*') {
            return true;
        }

        return in_array($action, $roleActions[$module] ?? [], true);
    }

    public function can(AgentStaffUser $user, string $module, string $action): bool
    {
        if ($this->isAccountOwnerStaff($user)) {
            return $this->roleAllows('Manager', $module, $action);
        }

        return (bool) ($this->effectiveMatrix($user)[$this->canonicalModule($module)][$action]['allowed'] ?? false);
    }

    public function canForActor(AgentAccount|AgentStaffUser|null $actor, string $module, string $action): bool
    {
        if ($actor instanceof AgentAccount) {
            return $this->roleAllows('Manager', $module, $action);
        }

        return $actor instanceof AgentStaffUser && $this->can($actor, $module, $action);
    }

    public function canDelegate(AgentStaffUser $user, string $module, string $action): bool
    {
        if ($this->isAccountOwnerStaff($user)) {
            return $this->roleAllows('Manager', $module, $action);
        }

        return (bool) ($this->effectiveMatrix($user)[$this->canonicalModule($module)][$action]['can_delegate'] ?? false);
    }

    public function canManageUsers(AgentStaffUser $user, string $action = 'update'): bool
    {
        return in_array($action, ['read', 'create', 'update'], true)
            && ($this->isAccountOwnerStaff($user) || $this->can($user, 'users', $action));
    }

    public function canAccessSetup(AgentStaffUser $user, string $module, string $action): bool
    {
        if (! $this->isAccountOwnerStaff($user)
            || ! in_array($module, ['profile', 'compliance', 'users', 'operations'], true)
            || ! $this->roleAllows('Manager', $module, $action)) {
            return false;
        }

        return true;
    }

    public function isAccountOwnerStaff(AgentStaffUser $user): bool
    {
        return $user->agent !== null
            && mb_strtolower($user->email) === mb_strtolower($user->agent->email);
    }

    public function effectiveMatrix(AgentStaffUser $user, array $path = []): array
    {
        if ($this->isAccountOwnerStaff($user)) {
            $matrix = [];
            foreach ($this->modules() as $module) {
                foreach (self::MODULE_ACTIONS[$module->slug] as $action) {
                    if ($this->roleAllows('Manager', $module->slug, $action)) {
                        $matrix[$module->slug][$action] = [
                            'allowed' => true,
                            'can_delegate' => true,
                            'source' => 'account-owner',
                        ];
                    }
                }
            }

            return $matrix;
        }

        $userId = (int) $user->getKey();
        if (in_array($userId, $path, true)) {
            return [];
        }
        $path[] = $userId;

        $permissions = $user->permissions()->with('module')->get()->keyBy(
            fn ($permission): int => (int) $permission->module_id
        );
        $parent = null;
        if ($user->created_by_agent_staff_user_id) {
            $parent = AgentStaffUser::query()
                ->where('agent_account_id', $user->agent_account_id)
                ->find($user->created_by_agent_staff_user_id);
            if (! $parent || ($parent->role !== $user->role && ! $this->isAccountOwnerStaff($parent))) {
                return [];
            }
        }

        $matrix = [];
        foreach ($this->modules() as $module) {
            $permission = $permissions->get((int) $module->id);
            if (! $permission) {
                continue;
            }

            foreach (self::MODULE_ACTIONS[$module->slug] as $action) {
                if (! $this->roleAllows($user->role, $module->slug, $action)) {
                    continue;
                }

                $allowed = (bool) $permission->{'can_'.$action};
                if ($parent) {
                    $parentPermission = $this->effectiveMatrix($parent, $path)[$module->slug][$action] ?? [];
                    if (! ($parentPermission['allowed'] ?? false)
                        || ! ($parentPermission['can_delegate'] ?? false)) {
                        $allowed = false;
                    }
                }

                $matrix[$module->slug][$action] = [
                    'allowed' => $allowed,
                    'can_delegate' => $allowed && (bool) $permission->can_delegate,
                    'source' => 'assigned',
                ];
            }
        }

        return $matrix;
    }

    public function assignableMatrix(AgentAccount|AgentStaffUser $actor, string $role): array
    {
        abort_unless(array_key_exists($role, self::ROLE_ACTIONS), 422, 'Invalid Agent staff role.');

        $matrix = [];
        $accountOwner = $actor instanceof AgentAccount
            || ($actor instanceof AgentStaffUser && $this->isAccountOwnerStaff($actor));
        foreach ($this->modules() as $module) {
            foreach (self::MODULE_ACTIONS[$module->slug] as $action) {
                $assignable = $accountOwner
                    ? $this->roleAllows($role, $module->slug, $action)
                    : $actor->role === $role && $this->canDelegate($actor, $module->slug, $action);
                if ($assignable) {
                    $matrix[$module->slug][] = $action;
                }
            }
        }

        return $matrix;
    }

    public function validatePermissionPayload(
        AgentAccount|AgentStaffUser $actor,
        string $role,
        array $permissions,
    ): void {
        $allowed = $this->assignableMatrix($actor, $role);
        $supportedModules = array_fill_keys(array_keys(self::MODULE_ACTIONS), true);

        foreach ($permissions as $module => $actions) {
            $module = (string) $module;
            abort_unless(isset($supportedModules[$module]), 422, 'The submitted Agent module is not supported.');
            abort_unless(is_array($actions), 422, 'Module actions must be a list.');

            foreach ($actions as $action) {
                abort_unless(
                    in_array($action, self::MODULE_ACTIONS[$module], true),
                    422,
                    'The submitted action is not supported for this Agent module.',
                );
                abort_unless(
                    in_array($action, $allowed[$module] ?? [], true),
                    403,
                    'You cannot delegate a permission you do not possess.',
                );
            }
        }
    }

    public function syncPermissions(
        AgentStaffUser $user,
        array $permissions,
        AgentAccount|AgentStaffUser $actor,
        string $role,
    ): void {
        $this->validatePermissionPayload($actor, $role, $permissions);

        foreach ($this->modules() as $module) {
            $selectedActions = array_values(array_unique($permissions[$module->slug] ?? []));
            $values = [
                'can_read' => in_array('read', $selectedActions, true),
                'can_create' => in_array('create', $selectedActions, true),
                'can_update' => in_array('update', $selectedActions, true),
                'can_approve' => in_array('approve', $selectedActions, true),
                'can_publish' => in_array('publish', $selectedActions, true),
                'can_delegate' => $selectedActions !== [],
            ];
            $user->permissions()->updateOrCreate(['module_id' => $module->id], $values);
        }
    }

    public function applyRoleDefaults(AgentStaffUser $user): void
    {
        foreach ($this->modules() as $module) {
            $values = [];
            foreach (self::ACTIONS as $action) {
                $values['can_'.$action] = $this->roleAllows($user->role, $module->slug, $action);
            }
            $values['can_delegate'] = collect($values)->contains(true);
            $user->permissions()->updateOrCreate(['module_id' => $module->id], $values);
        }
    }

    public function targetIsManageable(AgentAccount|AgentStaffUser $actor, AgentStaffUser $target): bool
    {
        if ((int) $target->agent_account_id !== (int) $this->agentId($actor)
            || ($actor instanceof AgentStaffUser && (int) $actor->id === (int) $target->id)) {
            return false;
        }
        if ($actor instanceof AgentAccount || $this->isAccountOwnerStaff($actor)) {
            return true;
        }

        $creatorId = $target->created_by_agent_staff_user_id;
        $seen = [];
        while ($creatorId && ! in_array((int) $creatorId, $seen, true)) {
            if ((int) $creatorId === (int) $actor->id) {
                return true;
            }
            $seen[] = (int) $creatorId;
            $creatorId = AgentStaffUser::query()
                ->where('agent_account_id', $target->agent_account_id)
                ->whereKey($creatorId)
                ->value('created_by_agent_staff_user_id');
        }

        return false;
    }

    public function manageableStaffQuery(AgentAccount|AgentStaffUser $actor): Builder
    {
        $query = AgentStaffUser::query()->where('agent_account_id', $this->agentId($actor));
        if ($actor instanceof AgentAccount || $this->isAccountOwnerStaff($actor)) {
            return $query;
        }

        $descendantIds = [];
        $frontier = [(int) $actor->id];
        while ($frontier !== []) {
            $children = AgentStaffUser::query()
                ->where('agent_account_id', $actor->agent_account_id)
                ->whereIn('created_by_agent_staff_user_id', $frontier)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();
            $frontier = array_values(array_diff($children, $descendantIds));
            $descendantIds = [...$descendantIds, ...$frontier];
        }

        $descendantIds = array_values(array_diff($descendantIds, [(int) $actor->id]));

        return $query->whereIn('id', $descendantIds);
    }

    private function agentId(AgentAccount|AgentStaffUser $actor): int
    {
        return (int) ($actor instanceof AgentAccount ? $actor->id : $actor->agent_account_id);
    }

    private function canonicalModule(string $module): string
    {
        return match (strtolower($module)) {
            'account', 'accounts' => 'profile',
            'closed-group-bookings', 'closed_group_bookings' => 'reservation',
            default => strtolower($module),
        };
    }
}
