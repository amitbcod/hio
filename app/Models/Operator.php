<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

class Operator extends Model implements AuthenticatableContract
{
    use Authenticatable;
    protected $table = 'operators';
    protected $guarded = [];
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Business relationship: an operator belongs to a business.
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
}
