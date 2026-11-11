<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Operator;
use App\Models\OperatorRoleModulePermission;
use App\Services\OperatorStaffPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private readonly OperatorStaffPermissionService $permissions) {}

    public function index()
    {
        [$owner, $actor] = $this->context();
        abort_unless($actor instanceof Operator, 403, 'Only the main Operator can manage staff roles.');
        $roles = $this->permissions->roleCatalog($owner);
        foreach ($roles as $role) {
            $role->setAttribute(
                'configured_actions',
                collect($this->permissions->roleMatrixFor($owner, $role->name))
                    ->sum(fn (array $module): int => collect($module)->where('allowed', true)->count()),
            );
        }

        return view('operator.roles.index', compact('roles'));
    }

    public function store()
    {
        abort(403, 'Operator roles are managed centrally by Global Admin and MPO.');
    }

    public function edit()
    {
        abort(403, 'Operator roles are managed centrally by Global Admin and MPO.');
    }

    public function update()
    {
        abort(403, 'Operator roles are managed centrally by Global Admin and MPO.');
    }

    public function destroy()
    {
        abort(403, 'Operator roles are managed centrally by Global Admin and MPO.');
    }

    public function permissions(Role $role)
    {
        [$owner, $actor] = $this->context();
        abort_unless($actor instanceof Operator, 403, 'Only the main Operator can configure role access.');
        abort_unless($this->permissions->roleAssignable($owner, $role), 404);

        $modules = $this->permissions->availableModules($owner);
        $roleMatrix = $this->permissions->roleMatrixFor($owner, $role->name);
        $roles = $this->permissions->roleCatalog($owner);

        return view('operator.roles.permissions', compact('role', 'roles', 'modules', 'roleMatrix'));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        [$owner, $actor] = $this->context();
        abort_unless($actor instanceof Operator, 403, 'Only the main Operator can configure role access.');
        abort_unless($this->permissions->roleAssignable($owner, $role), 404);

        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['array'],
            'permissions.*.*.allowed' => ['nullable', 'boolean'],
            'permissions.*.*.can_delegate' => ['nullable', 'boolean'],
        ]);

        $availableModules = $this->permissions->availableModules($owner)->keyBy('slug');
        $actions = array_keys(config('operator_staff.actions', []));
        $submitted = $data['permissions'] ?? [];

        foreach ($submitted as $slug => $permissionActions) {
            abort_unless($availableModules->has($slug), 422, 'The submitted module is not available for this Operator.');
            foreach ($permissionActions as $action => $values) {
                abort_unless(in_array($action, $actions, true), 422, 'This permission action is not supported.');
                $allowed = (bool) ($values['allowed'] ?? false);
                $canDelegate = (bool) ($values['can_delegate'] ?? false);
                abort_if($canDelegate && ! $allowed, 422, 'A delegated action must first be granted.');
            }
        }

        DB::transaction(function () use ($owner, $role, $availableModules, $actions, $submitted): void {
            foreach ($availableModules as $slug => $module) {
                foreach ($actions as $action) {
                    $values = $submitted[$slug][$action] ?? [];
                    $allowed = (bool) ($values['allowed'] ?? false);
                    $canDelegate = $allowed && (bool) ($values['can_delegate'] ?? false);

                    OperatorRoleModulePermission::query()->updateOrCreate(
                        [
                            'operator_id' => $owner->id,
                            'role_name' => $role->name,
                            'module_id' => $module->id,
                            'action' => $action,
                        ],
                        [
                            'is_allowed' => $allowed,
                            'can_delegate' => $canDelegate,
                        ],
                    );

                    if (! $allowed || ! $canDelegate) {
                        $staff = \App\Models\OperatorUser::query()
                            ->where('operator_id', $owner->operator_id)
                            ->where('business_id', $owner->business_id)
                            ->where('role', $role->name)
                            ->get();

                        foreach ($staff as $user) {
                            if (! $allowed) {
                                $user->permissionOverrides()
                                    ->where('module_id', $module->id)
                                    ->where('action', $action)
                                    ->delete();
                            }
                            app(OperatorStaffPermissionService::class)
                                ->pruneDescendantGrants($user, $slug, $action);
                        }
                    }
                }
            }
        });

        return redirect()
            ->route('operator.roles.permissions', $role)
            ->with('success', 'Role access limits saved. Staff permissions remain individually assigned.');
    }

    private function context(): array
    {
        $actor = auth('operator')->user() ?? auth('operator_staff')->user();
        abort_unless($actor instanceof Operator || $actor instanceof \App\Models\OperatorUser, 403);
        $owner = $this->permissions->owner($actor);
        abort_unless($owner, 403, 'Operator account not found.');

        return [$owner, $actor];
    }
}
