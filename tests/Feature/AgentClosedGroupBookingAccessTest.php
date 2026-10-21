<?php

namespace Tests\Feature;

use App\Http\Controllers\Agent\ClosedGroupBookingController;
use App\Models\AgentAccount;
use App\Models\Group;
use App\Support\ClosedGroupBookingDates;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AgentClosedGroupBookingAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'agent_group_booking_access_test',
            'database.connections.agent_group_booking_access_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('agent_group_booking_access_test');
        DB::setDefaultConnection('agent_group_booking_access_test');

        Schema::create('agent_accounts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('country_id');
            $table->string('agency_name');
            $table->string('contact_person');
            $table->string('account_status');
        });
        Schema::create('groups', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('country_id');
            $table->string('name');
            $table->string('group_type');
            $table->string('status');
            $table->date('available_from')->nullable();
            $table->date('available_to')->nullable();
            $table->unsignedInteger('booking_cutoff_days')->nullable();
            $table->unsignedInteger('no_of_days')->nullable();
            $table->unsignedInteger('minimum_pax')->nullable();
            $table->unsignedInteger('maximum_pax')->nullable();
            $table->timestamps();
        });

        DB::table('agent_accounts')->insert([
            'id' => 1,
            'country_id' => 10,
            'agency_name' => 'Country Ten Agency',
            'contact_person' => 'Agency Contact',
            'account_status' => 'approved',
        ]);
        DB::table('groups')->insert([
            $this->group(1, 10, 'Eligible group', 'Closed Group', 'published'),
            $this->group(2, 20, 'Other country', 'Closed Group', 'published'),
            $this->group(3, 10, 'Draft group', 'Closed Group', 'draft'),
            $this->group(4, 10, 'Expired group', 'Closed Group', 'published', now()->subDays(10)->toDateString(), now()->subDays(3)->toDateString()),
            $this->group(5, 10, 'No eligible start date', 'Closed Group', 'published', now()->addDays(2)->toDateString(), now()->addDays(3)->toDateString(), 5),
            $this->group(6, 10, 'Open group', 'Open Group', 'published'),
            $this->group(7, 10, 'Singular closed group spelling', 'Close Group', 'published'),
            $this->group(8, 10, 'Past start with dates remaining', 'Closed Group', 'published', now()->subDays(10)->toDateString(), now()->addDays(10)->toDateString()),
            $this->group(9, 10, 'Not enough duration remaining', 'Closed Group', 'published', now()->subDays(10)->toDateString(), now()->addDay()->toDateString(), 1),
        ]);
    }

    public function test_agent_booking_wizard_only_lists_available_published_groups_in_its_country(): void
    {
        $agent = AgentAccount::findOrFail(1);
        Auth::guard('agent')->setUser($agent);

        $view = app(ClosedGroupBookingController::class)->create(Request::create('/agent/closed-groups/book'));
        $groups = $view->getData()['groups'];

        $this->assertSame([1, 7, 8], $groups->pluck('id')->all());
        $this->assertSame(now()->toDateString(), $groups->firstWhere('id', 8)->agent_booking_min_start_date);
        $this->assertSame(now()->addDays(9)->toDateString(), $groups->firstWhere('id', 8)->agent_booking_max_start_date);
        $this->assertStringContainsString('Country Ten Agency', $view->with('errors', new ViewErrorBag())->render());
    }

    public function test_agent_cannot_load_a_group_from_another_country(): void
    {
        Auth::guard('agent')->setUser(AgentAccount::findOrFail(1));

        try {
            app(ClosedGroupBookingController::class)->rooms(Request::create('/agent/closed-groups/2/rooms'), 2);
            $this->fail('An Agent must not load room details for another country.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
    }

    public function test_agent_booking_dates_allow_a_full_tour_within_the_available_window(): void
    {
        $group = Group::findOrFail(8);
        $today = Carbon::today();

        $this->assertTrue(ClosedGroupBookingDates::isBookableStartDate($group, $today->toDateString(), $today));
        $this->assertTrue(ClosedGroupBookingDates::isBookableStartDate($group, $today->copy()->addDays(9)->toDateString(), $today));
        $this->assertFalse(ClosedGroupBookingDates::isBookableStartDate($group, $today->copy()->addDays(10)->toDateString(), $today));
    }

    public function test_agent_approval_timestamp_is_cast_to_a_date_time(): void
    {
        $agent = new AgentAccount();
        $agent->setRawAttributes(['approved_at' => '2026-10-19 13:38:22']);

        $this->assertInstanceOf(Carbon::class, $agent->approved_at);
        $this->assertSame('2026-10-19 13:38', $agent->approved_at->format('Y-m-d H:i'));
    }

    private function group(
        int $id,
        int $countryId,
        string $name,
        string $type,
        string $status,
        ?string $from = null,
        ?string $to = null,
        int $cutoffDays = 0
    ): array {
        return [
            'id' => $id,
            'country_id' => $countryId,
            'name' => $name,
            'group_type' => $type,
            'status' => $status,
            'available_from' => $from ?? now()->addDays(30)->toDateString(),
            'available_to' => $to ?? now()->addDays(40)->toDateString(),
            'booking_cutoff_days' => $cutoffDays,
            'no_of_days' => 2,
            'minimum_pax' => 2,
            'maximum_pax' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
