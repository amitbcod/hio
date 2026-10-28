<?php

namespace App\Services;

use App\Models\AgentStaffUser;
use App\Models\Module;

class AgentPermissionService
{
    private const ROLE_MODULES = [
        'Manager' => ['*'],
        'Consultant' => ['account', 'profile', 'compliance', 'reservation', 'support'],
        'Accountant' => ['account', 'accounting', 'reservation', 'reporting'],
        'Support' => ['account', 'profile', 'reservation', 'support', 'feedback'],
    ];

    private const ROLE_MANAGE_MODULES = [
        'Manager' => ['*'],
        'Consultant' => ['reservation', 'support'],
        'Accountant' => ['accounting'],
        'Support' => ['support', 'feedback'],
    ];

    public function can(AgentStaffUser $user, string $module, string $action): bool
    {
        if (! in_array($action, ['read', 'create', 'update', 'approve', 'publish'], true)) {
            return false;
        }

        if ($this->canAccessSetup($user, $module, $action)) {
            return true;
        }

        $moduleRecord = Module::query()
            ->whereRaw('LOWER(slug) = ?', [strtolower($module)])
            ->orWhereRaw('LOWER(name) = ?', [strtolower($module)])
            ->first();
        if (! $moduleRecord) {
            return false;
        }

        $override = $user->permissions()->where('module_id', $moduleRecord->id)->first();
        if ($override) {
            return (bool) $override->{'can_'.$action};
        }

        $roleModules = self::ROLE_MODULES[$user->role] ?? [];

        return in_array('*', $roleModules, true)
            || in_array(strtolower($moduleRecord->slug), $roleModules, true);
    }

    public function canAccessSetup(AgentStaffUser $user, string $module, string $action): bool
    {
        $module = strtolower($module);
        if (! in_array($module, ['profile', 'compliance', 'users', 'operations'], true)) {
            return false;
        }

        $agent = $user->agent;
        if (! $agent || mb_strtolower($user->email) !== mb_strtolower($agent->email)) {
            return false;
        }

        if ($module === 'users') {
            return in_array($action, ['read', 'create', 'update'], true);
        }

        return in_array($action, ['read', 'update'], true);
    }

    public function canManageUsers(AgentStaffUser $user): bool
    {
        return $user->role === 'Manager'
            || ($user->agent && mb_strtolower($user->email) === mb_strtolower($user->agent->email));
    }

    public function applyRoleDefaults(AgentStaffUser $user): void
    {
        $roleModules = self::ROLE_MODULES[$user->role] ?? [];
        foreach (Module::query()->get() as $module) {
            $allowed = in_array('*', $roleModules, true)
                || in_array(strtolower($module->slug), $roleModules, true);
            $canManage = in_array('*', self::ROLE_MANAGE_MODULES[$user->role] ?? [], true)
                || in_array(strtolower($module->slug), self::ROLE_MANAGE_MODULES[$user->role] ?? [], true);

            $user->permissions()->updateOrCreate(
                ['module_id' => $module->id],
                [
                    'can_read' => $allowed,
                    'can_create' => $canManage,
                    'can_update' => $canManage,
                    'can_approve' => $user->role === 'Manager' && $allowed,
                    'can_publish' => $user->role === 'Manager' && $allowed,
                ]
            );
        }
    }

    public function syncPermissions(AgentStaffUser $user, array $permissions): void
    {
        foreach (Module::query()->get() as $module) {
            $actions = $permissions[$module->slug] ?? [];
            $user->permissions()->updateOrCreate(
                ['module_id' => $module->id],
                [
                    'can_read' => in_array('read', $actions, true),
                    'can_create' => in_array('create', $actions, true),
                    'can_update' => in_array('update', $actions, true),
                    'can_approve' => in_array('approve', $actions, true),
                    'can_publish' => in_array('publish', $actions, true),
                ]
            );
        }
    }

    public function roleAllows(string $role, string $module, string $action): bool
    {
        $module = strtolower($module);
        if ($role === 'Manager') {
            return true;
        }
        if (! in_array($module, self::ROLE_MODULES[$role] ?? [], true)) {
            return false;
        }
        if ($action === 'read') {
            return true;
        }

        return in_array($module, self::ROLE_MANAGE_MODULES[$role] ?? [], true)
            && in_array($action, ['create', 'update'], true);
    }
}
