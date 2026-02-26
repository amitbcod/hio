<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityAccounting extends Model
{
    protected $table = 'activity_accounting';
    
    protected $fillable = [
        'activity_id',
        'bank_account_holder_name',
        'bank_name',
        'account_number',
        'iban',
        'swift_code',
        'vat_number',
        'vat_exempted',
        'agreement_name',
        'commission_type',
        'commission_value',
        'currency_net',
        'tax_type',
        'tax_charges_basis',
        'tax_charges_type',
        'tax_charges_value',
        'tax_payment_collection',
    ];

    protected $casts = [
        'vat_exempted' => 'boolean',
        'commission_value' => 'decimal:2',
        'tax_charges_value' => 'decimal:2',
    ];

    /**
     * Relationship: Belongs to Activity
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
