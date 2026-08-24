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
    ];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

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
