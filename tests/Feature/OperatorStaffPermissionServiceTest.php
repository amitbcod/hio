<?php

namespace Tests\Feature;

use App\Models\Operator;
use App\Models\OperatorUser;
use App\Http\Controllers\Operator\AccommodationController;
use App\Http\Controllers\Frontend\BookingWidgetController;
use App\Services\OperatorStaffPermissionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OperatorStaffPermissionServiceTest extends TestCase
{
    private OperatorStaffPermissionService $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'operator_staff_permissions_test',
            'database.connections.operator_staff_permissions_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('operator_staff_permissions_test');
        DB::setDefaultConnection('operator_staff_permissions_test');

        Schema::create('operators', function (Blueprint $table): void {
            $table->id();
            $table->string('operator_id');
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('is_owner')->default('yes');
        });
        Schema::create('operator_users', function (Blueprint $table): void {
            $table->id();
            $table->string('operator_id');
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('created_by_operator_user_id')->nullable();
            $table->string('role');
            $table->timestamps();
        });
        Schema::create('operator_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('operator_id');
            $table->unsignedBigInteger('business_id')->nullable();
            $table->json('service_types')->nullable();
            $table->timestamps();
        });
        Schema::create('modules', function (Blueprint $table): void {
            $table->id();
            $table->string('slug');
            $table->string('name');
        });
        Schema::create('operator_role_module_permissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('operator_id');
            $table->string('role_name');
            $table->unsignedBigInteger('module_id');
            $table->string('action');
            $table->boolean('is_allowed');
            $table->boolean('can_delegate');
            $table->timestamps();
        });
        Schema::create('operator_staff_permission_overrides', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('operator_user_id');
            $table->unsignedBigInteger('module_id');
            $table->string('action');
            $table->string('effect');
            $table->boolean('can_delegate');
            $table->unsignedBigInteger('granted_by_operator_user_id')->nullable();
            $table->timestamps();
        });

        DB::table('operators')->insert([
            'id' => 10,
            'operator_id' => 'OP-1',
            'business_id' => 20,
            'is_owner' => 'yes',
        ]);
        DB::table('operator_profiles')->insert([
            'operator_id' => 'OP-1',
            'business_id' => 20,
            'service_types' => json_encode(['Transport']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('modules')->insert([
            ['id' => 1, 'slug' => 'transport', 'name' => 'Transport'],
            ['id' => 2, 'slug' => 'users', 'name' => 'Users'],
        ]);
        DB::table('operator_users')->insert([
            ['id' => 101, 'operator_id' => 'OP-1', 'business_id' => 20, 'created_by_operator_user_id' => null, 'role' => 'Finance Manager', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 102, 'operator_id' => 'OP-1', 'business_id' => 20, 'created_by_operator_user_id' => 101, 'role' => 'Finance Manager', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 103, 'operator_id' => 'OP-1', 'business_id' => 20, 'created_by_operator_user_id' => 102, 'role' => 'Finance Manager', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 104, 'operator_id' => 'OP-1', 'business_id' => 20, 'created_by_operator_user_id' => null, 'role' => 'Finance Manager', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('operator_role_module_permissions')->insert([
            ['operator_id' => 10, 'role_name' => 'Finance Manager', 'module_id' => 1, 'action' => 'read', 'is_allowed' => true, 'can_delegate' => true, 'created_at' => now(), 'updated_at' => now()],
            ['operator_id' => 10, 'role_name' => 'Finance Manager', 'module_id' => 1, 'action' => 'create', 'is_allowed' => true, 'can_delegate' => true, 'created_at' => now(), 'updated_at' => now()],
            ['operator_id' => 10, 'role_name' => 'Finance Manager', 'module_id' => 1, 'action' => 'update', 'is_allowed' => true, 'can_delegate' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('operator_staff_permission_overrides')->insert([
            ['operator_user_id' => 101, 'module_id' => 1, 'action' => 'read', 'effect' => 'allow', 'can_delegate' => true, 'granted_by_operator_user_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['operator_user_id' => 102, 'module_id' => 1, 'action' => 'read', 'effect' => 'allow', 'can_delegate' => false, 'granted_by_operator_user_id' => 101, 'created_at' => now(), 'updated_at' => now()],
            ['operator_user_id' => 103, 'module_id' => 1, 'action' => 'read', 'effect' => 'allow', 'can_delegate' => true, 'granted_by_operator_user_id' => 102, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->permissions = new OperatorStaffPermissionService();
    }

    public function test_owner_and_staff_are_limited_to_their_manageable_creator_hierarchy(): void
    {
        $owner = Operator::query()->findOrFail(10);
        $staff = OperatorUser::query()->findOrFail(101);
        $otherRoot = OperatorUser::query()->findOrFail(104);
        $descendant = OperatorUser::query()->findOrFail(103);

        $this->assertEqualsCanonicalizing([101, 102, 103, 104], $this->permissions
            ->manageableStaffQuery($owner)->pluck('id')->all());
        $this->assertEqualsCanonicalizing([102, 103], $this->permissions
            ->manageableStaffQuery($staff)->pluck('id')->all());
        $this->assertTrue($this->permissions->targetIsManageable($staff, $descendant));
        $this->assertFalse($this->permissions->targetIsManageable($staff, $otherRoot));
    }

    public function test_staff_permissions_cannot_be_delegated_beyond_the_creator_grant(): void
    {
        $child = OperatorUser::query()->findOrFail(102);
        $grandchild = OperatorUser::query()->findOrFail(103);

        $this->assertTrue($this->permissions->can($child, 'transport', 'read'));
        $this->assertFalse($this->permissions->canDelegate($child, 'transport', 'read'));
        $this->assertFalse($this->permissions->can($grandchild, 'transport', 'read'));
        $this->assertFalse($this->permissions->can($child, 'activities', 'read'));
    }

    public function test_assignable_permissions_intersect_creator_delegation_role_limits_and_service_types(): void
    {
        $owner = Operator::query()->findOrFail(10);
        $creator = OperatorUser::query()->findOrFail(101);
        $nonDelegatingChild = OperatorUser::query()->findOrFail(102);

        $ownerMatrix = $this->permissions->assignablePermissionMatrix($owner, 'Finance Manager');
        $creatorMatrix = $this->permissions->assignablePermissionMatrix($creator, 'Finance Manager');
        $childMatrix = $this->permissions->assignablePermissionMatrix($nonDelegatingChild, 'Finance Manager');

        $this->assertSame(
            ['create', 'read', 'update'],
            collect(array_keys($ownerMatrix['transport']))->sort()->values()->all(),
        );
        $this->assertSame(['read'], array_keys($creatorMatrix['transport']));
        $this->assertSame([], $childMatrix);
        $this->assertArrayNotHasKey('accommodations', $ownerMatrix);
        $this->assertArrayNotHasKey('activities', $ownerMatrix);
    }

    public function test_permission_payload_rejects_grants_the_creator_cannot_delegate(): void
    {
        $creator = OperatorUser::query()->findOrFail(101);

        try {
            $this->permissions->validatePermissionPayload($creator, 'Finance Manager', [
                'transport' => ['create' => ['allowed' => true]],
            ]);
            $this->fail('A creator must not grant an action they do not have permission to delegate.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_non_owner_operator_accounts_do_not_gain_staff_administration(): void
    {
        DB::table('operators')->where('id', 10)->update(['is_owner' => 'no']);
        $nonOwner = Operator::query()->findOrFail(10);

        $this->assertNull($this->permissions->owner($nonOwner));
        $this->assertFalse($this->permissions->canManageUsers($nonOwner, 'create'));
    }

    public function test_accommodation_routes_use_the_same_read_create_and_update_actions_as_navigation(): void
    {
        $this->assertSame(
            ['accommodations', 'read'],
            $this->permissions->permissionForRoute(
                'operator.accommodation.index',
                'GET',
                [],
                'operator/accommodation',
            ),
        );
        $this->assertSame(
            ['accommodations', 'create'],
            $this->permissions->permissionForRoute(
                'operator.accommodation.create',
                'GET',
                [],
                'operator/accommodation/create',
            ),
        );
        $this->assertSame(
            ['accommodations', 'update'],
            $this->permissions->permissionForRoute(
                'operator.accommodation.step1.edit',
                'GET',
                ['id' => 1],
                'operator/accommodation/{id}/edit/step1',
            ),
        );
    }

    public function test_accommodation_controller_resolves_staff_to_their_owning_operator(): void
    {
        $staff = OperatorUser::query()->findOrFail(101);
        auth('operator_staff')->setUser($staff);

        $controller = new AccommodationController();
        $method = new \ReflectionMethod($controller, 'operatorAccount');
        $method->setAccessible(true);

        $this->assertSame(10, $method->invoke($controller)->getKey());
    }

    public function test_booking_widget_routes_use_operations_permissions_and_owner_scope(): void
    {
        $this->assertSame(
            ['operations', 'read'],
            $this->permissions->permissionForRoute(
                'operator.booking-widget.script',
                'GET',
                [],
                'operator/booking-widget/script',
            ),
        );
        $this->assertSame(
            ['operations', 'create'],
            $this->permissions->permissionForRoute(
                'operator.booking-widget.generate',
                'POST',
                [],
                'operator/booking-widget/generate',
            ),
        );

        $staff = OperatorUser::query()->findOrFail(101);
        $request = new \Illuminate\Http\Request();
        $request->setUserResolver(fn (?string $guard = null) => $staff);
        $controller = new BookingWidgetController();
        $method = new \ReflectionMethod($controller, 'operatorAccount');
        $method->setAccessible(true);

        $this->assertSame(10, $method->invoke($controller, $request)->getKey());
    }
}
