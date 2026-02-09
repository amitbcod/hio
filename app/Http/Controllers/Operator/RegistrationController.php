<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Operator;
use App\Models\OperatorProfile;
use App\Models\OperatorRegistrationProgress;
use App\Models\OperatorLegalCompliance;
use App\Models\OperatorCollaborationAgreement;
use App\Models\OperatorUser;
use App\Models\OperatorAccountingPayout;
use App\Models\OperatorServiceOperation;
use App\Models\OperatorRoleAccessMapping;
use App\Models\Business; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\AgreementConfirmed;

class RegistrationController extends Controller
{
   /* public function editStep6User($userId) {
        $operator = auth()->user();
        $user = \App\Models\OperatorUser::where('operator_id', $operator->operator_id)->where('id', $userId)->firstOrFail();
        $users = \App\Models\OperatorUser::where('operator_id', $operator->operator_id)->get();
        $progress = \App\Models\OperatorRegistrationProgress::where('operator_id', $operator->operator_id)->first();
        return view('operator.registration.step6_users', compact('users', 'progress', 'user'));
    }*/

    public function editStep6User($userId)
{
    $operator = auth()->user();

    // Prefer business-scoped users when operator is linked to a business
    if (!empty($operator->business_id)) {
        $user = \App\Models\OperatorUser::where('id', $userId)->where('business_id', $operator->business_id)->firstOrFail();
        $users = \App\Models\OperatorUser::where('business_id', $operator->business_id)->get();
    } else {
        $user = \App\Models\OperatorUser::where('operator_id', $operator->operator_id)->where('id', $userId)->firstOrFail();
        $users = \App\Models\OperatorUser::where('operator_id', $operator->operator_id)->get();
    }

    // ✅ use imported model
    // Prefer business-scoped mappings when linked
    $query = OperatorRoleAccessMapping::whereIn('user_id', $users->pluck('id'));
    if (!empty($operator->business_id)) {
        $query->where('business_id', $operator->business_id);
    }
    $roleAccessMappings = $query->get();

    // ✅ group by user_id for JS modal prefill
    $roleAccessMappingsByUser = $roleAccessMappings->groupBy('user_id');

    $progress = \App\Models\OperatorRegistrationProgress::where(
        'operator_id',
        $operator->operator_id
    )->first();

    return view(
        'operator.registration.step6_users',
        compact(
            'users',
            'progress',
            'user',
            'roleAccessMappingsByUser'
        )
    );
}

    public function updateStep6User(Request $request, $userId) {
        $operator = auth()->user();
        // Ensure user is in the same business when linked, otherwise restrict by operator
        if (!empty($operator->business_id)) {
            $user = \App\Models\OperatorUser::where('id', $userId)->where('business_id', $operator->business_id)->firstOrFail();
        } else {
            $user = \App\Models\OperatorUser::where('operator_id', $operator->operator_id)->where('id', $userId)->firstOrFail();
        }
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email|unique:operator_users,email,' . $user->id,
            'mobile' => 'required',
            'role' => 'required',
            'access_rights' => 'nullable|array',
        ]);
        $user->full_name = $request->full_name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        if ($request->filled('password')) {
            $user->password_hash = bcrypt($request->password);
            $user->account_reset_required = 1;
        }
        $user->role = $request->role;
        $user->access_rights = $request->access_rights ? json_encode($request->access_rights) : null;
        $user->save();
        return redirect()->route('operator.register.step6')->with('success', 'User updated successfully!');
    }

    public function deleteStep6User($userId) {
        $operator = auth()->user();
        // Ensure user is in the same business when linked, otherwise restrict by operator
        if (!empty($operator->business_id)) {
            $user = \App\Models\OperatorUser::where('id', $userId)->where('business_id', $operator->business_id)->firstOrFail();
        } else {
            $user = \App\Models\OperatorUser::where('operator_id', $operator->operator_id)->where('id', $userId)->firstOrFail();
        }
        $user->delete();
        return redirect()->route('operator.register.step6')->with('success', 'User deleted successfully!');
    }
    /**
     * Handle unauthenticated users for registration steps.
     */
    protected function ensureAuthenticated()
    {
        if (!auth()->check()) {
            return redirect()->route('operator.login');
        }
        return null;
    }

    /**
     * Check if a step is accessible based on previous steps being completed
     * New display order: Profile, Collaboration, Users, System Processes, Accounting, Operations, Review
     */
    protected function checkStepAccess($step)
    {
        if ($step <= 2) {
            return true; // Steps 1 and 2 are always accessible
        }

        $operator = auth()->user();
        $progress = $this->getProgressForOperator($operator);

        if (!$progress) {
            return false; // No progress found
        }

        // Define which progress field corresponds to each step
        // Updated for new display order
        $progressFields = [
            2 => null,                 // Step 2 always accessible
            5 => 'step2_profile',      // Step 5 (Collaboration) requires step 2 (Profile)
            6 => 'step5_collaboration', // Step 6 (Users) requires step 5 (Collaboration)
            4 => 'step6_users',        // Step 4 (System Processes) requires step 6 (Users)
            7 => 'step4_system_process', // Step 7 (Accounting) requires step 4 (System Processes)
            8 => 'step7_accounting',   // Step 8 (Operations) requires step 7 (Accounting)
            9 => 'step8_operations',   // Step 9 (Review) requires step 8 (Operations)
        ];

        $requiredField = $progressFields[$step] ?? null;
        if (!$requiredField) {
            return false;
        }

        return $progress->{$requiredField} ? true : false;
    }

    // Helper to return OperatorRegistrationProgress scoped to business when available
    protected function getProgressForOperator($operator)
    {
        if (!empty($operator->business_id)) {
            return OperatorRegistrationProgress::where('business_id', $operator->business_id)->first();
        }
        return OperatorRegistrationProgress::where('operator_id', $operator->operator_id)->first();
    }
    public function step2Profile(Request $request) {
        if ($redirect = $this->ensureAuthenticated()) return $redirect;
        $operator = auth()->user();
        // Prefer business-scoped profile if operator is linked to a business, otherwise fall back to existing operator_id records
        $profile = null;
        if (!empty($operator->business_id)) {
            $profile = \App\Models\OperatorProfile::where('business_id', $operator->business_id)->first();
        }
        if (!$profile) {
            $profile = \App\Models\OperatorProfile::where('operator_id', $operator->operator_id)->first();
        }

        // Other records remain operator-scoped for now (will be migrated in subsequent steps)
        // Prefer business-scoped legal compliance record when available
        if (!empty($operator->business_id)) {
            $legal = \App\Models\OperatorLegalCompliance::where('business_id', $operator->business_id)->first();
        } else {
            $legal = \App\Models\OperatorLegalCompliance::where('operator_id', $operator->operator_id)->first();
        }
        $progress = $this->getProgressForOperator($operator);
        // Autofill business legal name from operators table if profile is empty
        if (!$profile || empty($profile->business_legal_name)) {
            $businessLegalName = $operator->business_legal_name ?? '';
        } else {
            $businessLegalName = $profile->business_legal_name;
        }

        // Load linked business if present
        $business = null;
        if (!empty($operator->business_id)) {
            $business = Business::find($operator->business_id);
        }

        return view('operator.registration.step2_profile', compact('operator', 'profile', 'legal', 'progress', 'businessLegalName', 'business')); 
    }

    public function saveStep2Profile(Request $request) {
        $request->validate([
            'business_legal_name' => 'required',
            'business_registration_number' => 'nullable',
            'registered_address' => 'nullable',
            'operational_address' => 'nullable',
            'service_types' => 'nullable|array',
            'years_in_operation' => 'nullable|integer',
            'trading_name' => 'nullable',
            'company_logo' => 'nullable|file|image|max:2048',
            'company_description' => 'nullable',
            'contact_name' => 'nullable',
            'contact_phone' => 'nullable',
            'contact_email' => 'nullable|email',
            'facebook_link' => 'nullable',
            'instagram_link' => 'nullable',
            'linkedin_link' => 'nullable',
        ]);
        $authUser = auth()->user();
        $data = [
            'business_legal_name' => $request->business_legal_name,
            'business_registration_number' => $request->business_registration_number,
            'registered_address' => $request->registered_address,
            'operational_address' => $request->operational_address,
            'service_types' => $request->service_types ? json_encode($request->service_types) : null,
            'years_in_operation' => $request->years_in_operation,
            'trading_name' => $request->trading_name,
            'company_description' => $request->company_description,
            'contact_name' => $request->contact_name,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'facebook_link' => $request->facebook_link,
            'instagram_link' => $request->instagram_link,
            'linkedin_link' => $request->linkedin_link,
        ];
        // Handle logo upload
        if ($request->hasFile('company_logo')) {
            $logoPath = $request->file('company_logo')->store('logos', 'public');
            $data['company_logo'] = $logoPath;
        }
        // Save profile keyed by business when possible. Keep operator_id as fallback for backwards compatibility.
        if (!empty($authUser->business_id)) {
            OperatorProfile::updateOrCreate(
                ['business_id' => $authUser->business_id],
                array_merge($data, ['operator_id' => $authUser->operator_id, 'business_legal_name' => $request->business_legal_name])
            );
        } else {
            OperatorProfile::updateOrCreate(
                ['operator_id' => $authUser->operator_id],
                $data
            );
        }

        // Create or update linked Business record
        if (!empty($authUser->business_id)) {
            $business = Business::find($authUser->business_id);
            if ($business) {
                $business->legal_name = $request->business_legal_name;
                $business->registration_number = $request->business_registration_number ?? $business->registration_number;
                $business->primary_contact_email = $request->contact_email ?? $business->primary_contact_email;
                $business->save();
            }
        } else {
            // If operator has no business link yet, create one and link
            $business = Business::create([
                'business_id' => Business::generateBusinessId(),
                'legal_name' => $request->business_legal_name,
                'country' => null,
                'registration_number' => $request->business_registration_number ?? null,
                'primary_contact_email' => $request->contact_email ?? $authUser->email,
                'status' => 'pending',
            ]);
            // link
            $operatorModel = \App\Models\Operator::where('operator_id', $authUser->operator_id)->first();
            if ($operatorModel) {
                $operatorModel->business_id = $business->id;
                $operatorModel->save();
                // If a profile exists keyed by operator_id, promote it to the business row
                \App\Models\OperatorProfile::where('operator_id', $authUser->operator_id)->update(['business_id' => $business->id]);
            }
        }

        // Mark step as complete
        // Record progress keyed by business where possible, fallback to operator_id
        if (!empty($authUser->business_id)) {
            OperatorRegistrationProgress::updateOrCreate(
                ['business_id' => $authUser->business_id],
                ['step2_profile' => 1, 'current_step' => 3, 'operator_id' => $authUser->operator_id]
            );
        } else {
            OperatorRegistrationProgress::updateOrCreate(
                ['operator_id' => $authUser->operator_id],
                ['step2_profile' => 1, 'current_step' => 3]
            );
        }

        // Redirect to Collaboration Agreement (step5) - which displays as step 3
        return redirect()->route('operator.register.step5')->with('success', 'Profile information saved. Proceeding to Collaboration Agreement.');
    }
    public function step3Legal(Request $request) {
        // This step is now handled via modal in profile step. Optionally, you can remove this method.
        abort(404);
    }

    public function saveStep3Legal(Request $request) {
        $request->validate([
            'business_license_number' => 'required',
            'license_type' => 'required',
            // Add other required fields as needed
        ]);
        $operator = auth()->user();
        $data = [
            'business_license_number' => $request->business_license_number,
            'license_type' => $request->license_type,
            'license_expiry_date' => $request->license_expiry_date,
            'service_package' => $request->service_package,
        ];
        // Handle file uploads
        if ($request->hasFile('proof_of_license')) {
            $data['proof_of_license'] = $request->file('proof_of_license')->store('compliance', 'public');
        }
        if ($request->hasFile('insurance_certificate')) {
            $data['insurance_certificate'] = $request->file('insurance_certificate')->store('compliance', 'public');
        }
        if ($request->hasFile('signed_agreement')) {
            $data['signed_agreement'] = $request->file('signed_agreement')->store('compliance', 'public');
        }
        // Save legal compliance keyed to business when available; otherwise keep operator-scoped record
        if (!empty($operator->business_id)) {
            $legal = OperatorLegalCompliance::updateOrCreate(
                ['business_id' => $operator->business_id],
                array_merge($data, ['operator_id' => $operator->operator_id])
            );
            \App\Models\OperatorRegistrationProgress::updateOrCreate(
                ['business_id' => $operator->business_id],
                ['step3_legal' => 1, 'current_step' => 4, 'operator_id' => $operator->operator_id]
            );
        } else {
            $legal = OperatorLegalCompliance::updateOrCreate(
                ['operator_id' => $operator->operator_id],
                $data
            );
            \App\Models\OperatorRegistrationProgress::updateOrCreate(
                ['operator_id' => $operator->operator_id],
                ['step3_legal' => 1, 'current_step' => 4]
            );
        }
        // Redirect to Collaboration Agreement (step5) - which displays as step 3
        return redirect()->route('operator.register.step5')->with('success', 'Legal Compliance information saved. Proceeding to next step.');
    }
    public function step4SystemProcess(Request $request) {
        if (!$this->checkStepAccess(4)) {
            return redirect()->route('operator.register.step2')->with('error', 'Please complete previous steps first.');
        }
        $operator = auth()->user();
        $system = null;
        // Prefer business-scoped system process when operator is linked to a business
        if (!empty($operator->business_id)) {
            $system = \App\Models\OperatorSystemProcess::where('business_id', $operator->business_id)->first();
        }
        // Fallback: try operator_id if no business-scoped record found
        if (!$system) {
            $system = \App\Models\OperatorSystemProcess::where('operator_id', $operator->operator_id)->first();
        }
        $progress = $this->getProgressForOperator($operator);
        return view('operator.registration.step4_system_process', compact('operator', 'system', 'progress'));
    }

    // public function saveStep4SystemProcess(Request $request) {
    //     // No validation for service_category needed, as it is not saved here
    //     $operator = auth()->user();
    //     // Save into business scope if operator is linked to a business
    //     if($request->service_category){
    //         $serviceCategory = implode(',', $request->service_category);
    //     }
       
    //     if (!empty($operator->business_id)) {
    //         $system = \App\Models\OperatorSystemProcess::updateOrCreate(
    //             ['business_id' => $operator->business_id],
    //             [
    //                 'business_id' => $operator->business_id,
    //                 'operator_id' => $operator->operator_id,
    //                 'service_category' => $serviceCategory ?? 'Accommodation',
    //                 'communication_preference' => $request->communication_preference ?? null,
    //                 'assigned_operator_name' => $request->assigned_operator_name ?? null,
    //                 'assigned_operator_role' => $request->assigned_operator_role ?? null,
    //                 'status' => $request->status ?? null,
    //             ]
    //         );
    //     } else {
    //         $system = \App\Models\OperatorSystemProcess::updateOrCreate(
    //             ['operator_id' => $operator->operator_id],
    //             [
    //                 'service_category' => $request->service_category ?? 'Accommodation',
    //                 'communication_preference' => $request->communication_preference ?? null,
    //                 'assigned_operator_name' => $request->assigned_operator_name ?? null,
    //                 'assigned_operator_role' => $request->assigned_operator_role ?? null,
    //                 'status' => $request->status ?? null,
    //             ]
    //         );
    //     }
    //     if (!empty($operator->business_id)) {
    //         OperatorRegistrationProgress::updateOrCreate(
    //             ['business_id' => $operator->business_id],
    //             ['step4_system_process' => 1, 'current_step' => 5, 'operator_id' => $operator->operator_id]
    //         );
    //     } else {
    //         OperatorRegistrationProgress::updateOrCreate(
    //             ['operator_id' => $operator->operator_id],
    //             ['step4_system_process' => 1, 'current_step' => 5]
    //         );
    //     }
    //     return redirect()->route('operator.register.step5')->with('success', 'System process info saved.');
    // }


    public function saveStep4SystemProcess(Request $request)
    {
        $operator = auth()->user();

        // Decide scope (business or operator)
        $match = !empty($operator->business_id)
            ? ['business_id' => $operator->business_id]
            : ['operator_id' => $operator->operator_id];

        // Save / update system process settings
        \App\Models\OperatorSystemProcess::updateOrCreate(
            $match,
            [
                'business_id' => $operator->business_id,
                'operator_id' => $operator->operator_id,
                'communication_preference' => $request->communication_preference ?? null,
                'assigned_operator_name' => $request->assigned_operator_name ?? null,
                'assigned_operator_role' => $request->assigned_operator_role ?? null,
                'status' => $request->status ?? null,
            ]
        );

        // Update registration progress
        OperatorRegistrationProgress::updateOrCreate(
            $match,
            [
                'step4_system_process' => 1,
                'current_step' => 5,
                'operator_id' => $operator->operator_id
            ]
        );

        // Redirect to Accounting & Payouts (step7) after System Processes
        return redirect()
            ->route('operator.register.step7')
            ->with('success', 'System process info saved.');
    }


    public function step5Collaboration(Request $request) {
        if (!$this->checkStepAccess(5)) {
            return redirect()->route('operator.register.step2')->with('error', 'Please complete previous steps first.');
        }
        $operator = auth()->user();
        $collab = null;
        // Prefer collaboration agreement by business when linked
        if (!empty($operator->business_id)) {
            $collab = OperatorCollaborationAgreement::where('business_id', $operator->business_id)->first();
        }
        // Fallback: try operator_id if no business-scoped record found
        if (!$collab) {
            $collab = OperatorCollaborationAgreement::where('operator_id', $operator->operator_id)->first();
        }
        $progress = $this->getProgressForOperator($operator);
        $business = !empty($operator->business_id) ? \App\Models\Business::find($operator->business_id) : null;
        return view('operator.registration.step5_collaboration', compact('operator', 'collab', 'progress', 'business'));
    }

    public function saveStep5Collaboration(Request $request) {
        $request->validate([
            'agreement_type' => 'required',
            'contact_management_name' => 'required',
            'contact_management_email' => 'nullable|email',
            'contact_management_phone' => 'nullable',
            'contact_management_mobile' => 'nullable',
            'contact_accounting_name' => 'nullable',
            'contact_accounting_email' => 'nullable|email',
            'contact_accounting_phone' => 'nullable',
            'contact_accounting_mobile' => 'nullable',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'renewal_date' => 'required|date',
            'commission_model' => 'required',
            'commission_value' => 'required|numeric',
            'marketing_contribution_percent' => 'required|numeric',
            'status' => 'required',
        ]);
        $operator = auth()->user();
        $data = [
            'agreement_type' => $request->agreement_type,
            'contact_management_name' => $request->contact_management_name,
            'contact_management_email' => $request->contact_management_email,
            'contact_management_phone' => $request->contact_management_phone,
            'contact_management_mobile' => $request->contact_management_mobile,
            'contact_accounting_name' => $request->contact_accounting_name,
            'contact_accounting_email' => $request->contact_accounting_email,
            'contact_accounting_phone' => $request->contact_accounting_phone,
            'contact_accounting_mobile' => $request->contact_accounting_mobile,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'renewal_date' => $request->renewal_date,
            'commission_model' => $request->commission_model,
            'commission_value' => $request->commission_value,
            'marketing_contribution_percent' => $request->marketing_contribution_percent,
            'status' => $request->status,
        ];
        // Handle agreement file upload if present
        if ($request->hasFile('agreement_file')) {
            $data['agreement_file'] = $request->file('agreement_file')->store('agreements', 'public');
        }
        // Handle responsibilities document if present
        if ($request->responsibilities_document) {
            $data['responsibilities_document'] = $request->responsibilities_document;
        }

        // Save collaboration agreement keyed by business when possible; keep operator_id as fallback
        if (!empty($operator->business_id)) {
            OperatorCollaborationAgreement::updateOrCreate(
                ['business_id' => $operator->business_id],
                array_merge($data, ['operator_id' => $operator->operator_id])
            );
        } else {
            OperatorCollaborationAgreement::updateOrCreate(
                ['operator_id' => $operator->operator_id],
                $data
            );
        }

        // Persist agreement type on the business record when linked
        if (!empty($operator->business_id)) {
            \App\Models\Business::where('id', $operator->business_id)->update(['agreement_type' => $request->agreement_type]);
        }

        if (!empty($operator->business_id)) {
            OperatorRegistrationProgress::updateOrCreate(
                ['business_id' => $operator->business_id],
                ['step5_collaboration' => 1, 'current_step' => 6, 'operator_id' => $operator->operator_id]
            );
        } else {
            OperatorRegistrationProgress::updateOrCreate(
                ['operator_id' => $operator->operator_id],
                ['step5_collaboration' => 1, 'current_step' => 6]
            );
        }
        return redirect()->route('operator.register.step6')->with('success', 'Collaboration info saved.');
    }


    /*public function step6Users(Request $request) {
        if (!$this->checkStepAccess(6)) {
            return redirect()->route('operator.register.step2')->with('error', 'Please complete previous steps first.');
        }
        $operator = auth()->user();
        $users = \App\Models\OperatorUser::where('operator_id', $operator->operator_id)->get();
        $roleAccessMappings = \App\Models\OperatorRoleAccessMapping::whereIn('user_id', $users->pluck('id'))->get() ?? collect();
        $progress = \App\Models\OperatorRegistrationProgress::where('operator_id', $operator->operator_id)->first();
        return view('operator.registration.step6_users', compact('users', 'roleAccessMappings', 'progress'));
    }*/

        public function step6Users(Request $request)
{
    if (!$this->checkStepAccess(6)) {
        return redirect()
            ->route('operator.register.step2')
            ->with('error', 'Please complete previous steps first.');
    }

    $operator = auth()->user();

    // Prefer business-scoped users when operator is linked to a business
    if (!empty($operator->business_id)) {
        $users = \App\Models\OperatorUser::where('business_id', $operator->business_id)->get();
    } else {
        $users = \App\Models\OperatorUser::where('operator_id', $operator->operator_id)->get();
    }

    $roleAccessMappings = \App\Models\OperatorRoleAccessMapping::whereIn(
        'user_id',
        $users->pluck('id')
    )->get();

    // ✅ GROUP BY USER ID (KEY FIX)
    $roleAccessMappingsByUser = $roleAccessMappings->groupBy('user_id');

    // Fetch roles available to this operator's business (or global roles)
    $roles = collect();
    if (class_exists(\Spatie\Permission\Models\Role::class) && \Illuminate\Support\Facades\Schema::hasTable('roles')) {
        if (\Illuminate\Support\Facades\Schema::hasColumn('roles', 'business_id')) {
            $roles = \Spatie\Permission\Models\Role::where(function($q) use ($operator) {
                $q->whereNull('business_id');
                if (!empty($operator->business_id)) {
                    $q->orWhere('business_id', $operator->business_id);
                }
            })->orderBy('name')->get();
        } else {
            $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
        }
    }

    // Load dynamic modules available for this business (or global modules)
    $modules = \App\Models\Module::orderBy('name')->get();

    // group by user id for JS modal prefill
    $roleAccessMappingsByUser = $roleAccessMappings->groupBy('user_id');

    $progress = $this->getProgressForOperator($operator);

    return view(
        'operator.registration.step6_users',
        compact(
            'users',
            'progress',
            'roleAccessMappingsByUser',
            'roles'
        )
    );
}


    /*public function saveStep6Users(Request $request) {
        $operator = auth()->user();
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email|unique:operator_users,email',
            'mobile' => 'required',
            'password' => 'required|min:8',
            'role' => 'required',
            'access_rights' => 'nullable|array',
        ]);
        $user = new \App\Models\OperatorUser();
        $user->user_id = uniqid('OPU');
        $user->operator_id = $operator->operator_id;
        $user->full_name = $request->full_name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->password_hash = bcrypt($request->password);
        $user->role = $request->role;
        $user->access_rights = $request->access_rights ? json_encode($request->access_rights) : null;
        $user->status = 'Active';
        $user->account_reset_required = 1;
        $user->created_by = $operator->id;
        $user->save();
        // Mark step as complete
        \App\Models\OperatorRegistrationProgress::updateOrCreate(
            ['operator_id' => $operator->operator_id],
            ['step6_users' => 1, 'current_step' => 7]
        );
        return redirect()->route('operator.register.step6')->with('success', 'New user added successfully!');
    }*/

        public function saveStep6Users(Request $request)
{
    $operator = auth()->user();

    $request->validate([
        'full_name' => 'required|string|max:255',
        'email' => 'required|email|unique:operator_users,email',
        'mobile' => 'required|string|max:20',
        'password' => 'required|string|min:8',
        'role' => 'required|string',
    ]);

    $user = new OperatorUser();
    $user->user_id = uniqid('OPU');
    $user->operator_id = $operator->operator_id;
    // Associate with business when operator belongs to one
    if (!empty($operator->business_id)) {
        $user->business_id = $operator->business_id;
    }
    $user->full_name = $request->full_name;
    $user->email = $request->email;
    $user->mobile = $request->mobile;
    $user->password_hash = bcrypt($request->password);
    // keep human-readable role for legacy display, but authoritative role will be assigned via Spatie
    $user->role = $request->role;
    $user->status = 'Active';
    $user->account_reset_required = 1;
    $user->created_by = $operator->id;

    $user->save();

    // Assign role via Spatie, ensuring role belongs to this business (if applicable)
    try {
        $roleName = $request->role;
        $roleModel = null;

        // If Spatie not available, skip role assignment but keep human-readable role value
        if (!class_exists(\Spatie\Permission\Models\Role::class) || !\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            \Log::warning('Spatie Role model not available - role not assigned programmatically');
        } else {
            if (!empty($operator->business_id)) {
                $roleModel = \Spatie\Permission\Models\Role::where('name', $roleName)
                    ->where('business_id', $operator->business_id)
                    ->first();
            }
            // fallback to global role (no business_id)
            if (!$roleModel) {
                $roleModel = \Spatie\Permission\Models\Role::where('name', $roleName)
                    ->whereNull('business_id')
                    ->first();
            }
            if ($roleModel) {
                $user->assignRole($roleModel->name);
            } else {
                // If the role wasn't found for this business, remove the created user and report error
                $user->delete();
                return redirect()->back()->withErrors(['role' => 'Selected role not found for this business.'])->withInput();
            }
        }
    } catch (\Exception $e) {
        // Log and continue; role assignment failed
        \Log::error('saveStep6Users - role assignment error', ['err' => $e->getMessage()]);
    }

    // mark step as complete
    OperatorRegistrationProgress::updateOrCreate(
        ['operator_id' => $operator->operator_id],
        ['step6_users' => 1, 'current_step' => 4]
    );

    // Redirect to System Processes (step4) which displays as step 5
    return redirect()->route('operator.register.step4')->with('success', 'New user added successfully!');
}
    public function step7Accounting(Request $request) {
        if (!$this->checkStepAccess(7)) {
            return redirect()->route('operator.register.step2')->with('error', 'Please complete previous steps first.');
        }
        $operator = auth()->user();
        // Prefer accounting payouts by business when operator is linked to a business
        if (!empty($operator->business_id)) {
            $accounting = \App\Models\OperatorAccountingPayout::where('business_id', $operator->business_id)->first();
            // Load existing payout records for this business
            $payouts = \App\Models\OperatorPayout::where('business_id', $operator->business_id)->orderBy('created_at', 'desc')->get();
        } else {
            $accounting = \App\Models\OperatorAccountingPayout::where('beneficiary_id', $operator->operator_id)->first();
            // Load existing payout records for this operator (for modal listing)
            $payouts = \App\Models\OperatorPayout::where('beneficiary_id', $operator->operator_id)->orderBy('created_at', 'desc')->get();
        }
        return view('operator.registration.step7_accounting', compact('accounting', 'payouts'));
    }

    public function saveStep7Accounting(Request $request) {
        $operator = auth()->user();
        $request->validate([
            'bank_account_holder_name' => 'required',
            'bank_name' => 'required',
            'account_number' => 'required',
            'iban' => 'nullable',
            'swift_code' => 'nullable',
            'currency_preference' => 'required',
            'vat_number' => 'nullable',
            'tax_id' => 'nullable|string|max:100',
            'commission_type' => 'required',
            // commission_value removed from form per requirements
            'payment_schedule' => 'required',
            'status' => 'required',
            'credit_limit_days' => 'nullable|integer',
            'credit_limit_amount' => 'nullable|numeric',
            'credit_value' => 'nullable|numeric',
        ]);

        // Prepare base data
        $data = [
            'beneficiary_id' => $operator->operator_id,
            'bank_account_holder_name' => $request->bank_account_holder_name,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'iban' => $request->iban,
            'swift_code' => $request->swift_code,
            'currency_preference' => $request->currency_preference,
            'vat_number' => $request->has('vat_exempted') ? null : $request->vat_number,
            'tax_id' => $request->tax_id ?? null,
            'vat_exempted' => $request->has('vat_exempted') ? 1 : 0,
            'commission_type' => $request->commission_type,
            // commission_value intentionally cleared (removed from form)
            'commission_value' => null,
            'credit_limit_days' => null,
            'credit_limit_amount' => null,
            'credit_value' => $request->credit_value ?? null,
            'payment_schedule' => $request->payment_schedule,
            'status' => $request->status,
        ];

        // Accept both credit limit days and amount; both optional
        $data['credit_limit_days'] = $request->filled('credit_limit_days') ? (int) $request->credit_limit_days : null;
        $data['credit_limit_amount'] = $request->filled('credit_limit_amount') ? $request->credit_limit_amount : null;

        // Prefer scoping accounting payouts to business when operator is linked
        if (!empty($operator->business_id)) {
            $data['business_id'] = $operator->business_id;
            \App\Models\OperatorAccountingPayout::updateOrCreate(
                ['business_id' => $operator->business_id],
                $data
            );
        } else {
            \App\Models\OperatorAccountingPayout::updateOrCreate(
                ['beneficiary_id' => $operator->operator_id],
                $data
            );
        }
        \App\Models\OperatorRegistrationProgress::updateOrCreate(
            ['operator_id' => $operator->operator_id],
            ['step7_accounting' => 1, 'current_step' => 8]
        );
        return redirect()->route('operator.register.step8')->with('success', 'Accounting details saved.');
    }

    // Save payout additional details from modal
    public function savePayoutDetails(Request $request)
    {
        $operator = auth()->user();

        $request->validate([
            'period_covered' => 'required|string|max:50',
            'total_commission' => 'nullable|numeric|min:0',
            'adjustments' => 'nullable|numeric',
            'processing_fee' => 'nullable|numeric|min:0',
            'payout_amount' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'payout_method' => 'required|in:Bank,Wallet',
            'transaction_ref' => 'nullable|string|max:255',
            'status' => 'required|in:Pending,Processing,Paid,Failed',
            'processed_by' => 'nullable|string|max:255',
        ]);

        // Protect against Operators creating/editing payout records
        if (!$this->ensureCanManagePayouts()) {
            return redirect()->route('operator.register.step7')->with('error', 'Unauthorized action.');
        }

        $payout = new \App\Models\OperatorPayout();
        $payout->payout_id = \App\Models\OperatorPayout::generatePayoutId();
        $payout->beneficiary_id = $operator->operator_id;
        $payout->beneficiary = $operator->business->legal_name ?? $operator->business_legal_name ?? $operator->full_name ?? '';
        // If operator belongs to a business, associate payout to business as well
        if (!empty($operator->business_id)) {
            $payout->business_id = $operator->business_id;
        }
        $payout->period_covered = $request->period_covered;
        $payout->total_commission = $request->total_commission ?? 0;
        $payout->adjustments = $request->adjustments ?? null;
        $payout->processing_fee = $request->processing_fee ?? 0;
        $payout->payout_amount = $request->payout_amount ?? 0;
        $payout->currency = $request->currency;
        $payout->payout_method = $request->payout_method;
        $payout->transaction_ref = $request->transaction_ref;
        $payout->status = $request->status;
        $payout->processed_by = $request->processed_by;
        $payout->save();

        return redirect()->route('operator.register.step7')->with('success', 'Payout record saved successfully.');
    }

    // Prevent operators (user_type = 'Operator') from saving payout records
    protected function ensureCanManagePayouts()
    {
        $user = auth()->user();
        if ($user && ($user->user_type ?? 'Operator') === 'Operator') {
            return false;
        }
        return true;
    }
    public function step8Operations(Request $request) {
        if (!$this->checkStepAccess(8)) {
            return redirect()->route('operator.register.step2')->with('error', 'Please complete previous steps first.');
        }
        $operator = auth()->user();
        $serviceOps = null;
        // Prefer service operations by business when linked
        if (!empty($operator->business_id)) {
            $serviceOps = \App\Models\OperatorServiceOperation::where('business_id', $operator->business_id)->first();
        }
        // Fallback: try operator_id if no business-scoped record found
        if (!$serviceOps) {
            $serviceOps = \App\Models\OperatorServiceOperation::where('operator_id', $operator->operator_id)->first();
        }
        return view('operator.registration.step8_operations', compact('serviceOps'));
    }

    public function saveStep8Operations(Request $request) {
        $operator = auth()->user();
        $request->validate([
            'service_location' => 'required',
            'emergency_contact_name' => 'required',
            'emergency_contact_phone' => 'required',
            'emergency_contact_email' => 'required|email',
        ]);
        $data = [
            'operator_id' => $operator->operator_id,
            'service_location' => $request->service_location,
            'gps_coordinates' => $request->gps_coordinates,
            'operating_areas' => $request->operating_areas ? json_encode($request->operating_areas) : null,
            'is_nationwide' => $request->has('is_nationwide') ? 1 : 0,
            'has_pickup_dropoff' => $request->has_pickup_dropoff ?? 0,
            'pickup_dropoff_details' => $request->pickup_dropoff_details,
            'pickup_dropoff_surcharge' => $request->pickup_dropoff_surcharge,
            'pickup_dropoff_free' => $request->has('pickup_dropoff_free') ? 1 : 0,
            'service_notes' => $request->service_notes,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'emergency_contact_email' => $request->emergency_contact_email,
            'status' => 'draft',
        ];
        // Save service operations keyed to business when available; otherwise fallback to operator_id
        if (!empty($operator->business_id)) {
            \App\Models\OperatorServiceOperation::updateOrCreate(
                ['business_id' => $operator->business_id],
                array_merge($data, ['operator_id' => $operator->operator_id])
            );
            \App\Models\OperatorRegistrationProgress::updateOrCreate(
                ['business_id' => $operator->business_id],
                ['step8_operations' => 1, 'current_step' => 9, 'operator_id' => $operator->operator_id]
            );
        } else {
            \App\Models\OperatorServiceOperation::updateOrCreate(
                ['operator_id' => $operator->operator_id],
                $data
            );
            \App\Models\OperatorRegistrationProgress::updateOrCreate(
                ['operator_id' => $operator->operator_id],
                ['step8_operations' => 1, 'current_step' => 9]
            );
        }
        return redirect()->route('operator.register.step9')->with('success', 'Service operations saved.');
    }

    // Role Access Mapping Methods
    /*public function saveRoleAccessMapping(Request $request) {
        $operator = auth()->user();
        $request->validate([
            'user_id' => 'required|exists:operator_users,id',
            'role' => 'required',
            'module' => 'required',
            'capacity_level' => 'required',
            'permissions' => 'required|array',
        ]);

        $mapping = new \App\Models\OperatorRoleAccessMapping();
        $mapping->user_id = $request->user_id;
        $mapping->role = $request->role;
        $mapping->module = $request->module;
        $mapping->capacity_level = $request->capacity_level;
        $mapping->can_read = in_array('Read', $request->permissions) ? 1 : 0;
        $mapping->can_create = in_array('Create', $request->permissions) ? 1 : 0;
        $mapping->can_update = in_array('Update', $request->permissions) ? 1 : 0;
        $mapping->can_approve = in_array('Approve', $request->permissions) ? 1 : 0;
        $mapping->can_publish = in_array('Publish', $request->permissions) ? 1 : 0;
        $mapping->notes = $request->notes ?? null;
        $mapping->save();

        return redirect()->route('operator.register.step6')->with('success', 'Role access mapping saved successfully!');
    }*/

    public function saveRoleAccessMapping(Request $request)
{
    $request->validate([
        'user_id'        => 'required|exists:operator_users,id',
        'role'           => 'required|string',
        'module'         => 'required|string',
        'capacity_level' => 'required|string',
        'permissions'    => 'nullable|array',
    ]);

    $permissions = $request->permissions ?? [];

    $operator = auth()->user();

    // Only business owner can assign or change role-access mappings
    if (empty($operator->business_id) || ($operator->is_owner ?? '') !== 'yes') {
        return redirect()->route('operator.register.step6')->with('error', 'Only the business owner can manage role permissions.');
    }

    // Ensure the target user belongs to the same business
    $targetUser = \App\Models\OperatorUser::find($request->user_id);
    if (!$targetUser || ($targetUser->business_id ?? null) != $operator->business_id) {
        return redirect()->route('operator.register.step6')->with('error', 'Target user not part of your business.');
    }

    // Validate module exists
    $module = \App\Models\Module::where('slug', $request->module)->orWhere('name', $request->module)->first();
    if (!$module) {
        return redirect()->route('operator.register.step6')->with('error', 'Selected module not found.');
    }

    $attributes = [
        'user_id' => $request->user_id,
        'module'  => $module->slug,
        'business_id' => $operator->business_id,
    ];

    $values = [
        'role'           => $request->role,
        'capacity_level' => $request->capacity_level,
        'can_read'       => in_array('Read', $permissions) ? 1 : 0,
        'can_create'     => in_array('Create', $permissions) ? 1 : 0,
        'can_update'     => in_array('Update', $permissions) ? 1 : 0,
        'can_approve'    => in_array('Approve', $permissions) ? 1 : 0,
        'can_publish'    => in_array('Publish', $permissions) ? 1 : 0,
        'notes'          => $request->notes,
        'business_id'    => $operator->business_id,
    ];

    OperatorRoleAccessMapping::updateOrCreate($attributes, $values);

    return redirect()
        ->route('operator.register.step6')
        ->with('success', 'Role access mapping saved successfully!');
}

    public function deleteRoleAccessMapping($mappingId) {
        $operator = auth()->user();
        $mapping = \App\Models\OperatorRoleAccessMapping::find($mappingId);
        if ($mapping) {
            // Only owner of the business may delete mappings
            if (empty($operator->business_id) || ($operator->is_owner ?? '') !== 'yes' || ($mapping->business_id ?? null) != $operator->business_id) {
                return redirect()->route('operator.register.step6')->with('error', 'Unauthorized action.');
            }
            $mapping->delete();
            return redirect()->route('operator.register.step6')->with('success', 'Role access mapping deleted successfully!');
        }
        return redirect()->route('operator.register.step6')->with('error', 'Unauthorized action.');
    }

    /*public function step9Review(Request $request) {
        if (!$this->checkStepAccess(9)) {
            return redirect()->route('operator.register.step2')->with('error', 'Please complete previous steps first.');
        }
        $operator = auth()->user();
        $statusReview = null;
        // Prefer business-scoped status review when linked
        if (!empty($operator->business_id)) {
            $statusReview = \App\Models\OperatorStatusReview::where('business_id', $operator->business_id)->first();
        }
        // Fallback: try operator_id if no business-scoped record found
        if (!$statusReview) {
            $statusReview = \App\Models\OperatorStatusReview::where('operator_id', $operator->operator_id)->first();
        }
        $progress = $this->getProgressForOperator($operator);
        return view('operator.registration.step9_review', compact('statusReview', 'progress'));
    }*/

    public function step9Review(Request $request)
    {
        if (!$this->checkStepAccess(9)) {
            return redirect()->route('operator.register.step2')
                ->with('error', 'Please complete previous steps first.');
        }

        $operator = auth()->user();

      

        $statusReview = null;
        if (!empty($operator->business_id)) {
            $statusReview = \App\Models\OperatorStatusReview::where('business_id', $operator->business_id)->first();
        }

        if (!$statusReview) {
            $statusReview = \App\Models\OperatorStatusReview::where('operator_id', $operator->operator_id)->first();
        }

        $business = null;
        if (!empty($operator->business_id)) {
            $business = Business::find($operator->business_id); 
        }

        $progress = $this->getProgressForOperator($operator);

        return view(
            'operator.registration.step9_review',
            compact('statusReview', 'progress', 'business')
        );
    }

    public function submitForApproval(Request $request)
    {
        $operator = auth()->user();
        if (!$operator) {
            return redirect()->route('operator.login');
        }

        // mark progress as submitted (prefer business-scoped progress)
        if (!empty($operator->business_id)) {
            $progress = \App\Models\OperatorRegistrationProgress::firstOrCreate([
                'business_id' => $operator->business_id
            ]);
        } else {
            $progress = \App\Models\OperatorRegistrationProgress::firstOrCreate([
                'operator_id' => $operator->operator_id
            ]);
        }
        $progress->step9_review = 1;
        $progress->save();

        // ensure there is a status review record (prefer business-scoped)
        if (!empty($operator->business_id)) {
            $statusReview = \App\Models\OperatorStatusReview::firstOrCreate([
                'business_id' => $operator->business_id
            ], ['operator_id' => $operator->operator_id]);
        } else {
            $statusReview = \App\Models\OperatorStatusReview::firstOrCreate([
                'operator_id' => $operator->operator_id
            ]);
        }

        // notify admins
        try {
            $adminEmails = \App\Models\AdminUser::where('status', 'active')->pluck('email');
            foreach ($adminEmails as $email) {
                \Mail::to($email)->send(new \App\Mail\AdminApprovalRequested($operator, $statusReview));
            }
        } catch (\Exception $e) {
            \Log::error('RegistrationController::submitForApproval - mail error', ['err' => $e->getMessage()]);
            return redirect()->route('operator.pending.approval')->with('error', 'Failed to notify admins.');
        }

        return redirect()->route('operator.pending.approval')->with('success', 'Submitted for approval. Admins have been notified.');
    }
}
