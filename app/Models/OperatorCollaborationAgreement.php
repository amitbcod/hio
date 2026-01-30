<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorCollaborationAgreement extends Model
{
    protected $table = 'operator_collaboration_agreements';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}
