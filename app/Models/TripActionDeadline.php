<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class TripActionDeadline extends Model
{
    protected $guarded = [];

    public function getTargetAtAttribute($value): ?CarbonImmutable
    {
        return $value ? CarbonImmutable::parse($value, 'UTC') : null;
    }

    public function setTargetAtAttribute($value): void
    {
        $this->attributes['target_at'] = $this->asUtcString($value);
    }

    public function getDeadlineAtAttribute($value): ?CarbonImmutable
    {
        return $value ? CarbonImmutable::parse($value, 'UTC') : null;
    }

    public function setDeadlineAtAttribute($value): void
    {
        $this->attributes['deadline_at'] = $this->asUtcString($value);
    }

    public function getCompletedAtAttribute($value): ?CarbonImmutable
    {
        return $value ? CarbonImmutable::parse($value, 'UTC') : null;
    }

    public function setCompletedAtAttribute($value): void
    {
        $this->attributes['completed_at'] = $this->asUtcString($value);
    }

    public function getChangedAtAttribute($value): ?CarbonImmutable
    {
        return $value ? CarbonImmutable::parse($value, 'UTC') : null;
    }

    public function setChangedAtAttribute($value): void
    {
        $this->attributes['changed_at'] = $this->asUtcString($value);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    private function asUtcString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $dateTime = $value instanceof \DateTimeInterface
            ? CarbonImmutable::instance($value)
            : CarbonImmutable::parse((string) $value, 'UTC');

        return $dateTime->utc()->format('Y-m-d H:i:s');
    }
}
