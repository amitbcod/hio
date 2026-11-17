<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Operator;
use App\Models\Transport;
use App\Models\TransportVehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class TransportListingQuery
{
    private const SORTABLE_COLUMNS = [
        'vehicle_name',
        'vehicle_type',
        'status',
        'created_at',
        'updated_at',
    ];

    public function paginate(Request $request, ?int $countryId = null, bool $includeCountryFilter = false): array
    {
        $rules = [
            'vehicle_name' => ['nullable', 'string', 'max:150'],
            'vehicle_type' => ['nullable', 'string', 'max:150'],
            'operator' => ['nullable', 'string', 'max:191'],
            'status' => ['nullable', Rule::in([
                Transport::STATUS_DRAFT,
                Transport::STATUS_IN_REVIEW,
                Transport::STATUS_ACTIVE,
                Transport::STATUS_INACTIVE,
                Transport::STATUS_ARCHIVED,
            ])],
            'created_date' => ['nullable', 'date_format:Y-m-d'],
            'sort' => ['nullable', Rule::in([...self::SORTABLE_COLUMNS, 'operator'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
        if ($includeCountryFilter) {
            $rules['country_id'] = ['nullable', 'integer', 'exists:countries,id'];
        }

        $filters = $request->validate($rules);
        $filters = array_merge([
            'vehicle_name' => '',
            'vehicle_type' => '',
            'operator' => '',
            'status' => '',
            'created_date' => '',
            'country_id' => '',
            'sort' => 'created_at',
            'direction' => 'desc',
        ], $filters);

        $effectiveCountryId = $countryId;
        if ($includeCountryFilter && $filters['country_id'] !== '') {
            $effectiveCountryId = (int) $filters['country_id'];
        }

        $query = $this->baseQuery($effectiveCountryId);
        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters['sort'], $filters['direction']);

        $transports = $query->paginate(20)->appends($filters);
        $this->addVehicleCounts($transports);

        return [
            'transports' => $transports,
            'filters' => $filters,
            'statuses' => [
                Transport::STATUS_DRAFT,
                Transport::STATUS_IN_REVIEW,
                Transport::STATUS_ACTIVE,
                Transport::STATUS_INACTIVE,
                Transport::STATUS_ARCHIVED,
            ],
            'countries' => $includeCountryFilter
                ? Country::query()->orderBy('name')->get(['id', 'name'])
                : collect(),
        ];
    }

    public function findOrFail(int $transportId, ?int $countryId = null): Transport
    {
        return $this->baseQuery($countryId)->findOrFail($transportId);
    }

    private function baseQuery(?int $countryId): Builder
    {
        $query = Transport::query()
            ->withoutGlobalScope('admin_country')
            ->with([
                'operator.business',
                'operator.country',
                'operator.profile',
                'vehicleName.vehicleType',
            ]);

        if ($countryId !== null) {
            $query->whereHas('operator', fn (Builder $operatorQuery) => $operatorQuery
                ->where('country_id', $countryId));
        }

        if (Schema::hasTable('transport_vehicles')) {
            $query->withCount([
                'vehicles as total_vehicle_qty' => fn (Builder $vehicleQuery) => $vehicleQuery
                    ->withoutGlobalScope('admin_country'),
                'activeVehicles as active_vehicle_qty' => fn (Builder $vehicleQuery) => $vehicleQuery
                    ->withoutGlobalScope('admin_country'),
                'vehicles as available_vehicle_qty' => function (Builder $vehicleQuery): void {
                    $vehicleQuery->withoutGlobalScope('admin_country')
                        ->where('status', 'Active')
                        ->where(function (Builder $expiryQuery): void {
                            $expiryQuery->whereNull('license_expiry_date')
                                ->orWhereDate('license_expiry_date', '>=', now()->toDateString());
                        })
                        ->where(function (Builder $expiryQuery): void {
                            $expiryQuery->whereNull('insurance_expiry_date')
                                ->orWhereDate('insurance_expiry_date', '>=', now()->toDateString());
                        });
                },
            ])->with([
                'vehicles' => fn (Relation $vehicleRelation) => $vehicleRelation->withoutGlobalScope('admin_country'),
            ]);
        }

        return $query;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['vehicle_name'] !== '') {
            $query->where(function (Builder $nameQuery) use ($filters): void {
                $nameQuery->where('vehicle_name', 'like', '%'.$filters['vehicle_name'].'%')
                    ->orWhereHas('vehicleName', fn (Builder $vehicleNameQuery) => $vehicleNameQuery
                        ->where('name', 'like', '%'.$filters['vehicle_name'].'%'));
            });
        }

        if ($filters['vehicle_type'] !== '') {
            $query->where(function (Builder $typeQuery) use ($filters): void {
                $typeQuery->where('vehicle_type', 'like', '%'.$filters['vehicle_type'].'%')
                    ->orWhereHas('vehicleName.vehicleType', fn (Builder $vehicleTypeQuery) => $vehicleTypeQuery
                        ->where('name', 'like', '%'.$filters['vehicle_type'].'%'));
            });
        }

        if ($filters['operator'] !== '') {
            $query->whereHas('operator', function (Builder $operatorQuery) use ($filters): void {
                $search = '%'.$filters['operator'].'%';
                $operatorQuery->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery->where('full_name', 'like', $search)
                        ->orWhere('business_legal_name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhereHas('business', fn (Builder $businessQuery) => $businessQuery
                            ->where('legal_name', 'like', $search));
                });
            });
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if ($filters['created_date'] !== '') {
            $query->whereDate('created_at', $filters['created_date']);
        }
    }

    private function applySorting(Builder $query, string $sort, string $direction): void
    {
        if ($sort === 'operator') {
            $query->orderBy(
                Operator::query()
                    ->select('business_legal_name')
                    ->whereColumn('operators.id', 'transports.operator_id'),
                $direction,
            );
        } else {
            $query->orderBy('transports.'.$sort, $direction);
        }

        $query->orderByDesc('transports.id');
    }

    private function addVehicleCounts(LengthAwarePaginator $transports): void
    {
        if (! Schema::hasTable('transport_vehicles')) {
            $transports->getCollection()->each(function (Transport $transport): void {
                $transport->setAttribute('total_vehicle_qty', 1);
                $transport->setAttribute('active_vehicle_qty', 1);
                $transport->setAttribute('available_vehicle_qty', 1);
                $transport->setAttribute('has_expired_vehicle', false);
            });

            return;
        }

        $today = now()->startOfDay();
        $transports->getCollection()->each(function (Transport $transport) use ($today): void {
            $expiredVehicles = $transport->vehicles->filter(function (TransportVehicle $vehicle) use ($today): bool {
                return ($vehicle->license_expiry_date && $vehicle->license_expiry_date->lt($today))
                    || ($vehicle->insurance_expiry_date && $vehicle->insurance_expiry_date->lt($today));
            });
            $transport->setAttribute('has_expired_vehicle', $expiredVehicles->isNotEmpty());
            $transport->setAttribute(
                'expired_vehicle_license_numbers',
                $expiredVehicles->pluck('license_number')->filter()->values(),
            );
        });
    }
}
