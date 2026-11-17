<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialBookingReview extends Model
{
    protected $guarded = [];

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
}
