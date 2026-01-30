<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorStatusReview extends Model
{
    protected $table = 'operator_status_review';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}
