<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use DateTimeZone;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class MpoCompliance extends Model
{
    protected $fillable = [
        'business_license_number',
        'license_type',
        'other_license_type_description',
        'license_expiry_date',
        'proof_of_license_path',
        'proof_of_license_filename',
        'insurance_certificate_path',
        'insurance_certificate_filename',
        'verification_status',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
        'reviewed_by_admin_id',
    ];

    protected $casts = [
        'license_expiry_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function mpo()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(AdminUser::class, 'reviewed_by_admin_id');
    }

    public function expiryStatus(?string $timezone = null): string
    {
        if (! $this->license_expiry_date) {
            return 'unknown';
        }

        $timezone ??= config('app.timezone', 'UTC');
        if (! in_array($timezone, DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC), true)) {
            $timezone = config('app.timezone', 'UTC');
        }

        $today = CarbonImmutable::today($timezone);
        $expiryDate = CarbonImmutable::parse($this->license_expiry_date->toDateString(), $timezone);

        if ($expiryDate->lt($today)) {
            return 'expired';
        }

        if ($expiryDate->lte($today->addDays(30))) {
            return 'expiring_soon';
        }

        return 'valid';
    }

    public function verificationStatusLabel(): string
    {
        return match ($this->verification_status) {
            'verified' => 'Verified',
            'rejected' => 'Rejected',
            default => 'Pending Verification',
        };
    }

    public function requireReverification(): void
    {
        $this->verification_status = 'pending_verification';
        $this->rejection_reason = null;
        $this->reviewed_at = null;
        $this->reviewed_by_admin_id = null;
        if (! $this->save()) {
            throw new RuntimeException('MPO compliance could not be reset for re-verification.');
        }
    }
}
