<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class OperatorUser extends Authenticatable
{
    protected $table = 'operator_users';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Use password_hash column for authentication
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
