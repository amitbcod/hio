<?php

namespace Tests\Feature;

use App\Models\AgentAccount;
use App\Models\AgentStaffUser;
use App\Models\Module;
use App\Services\AgentPermissionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AgentPermissionDelegationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'agent_permission_test',
            'database.connections.agent_permission_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('agent_permission_test');
        DB::setDefaultConnection('agent_permission_test');

        Schema::create('agent_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('email');
        });
        Schema::create('agent_staff_users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('agent_account_id');
            $table->unsignedBigInteger('created_by_agent_staff_user_id')->nullable();
            $table->string('user_id');
            $table->string('full_name');
            $table->string('email');
            $table->string('role');
            $table->string('status')->default('Active');
            $table->timestamps();
        });
        Schema::create('modules', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('agent_staff_permissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('agent_staff_user_id');
            $table->unsignedBigInteger('module_id');
            $table->boolean('can_read')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_approve')->default(false);
            $table->boolean('can_publish')->default(false);
            $table->boolean('can_delegate')->default(false);
            $table->timestamps();
            $table->unique(['agent_staff_user_id', 'module_id']);
        });

        DB::table('agent_accounts')->insert([
            ['id' => 1, 'email' => 'owner@example.test'],
            ['id' => 2, 'email' => 'other-owner@example.test'],
        ]);
        foreach ([
            ['profile', 'Profile'],
            ['compliance', 'Compliance'],
            ['users', 'Users'],
            ['operations', 'Operations'],
            ['reservation', 'Reservation'],
            ['accounting', 'Accounting'],
            ['support', 'Support'],
            ['collaboration-agreements', 'Collaboration Agreements'],
        ] as [$slug, $name]) {
            Module::query()->create(['slug' => $slug, 'name' => $name]);
        }
    }

    public function test_permissions_are_exact_and_descendant_access_is_revoked_with_the_parent(): void
    {
        $agent = AgentAccount::query()->findOrFail(1);
        $manager = $this->staff(1, 'Manager', null, 'manager@example.test');
        $child = $this->staff(1, 'Manager', $manager->id, 'child@example.test');
        $grandchild = $this->staff(1, 'Manager', $child->id, 'grandchild@example.test');
        $service = app(AgentPermissionService::class);

        $service->syncPermissions($manager, ['reservation' => ['read', 'create']], $agent, 'Manager');
        $service->syncPermissions($child, ['reservation' => ['read', 'create']], $manager, 'Manager');
        $service->syncPermissions($grandchild, ['reservation' => ['read']], $child, 'Manager');

        $this->assertTrue($service->can($child, 'reservation', 'read'));
        $this->assertTrue($service->can($child, 'reservation', 'create'));
        $this->assertFalse($service->can($child, 'reservation', 'update'));
        $this->assertTrue($service->can($grandchild, 'reservation', 'read'));
        $this->assertFalse($service->can($grandchild, 'reservation', 'create'));

        try {
            $service->validatePermissionPayload($child, 'Manager', ['reservation' => ['update']]);
            $this->fail('A child must not delegate an action it does not possess.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        $service->syncPermissions($manager, [], $agent, 'Manager');
        $this->assertFalse($service->can($child, 'reservation', 'read'));
        $this->assertFalse($service->can($grandchild, 'reservation', 'read'));
    }

    public function test_staff_management_is_limited_to_creator_descendants_and_agent_account(): void
    {
        $agent = AgentAccount::query()->findOrFail(1);
        $manager = $this->staff(1, 'Manager', null, 'manager@example.test');
        $child = $this->staff(1, 'Manager', $manager->id, 'child@example.test');
        $grandchild = $this->staff(1, 'Manager', $child->id, 'grandchild@example.test');
        $unrelated = $this->staff(1, 'Manager', null, 'unrelated@example.test');
        $otherAgentStaff = $this->staff(2, 'Manager', null, 'other@example.test');
        $service = app(AgentPermissionService::class);

        $this->assertEqualsCanonicalizing(
            [$child->id, $grandchild->id],
            $service->manageableStaffQuery($manager)->pluck('id')->all(),
        );
        $this->assertTrue($service->targetIsManageable($manager, $grandchild));
        $this->assertFalse($service->targetIsManageable($manager, $unrelated));
        $this->assertFalse($service->targetIsManageable($manager, $otherAgentStaff));
        $this->assertEqualsCanonicalizing(
            [$manager->id, $child->id, $grandchild->id, $unrelated->id],
            $service->manageableStaffQuery($agent)->pluck('id')->all(),
        );
    }

    public function test_role_caps_exclude_nonexistent_agent_workflows_and_owner_staff_keeps_setup_access(): void
    {
        $ownerStaff = $this->staff(1, 'Consultant', null, 'owner@example.test');
        $service = app(AgentPermissionService::class);

        $this->assertTrue($service->roleAllows('Manager', 'users', 'create'));
        $this->assertTrue($service->roleAllows('Consultant', 'reservation', 'create'));
        $this->assertFalse($service->roleAllows('Consultant', 'users', 'read'));
        $this->assertTrue($service->roleAllows('Accountant', 'reservation', 'read'));
        $this->assertFalse($service->roleAllows('Accountant', 'reservation', 'create'));
        $this->assertTrue($service->roleAllows('Support', 'profile', 'update'));
        $this->assertFalse($service->roleAllows('Support', 'reservation', 'create'));
        $this->assertTrue($service->isAccountOwnerStaff($ownerStaff));
        $this->assertTrue($service->canManageUsers($ownerStaff, 'create'));
        $this->assertContains('create', $service->assignableMatrix($ownerStaff, 'Manager')['users']);
        $this->assertFalse($service->roleAllows('Manager', 'accounting', 'read'));
        $this->assertFalse($service->roleAllows('Manager', 'support', 'read'));
        $this->assertFalse($service->roleAllows('Manager', 'collaboration-agreements', 'read'));
        $this->assertFalse($service->roleAllows('Manager', 'reservation', 'approve'));
    }

    private function staff(int $agentId, string $role, ?int $creatorId, string $email): AgentStaffUser
    {
        return AgentStaffUser::query()->create([
            'agent_account_id' => $agentId,
            'created_by_agent_staff_user_id' => $creatorId,
            'user_id' => 'AGU-'.bin2hex(random_bytes(5)),
            'full_name' => $email,
            'email' => $email,
            'role' => $role,
            'status' => 'Active',
        ]);
    }
}
