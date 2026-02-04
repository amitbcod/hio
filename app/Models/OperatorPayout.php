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

    // Relations
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    protected $casts = [
        'total_commission' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'payout_amount' => 'decimal:2',
    ];
}