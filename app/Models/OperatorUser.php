<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Concerns\ScopesToAdminCountry;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperatorUser extends Authenticatable
{
    use ScopesToAdminCountry;
    protected $table = 'operator_users';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Use password_hash column for authentication
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Business relationship: user may be scoped to a business
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    // Accounting relationship: step 7 accounting/payout data
    public function accounting()
    {
        return $this->hasOne(OperatorAccountingPayout::class, 'business_id', 'business_id');
    }

    public function createdByStaffUser(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by_operator_user_id');
    }

    public function createdStaffUsers(): HasMany
    {
        return $this->hasMany(self::class, 'created_by_operator_user_id');
    }

    public function permissionOverrides(): HasMany
    {
        return $this->hasMany(OperatorStaffPermissionOverride::class, 'operator_user_id');
    }
}
