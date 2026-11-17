<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialSettlementItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'service_date' => 'date',
        'eligible_date' => 'date',
        'calculation_snapshot' => 'array',
        'gross_amount' => 'decimal:2',
        'commission_value' => 'decimal:4',
        'commission_base_amount' => 'decimal:2',
        'operator_commission_amount' => 'decimal:2',
        'hio_sla_rate' => 'decimal:4',
        'hio_sla_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function settlement()
    {
        return $this->belongsTo(FinancialSettlement::class, 'financial_settlement_id');
    }

    public function operator()
    {
        return $this->belongsTo(Operator::class);
    }
}
