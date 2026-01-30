<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorPayoutHistory extends Model
{
    protected $table = 'operator_payout_history';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}
