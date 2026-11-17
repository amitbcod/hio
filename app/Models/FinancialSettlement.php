<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\FinancialAmount;

class FinancialSettlement extends Model
{
    protected $guarded = [];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'invoice_issued_on' => 'date',
        'invoice_due_on' => 'date',
        'configuration_snapshot' => 'array',
        'gross_amount' => 'decimal:2',
        'operator_commission_amount' => 'decimal:2',
        'hio_sla_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function mpo()
    {
        return $this->belongsTo(AdminUser::class, 'mpo_admin_user_id');
    }

    public function operator()
    {
        return $this->belongsTo(Operator::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function items()
    {
        return $this->hasMany(FinancialSettlementItem::class);
    }

    public function transfers()
    {
        return $this->hasMany(FinancialSettlementTransfer::class);
    }

    public function events()
    {
        return $this->hasMany(FinancialSettlementEvent::class);
    }

    public function getVerifiedAmountAttribute(): string
    {
        return FinancialAmount::sum($this->transfers()->where('status', 'Verified')->pluck('amount'));
    }

    public function getOutstandingAmountAttribute(): string
    {
        return FinancialAmount::decimal(
            FinancialAmount::cents($this->net_amount)
            - FinancialAmount::cents(FinancialAmount::sum($this->transfers()->pluck('amount'))),
        );
    }
}
