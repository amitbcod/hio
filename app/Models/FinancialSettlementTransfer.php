<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialSettlementTransfer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'transferred_on' => 'date',
        'verified_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function settlement()
    {
        return $this->belongsTo(FinancialSettlement::class, 'financial_settlement_id');
    }
}
