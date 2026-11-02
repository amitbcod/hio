<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'admin_users';

    protected $guarded = [];

    protected $casts = [
        'package_policy' => 'array',
        'group_policy' => 'array',
        'supported_languages' => 'array',
        'covered_region_ids' => 'array',
        'social_links' => 'array',
        'mpo_password_setup_expires_at' => 'datetime',
        'mpo_onboarded_at' => 'datetime',
    ];

    public $timestamps = true;

    public static function defaultPackagePolicy(): array
    {
        $admin = self::query()->first();
        $policy = $admin?->package_policy ?? [];

        return is_array($policy) ? $policy : [];
    }

    public static function defaultGroupPolicy(): array
    {
        $admin = self::query()->first();
        $policy = $admin?->group_policy ?? [];

        return is_array($policy) ? $policy : [];
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function compliance()
    {
        return $this->hasOne(MpoCompliance::class, 'admin_user_id');
    }

    public function staffUsers()
    {
        return $this->hasMany(MpoStaffUser::class, 'admin_user_id');
    }

    public function collaborationAgreements()
    {
        return $this->hasMany(MpoCollaborationAgreement::class, 'admin_user_id');
    }

    public function accountingConfiguration()
    {
        return $this->hasOne(MpoAccountingConfiguration::class, 'admin_user_id');
    }

    public function payoutConfiguration()
    {
        return $this->hasOne(MpoPayoutConfiguration::class, 'admin_user_id');
    }

    public function getAuthIdentifier()
    {
        return 'admin:'.$this->getKey();
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function hasCompleteMpoProfile(): bool
    {
        if ($this->admin_type !== 'mpo' || ! $this->country_id) {
            return false;
        }

        foreach ([
            $this->registered_business_name,
            $this->brn_number,
            $this->business_address,
            $this->official_business_email,
            $this->phone_number,
            $this->emergency_mobile,
            $this->operating_timezone,
            $this->brand_name,
        ] as $value) {
            if (! is_string($value) || trim($value) === '') {
                return false;
            }
        }

        if (! filter_var($this->official_business_email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $phonePattern = '/^\+[1-9]\d{7,14}$/';
        if (! preg_match($phonePattern, $this->phone_number)
            || ! preg_match($phonePattern, $this->emergency_mobile)) {
            return false;
        }

        if (! in_array($this->operating_timezone, \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC), true)) {
            return false;
        }

        $languages = $this->supported_languages;
        if (! is_array($languages) || $languages === []) {
            return false;
        }

        return count(array_diff($languages, array_keys(config('mpo.languages', [])))) === 0;
    }
}
