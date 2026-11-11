<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Module;
use App\Models\MpoAccountingConfigurationEvent;
use App\Models\MpoStaffUser;
use App\Services\MpoStaffPermissionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StaffUserController extends Controller
{
    public function __construct(private readonly MpoStaffPermissionService $permissions) {}

    public function index(Request $request)
    {
        return $this->renderIndex($request, $this->context($request));
    }

    public function adminIndex(Request $request, AdminUser $administrator)
    {
        return $this->renderIndex($request, $this->adminContext($administrator));
    }

    private function renderIndex(Request $request, array $context)
    {
        [$owner, $actor, $isAdminView] = $context;
        $this->assertCanManage($actor, 'read');

        $query = $owner->staffUsers()->with('permissionOverrides.module');
        if ($actor instanceof MpoStaffUser) {
            $query->where('created_by_staff_user_id', $actor->id);
        }
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                if (ctype_digit($search)) {
                    $builder->where('id', (int) $search);
                }
                $builder->orWhere('full_name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if (in_array($request->query('role'), config('mpo_staff.roles', []), true)) {
            $role = $request->query('role');
            $query->whereIn('role', $role === 'Finance' ? ['Finance', 'Accountant'] : [$role]);
        }
        if (in_array($request->query('status'), ['Active', 'Disabled'], true)) {
            $query->where('status', $request->query('status'));
        }

        $staffUsers = $query->orderBy('full_name')->paginate(15)->withQueryString();

        return view('mpo.users.index', [
            'mpoAdmin' => $owner,
            'actor' => $actor,
            'isAdminView' => $isAdminView,
            'staffUsers' => $staffUsers,
            'roleOptions' => config('mpo_staff.roles', []),
            'permissionService' => $this->permissions,
        ]);
    }

    public function create(Request $request)
    {
        return $this->renderForm($this->context($request), null);
    }

    public function adminCreate(Request $request, AdminUser $administrator)
    {
        return $this->renderForm($this->adminContext($administrator), null);
    }

    private function renderForm(array $context, ?MpoStaffUser $staffUser)
    {
        [$owner, $actor, $isAdminView] = $context;
        $this->assertCanManage($actor, $staffUser ? 'update' : 'create', $staffUser);
        abort_if(
            $staffUser && $actor instanceof MpoStaffUser && (int) $actor->id === (int) $staffUser->id,
            403,
            'You cannot edit your own account access.',
        );
        $modules = Module::query()
            ->whereIn('slug', array_keys(config('mpo_staff.modules', [])))
            ->orderBy('name')
            ->get();
        $roleOptions = $this->permissions->assignableRoles($actor);
        if (! $staffUser && $actor instanceof MpoStaffUser) {
            $roleOptions = [$this->permissions->canonicalRole($actor->role)];
        }
        $staffRole = $staffUser ? $this->permissions->canonicalRole($staffUser->role) : null;
        if ($staffUser && ! in_array($staffRole, $roleOptions, true)) {
            $roleOptions[] = $staffRole;
        }
        $roleMatrices = [];
        $roleSettings = [];
        foreach ($roleOptions as $role) {
            $roleMatrices[$role] = $this->permissions->roleMatrix((int) $owner->id, $role);
            foreach ($modules as $module) {
                $roleSettings[$role][$module->slug] = $this->permissions->roleSettings(
                    (int) $owner->id,
                    $role,
                    (int) $module->id,
                );
            }
        }

        return view('mpo.users.form', [
            'mpoAdmin' => $owner,
            'actor' => $actor,
            'isAdminView' => $isAdminView,
            'staffUser' => $staffUser,
            'modules' => $modules,
            'roleOptions' => $roleOptions,
            'roleMatrices' => $roleMatrices,
            'roleSettings' => $roleSettings,
            'delegatableActions' => $this->permissions->delegatedActionMatrix($actor, $modules->all()),
            'effectiveMatrix' => $staffUser ? $this->permissions->effectiveMatrix($staffUser) : [],
        ]);
    }

    public function store(Request $request)
    {
        return $this->saveUser($request, $this->context($request), null);
    }

    public function adminStore(Request $request, AdminUser $administrator)
    {
        return $this->saveUser($request, $this->adminContext($administrator), null);
    }

    private function saveUser(Request $request, array $context, ?MpoStaffUser $staffUser)
    {
        [$owner, $actor, $isAdminView] = $context;
        $this->assertCanManage($actor, $staffUser ? 'update' : 'create', $staffUser);
        if ($staffUser && (int) $staffUser->id === ($actor instanceof MpoStaffUser ? (int) $actor->id : 0)) {
            abort(403, 'You cannot change your own account access.');
        }

        $roleOptions = $this->permissions->assignableRoles($actor);
        if (! $staffUser && $actor instanceof MpoStaffUser) {
            $roleOptions = [$this->permissions->canonicalRole($actor->role)];
        }
        if ($staffUser && $this->permissions->canonicalRole($staffUser->role) === $request->input('role')) {
            $roleOptions[] = $this->permissions->canonicalRole($staffUser->role);
        }
        $data = $this->validateUser($request, $staffUser, $roleOptions);
        $email = mb_strtolower(trim($data['email']));

        if ($staffUser && $data['role'] !== $this->permissions->canonicalRole($staffUser->role)) {
            abort_unless($this->permissions->roleAssignable($actor, $data['role']), 403, 'You cannot assign this role.');
        }
        if (! $staffUser) {
            if ($actor instanceof MpoStaffUser) {
                abort_unless(
                    $this->permissions->canonicalRole($data['role']) === $this->permissions->canonicalRole($actor->role),
                    403,
                    'MPO staff may create users only in their own role.',
                );
            } else {
                abort_unless($this->permissions->roleAssignable($actor, $data['role']), 403, 'You cannot assign this role.');
            }
        }
        if ($staffUser
            && $staffUser->status === 'Active'
            && $this->permissions->canonicalRole($staffUser->role) === 'Admin'
            && $data['role'] !== 'Admin') {
            $activeAdmins = $owner->staffUsers()->where('role', 'Admin')->where('status', 'Active')->count();
            abort_if($activeAdmins <= 1, 422, 'The last active MPO staff Admin cannot be demoted.');
        }

        $this->validatePermissionOverrides($data['permission_overrides'] ?? [], $actor, $owner);
        $previousRole = $staffUser?->role;

        DB::transaction(function () use ($owner, $actor, $staffUser, $data, $email): void {
            $record = $staffUser ?? new MpoStaffUser;
            if (! $staffUser) {
                $record->admin_user_id = $owner->id;
                if ($actor instanceof MpoStaffUser) {
                    $record->created_by_staff_user_id = $actor->id;
                } else {
                    $record->created_by_admin_user_id = $owner->id;
                }
                $record->password_hash = Hash::make($data['password']);
                $record->status = 'Active';
                $record->account_reset_required = false;
            } elseif (filled($data['password'] ?? null)) {
                $record->password_hash = Hash::make($data['password']);
                $record->remember_token = Str::random(60);
            }

            $record->full_name = trim($data['full_name']);
            $record->email = $email;
            $record->mobile = $data['mobile'] ?? null;
            $record->role = $data['role'];
            if (! $record->save()) {
                throw new \RuntimeException('The MPO staff user could not be saved.');
            }

            $this->syncOverrides($record, $data['permission_overrides'] ?? [], $actor);
            if (! $this->permissions->can($record, 'accounting', 'read')) {
                $unassigned = $owner->accountingConfiguration()
                    ->where('responsible_accountant_user_id', $record->id)
                    ->update(['responsible_accountant_user_id' => null]);
                if ($unassigned > 0) {
                    MpoAccountingConfigurationEvent::query()->create([
                        'admin_user_id' => $owner->id,
                        'event_type' => 'accountant_unassigned',
                        'actor_type' => $actor instanceof MpoStaffUser ? 'mpo_staff' : $actor->admin_type,
                        'actor_id' => $actor->id,
                        'changes' => ['fields' => ['responsible_accountant_user_id'], 'staff_user_id' => $record->id],
                    ]);
                }
            }
        });

        $event = $staffUser ? 'updated' : 'created';
        Log::info('MPO staff account '.$event.'.', [
            'mpo_admin_id' => $owner->id,
            'staff_user_id' => $staffUser?->id,
            'actor_type' => $actor instanceof MpoStaffUser ? 'staff' : 'mpo_admin',
            'actor_id' => $actor->id,
            'role_changed' => $staffUser ? $previousRole !== $data['role'] : false,
            'permission_override_fields_submitted' => count($data['permission_overrides'] ?? []),
        ]);

        return redirect($this->usersRoute($owner, $actor, $isAdminView))
            ->with('success', $staffUser ? 'MPO staff user updated.' : 'MPO staff user created.');
    }

    public function show(Request $request, int $staffUser)
    {
        return $this->renderShow($this->context($request), $staffUser);
    }

    public function adminShow(Request $request, AdminUser $administrator, int $staffUser)
    {
        return $this->renderShow($this->adminContext($administrator), $staffUser);
    }

    private function renderShow(array $context, int $staffUserId)
    {
        [$owner, $actor, $isAdminView] = $context;
        $this->assertCanManage($actor, 'read');
        $staffUser = $this->target($owner, $actor, $staffUserId)->load('permissionOverrides.module');

        return view('mpo.users.show', [
            'mpoAdmin' => $owner,
            'actor' => $actor,
            'isAdminView' => $isAdminView,
            'staffUser' => $staffUser,
            'effectiveMatrix' => $this->permissions->effectiveMatrix($staffUser),
            'permissionService' => $this->permissions,
        ]);
    }

    public function edit(Request $request, int $staffUser)
    {
        $context = $this->context($request);

        return $this->renderForm($context, $this->target($context[0], $context[1], $staffUser)->load('permissionOverrides.module'));
    }

    public function adminEdit(Request $request, AdminUser $administrator, int $staffUser)
    {
        $context = $this->adminContext($administrator);

        return $this->renderForm($context, $this->target($context[0], $context[1], $staffUser)->load('permissionOverrides.module'));
    }

    public function update(Request $request, int $staffUser)
    {
        $context = $this->context($request);

        return $this->saveUser($request, $context, $this->target($context[0], $context[1], $staffUser));
    }

    public function adminUpdate(Request $request, AdminUser $administrator, int $staffUser)
    {
        $context = $this->adminContext($administrator);

        return $this->saveUser($request, $context, $this->target($context[0], $context[1], $staffUser));
    }

    public function updateStatus(Request $request, int $staffUser)
    {
        return $this->changeStatus($request, $this->context($request), $staffUser);
    }

    public function adminUpdateStatus(Request $request, AdminUser $administrator, int $staffUser)
    {
        return $this->changeStatus($request, $this->adminContext($administrator), $staffUser);
    }

    private function changeStatus(Request $request, array $context, int $staffUserId)
    {
        [$owner, $actor, $isAdminView] = $context;
        $this->assertCanManage($actor, 'disable');
        $staffUser = $this->target($owner, $actor, $staffUserId);
        abort_if($actor instanceof MpoStaffUser && (int) $actor->id === (int) $staffUser->id, 403, 'You cannot change your own account status.');
        $data = $request->validate([
            'status' => ['required', Rule::in(['Active', 'Disabled'])],
        ]);

        if ($staffUser->status === 'Active' && $data['status'] === 'Disabled' && $staffUser->role === 'Admin') {
            $activeAdmins = $owner->staffUsers()->where('role', 'Admin')->where('status', 'Active')->count();
            abort_if($activeAdmins <= 1, 422, 'The last active MPO staff Admin cannot be disabled.');
        }

        $staffUser->status = $data['status'];
        $staffUser->remember_token = Str::random(60);
        DB::transaction(function () use ($staffUser, $owner, $actor): void {
            if (! $staffUser->save()) {
                throw new \RuntimeException('The MPO staff user status could not be saved.');
            }
            if ($staffUser->status === 'Disabled') {
                $unassigned = $owner->accountingConfiguration()
                    ->where('responsible_accountant_user_id', $staffUser->id)
                    ->update(['responsible_accountant_user_id' => null]);
                if ($unassigned > 0) {
                    MpoAccountingConfigurationEvent::query()->create([
                        'admin_user_id' => $owner->id,
                        'event_type' => 'accountant_unassigned',
                        'actor_type' => $actor instanceof MpoStaffUser ? 'mpo_staff' : $actor->admin_type,
                        'actor_id' => $actor->id,
                        'changes' => ['fields' => ['responsible_accountant_user_id'], 'disabled_staff_user_id' => $staffUser->id],
                    ]);
                }
            }
        });
        Log::info('MPO staff account status changed.', [
            'mpo_admin_id' => $owner->id,
            'staff_user_id' => $staffUser->id,
            'status' => $staffUser->status,
            'actor_id' => $actor->id,
        ]);

        return redirect()->to($this->userRoute($owner, $actor, $isAdminView, $staffUser))
            ->with('success', 'MPO staff user status updated.');
    }

    public function sendPasswordReset(Request $request, int $staffUser)
    {
        return $this->requestPasswordReset($request, $this->context($request), $staffUser);
    }

    public function adminSendPasswordReset(Request $request, AdminUser $administrator, int $staffUser)
    {
        return $this->requestPasswordReset($request, $this->adminContext($administrator), $staffUser);
    }

    private function requestPasswordReset(Request $request, array $context, int $staffUserId)
    {
        [$owner, $actor, $isAdminView] = $context;
        $this->assertCanManage($actor, 'reset_password');
        $staffUser = $this->target($owner, $actor, $staffUserId);
        abort_if($actor instanceof MpoStaffUser && (int) $actor->id === (int) $staffUser->id, 403, 'You cannot reset your own password from here.');

        try {
            $status = Password::broker('mpo_staff')->sendResetLink(['email' => $staffUser->email]);
        } catch (\Throwable $exception) {
            Log::error('MPO staff password reset email could not be sent.', [
                'mpo_admin_id' => $owner->id,
                'staff_user_id' => $staffUser->id,
                'exception_class' => $exception::class,
            ]);

            return back()->withErrors(['password' => 'The password reset email could not be delivered. Please try again later.']);
        }
        if ($status !== Password::RESET_LINK_SENT) {
            return back()->withErrors(['password' => 'The password reset link could not be sent. Please try again later.']);
        }

        $staffUser->forceFill(['account_reset_required' => true])->save();
        Log::info('MPO staff password reset initiated.', [
            'mpo_admin_id' => $owner->id,
            'staff_user_id' => $staffUser->id,
            'actor_id' => $actor->id,
        ]);

        return redirect()->to($this->userRoute($owner, $actor, $isAdminView, $staffUser))
            ->with('success', 'A secure password reset link has been sent to the staff member.');
    }

    public function rolePermissions(Request $request)
    {
        [$owner, $actor] = $this->context($request);
        abort_unless(
            $this->permissions->canManage($actor, 'create')
            || $this->permissions->canManage($actor, 'update'),
            403,
            'You do not have permission to manage MPO staff users.',
        );
        $role = (string) $request->query('role');
        abort_unless($this->permissions->roleAssignable($actor, $role), 403);

        return response()->json($this->permissions->roleMatrix((int) $owner->id, $role));
    }

    public function showPasswordReset(Request $request, string $token)
    {
        return view('mpo.users.password-reset', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[@#%!*^$&]/'],
        ]);

        $status = Password::broker('mpo_staff')->reset(
            $data,
            function (MpoStaffUser $staffUser, string $password): void {
                abort_unless($staffUser->status === 'Active', 403, 'This staff account is disabled.');
                $owner = $staffUser->mpo;
                abort_unless($owner && $owner->admin_type === 'mpo' && $owner->status === 'active', 403);

                if (! $staffUser->forceFill([
                    'password_hash' => Hash::make($password),
                    'account_reset_required' => false,
                    'remember_token' => Str::random(60),
                ])->save()) {
                    throw new \RuntimeException('MPO staff password reset could not be saved.');
                }
                Log::info('MPO staff password reset completed.', [
                    'mpo_admin_id' => $owner->id,
                    'staff_user_id' => $staffUser->id,
                ]);
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
        }

        return redirect()->route('mpo.login')->with('success', 'Your password has been reset. You can now sign in.');
    }

    private function context(Request $request): array
    {
        $owner = $request->attributes->get('mpo_admin');
        abort_unless($owner instanceof AdminUser && $owner->admin_type === 'mpo', 403);
        $actor = $request->attributes->get('mpo_staff_user', $owner);
        abort_unless($actor instanceof AdminUser || $actor instanceof MpoStaffUser, 403);

        return [$owner, $actor, false];
    }

    private function adminContext(AdminUser $administrator): array
    {
        abort_unless($administrator->admin_type === 'mpo', 404);
        abort_unless(session('admin_id'), 403);

        return [$administrator, $administrator, true];
    }

    private function target(AdminUser $owner, AdminUser|MpoStaffUser $actor, int $staffUserId): MpoStaffUser
    {
        $query = $owner->staffUsers()->whereKey($staffUserId);
        if ($actor instanceof MpoStaffUser) {
            $query->where('created_by_staff_user_id', $actor->id);
        }

        return $query->firstOrFail();
    }

    private function assertCanManage(AdminUser|MpoStaffUser $actor, string $action, ?MpoStaffUser $target = null): void
    {
        abort_unless($this->permissions->canManage($actor, $action), 403, 'You do not have permission to manage MPO staff users.');
        if ($target && $actor instanceof MpoStaffUser) {
            abort_if((int) $target->admin_user_id !== (int) $actor->admin_user_id, 404);
        }
    }

    private function validateUser(Request $request, ?MpoStaffUser $staffUser, array $roles): array
    {
        foreach (['full_name', 'email', 'mobile'] as $field) {
            if (is_string($request->input($field))) {
                $request->merge([$field => trim($request->input($field))]);
            }
        }
        $emailUnique = function (string $attribute, mixed $value, \Closure $fail) use ($staffUser): void {
            $email = mb_strtolower(trim((string) $value));
            if ($this->emailInUse($email, $staffUser)) {
                $fail('This email is already in use. Please use another email address.');
            }
        };
        $rules = [
            'full_name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', $emailUnique],
            'mobile' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'role' => ['required', Rule::in($roles)],
            'password' => [$staffUser ? 'nullable' : 'required', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[@#%!*^$&]/'],
            'password_confirmation' => [$staffUser ? 'required_with:password' : 'required', 'same:password'],
            'permission_overrides' => ['sometimes', 'array'],
            'permission_overrides.*' => ['array'],
            'permission_overrides.*.*.effect' => ['required', Rule::in(['inherit', 'allow', 'deny'])],
            'permission_overrides.*.*.can_delegate' => ['nullable', 'boolean'],
        ];

        return $request->validate($rules, [
            'mobile.regex' => 'Enter the mobile number in E.164 format, including the +country code.',
            'password.regex' => 'Use at least one uppercase letter, one lowercase letter, one number, and one supported symbol.',
        ]);
    }

    private function emailInUse(string $email, ?MpoStaffUser $staffUser): bool
    {
        $accountTables = [
            'admin_users' => 'email',
            'mpos' => 'email',
            'operators' => 'email',
            'operator_users' => 'email',
            'agent_accounts' => 'email',
            'agent_staff_users' => 'email',
            'traveler_accounts' => 'email',
            'users' => 'email',
        ];
        foreach ($accountTables as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $query = DB::table($table)->whereRaw('LOWER('.$column.') = ?', [$email]);
            if ($table === 'mpo_staff_users' && $staffUser) {
                $query->where('id', '<>', $staffUser->id);
            }
            if ($query->exists()) {
                return true;
            }
        }

        if (Schema::hasTable('mpo_staff_users')) {
            $query = DB::table('mpo_staff_users')->whereRaw('LOWER(email) = ?', [$email]);
            if ($staffUser) {
                $query->where('id', '<>', $staffUser->id);
            }

            return $query->exists();
        }

        return false;
    }

    private function validatePermissionOverrides(array $overrides, AdminUser|MpoStaffUser $actor, AdminUser $owner): void
    {
        $modules = Module::query()->get()->keyBy('slug');
        foreach ($overrides as $slug => $actions) {
            abort_unless(is_string($slug) && $modules->has($slug) && is_array($actions), 422, 'Invalid permission module.');
            foreach ($actions as $action => $settings) {
                abort_unless(
                    is_string($action)
                    && array_key_exists($action, config('mpo_staff.actions', []))
                    && is_array($settings)
                    && in_array($settings['effect'] ?? null, ['inherit', 'allow', 'deny'], true),
                    422,
                    'Invalid permission override.',
                );
                abort_unless($this->permissions->canDelegate($actor, $slug, $action), 403, 'You cannot delegate this module action.');
                if (($settings['can_delegate'] ?? false) && $settings['effect'] !== 'allow') {
                    abort(422, 'Only explicitly allowed permissions can be delegated.');
                }
            }
        }
    }

    private function syncOverrides(MpoStaffUser $user, array $overrides, AdminUser|MpoStaffUser $actor): void
    {
        $modules = Module::query()->get()->keyBy('slug');
        foreach ($overrides as $slug => $actions) {
            $module = $modules->get($slug);
            if (! $module || ! is_array($actions)) {
                continue;
            }
            foreach ($actions as $action => $settings) {
                if (! is_array($settings) || ! $this->permissions->canDelegate($actor, $slug, (string) $action)) {
                    continue;
                }

                $existing = $user->permissionOverrides()
                    ->where('module_id', $module->id)
                    ->where('action', $action);
                if (($settings['effect'] ?? 'inherit') === 'inherit') {
                    $existing->delete();

                    continue;
                }

                $user->permissionOverrides()->updateOrCreate(
                    ['module_id' => $module->id, 'action' => $action],
                    [
                        'effect' => $settings['effect'],
                        'can_delegate' => ($settings['effect'] === 'allow') && (bool) ($settings['can_delegate'] ?? false),
                        'grantor_role' => $actor instanceof MpoStaffUser ? $actor->role : null,
                        'granted_by_staff_user_id' => $actor instanceof MpoStaffUser ? $actor->id : null,
                    ],
                );
            }
        }
    }

    private function usersRoute(AdminUser $owner, AdminUser|MpoStaffUser $actor, bool $isAdminView): string
    {
        if ($actor instanceof MpoStaffUser && ! $this->permissions->canManage($actor, 'read')) {
            return route('mpo.users.create');
        }

        return $isAdminView
            ? route('admin.country-admins.users', $owner)
            : route('mpo.users');
    }

    private function userRoute(AdminUser $owner, AdminUser|MpoStaffUser $actor, bool $isAdminView, MpoStaffUser $user): string
    {
        if ($actor instanceof MpoStaffUser && ! $this->permissions->canManage($actor, 'read')) {
            return route('mpo.users.create');
        }

        return $isAdminView
            ? route('admin.country-admins.users.show', [$owner, $user->id])
            : route('mpo.users.show', $user->id);
    }
}
