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
        if (!empty($this->requester_agent_staff_user_id)) {
            return AgentStaffUser::find($this->requester_agent_staff_user_id);
        }

        if (!empty($this->requester_agent_account_id)) {
            return AgentAccount::find($this->requester_agent_account_id);
        }

        if (empty($this->requester_operator_id)) {
            return null;
        }

        $operator = Operator::where('operator_id', $this->requester_operator_id)->first();
        if ($operator) {
            return $operator;
        }

        return Mpo::where('mpo_id', $this->requester_operator_id)->first();
    }

    public function releaseRequesterAfterApproval(): void
    {
        $agent = $this->requesterAgentAccount();
        if ($agent) {
            $agent->account_status = 'pending';
            $agent->save();

            return;
        }

        $requester = $this->requester;
        if ($requester) {
            $requester->account_status = 'active';
            $requester->save();
        }
    }

    public function blockRequesterAfterRejection(): void
    {
        $agent = $this->requesterAgentAccount();
        if ($agent) {
            $agent->account_status = 'owner_rejected';
            $agent->save();
        }
    }

    public function requesterAgentAccount(): ?AgentAccount
    {
        if (!empty($this->requester_agent_account_id)) {
            return AgentAccount::find($this->requester_agent_account_id);
        }

        $requester = $this->requester;

        return $requester instanceof AgentStaffUser ? $requester->agent : null;
    }

    public function acceptBy($actor)
    {
        $actorId = null;
        try {
            $actorId = $actor->id ?? null;
        } catch (\Exception $e) {
            $actorId = null;
        }
        \Log::info('ControllerVerification::acceptBy - start', ['cv_id' => $this->id, 'actor_id' => $actorId]);
        $this->status = 'accepted';
        $this->accepted_by = $actorId;
        $this->accepted_at = now();

        // first try Eloquent save
        $saved = $this->save();
        \Log::info('ControllerVerification::acceptBy - saved', ['saved' => $saved, 'cv_id' => $this->id, 'status' => $this->status, 'accepted_by' => $this->accepted_by]);

        // ensure persistence using raw query as fallback
        try {
            $updated = \DB::table($this->getTable())->where('id', $this->id)->update([
                'status' => 'accepted',
                'accepted_by' => $actorId,
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