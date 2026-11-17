<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Group;
use App\Models\MpoStaffUser;
use App\Models\Package;
use App\Support\CountryAccess;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MpoGroupPackageCountryScopeTest extends TestCase
{
    public function test_mpo_guard_resolves_country_before_portal_middleware_binds_models(): void
    {
        $originalRequest = app('request');
        $mpo = new AdminUser();
        $mpo->id = 10;
        $mpo->admin_type = 'mpo';
        $mpo->country_id = 20;
        \Illuminate\Support\Facades\Auth::guard('mpo')->login($mpo);
        app()->instance('request', Request::create('/mpo/packages/3/edit', 'GET'));

        try {
            $this->assertTrue(CountryAccess::isMpoAdmin());
            $this->assertSame(20, CountryAccess::countryId());
        } finally {
            \Illuminate\Support\Facades\Auth::guard('mpo')->logout();
            app()->instance('request', $originalRequest);
        }
    }

    public function test_mpo_staff_guard_resolves_parent_country_before_portal_middleware_binds_models(): void
    {
        $originalRequest = app('request');
        $mpo = new AdminUser();
        $mpo->id = 10;
        $mpo->admin_type = 'mpo';
        $mpo->country_id = 20;
        $staff = new MpoStaffUser();
        $staff->id = 7;
        $staff->setRelation('mpo', $mpo);
        \Illuminate\Support\Facades\Auth::guard('mpo_staff')->login($staff);
        app()->instance('request', Request::create('/mpo/groups/2/edit', 'GET'));

        try {
            $this->assertTrue(CountryAccess::isMpoAdmin());
            $this->assertSame(20, CountryAccess::countryId());
        } finally {
            \Illuminate\Support\Facades\Auth::guard('mpo_staff')->logout();
            app()->instance('request', $originalRequest);
        }
    }

    public function test_mpo_can_only_query_and_create_groups_and_packages_for_its_assigned_country(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->timestamps();
        });
        Schema::create('packages', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->timestamps();
        });
        DB::table('groups')->insert([
            ['name' => 'Country A group', 'country_id' => 10],
            ['name' => 'Country B group', 'country_id' => 20],
        ]);
        DB::table('packages')->insert([
            ['name' => 'Country A package', 'country_id' => 10],
            ['name' => 'Country B package', 'country_id' => 20],
        ]);

        $originalRequest = app('request');
        $mpo = new AdminUser();
        $mpo->id = 10;
        $mpo->admin_type = 'mpo';
        $mpo->country_id = 10;
        \Illuminate\Support\Facades\Auth::guard('mpo')->login($mpo);
        $mpoRequest = Request::create('/mpo/groups', 'GET');
        app()->instance('request', $mpoRequest);

        try {
            $this->assertSame(['Country A group'], Group::query()->orderBy('id')->pluck('name')->all());
            $this->assertSame(['Country A package'], Package::query()->orderBy('id')->pluck('name')->all());
            $this->assertNull(Group::query()->find(2));
            $this->assertNull(Package::query()->find(2));

            $group = Group::query()->create(['name' => 'New Country A group']);
            $package = Package::query()->create(['name' => 'New Country A package']);
            $this->assertSame(10, (int) $group->country_id);
            $this->assertSame(10, (int) $package->country_id);

            foreach ([Group::class, Package::class] as $modelClass) {
                try {
                    $modelClass::query()->create(['name' => 'Invalid country record', 'country_id' => 20]);
                    $this->fail('An MPO must not create records assigned to another country.');
                } catch (HttpException $exception) {
                    $this->assertSame(403, $exception->getStatusCode());
                }
            }

            app()->instance('request', Request::create('/admin/groups', 'GET'));
            $this->assertSame(3, Group::query()->count());
            $this->assertSame(3, Package::query()->count());
        } finally {
            \Illuminate\Support\Facades\Auth::guard('mpo')->logout();
            app()->instance('request', $originalRequest);
        }
    }
}
