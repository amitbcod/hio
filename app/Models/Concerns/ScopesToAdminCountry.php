<?php

namespace App\Models\Concerns;

use App\Support\CountryAccess;
use App\Support\CountryOwnership;
use Illuminate\Database\Eloquent\Builder;

trait ScopesToAdminCountry
{
    protected static function bootScopesToAdminCountry(): void
    {
        static::addGlobalScope('admin_country', function (Builder $query): void {
            if (! CountryAccess::isMpoAdmin()) {
                return;
            }

            $countryId = CountryAccess::countryId();
            if ($countryId === null) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->where($query->getModel()->qualifyColumn('country_id'), $countryId);
        });

        static::creating(function ($model): void {
            if (CountryAccess::isMpoAdmin()) {
                $countryId = CountryAccess::countryId();
                self::assertOwnership($model, $countryId);
            } else {
                $countryId = CountryOwnership::resolve($model);
            }
            if ($countryId !== null) {
                $model->country_id = $countryId;
            }
        });

        static::updating(function ($model): void {
            if (CountryAccess::isMpoAdmin()) {
                $countryId = CountryAccess::countryId();
                self::assertOwnership($model, $countryId);
                $model->country_id = $countryId;
            } else {
                $countryId = CountryOwnership::resolve($model);
                if ($countryId !== null) {
                    $model->country_id = $countryId;
                }
            }
        });
    }

    private static function assertOwnership($model, ?int $countryId): void
    {
        abort_if($countryId === null, 403);

        $ownerCountryId = CountryOwnership::resolve($model);
        abort_if($ownerCountryId !== null && $ownerCountryId !== $countryId, 403);
    }

    public function country()
    {
        return $this->belongsTo(\App\Models\Country::class);
    }
}
