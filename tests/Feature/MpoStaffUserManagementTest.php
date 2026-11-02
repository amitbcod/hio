<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\Country;
use App\Models\Module;
use App\Models\MpoRoleDelegation;
use App\Models\MpoRoleModulePermission;
use App\Models\MpoRoleModuleSetting;
use App\Models\MpoStaffUser;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MpoStaffUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_country_cannot_be_assigned_to_two_mpo_accounts(): void
    {
        $mpo = $this->mpo();

        $this->expectException(\Illuminate\Database\QueryException::class);
        AdminUser::query()->create([
            'name' => 'Second MPO Owner',
            'email' => 'second-owner@example.test',
            'password_hash' => Hash::make('OwnerPassword1!'),
            'role' => 'admin',
            'admin_type' => 'mpo',
            'country_id' => $mpo->country_id,
            'status' => 'active',
        ]);
    }

    public function test_mpo_staff_model_queries_are_scoped_to_the_parent_mpos_country(): void
    {
        $mpo = $this->mpo();
        $otherMpo = $this->mpo('other-country-owner@example.test');
        Region::query()->create(['name' => 'Owned Region', 'country_id' => $mpo->country_id]);
        Region::query()->create(['name' => 'Foreign Region', 'country_id' => $otherMpo->country_id]);

        request()->attributes->set('mpo_admin', $mpo);

        $this->assertTrue(\App\Support\CountryAccess::isMpoAdmin());
        $this->assertSame(
            (int) $mpo->country_id,
            \App\Support\CountryAccess::countryId(),
        );
        $this->assertSame(['Owned Region'], Region::query()->pluck('name')->all());
    }

    public function test_operational_module_route_requires_read_permission_and_is_country_scoped(): void
    {
        $mpo = $this->mpo();
        $otherMpo = $this->mpo('other-operational-mpo@example.test');
        $staff = $this->staff($mpo, 'Regional Reader');
        $regions = $this->module('regions', 'Regions');
        $this->rolePermission($mpo, 'Finance', $regions, 'read', true, false);
        Region::query()->create(['name' => 'Owned Region', 'country_id' => $mpo->country_id]);
        Region::query()->create(['name' => 'Foreign Region', 'country_id' => $otherMpo->country_id]);

        $response = $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.operations.module', ['module' => 'regions']))
            ->assertOk()
            ->assertSee('Owned Region')
            ->assertDontSee('Foreign Region')
            ->assertSee('Regions')
            ->assertDontSee('Payment Transactions');
        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    public function test_section_capacity_grants_every_supported_booking_action_for_each_service(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Section Staff');
        $permissionService = app(\App\Services\MpoStaffPermissionService::class);

        foreach ([
            'accommodation-bookings',
            'activity-bookings',
            'transport-bookings',
        ] as $slug) {
            $module = $this->module($slug, \Illuminate\Support\Str::headline($slug));
            MpoRoleModuleSetting::query()->create([
                'admin_user_id' => $mpo->id,
                'role' => 'Finance',
                'module_id' => $module->id,
                'capacity_level' => 'Section',
                'data_scope' => 'all',
            ]);

            foreach (config("mpo_staff.modules.{$slug}", []) as $action) {
                $this->assertTrue(
                    $permissionService->can($staff, $slug, $action),
                    "{$slug}.{$action} should be granted by Section capacity.",
                );
            }
        }
    }

    public function test_section_capacity_is_saved_as_all_supported_booking_actions(): void
    {
        $mpo = $this->mpo();
        $this->module('capacity-permissions', 'Capacity Permissions and Roles');

        foreach ([
            'accommodation-bookings',
            'activity-bookings',
            'transport-bookings',
        ] as $slug) {
            $module = $this->module($slug, \Illuminate\Support\Str::headline($slug));
            $this->actingAs($mpo, 'mpo')
                ->put(route('mpo.capacity-permissions.update'), [
                    'role' => 'Finance',
                    'settings' => [
                        $slug => [
                            'capacity_level' => 'Section',
                            'data_scope' => 'all',
                        ],
                    ],
                ])
                ->assertRedirect(route('mpo.capacity-permissions', ['role' => 'Finance']));

            $staff = $this->staff($mpo, 'Section '.$slug, 'section-'.$slug.'-staff@example.test');
            foreach (config("mpo_staff.modules.{$slug}", []) as $action) {
                $this->assertTrue(app(\App\Services\MpoStaffPermissionService::class)->can($staff, $slug, $action));
            }
        }

        $response = $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.capacity-permissions', ['role' => 'Finance']))
            ->assertOk()
            ->assertSee('Allowed by Section')
            ->assertDontSee('Accommodation Bookings: No actions granted');
        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    public function test_switching_booking_modules_from_section_to_tick_removes_inherited_actions(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Tick After Section');
        $permissionService = app(\App\Services\MpoStaffPermissionService::class);

        foreach ([
            'accommodation-bookings' => 'Accommodation Bookings',
            'activity-bookings' => 'Activity Bookings',
            'transport-bookings' => 'Transport Bookings',
        ] as $slug => $name) {
            $module = $this->module($slug, $name);
            MpoRoleModuleSetting::query()->create([
                'admin_user_id' => $mpo->id,
                'role' => 'Finance',
                'module_id' => $module->id,
                'capacity_level' => 'Section',
                'data_scope' => 'all',
            ]);
            foreach (config("mpo_staff.modules.{$slug}", []) as $action) {
                $this->rolePermission($mpo, 'Finance', $module, $action, true, false);
            }

            $this->actingAs($mpo, 'mpo')
                ->put(route('mpo.capacity-permissions.update'), [
                    'role' => 'Finance',
                    'settings' => [
                        $slug => [
                            'capacity_level' => 'Tick Function',
                            'data_scope' => 'all',
                        ],
                    ],
                    'permissions' => [
                        $slug => [
                            'read' => ['allowed' => 1],
                            'update' => ['allowed' => 1],
                        ],
                    ],
                ])
                ->assertRedirect(route('mpo.capacity-permissions', ['role' => 'Finance']));

            $this->assertTrue($permissionService->can($staff, $slug, 'read'));
            $this->assertTrue($permissionService->can($staff, $slug, 'update'));
            foreach (array_diff(config("mpo_staff.modules.{$slug}", []), ['read', 'update']) as $action) {
                $this->assertFalse($permissionService->can($staff, $slug, $action), "{$slug}.{$action} should be removed when switching to Tick.");
                $this->assertDatabaseMissing('mpo_role_module_permissions', [
                    'admin_user_id' => $mpo->id,
                    'role' => 'Finance',
                    'module_id' => $module->id,
                    'action' => $action,
                ]);
            }
        }
    }

    public function test_tick_capacity_grants_only_selected_booking_actions_and_update_is_supported(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Tick Staff');
        $permissionService = app(\App\Services\MpoStaffPermissionService::class);

        foreach ([
            'accommodation-bookings',
            'activity-bookings',
            'transport-bookings',
        ] as $slug) {
            $module = $this->module($slug, \Illuminate\Support\Str::headline($slug));
            MpoRoleModuleSetting::query()->create([
                'admin_user_id' => $mpo->id,
                'role' => 'Finance',
                'module_id' => $module->id,
                'capacity_level' => 'Tick Function',
                'data_scope' => 'all',
            ]);
            $this->rolePermission($mpo, 'Finance', $module, 'read', true, false);
            $this->rolePermission($mpo, 'Finance', $module, 'update', true, false);

            $this->assertTrue($permissionService->can($staff, $slug, 'update'));
            $this->assertFalse($permissionService->can($staff, $slug, 'confirm'));
            $serviceType = match ($slug) {
                'accommodation-bookings' => 'accommodation',
                'activity-bookings' => 'activity',
                default => 'transport',
            };
            $this->actingAs($staff, 'mpo_staff')
                ->post(route('mpo.operations.booking.update', [
                    'serviceType' => $serviceType,
                    'booking' => 99999,
                ]), [])
                ->assertNotFound();
        }
    }

    public function test_explicit_deny_override_still_applies_to_section_permissions(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Section With Deny');
        $module = $this->module('accommodation-bookings', 'Accommodation Bookings');
        MpoRoleModuleSetting::query()->create([
            'admin_user_id' => $mpo->id,
            'role' => 'Finance',
            'module_id' => $module->id,
            'capacity_level' => 'Section',
            'data_scope' => 'all',
        ]);
        \App\Models\MpoStaffPermissionOverride::query()->create([
            'mpo_staff_user_id' => $staff->id,
            'module_id' => $module->id,
            'action' => 'confirm',
            'effect' => 'deny',
            'can_delegate' => false,
        ]);

        $permissionService = app(\App\Services\MpoStaffPermissionService::class);
        $this->assertTrue($permissionService->can($staff, 'accommodation-bookings', 'read'));
        $this->assertFalse($permissionService->can($staff, 'accommodation-bookings', 'confirm'));
    }

    public function test_staff_with_update_cannot_confirm_without_confirm_permission_on_tick_access(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Update Only', 'update-only@example.test');
        $accommodationBookings = $this->module('accommodation-bookings', 'Accommodation Bookings');
        $this->rolePermission($mpo, 'Finance', $accommodationBookings, 'update', true, false);

        $this->actingAs($staff, 'mpo_staff')
            ->post(route('mpo.operations.booking.status', [
                'serviceType' => 'accommodation',
                'booking' => 99999,
            ]), ['booking_status' => 'Confirmed'])
            ->assertForbidden();
    }

    public function test_section_booking_access_authorizes_confirmation_without_bypassing_record_scope(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Section Booking Staff');

        foreach ([
            'accommodation' => 'accommodation-bookings',
            'activity' => 'activity-bookings',
            'transport' => 'transport-bookings',
        ] as $serviceType => $slug) {
            $module = $this->module($slug, \Illuminate\Support\Str::headline($slug));
            MpoRoleModuleSetting::query()->create([
                'admin_user_id' => $mpo->id,
                'role' => 'Finance',
                'module_id' => $module->id,
                'capacity_level' => 'Section',
                'data_scope' => 'all',
            ]);

            $this->actingAs($staff, 'mpo_staff')
                ->post(route('mpo.operations.booking.status', [
                    'serviceType' => $serviceType,
                    'booking' => 99999,
                ]), ['booking_status' => 'Confirmed'])
                ->assertNotFound();
        }

        $withoutPermission = $this->staff($mpo, 'No Region Permission', 'no-region@example.test', 'Support');
        $this->actingAs($withoutPermission, 'mpo_staff')
            ->get(route('mpo.operations.module', ['module' => 'regions']))
            ->assertForbidden();
    }

    public function test_section_staff_can_confirm_an_owned_booking_but_cannot_open_another_country_booking(): void
    {
        Mail::fake();
        $mpo = $this->mpo();
        $otherMpo = $this->mpo('other-booking-owner@example.test');
        $staff = $this->staff($mpo, 'Booking Supervisor');
        $module = $this->module('accommodation-bookings', 'Accommodation Bookings');
        MpoRoleModuleSetting::query()->create([
            'admin_user_id' => $mpo->id,
            'role' => 'Finance',
            'module_id' => $module->id,
            'capacity_level' => 'Section',
            'data_scope' => 'all',
        ]);

        $ownedBooking = $this->accommodationBooking($mpo, 'OWNED-BOOKING');
        $foreignBooking = $this->accommodationBooking($otherMpo, 'FOREIGN-BOOKING');

        $this->actingAs($staff, 'mpo_staff')
            ->post(route('mpo.operations.booking.status', [
                'serviceType' => 'accommodation',
                'booking' => $ownedBooking->id,
            ]), ['booking_status' => 'Confirmed'])
            ->assertRedirect();
        $this->assertSame('Confirmed', $ownedBooking->fresh()->booking_status);

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.operations.booking.show', [
                'serviceType' => 'accommodation',
                'booking' => $foreignBooking->id,
            ]))
            ->assertNotFound();
    }

    public function test_mpo_owner_lists_only_its_own_staff_and_foreign_ids_are_not_found(): void
    {
        $mpo = $this->mpo();
        $otherMpo = $this->mpo('other-mpo@example.test');
        $staff = $this->staff($mpo, 'Owned Staff');
        $foreignStaff = $this->staff($otherMpo, 'Foreign Staff');

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.users'))
            ->assertOk()
            ->assertSee('Owned Staff')
            ->assertDontSee('Foreign Staff');

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.users.edit', $staff->id))
            ->assertOk()
            ->assertSee('Access Rights')
            ->assertSee('Save User');

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.users.show', $foreignStaff->id))
            ->assertNotFound();

        $this->actingAs($mpo, 'mpo')
            ->put(route('mpo.users.update', $foreignStaff->id), [])
            ->assertNotFound();

        $this->assertSame($mpo->id, $staff->admin_user_id);
    }

    public function test_owner_can_create_staff_with_server_controlled_ownership_and_hashed_password(): void
    {
        $mpo = $this->mpo();

        $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.users.store'), [
                'full_name' => '  New Staff  ',
                'email' => 'new-staff@example.test',
                'mobile' => '+23051234567',
                'password' => 'StaffPassword1!',
                'password_confirmation' => 'StaffPassword1!',
                'role' => 'Finance',
                'admin_user_id' => 99999,
                'status' => 'Disabled',
                'last_login' => now()->subYears(2)->toDateTimeString(),
                'account_reset_required' => true,
                'permission_overrides' => [],
            ])
            ->assertRedirect(route('mpo.users'))
            ->assertSessionHas('success');

        $staff = MpoStaffUser::query()->where('email', 'new-staff@example.test')->firstOrFail();
        $this->assertSame($mpo->id, $staff->admin_user_id);
        $this->assertSame('New Staff', $staff->full_name);
        $this->assertSame('Active', $staff->status);
        $this->assertNull($staff->last_login);
        $this->assertFalse($staff->account_reset_required);
        $this->assertTrue(Hash::check('StaffPassword1!', $staff->password_hash));
        $this->assertNotSame('StaffPassword1!', $staff->password_hash);
    }

    public function test_required_fields_duplicate_emails_and_unauthorized_role_values_are_rejected(): void
    {
        $mpo = $this->mpo();
        $this->staff($mpo, 'Existing Staff', 'existing@example.test');

        $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.users.store'), [])
            ->assertSessionHasErrors(['full_name', 'email', 'password', 'role']);

        $this->post(route('mpo.users.store'), $this->validPayload([
            'email' => 'existing@example.test',
        ]))->assertSessionHasErrors('email');

        $this->post(route('mpo.users.store'), $this->validPayload([
            'email' => 'owner@example.test',
        ]))->assertSessionHasErrors('email');

        $this->post(route('mpo.users.store'), $this->validPayload([
            'email' => 'unused@example.test',
            'role' => 'global-admin',
        ]))->assertSessionHasErrors('role');
    }

    public function test_staff_user_needs_users_create_permission_and_cannot_delegate_unowned_actions_or_roles(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Finance', 'finance@example.test', 'Finance');
        $users = $this->module('users', 'Users');
        $profile = $this->module('profile', 'Profile');
        $this->rolePermission($mpo, 'Finance', $users, 'read', true, true);
        $this->rolePermission($mpo, 'Finance', $users, 'create', true, true);
        $this->rolePermission($mpo, 'Finance', $profile, 'read', true, true);
        $this->rolePermission($mpo, 'Finance', $profile, 'update', true, true);
        MpoRoleDelegation::query()->create([
            'admin_user_id' => $mpo->id,
            'assigner_role' => 'Finance',
            'assignable_role' => 'Support',
        ]);

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.users'))
            ->assertOk();
        $this->assertStringContainsString('Create User', $this->get(route('mpo.users'))->getContent());

        $this->actingAs($staff, 'mpo_staff')
            ->post(route('mpo.users.store'), $this->validPayload([
                'role' => 'Support',
                'permission_overrides' => [
                    'profile' => [
                        'read' => ['effect' => 'allow', 'can_delegate' => '1'],
                        'update' => ['effect' => 'allow', 'can_delegate' => '1'],
                    ],
                ],
            ]))
            ->assertRedirect(route('mpo.users'));

        $created = MpoStaffUser::query()->where('email', 'staff@example.test')->firstOrFail();
        $this->assertSame($mpo->id, $created->admin_user_id);
        $this->assertDatabaseHas('mpo_staff_permission_overrides', [
            'mpo_staff_user_id' => $created->id,
            'module_id' => $profile->id,
            'action' => 'update',
            'effect' => 'allow',
            'can_delegate' => 1,
        ]);

        $this->actingAs($staff, 'mpo_staff')
            ->post(route('mpo.users.store'), $this->validPayload([
                'email' => 'forbidden@example.test',
                'role' => 'Support',
                'permission_overrides' => [
                    'users' => ['disable' => ['effect' => 'allow']],
                ],
            ]))
            ->assertForbidden();

        $this->post(route('mpo.users.store'), $this->validPayload([
            'email' => 'forbidden-role@example.test',
            'role' => 'Admin',
        ]))->assertSessionHasErrors('role');

        $otherMpo = $this->mpo('other-owner@example.test');
        $otherStaff = $this->staff($otherMpo, 'Other Staff');
        $this->get(route('mpo.users.show', $otherStaff->id))->assertNotFound();
    }

    public function test_staff_without_users_create_cannot_create_accounts(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Reader', 'reader@example.test', 'Support');
        $users = $this->module('users', 'Users');
        $this->rolePermission($mpo, 'Support', $users, 'read', true, false);

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.users'))
            ->assertOk();
        $this->assertStringNotContainsString('Create User', $this->get(route('mpo.users'))->getContent());

        $this->post(route('mpo.users.store'), $this->validPayload())
            ->assertForbidden();
    }

    public function test_role_inheritance_and_explicit_denial_are_reflected_in_effective_permissions(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Inherited Access', 'inherited@example.test', 'Finance');
        $compliance = $this->module('compliance', 'Compliance');
        $this->rolePermission($mpo, 'Finance', $compliance, 'read', true, true);

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.users.show', $staff->id))
            ->assertOk()
            ->assertSee('Read (Inherited)');

        $staff->permissionOverrides()->create([
            'module_id' => $compliance->id,
            'action' => 'read',
            'effect' => 'deny',
            'can_delegate' => false,
        ]);

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.users.show', $staff->id))
            ->assertOk()
            ->assertSee('Explicitly denied');
    }

    public function test_mpo_owner_can_configure_role_permissions_and_data_is_tenant_scoped(): void
    {
        $mpo = $this->mpo();
        $otherMpo = $this->mpo('other-configuration@example.test');
        $profile = $this->module('profile', 'Profile');
        $compliance = $this->module('compliance', 'Compliance');
        $this->module('users', 'Users');
        $this->module('capacity-permissions', 'Capacity Permissions and Roles');

        $this->actingAs($mpo, 'mpo')
            ->get(route('mpo.capacity-permissions'))
            ->assertOk()
            ->assertSee('Supported MPO Modules and Actions')
            ->assertSee('Finance');

        $this->actingAs($mpo, 'mpo')
            ->put(route('mpo.capacity-permissions.update'), [
                'role' => 'Finance',
                'permissions' => [
                    'profile' => [
                        'read' => ['allowed' => '1', 'can_delegate' => '1'],
                        'update' => ['allowed' => '0', 'can_delegate' => '0'],
                    ],
                    'compliance' => [
                        'read' => ['allowed' => '1', 'can_delegate' => '0'],
                    ],
                ],
                'settings' => [
                    'profile' => [
                        'capacity_level' => 'Tick Function',
                        'data_scope' => 'all',
                        'notes' => 'Review profile changes.',
                    ],
                ],
                'assignable_roles' => ['Support'],
            ])
            ->assertRedirect(route('mpo.capacity-permissions', ['role' => 'Finance']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mpo_role_module_permissions', [
            'admin_user_id' => $mpo->id,
            'role' => 'Finance',
            'module_id' => $profile->id,
            'action' => 'read',
            'is_allowed' => 1,
            'can_delegate' => 1,
        ]);
        $this->assertDatabaseMissing('mpo_role_module_permissions', [
            'admin_user_id' => $mpo->id,
            'role' => 'Finance',
            'module_id' => $profile->id,
            'action' => 'update',
        ]);
        $this->assertDatabaseHas('mpo_role_module_settings', [
            'admin_user_id' => $mpo->id,
            'role' => 'Finance',
            'module_id' => $profile->id,
            'capacity_level' => 'Tick Function',
            'data_scope' => 'all',
            'notes' => 'Review profile changes.',
        ]);
        $this->assertDatabaseHas('mpo_role_delegations', [
            'admin_user_id' => $mpo->id,
            'assigner_role' => 'Finance',
            'assignable_role' => 'Support',
        ]);
        $this->assertDatabaseMissing('mpo_role_module_permissions', [
            'admin_user_id' => $otherMpo->id,
            'role' => 'Finance',
            'module_id' => $compliance->id,
        ]);
    }

    public function test_role_configuration_requires_explicit_staff_permission_and_delegated_role(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Supervisor', 'supervisor@example.test', 'Supervisor');
        $capacity = $this->module('capacity-permissions', 'Capacity Permissions and Roles');
        $users = $this->module('users', 'Users');
        $profile = $this->module('profile', 'Profile');
        $this->rolePermission($mpo, 'Supervisor', $capacity, 'read', true, true);

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.capacity-permissions', ['role' => 'Support']))
            ->assertForbidden();
        $this->put(route('mpo.capacity-permissions.update'), [
            'role' => 'Support',
            'permissions' => [
                'profile' => ['read' => ['allowed' => '1', 'can_delegate' => '0']],
            ],
        ])->assertForbidden();

        $this->rolePermission($mpo, 'Supervisor', $capacity, 'update', true, true);
        $this->rolePermission($mpo, 'Supervisor', $users, 'update', true, false);
        $this->rolePermission($mpo, 'Supervisor', $profile, 'read', true, true);
        MpoRoleDelegation::query()->create([
            'admin_user_id' => $mpo->id,
            'assigner_role' => 'Supervisor',
            'assignable_role' => 'Support',
        ]);
        $permissionService = app(\App\Services\MpoStaffPermissionService::class);
        $this->assertTrue($permissionService->can($staff, 'capacity-permissions', 'read'));
        $this->assertTrue($permissionService->can($staff, 'capacity-permissions', 'update'));
        $this->assertTrue($permissionService->roleAssignable($staff, 'Support'));
        $this->assertTrue($permissionService->canConfigureRole($staff, 'Support'));

        $rolePage = $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.capacity-permissions', ['role' => 'Support']));
        $rolePage->assertOk()
            ->assertSee('Support');
        $this->put(route('mpo.capacity-permissions.update'), [
            'role' => 'Support',
            'permissions' => [
                'profile' => ['read' => ['allowed' => '1', 'can_delegate' => '0']],
            ],
        ])->assertRedirect(route('mpo.capacity-permissions', ['role' => 'Support']));

        $this->assertDatabaseHas('mpo_role_module_permissions', [
            'admin_user_id' => $mpo->id,
            'role' => 'Support',
            'module_id' => $profile->id,
            'action' => 'read',
            'is_allowed' => 1,
        ]);
    }

    public function test_unsupported_module_actions_and_non_mpo_scopes_are_rejected(): void
    {
        $mpo = $this->mpo();
        $this->module('capacity-permissions', 'Capacity Permissions and Roles');
        $this->module('profile', 'Profile');

        $this->actingAs($mpo, 'mpo')
            ->put(route('mpo.capacity-permissions.update'), [
                'role' => 'Finance',
                'permissions' => [
                    'accounting' => ['approve' => ['allowed' => '1', 'can_delegate' => '1']],
                ],
                'settings' => [
                    'profile' => ['data_scope' => 'region'],
                ],
            ])
            ->assertSessionHasErrors('settings.profile.data_scope');

        $this->assertDatabaseMissing('mpo_role_module_permissions', [
            'admin_user_id' => $mpo->id,
            'role' => 'Finance',
        ]);
    }

    public function test_role_changes_are_immediately_enforced_and_unsupported_scopes_fail_closed(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Profile Reader', 'profile-reader@example.test', 'Finance');
        $profile = $this->module('profile', 'Profile');
        $this->rolePermission($mpo, 'Finance', $profile, 'read', true, false);

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.profile'))
            ->assertOk();

        MpoRoleModulePermission::query()
            ->where('admin_user_id', $mpo->id)
            ->where('role', 'Finance')
            ->where('module_id', $profile->id)
            ->where('action', 'read')
            ->delete();

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.profile'))
            ->assertForbidden();

        $this->rolePermission($mpo, 'Finance', $profile, 'read', true, false);
        \App\Models\MpoRoleModuleSetting::query()->create([
            'admin_user_id' => $mpo->id,
            'role' => 'Finance',
            'module_id' => $profile->id,
            'capacity_level' => 'Section',
            'data_scope' => 'region',
        ]);
        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.profile'))
            ->assertForbidden();
    }

    public function test_staff_permission_override_is_invalidated_when_its_grantor_loses_delegation_authority(): void
    {
        $mpo = $this->mpo();
        $actor = $this->staff($mpo, 'Delegating Finance', 'delegating-finance@example.test', 'Finance');
        $users = $this->module('users', 'Users');
        $profile = $this->module('profile', 'Profile');
        $this->rolePermission($mpo, 'Finance', $users, 'create', true, true);
        $this->rolePermission($mpo, 'Finance', $users, 'read', true, true);
        $this->rolePermission($mpo, 'Finance', $profile, 'read', true, true);
        MpoRoleDelegation::query()->create([
            'admin_user_id' => $mpo->id,
            'assigner_role' => 'Finance',
            'assignable_role' => 'Support',
        ]);

        $this->actingAs($actor, 'mpo_staff')
            ->post(route('mpo.users.store'), $this->validPayload([
                'role' => 'Support',
                'permission_overrides' => [
                    'profile' => ['read' => ['effect' => 'allow', 'can_delegate' => '1']],
                ],
            ]))
            ->assertRedirect(route('mpo.users'));

        $target = MpoStaffUser::query()->where('email', 'staff@example.test')->firstOrFail();
        $this->assertTrue(app(\App\Services\MpoStaffPermissionService::class)->can($target, 'profile', 'read'));

        MpoRoleModulePermission::query()
            ->where('admin_user_id', $mpo->id)
            ->where('role', 'Finance')
            ->where('module_id', $profile->id)
            ->where('action', 'read')
            ->delete();

        $this->assertFalse(app(\App\Services\MpoStaffPermissionService::class)->can($target->fresh(), 'profile', 'read'));
    }

    public function test_staff_can_disable_users_only_with_users_disable_permission_and_disabled_users_are_logged_out(): void
    {
        $mpo = $this->mpo();
        $actor = $this->staff($mpo, 'Supervisor', 'supervisor@example.test', 'Supervisor');
        $target = $this->staff($mpo, 'Target User', 'target@example.test', 'Support');
        $users = $this->module('users', 'Users');
        $this->rolePermission($mpo, 'Supervisor', $users, 'read', true, false);

        $this->actingAs($actor, 'mpo_staff')
            ->post(route('mpo.users.status', $target->id), ['status' => 'Disabled'])
            ->assertForbidden();

        $this->rolePermission($mpo, 'Supervisor', $users, 'disable', true, false);
        $this->actingAs($actor, 'mpo_staff')
            ->post(route('mpo.users.status', $target->id), ['status' => 'Disabled'])
            ->assertRedirect(route('mpo.users.show', $target->id));
        $this->assertSame('Disabled', $target->fresh()->status);

        $this->actingAs($target->fresh(), 'mpo_staff')
            ->get(route('mpo.users'))
            ->assertRedirect(route('mpo.login'));
    }

    public function test_last_active_mpo_staff_admin_cannot_be_disabled(): void
    {
        $mpo = $this->mpo();
        $adminStaff = $this->staff($mpo, 'Only Staff Admin', 'staff-admin@example.test', 'Admin');

        $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.users.status', $adminStaff->id), ['status' => 'Disabled'])
            ->assertStatus(422);

        $this->assertSame('Active', $adminStaff->fresh()->status);
    }

    public function test_blank_password_preserves_hash_and_permission_assignment_is_not_escalated(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Finance', 'finance@example.test', 'Finance');
        $target = $this->staff($mpo, 'Target', 'target@example.test', 'Support');
        $users = $this->module('users', 'Users');
        $this->rolePermission($mpo, 'Finance', $users, 'update', true, true);
        $this->rolePermission($mpo, 'Finance', $users, 'read', true, true);
        $this->rolePermission($mpo, 'Finance', $users, 'create', true, true);
        $hashBefore = $target->password_hash;

        $this->actingAs($staff, 'mpo_staff')
            ->put(route('mpo.users.update', $target->id), [
                'full_name' => 'Updated Target',
                'email' => $target->email,
                'mobile' => '',
                'role' => 'Support',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('mpo.users'));

        $this->assertSame($hashBefore, $target->fresh()->password_hash);
        $this->assertSame('Updated Target', $target->fresh()->full_name);
    }

    public function test_successful_staff_login_tracks_last_login_and_disabled_users_are_rejected(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Login Staff', 'login-staff@example.test');
        $users = $this->module('users', 'Users');
        $this->rolePermission($mpo, 'Finance', $users, 'read', true, false);

        $this->post(route('mpo.login'), [
            'email' => $staff->email,
            'password' => 'WrongPassword1!',
        ])->assertSessionHasErrors('email');
        $this->assertNull($staff->fresh()->last_login);

        $this->post(route('mpo.login'), [
            'email' => $staff->email,
            'password' => 'StaffPassword1!',
        ])->assertRedirect(route('mpo.users'));

        $this->assertNotNull($staff->fresh()->last_login);

        $staff->forceFill(['status' => 'Disabled', 'last_login' => null])->save();
        $this->post(route('mpo.logout'));
        $this->post(route('mpo.login'), [
            'email' => $staff->email,
            'password' => 'StaffPassword1!',
        ])->assertSessionHasErrors('email');
        $this->assertNull($staff->fresh()->last_login);
    }

    public function test_accounting_only_staff_can_log_in_to_their_accounting_module(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Accountant', 'accountant@example.test', 'Finance');
        $accounting = $this->module('accounting', 'Accounting');
        $this->rolePermission($mpo, 'Finance', $accounting, 'read', true, false);

        $this->post(route('mpo.login'), [
            'email' => $staff->email,
            'password' => 'StaffPassword1!',
        ])->assertRedirect(route('mpo.accounting-commission'));
    }

    public function test_admin_oversight_is_global_admin_only_and_mpo_staff_cannot_use_admin_routes(): void
    {
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Admin Visible Staff');
        $globalAdmin = $this->globalAdmin();

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('admin.country-admins.users', $mpo))
            ->assertRedirect(route('mpo.profile'));

        $this->withSession(['admin_id' => $globalAdmin->id])
            ->get(route('admin.country-admins.users', $mpo))
            ->assertOk()
            ->assertSee('Admin Visible Staff');
    }

    public function test_password_reset_uses_the_configured_laravel_broker_and_requires_status_action_permissions(): void
    {
        Notification::fake();
        Mail::fake();
        $mpo = $this->mpo();
        $staff = $this->staff($mpo, 'Reset Target', 'reset-target@example.test');

        $this->actingAs($mpo, 'mpo')
            ->post(route('mpo.users.send-password-reset', $staff->id))
            ->assertRedirect(route('mpo.users.show', $staff->id))
            ->assertSessionHas('success');

        $this->assertTrue($staff->fresh()->account_reset_required);
        Notification::assertSentTo($staff, \App\Notifications\MpoStaffPasswordReset::class);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'New Staff',
            'email' => 'staff@example.test',
            'mobile' => '+23051234567',
            'password' => 'StaffPassword1!',
            'password_confirmation' => 'StaffPassword1!',
            'role' => 'Finance',
        ], $overrides);
    }

    private function mpo(string $email = 'owner@example.test'): AdminUser
    {
        $country = Country::query()->create([
            'name' => 'Country '.str_replace(['@', '.'], '-', $email),
            'iso_code' => null,
            'currency_code' => 'MUR',
            'time_zone' => 'Indian/Mauritius',
            'is_active' => true,
        ]);

        return AdminUser::query()->create([
            'name' => 'MPO Owner',
            'email' => $email,
            'password_hash' => Hash::make('OwnerPassword1!'),
            'role' => 'admin',
            'admin_type' => 'mpo',
            'country_id' => $country->id,
            'status' => 'active',
        ]);
    }

    private function staff(AdminUser $mpo, string $name, string $email = 'staff@example.test', string $role = 'Finance'): MpoStaffUser
    {
        static $counter = 0;
        $counter++;

        return MpoStaffUser::query()->create([
            'admin_user_id' => $mpo->id,
            'full_name' => $name,
            'email' => str_replace('staff@example.test', 'staff'.$counter.'@example.test', $email),
            'password_hash' => Hash::make('StaffPassword1!'),
            'role' => $role,
            'status' => 'Active',
            'account_reset_required' => false,
        ]);
    }

    private function globalAdmin(): AdminUser
    {
        return AdminUser::query()->create([
            'name' => 'Global Admin',
            'email' => 'global-admin@example.test',
            'password_hash' => Hash::make('GlobalPassword1!'),
            'role' => 'super_admin',
            'admin_type' => 'global',
            'status' => 'active',
        ]);
    }

    private function module(string $slug, string $name): Module
    {
        return Module::query()->firstOrCreate(['slug' => $slug], ['name' => $name]);
    }

    private function rolePermission(AdminUser $mpo, string $role, Module $module, string $action, bool $allowed, bool $delegable): void
    {
        MpoRoleModulePermission::query()->create([
            'admin_user_id' => $mpo->id,
            'role' => $role,
            'module_id' => $module->id,
            'action' => $action,
            'is_allowed' => $allowed,
            'can_delegate' => $delegable,
        ]);
    }

    private function accommodationBooking(AdminUser $mpo, string $reference): AccommodationBooking
    {
        $accommodation = Accommodation::query()->create([
            'accommodation_id' => $reference,
            'property_name' => $reference,
            'property_type' => 'Hotel',
            'country_id' => $mpo->country_id,
        ]);

        return AccommodationBooking::query()->create([
            'accommodation_id' => $accommodation->id,
            'booking_reference' => $reference,
            'guest_name' => 'Test Guest',
            'guest_email' => 'guest@example.test',
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
            'booking_status' => 'Pending',
            'country_id' => $mpo->country_id,
        ]);
    }
}
