<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Country;
use App\Models\Module;
use App\Models\MpoRoleModulePermission;
use App\Models\MpoStaffUser;
use App\Models\Operator;
use App\Models\TransportVehicleName;
use App\Models\TransportVehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TransportListingAccessTest extends TestCase
{
    use RefreshDatabase;

    private string $suffix;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suffix = bin2hex(random_bytes(4));
    }

    public function test_global_admin_can_list_all_countries_and_open_transport_details(): void
    {
        [$firstCountry, $firstOperator] = $this->operator('Mauritius');
        [$secondCountry, $secondOperator] = $this->operator('India');
        $mauritiusTransport = $this->transport($firstOperator, $firstCountry, 'Mauritius Minibus');
        $indiaTransport = $this->transport($secondOperator, $secondCountry, 'India Sedan');
        $vehicleName = $this->vehicleName('Mauritius Minibus', 'Minibus');
        $admin = $this->globalAdmin();

        $response = $this->withSession(['admin_id' => $admin->id])
            ->get(route('admin.transport.index'));

        $response->assertOk()
            ->assertSee('Mauritius Minibus')
            ->assertSee('India Sedan')
            ->assertSee('Mauritius Operator')
            ->assertSee('India Operator')
            ->assertSee('Mauritius')
            ->assertSee('India')
            ->assertSee(route('admin.transport.index'), false);
        $this->assertMatchesRegularExpression(
            '/<a href="'.preg_quote(route('admin.transport.index'), '/').'"[^>]*>\s*Transport\s*<\/a>/s',
            $response->getContent(),
        );

        $this->withSession(['admin_id' => $admin->id])
            ->get(route('admin.transport.show', $mauritiusTransport))
            ->assertOk()
            ->assertSee('Mauritius Operator')
            ->assertSee('Mauritius');

        $this->withSession(['admin_id' => $admin->id])
            ->get(route('admin.transport.show', $indiaTransport))
            ->assertOk()
            ->assertSee('India Operator');

        $this->withSession(['admin_id' => $admin->id])
            ->get(route('admin.transport.edit', $mauritiusTransport))
            ->assertOk()
            ->assertSee('Edit Transport Listing');

        $this->withSession(['admin_id' => $admin->id])
            ->put(route('admin.transport.update', $mauritiusTransport), [
                'vehicle_name_id' => $vehicleName->id,
                'registration_number' => 'MU-1234',
                'service_description' => 'Updated by Global Admin',
                'overview' => 'Updated overview',
                'contact_person' => 'Admin Contact',
                'contact_phone' => '+2301234567',
                'contact_email' => 'admin-contact@example.test',
            ])
            ->assertRedirect(route('admin.transport.show', $mauritiusTransport));

        $this->assertDatabaseHas('transports', [
            'id' => $mauritiusTransport,
            'registration_number' => 'MU-1234',
            'service_description' => 'Updated by Global Admin',
            'vehicle_name' => 'Mauritius Minibus',
            'vehicle_type' => 'Minibus',
        ]);
    }

    public function test_operator_transport_listing_remains_available_to_its_owner(): void
    {
        [$country, $operator] = $this->operator('Mauritius');
        $this->transport($operator, $country, 'Operator-Owned Minibus');

        $this->actingAs($operator, 'operator')
            ->get(route('operator.transport.index'))
            ->assertOk()
            ->assertSee('Operator-Owned Minibus');
    }

    public function test_mpo_staff_with_transport_read_permission_only_sees_operator_country_records(): void
    {
        [$mauritius, $mauritiusOperator] = $this->operator('Mauritius');
        [$india, $indiaOperator] = $this->operator('India');
        $owned = $this->transport($mauritiusOperator, $india, 'Mauritius Operator Vehicle');
        $foreign = $this->transport($indiaOperator, $india, 'India Operator Vehicle');

        $mpo = $this->mpo($mauritius, 'mauritius-mpo@example.test');
        $staff = $this->staff($mpo, 'transport-reader@example.test');
        $this->grantTransportRead($mpo, 'Finance');

        $response = $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.transport.index', [
                'country_id' => $india->id,
                'operator' => 'Operator',
                'sort' => 'vehicle_name',
                'direction' => 'asc',
            ]))
            ->assertOk()
            ->assertSee('Mauritius Operator Vehicle')
            ->assertSee('Mauritius Operator')
            ->assertDontSee('India Operator Vehicle')
            ->assertDontSee('India Operator')
            ->assertSee(route('mpo.transport.index'), false);
        $response->assertDontSee(route('mpo.transport.edit', $owned), false);
        $this->assertMatchesRegularExpression(
            '/<a href="'.preg_quote(route('mpo.transport.index'), '/').'"[^>]*>\s*Transport\s*<\/a>/s',
            $response->getContent(),
        );

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.transport.show', $owned))
            ->assertOk()
            ->assertSee('Mauritius Operator Vehicle');

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.transport.edit', $owned))
            ->assertForbidden();

        $this->vehicleName('Mauritius Operator Vehicle', 'Minibus');
        $this->grantTransportPermission($mpo, 'Finance', 'update');

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.transport.index'))
            ->assertOk()
            ->assertSee(route('mpo.transport.edit', $owned), false);

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.transport.edit', $owned))
            ->assertOk()
            ->assertSee('Edit Transport Listing');

        $this->actingAs($staff, 'mpo_staff')
            ->put(route('mpo.transport.update', $owned), [
                'vehicle_name_id' => TransportVehicleName::query()
                    ->where('name', 'Mauritius Operator Vehicle')
                    ->value('id'),
                'registration_number' => 'MU-STAFF-1',
                'service_description' => 'Updated within MPO country',
            ])
            ->assertRedirect(route('mpo.transport.show', $owned));

        $this->assertDatabaseHas('transports', [
            'id' => $owned,
            'country_id' => $mauritius->id,
            'registration_number' => 'MU-STAFF-1',
            'service_description' => 'Updated within MPO country',
        ]);

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.transport.show', $foreign))
            ->assertNotFound();

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.transport.edit', $foreign))
            ->assertNotFound();

        $this->actingAs($staff, 'mpo_staff')
            ->put(route('mpo.transport.update', $foreign), [
                'vehicle_name_id' => TransportVehicleName::query()
                    ->where('name', 'Mauritius Operator Vehicle')
                    ->value('id'),
                'registration_number' => 'FORBIDDEN',
            ])
            ->assertNotFound();

        $indiaMpo = $this->mpo($india, 'india-mpo@example.test');
        $indiaStaff = $this->staff($indiaMpo, 'india-transport-reader@example.test');
        $this->grantTransportRead($indiaMpo, 'Finance');

        $this->actingAs($indiaStaff, 'mpo_staff')
            ->get(route('mpo.transport.index'))
            ->assertOk()
            ->assertSee('India Operator Vehicle')
            ->assertDontSee('Mauritius Operator Vehicle');
    }

    public function test_mpo_staff_without_transport_read_permission_cannot_open_listing(): void
    {
        [$country] = $this->operator('Mauritius');
        $mpo = $this->mpo($country, 'no-transport-permission@example.test');
        $staff = $this->staff($mpo, 'transport-denied@example.test');
        $this->grantRead($mpo, 'users', 'Finance');

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.users'))
            ->assertOk()
            ->assertDontSee(route('mpo.transport.index'), false);

        $this->actingAs($staff, 'mpo_staff')
            ->get(route('mpo.transport.index'))
            ->assertForbidden();
    }

    private function operator(string $countryName): array
    {
        $country = Country::query()->create([
            'name' => $countryName.' '.$this->suffix,
            'currency_code' => $countryName === 'India' ? 'INR' : 'MUR',
            'time_zone' => $countryName === 'India' ? 'Asia/Kolkata' : 'Indian/Mauritius',
            'is_active' => true,
        ]);
        $operator = Operator::query()->create([
            'operator_id' => 'OP-'.strtoupper(substr(md5($countryName.$this->suffix), 0, 10)),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => strtolower($countryName).'-'.$this->suffix.'@operator.example.test',
            'full_name' => $countryName.' Operator',
            'business_legal_name' => $countryName.' Operator',
            'password_hash' => Hash::make('Password123!'),
            'account_status' => 'active',
            'country_id' => $country->id,
        ]);
        $operator->profile()->create([
            'business_legal_name' => $countryName.' Operator',
            'service_types' => ['Transport'],
        ]);

        return [$country, $operator];
    }

    private function transport(Operator $operator, Country $country, string $vehicleName): int
    {
        return DB::table('transports')->insertGetId([
            'operator_id' => $operator->id,
            'country_id' => $country->id,
            'vehicle_name' => $vehicleName,
            'vehicle_type' => 'Minibus',
            'approval_status' => 'Pending',
            'status' => 'In Review',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function mpo(Country $country, string $email): AdminUser
    {
        return AdminUser::query()->create([
            'name' => 'Country MPO',
            'email' => str_replace('@', '-'.$this->suffix.'@', $email),
            'password_hash' => Hash::make('Password123!'),
            'role' => 'admin',
            'admin_type' => 'mpo',
            'country_id' => $country->id,
            'status' => 'active',
        ]);
    }

    private function staff(AdminUser $mpo, string $email): MpoStaffUser
    {
        return MpoStaffUser::query()->create([
            'admin_user_id' => $mpo->id,
            'created_by_admin_user_id' => $mpo->id,
            'full_name' => 'Transport Staff',
            'email' => str_replace('@', '-'.$this->suffix.'@', $email),
            'password_hash' => Hash::make('Password123!'),
            'role' => 'Finance',
            'status' => 'Active',
            'account_reset_required' => false,
        ]);
    }

    private function globalAdmin(): AdminUser
    {
        return AdminUser::query()->create([
            'name' => 'Global Admin',
            'email' => 'global-admin-transport-'.$this->suffix.'@example.test',
            'password_hash' => Hash::make('Password123!'),
            'role' => 'super_admin',
            'admin_type' => 'global',
            'status' => 'active',
        ]);
    }

    private function grantTransportRead(AdminUser $mpo, string $role): void
    {
        $this->grantRead($mpo, 'transport', $role);
    }

    private function grantTransportPermission(AdminUser $mpo, string $role, string $action): void
    {
        $module = Module::query()->firstOrCreate(
            ['slug' => 'transport'],
            ['name' => 'Transport'],
        );
        MpoRoleModulePermission::query()->create([
            'admin_user_id' => $mpo->id,
            'role' => $role,
            'module_id' => $module->id,
            'action' => $action,
            'is_allowed' => true,
            'can_delegate' => false,
        ]);
    }

    private function vehicleName(string $name, string $type): TransportVehicleName
    {
        $vehicleType = TransportVehicleType::query()->firstOrCreate(
            ['name' => $type],
            ['is_active' => true],
        );

        return TransportVehicleName::query()->firstOrCreate(
            [
                'name' => $name,
                'transport_vehicle_type_id' => $vehicleType->id,
            ],
            [
                'seat_capacity' => 10,
                'is_active' => true,
            ],
        );
    }

    private function grantRead(AdminUser $mpo, string $slug, string $role): void
    {
        $module = Module::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => ucfirst($slug)],
        );
        MpoRoleModulePermission::query()->create([
            'admin_user_id' => $mpo->id,
            'role' => $role,
            'module_id' => $module->id,
            'action' => 'read',
            'is_allowed' => true,
            'can_delegate' => false,
        ]);
    }
}
