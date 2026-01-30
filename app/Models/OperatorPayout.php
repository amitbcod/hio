<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorPayout extends Model
{
    protected $table = 'operator_payouts';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Convenience: generate payout id
    public static function generatePayoutId()
    {
        return 'PAYOUT-' . strtoupper(uniqid());
    }
}