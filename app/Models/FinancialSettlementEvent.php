<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialSettlementEvent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function settlement()
    {
        return $this->belongsTo(FinancialSettlement::class, 'financial_settlement_id');
    }
}
