<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\MpoStaffUser;
use App\Models\Trip;
use App\Services\MpoStaffPermissionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OperationalModuleController extends Controller
{
    public function __construct(private readonly MpoStaffPermissionService $permissions) {}

    public function index(Request $request, string $module)
    {
        $staffUser = $request->attributes->get('mpo_staff_user');
        abort_unless($staffUser instanceof MpoStaffUser, 403);
        abort_unless($this->permissions->can($staffUser, $module, 'read'), 403);

        $definition = config("mpo_staff.registry.{$module}");
        abort_unless(is_array($definition), 404);

        $countryId = $request->attributes->get('mpo_admin')?->country_id;
        abort_if(! $countryId, 403, 'The MPO has no assigned country.');

        if ($definition['source'] === 'trips') {
            $records = $this->trips((int) $countryId)
                ->paginate(25)
                ->through(static fn (Trip $trip): array => $trip->getAttributes());
        } else {
            $table = $definition['source'];
            abort_unless(Schema::hasTable($table) && Schema::hasColumn($table, 'country_id'), 404);
            foreach ($definition['columns'] as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    throw new \RuntimeException("The configured MPO module column {$table}.{$column} does not exist.");
                }
            }

            $records = DB::table($table)
                ->where("{$table}.country_id", $countryId)
                ->select(array_map(static fn (string $column): string => "{$table}.{$column}", $definition['columns']))
                ->orderByDesc("{$table}.id")
                ->paginate(25)
                ->through(static fn (object $record): array => (array) $record);
        }

        return view('mpo.operations.index', [
            'definition' => $definition,
            'records' => $records,
        ]);
    }

    private function trips(int $countryId): Builder
    {
        return Trip::query()
            ->select(['id', 'title', 'start_date', 'end_date', 'status', 'priority'])
            ->where(function (Builder $query) use ($countryId): void {
                $query->whereHas('accommodationBookings', fn (Builder $bookings) => $bookings->where('country_id', $countryId))
                    ->orWhereHas('activityBookings', fn (Builder $bookings) => $bookings->where('country_id', $countryId))
                    ->orWhereHas('transportBookings', fn (Builder $bookings) => $bookings->where('country_id', $countryId));
            })
            ->orderByDesc('id');
    }
}
