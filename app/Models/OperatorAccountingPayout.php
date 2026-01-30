<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorAccountingPayout extends Model
{
    protected $table = 'operator_accounting_payouts';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}
