<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorLegalCompliance extends Model
{
    protected $table = 'operator_legal_compliance';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}
