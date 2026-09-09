<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

class Operator extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'operators';
    protected $guarded = [];
    protected $casts = [
        'package_policy' => 'array',
        'group_policy' => 'array',
    ];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected static function booted(): void
    {
        static::creating(function (self $operator) {
            if (empty($operator->package_policy)) {
                $operator->package_policy = AdminUser::defaultPackagePolicy();
            }

            if (empty($operator->group_policy)) {
                $operator->group_policy = AdminUser::defaultGroupPolicy();
            }
        });
    }

    public function effectivePackagePolicy(): array
    {
        $value = $this->package_policy;
        if (is_array($value) && !empty($value)) {
            return $value;
        }

        return AdminUser::defaultPackagePolicy();
    }

    public function effectiveGroupPolicy(): array
    {
        $value = $this->group_policy;
        if (is_array($value) && !empty($value)) {
            return $value;
        }

        return AdminUser::defaultGroupPolicy();
    }

    /**
     * Business relationship: an operator belongs to a business.
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    /**
     * Accounting relationship: step 7 accounting/payout data
     */
    public function accounting()
    {
        return $this->hasOne(OperatorAccountingPayout::class, 'business_id', 'business_id');
    }

    /**
     * Transport services added by this operator.
     */
    public function transports()
    {
        return $this->hasMany(Transport::class, 'operator_id');
    }
}
