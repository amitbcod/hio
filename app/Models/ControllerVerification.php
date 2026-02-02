<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ControllerVerification extends Model
{
    protected $table = 'controller_verifications';
    protected $guarded = [];

    public $timestamps = true;

    public static function generateToken()
    {
        return Str::random(40);
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function requester()
    {
        return $this->hasOne(Operator::class, 'operator_id', 'requester_operator_id');
    }

    public function acceptBy(Operator $operator)
    {
        \Log::info('ControllerVerification::acceptBy - start', ['cv_id' => $this->id, 'operator_id' => $operator->id]);
        $this->status = 'approved';
        $this->accepted_by = $operator->id;
        $this->accepted_at = now();

        // first try Eloquent save
        $saved = $this->save();
        \Log::info('ControllerVerification::acceptBy - saved', ['saved' => $saved, 'cv_id' => $this->id, 'status' => $this->status, 'accepted_by' => $this->accepted_by]);

        // ensure persistence using raw query as fallback
        try {
            $updated = \DB::table($this->getTable())->where('id', $this->id)->update([
                'status' => 'approved',
                'accepted_by' => $operator->id,
                'accepted_at' => now(),
                'updated_at' => now(),
            ]);
            \Log::info('ControllerVerification::acceptBy - raw update', ['updated' => $updated]);
        } catch (\Exception $e) {
            \Log::error('ControllerVerification::acceptBy - raw update failed', ['err' => $e->getMessage()]);
        }

        $this->refresh();
    }
}