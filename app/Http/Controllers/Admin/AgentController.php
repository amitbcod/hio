<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentAccount;
use App\Models\AgentComplianceDocument;
use App\Models\AgentStaffUser;
use App\Services\AgentPermissionService;
use App\Support\CountryAccess;
use Illuminate\Support\Facades\Storage;

class AgentController extends Controller
{
    public function index()
    {
        $agents = $this->scopedAgents()
            ->with('country')
            ->orderBy('agency_name')
            ->paginate(25);

        return view('admin.agents.index', compact('agents'));
    }

    public function show(AgentAccount $agent)
    {
        $this->assertCountryAccess($agent);
        $agent->load([
            'country',
            'profile',
            'complianceDocuments',
            'setupProgress',
            'staffUsers.permissions.module',
            'operations',
        ]);

        return view('admin.agents.show', compact('agent'));
    }

    public function approve(AgentAccount $agent)
    {
        $this->assertCountryAccess($agent);
        $progress = $agent->setupProgress()->first();
        abort_unless(
            $progress
                && $progress->profile_complete
                && $progress->compliance_complete
                && $progress->users_complete
                && $progress->operations_complete,
            422,
            'Complete all Agent setup steps before approving this account.'
        );
        $agent->update([
            'account_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => session('admin_id'),
        ]);

        return redirect()->route('admin.agents.show', $agent)->with('success', 'Agent approved.');
    }

    public function suspend(AgentAccount $agent)
    {
        $this->assertCountryAccess($agent);
        $agent->update(['account_status' => 'suspended']);

        return redirect()->route('admin.agents.show', $agent)->with('success', 'Agent suspended.');
    }

    public function updateStaff(Request $request, AgentAccount $agent, AgentStaffUser $user, AgentPermissionService $permissions)
    {
        $this->assertCountryAccess($agent);
        abort_unless((int) $user->agent_account_id === (int) $agent->id, 404);
        $data = $request->validate([
            'role' => ['required', \Illuminate\Validation\Rule::in(['Manager', 'Consultant', 'Accountant', 'Support'])],
            'status' => ['required', \Illuminate\Validation\Rule::in(['Active', 'Disabled'])],
        ]);

        $roleChanged = $user->role !== $data['role'];
        $user->update($data);
        if ($roleChanged) {
            $permissions->applyRoleDefaults($user);
        }

        return redirect()->route('admin.agents.show', $agent)->with('success', 'Agent staff access updated.');
    }

    public function download(AgentComplianceDocument $document)
    {
        $agent = AgentAccount::query()->findOrFail($document->agent_account_id);
        $this->assertCountryAccess($agent);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path);
    }

    private function scopedAgents()
    {
        $query = AgentAccount::query();
        if (CountryAccess::isMpoAdmin()) {
            $countryId = CountryAccess::countryId();
            abort_if($countryId === null, 403);
            $query->where('country_id', $countryId);
        }

        return $query;
    }

    private function assertCountryAccess(AgentAccount $agent): void
    {
        if (CountryAccess::isMpoAdmin()) {
            abort_unless((int) $agent->country_id === CountryAccess::countryId(), 404);
        }
    }
}
