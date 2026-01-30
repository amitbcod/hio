<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $table = 'businesses';
    protected $guarded = [];

    public $timestamps = true;

    // simple helper to generate a business code
    public static function generateBusinessId(string $prefix = 'BUS') : string
    {
        return $prefix . strtolower(substr(sha1(uniqid((string) mt_rand(), true)), 0, 10));
    }

    public function operators()
    {
        return $this->hasMany(Operator::class, 'business_id');
    }
}
