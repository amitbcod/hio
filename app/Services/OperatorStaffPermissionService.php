<?php

namespace App\Services;

use App\Models\Module;
use App\Models\Operator;
use App\Models\OperatorUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class OperatorStaffPermissionService
{
    private const ACTION_COLUMNS = [
        'read' => 'can_read',
        'create' => 'can_create',
        'update' => 'can_update',
        'approve' => 'can_approve',
        'publish' => 'can_publish',
    ];

    public function owner(Operator|OperatorUser $actor): ?Operator
    {
        if ($actor instanceof Operator) {
            return $actor->is_owner === 'yes' ? $actor : null;
        }

        if (! $actor->business_id) {
            return null;
        }

        $query = Operator::query()
            ->where('operator_id', $actor->operator_id)
            ->where('business_id', $actor->business_id)
            ->where('is_owner', 'yes');

        return $query->first();
    }

    public function availableModules(Operator|OperatorUser $actor): Collection
    {
        $owner = $this->owner($actor);
        if (! $owner) {
            return collect();
        }

        $allowed = array_fill_keys($this->availableModuleSlugs($actor), true);

        return Module::query()
            ->whereIn('slug', array_keys($allowed))
            ->orderBy('name')
            ->get();
    }

    public function availableModuleSlugs(Operator|OperatorUser $actor): array
    {
        $owner = $this->owner($actor);
        if (! $owner) {
            return [];
        }

        $services = app(OperatorServiceAccess::class)->selectedServices($owner);
        $slugs = config('operator_staff.modules.shared', []);
        foreach ($services as $service) {
            $slugs = [...$slugs, ...(config("operator_staff.modules.{$service}", []))];
        }

        return array_values(array_unique($slugs));
    }

    public function moduleAvailable(Operator|OperatorUser $actor, string $moduleSlug): bool
    {
        return in_array($moduleSlug, $this->availableModuleSlugs($actor), true);
    }

    public function roleCatalog(Operator|OperatorUser $actor): Collection
    {
        $owner = $this->owner($actor);
        if (! $owner || ! Schema::hasTable('roles')) {
            return collect();
        }

        $query = Role::query()->where('guard_name', 'web');
        if (Schema::hasColumn('roles', 'scope')) {
            $countryId = $owner->country_id;
            $query->where(function ($scope) use ($countryId, $owner): void {
                $scope->where('scope', 'global');
                if ($countryId && Schema::hasColumn('roles', 'country_id')) {
                    $scope->orWhere(fn ($country) => $country
                        ->where('scope', 'country')
                        ->where('country_id', $countryId));
                }
                if ($owner->business_id && Schema::hasColumn('roles', 'business_id')) {
                    $scope->orWhere(fn ($business) => $business
                        ->where('scope', 'business')
                        ->where('business_id', $owner->business_id));
                }
            });
        } elseif (Schema::hasColumn('roles', 'business_id')) {
            $query->where(fn ($scope) => $scope
                ->whereNull('business_id')
                ->orWhere('business_id', $owner->business_id));
        }

        return $query->orderBy('name')->get();
    }

    public function roleAssignable(Operator|OperatorUser $actor, Role|string $role): bool
    {
        $roleName = $role instanceof Role ? $role->name : $role;
        $available = $this->roleCatalog($actor)->contains(
            fn (Role $candidate): bool => $candidate->name === $roleName
        );
        if (! $available) {
            return false;
        }

        return ! $actor instanceof OperatorUser
            || $actor->role === $roleName;
    }

    public function can(Operator|OperatorUser $actor, string $moduleSlug, string $action): bool
    {
        if (! $this->supports($moduleSlug, $action) || ! $this->moduleAvailable($actor, $moduleSlug)) {
            return false;
        }
        if ($actor instanceof Operator) {
            return $actor->is_owner === 'yes';
        }

        return (bool) ($this->effectiveMatrix($actor)[$moduleSlug][$action]['allowed'] ?? false);
    }

    public function permissionForRoute(
        ?string $routeName,
        string $method,
        array $routeParameters = [],
        ?string $routeUri = null,
    ): ?array {
        $routeName = (string) $routeName;
        $routeUri = trim((string) $routeUri, '/');

        if (str_starts_with($routeUri, 'operator/register/step6-users')) {
            $module = 'users';
        } elseif (str_contains($routeName, 'postpone.activity-slots')) {
            $module = 'activity-bookings';
        } elseif (str_starts_with($routeName, 'operator.bookings.postpone')
            || str_starts_with($routeUri, 'operator/bookings/')) {
            $module = match (strtolower((string) ($routeParameters['serviceType'] ?? ''))) {
                'accommodation' => 'accommodation-bookings',
                'activity' => 'activity-bookings',
                'transport' => 'transport-bookings',
                default => null,
            };
        } elseif (str_starts_with($routeName, 'operator.accommodation.bookings')
            || str_starts_with($routeUri, 'operator/accommodation/bookings')) {
            $module = 'accommodation-bookings';
        } elseif (str_starts_with($routeName, 'operator.activity.booking')
            || str_starts_with($routeUri, 'operator/activity/bookings')) {
            $module = 'activity-bookings';
        } elseif (str_starts_with($routeName, 'operator.transport.booking')
            || str_starts_with($routeUri, 'operator/transport-bookings')
            || str_starts_with($routeUri, 'operator/transport/bookings')) {
            $module = 'transport-bookings';
        } else {
            $module = null;
            foreach ([
                'operator.accommodation.' => 'accommodations',
                'operator.activity.' => 'activities',
                'operator.transport.' => 'transport',
                'operator.trips.' => 'trips',
                'operator.feedback.' => 'feedback',
                'operator.profile' => 'profile',
                'operator.register.step7' => 'accounting',
                'operator.register.step8' => 'operations',
            ] as $prefix => $mappedModule) {
                if (str_starts_with($routeName, $prefix)) {
                    $module = $mappedModule;
                    break;
                }
            }

            if ($module === null) {
                foreach ([
                    'operator/accommodation/' => 'accommodations',
                    'operator/activity/' => 'activities',
                    'operator/transport/' => 'transport',
                    'operator/trips' => 'trips',
                    'operator/feedback' => 'feedback',
                    'operator/profile' => 'profile',
                    'operator/drivers' => 'transport',
                    'operator/transport' => 'transport',
                    'operator/register/step7-' => 'accounting',
                    'operator/register/step8-' => 'operations',
                    'operator/manage/operators' => 'operators',
                    'operator/shared-carts' => 'trips',
                    'operator/booking-widget' => 'operations',
                    'operator/hio-agreement' => 'compliance',
                    'operator/responsibilities-pdf' => 'compliance',
                ] as $prefix => $mappedModule) {
                    if (str_starts_with($routeUri, $prefix)) {
                        $module = $mappedModule;
                        break;
                    }
                }
            }
        }

        if ($module === null) {
            return null;
        }

        $action = match (true) {
            str_contains($routeName, '.publish') => 'publish',
            str_contains($routeName, '.approve'),
            str_contains($routeName, '.submit-approval') => 'approve',
            str_contains($routeName, '.store') => 'create',
            str_ends_with($routeName, '.create') => 'create',
            str_contains($routeName, '.edit') => 'update',
            strtoupper($method) === 'GET',
            strtoupper($method) === 'HEAD' => 'read',
            strtoupper($method) === 'POST'
                && in_array($routeUri, [
                    'operator/register/step6-users',
                    'operator/accommodation',
                    'operator/activity',
                    'operator/transport',
                    'operator/drivers',
                    'operator/shared-carts',
                    'operator/booking-widget/generate',
                ], true) => 'create',
            default => 'update',
        };

        return [$module, $action];
    }

    public function canAccessRoute(
        Operator|OperatorUser $actor,
        ?string $routeName,
        string $method,
        array $routeParameters = [],
        ?string $routeUri = null,
    ): bool {
        $permission = $this->permissionForRoute($routeName, $method, $routeParameters, $routeUri);
        if ($permission === null) {
            return false;
        }

        return $this->can($actor, $permission[0], $permission[1]);
    }

    public function canDelegate(Operator|OperatorUser $actor, string $moduleSlug, string $action): bool
    {
        if (! $this->can($actor, $moduleSlug, $action)) {
            return false;
        }
        if ($actor instanceof Operator) {
            return $actor->is_owner === 'yes';
        }

        return (bool) ($this->effectiveMatrix($actor)[$moduleSlug][$action]['can_delegate'] ?? false);
    }

    public function canManageUsers(Operator|OperatorUser $actor, string $action): bool
    {
        if ($actor instanceof Operator) {
            return $actor->is_owner === 'yes';
        }

        return $this->can($actor, 'users', $action);
    }

    public function effectiveMatrix(OperatorUser $user, array $path = []): array
    {
        $userId = (int) $user->getKey();
        if (in_array($userId, $path, true)) {
            return [];
        }
        $path[] = $userId;

        $owner = $this->owner($user);
        if (! $owner) {
            return [];
        }
        $ownerCreator = $user->created_by_operator_user_id;
        $parent = $ownerCreator
            ? OperatorUser::query()
                ->where('operator_id', $user->operator_id)
                ->where('business_id', $user->business_id)
                ->find($ownerCreator)
            : null;
        if ($ownerCreator && ! $parent) {
            return [];
        }

        $base = $this->roleMatrix($user);
        $matrix = [];
        foreach ($user->permissionOverrides()->with('module')->get() as $override) {
            $slug = $override->module?->slug;
            $action = $override->action;
            if (! $slug || ! $this->moduleAvailable($owner, $slug) || ! $this->supports($slug, $action)) {
                continue;
            }
            $rolePermission = $base[$slug][$action] ?? null;
            if (! ($rolePermission['allowed'] ?? false)) {
                continue;
            }
            if ($parent) {
                $parentPermission = $this->effectiveMatrix($parent, $path)[$slug][$action] ?? [];
                if (! ($parentPermission['allowed'] ?? false) || ! ($parentPermission['can_delegate'] ?? false)) {
                    continue;
                }
            }

            $matrix[$slug][$action] = [
                'allowed' => $override->effect === 'allow',
                'can_delegate' => $override->effect === 'allow'
                    && $override->can_delegate
                    && (bool) ($rolePermission['can_delegate'] ?? false),
                'source' => 'explicit',
            ];
        }

        return $matrix;
    }

    public function roleMatrix(OperatorUser $user): array
    {
        $owner = $this->owner($user);
        if (! $owner || ! Schema::hasTable('operator_role_module_permissions')) {
            return [];
        }

        return $this->roleMatrixFor($owner, $user->role);
    }

    public function roleMatrixFor(Operator $owner, string $role): array
    {
        return \App\Models\OperatorRoleModulePermission::query()
            ->with('module')
            ->where('operator_id', $owner->id)
            ->where('role_name', $role)
            ->get()
            ->filter(fn ($row): bool => $row->module !== null)
            ->filter(fn ($row): bool => $this->moduleAvailable($owner, $row->module->slug))
            ->groupBy(fn ($row): string => $row->module->slug)
            ->map(fn (Collection $rows): array => $rows->keyBy('action')
                ->map(fn ($row): array => [
                    'allowed' => (bool) $row->is_allowed,
                    'can_delegate' => (bool) $row->can_delegate,
                    'source' => 'role',
                ])->all())
            ->all();
    }

    public function delegatedActionMatrix(Operator|OperatorUser $actor): array
    {
        $matrix = [];
        foreach ($this->availableModules($actor) as $module) {
            foreach (array_keys(config('operator_staff.actions', [])) as $action) {
                if ($this->canDelegate($actor, $module->slug, $action)) {
                    $matrix[$module->slug][$action] = true;
                }
            }
        }

        return $matrix;
    }

    public function assignablePermissionMatrix(Operator|OperatorUser $actor, string $role): array
    {
        $owner = $this->owner($actor);
        if (! $owner) {
            return [];
        }

        $roleMatrix = $this->roleMatrixFor($owner, $role);
        $matrix = [];
        foreach ($this->availableModules($actor) as $module) {
            foreach (array_keys(config('operator_staff.actions', [])) as $action) {
                if (! $this->canDelegate($actor, $module->slug, $action)
                    || ! ($roleMatrix[$module->slug][$action]['allowed'] ?? false)) {
                    continue;
                }

                $matrix[$module->slug][$action] = [
                    'can_delegate' => (bool) ($roleMatrix[$module->slug][$action]['can_delegate'] ?? false),
                ];
            }
        }

        return $matrix;
    }

    public function validatePermissionPayload(
        Operator|OperatorUser $actor,
        string $role,
        array $payload,
    ): void {
        $modules = $this->availableModules($actor)->keyBy('slug');
        $owner = $this->owner($actor);
        abort_unless($owner, 403, 'Operator account not found.');
        $roleMatrix = $this->roleMatrixFor($owner, $role);

        foreach ($payload as $slug => $actions) {
            abort_unless($modules->has($slug), 422, 'The submitted module is not available for this Operator.');
            foreach ($actions as $action => $values) {
                abort_unless($this->supports($slug, $action), 422, 'This module action is not supported.');
                $grant = (bool) ($values['allowed'] ?? false);
                $delegate = (bool) ($values['can_delegate'] ?? false);
                abort_if($delegate && ! $grant, 422, 'A delegated action must first be granted.');
                if ($grant) {
                    abort_unless(
                        $this->canDelegate($actor, $slug, $action)
                        && (bool) ($roleMatrix[$slug][$action]['allowed'] ?? false),
                        403,
                        'You cannot delegate this module action.',
                    );
                }
                if ($delegate) {
                    abort_unless(
                        (bool) ($roleMatrix[$slug][$action]['can_delegate'] ?? false),
                        403,
                        'This role does not allow this action to be delegated further.',
                    );
                }
            }
        }
    }

    public function targetIsManageable(Operator|OperatorUser $actor, OperatorUser $target): bool
    {
        $owner = $this->owner($actor);
        if (! $owner
            || (string) $target->operator_id !== (string) $owner->operator_id
            || (int) $target->business_id !== (int) $owner->business_id) {
            return false;
        }

        if ($actor instanceof Operator) {
            return true;
        }

        $creatorId = $target->created_by_operator_user_id;
        $seen = [];
        while ($creatorId && ! in_array((int) $creatorId, $seen, true)) {
            if ((int) $creatorId === (int) $actor->id) {
                return true;
            }
            $seen[] = (int) $creatorId;
            $creatorId = OperatorUser::query()
                ->where('operator_id', $target->operator_id)
                ->where('business_id', $target->business_id)
                ->whereKey($creatorId)
                ->value('created_by_operator_user_id');
        }

        return false;
    }

    public function manageableStaffQuery(Operator|OperatorUser $actor): Builder
    {
        $owner = $this->owner($actor);
        abort_unless($owner, 403, 'Operator account not found.');

        $query = OperatorUser::query()
            ->where('operator_id', $owner->operator_id)
            ->where('business_id', $owner->business_id);
        if ($actor instanceof Operator) {
            return $query;
        }

        $descendantIds = [];
        $frontier = [(int) $actor->id];
        while ($frontier !== []) {
            $children = OperatorUser::query()
                ->where('operator_id', $owner->operator_id)
                ->where('business_id', $owner->business_id)
                ->whereIn('created_by_operator_user_id', $frontier)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();
            $frontier = array_values(array_diff($children, $descendantIds));
            $descendantIds = [...$descendantIds, ...$frontier];
        }

        return $query->whereIn('id', $descendantIds);
    }

    public function pruneDescendantGrants(OperatorUser $parent, string $moduleSlug, string $action): void
    {
        $children = OperatorUser::query()
            ->where('created_by_operator_user_id', $parent->id)
            ->where('operator_id', $parent->operator_id)
            ->where('business_id', $parent->business_id)
            ->get();
        $parentPermission = $this->effectiveMatrix($parent)[$moduleSlug][$action] ?? [];

        foreach ($children as $child) {
            if (! ($parentPermission['allowed'] ?? false) || ! ($parentPermission['can_delegate'] ?? false)) {
                $moduleId = Module::query()->where('slug', $moduleSlug)->value('id');
                if ($moduleId) {
                    $child->permissionOverrides()
                        ->where('module_id', $moduleId)
                        ->where('action', $action)
                        ->delete();
                }
            }
            $this->pruneDescendantGrants($child, $moduleSlug, $action);
        }
    }

    public function supports(string $moduleSlug, string $action): bool
    {
        return $this->moduleKnown($moduleSlug)
            && in_array($action, array_keys(config('operator_staff.actions', [])), true);
    }

    private function moduleKnown(string $moduleSlug): bool
    {
        return in_array($moduleSlug, [
            ...config('operator_staff.modules.shared', []),
            ...config('operator_staff.modules.Accommodation', []),
            ...config('operator_staff.modules.Transport', []),
            ...config('operator_staff.modules.Activity', []),
        ], true);
    }
}
