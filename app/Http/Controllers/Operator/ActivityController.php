<?php

namespace App\Http\Controllers\Operator;

use App\Models\Activity;
use App\Models\ActivityVariant;
use App\Models\ActivityPromotion;
use App\Models\ActivitySeoSocial;
use App\Models\Operator;
use App\Services\OperatorBookingNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ActivityController extends Controller
{
    /**
     * Activity listing
     */
    public function index()
    {
        $operator = auth()->user();
        $activities = Activity::byOperator($operator->id)->paginate(15);

        return view('operator.activity.index', [
            'activities' => $activities,
            'operator' => $operator,
        ]);
    }

    /**
     * Create new activity
     */
    public function create()
    {
        $operator = auth()->user();

        return view('operator.activity.create', [
            'operator' => $operator,
        ]);
    }

    /**
     * Store new activity (initialize with Step 1 form)
     */
    public function store(Request $request)
    {
        $operator = auth()->user();

        try {
            $activity = new Activity();
            $activity->operator_id = $operator->id;
            $activity->service_id = Activity::generateServiceId();
            $activity->status = Activity::STATUS_DRAFT;
            $activity->save();

            return redirect()->route('operator.activity.step1.show', $activity->id)
                ->with('success', 'Activity created. Complete Step 1: Basic Information to proceed.');
        } catch (\Exception $e) {
            \Log::error('Activity create error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Failed to create activity: ' . $e->getMessage());
        }
    }

    /**
     * Activity details/show page
     */
    public function show($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        if ($activity->operator_id !== $operator->id) {
            abort(403);
        }

        return view('operator.activity.show', [
            'activity' => $activity,
        ]);
    }

    public function update(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        if ($activity->operator_id !== $operator->id) {
            abort(403);
        }

        if ($request->has('mark_step')) {
            $step = $request->input('mark_step');
            $activity->completeStep($step);
            return redirect()->route('operator.activity.show', $activity->id)
                ->with('success', 'Step completed successfully.');
        }

        return redirect()->route('operator.activity.show', $activity->id)
            ->with('error', 'Invalid request.');
    }

    /**
     * Step 1: Basic Information
     */
    public function step1Basic($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        if ($activity->operator_id !== $operator->id) {
            abort(403);
        }

        return view('operator.activity.step1_basic', [
            'activity' => $activity,
            'serviceTypes' => Activity::SERVICE_TYPES,
            'physicalLevels' => Activity::PHYSICAL_LEVELS,
            'priceRanges' => Activity::PRICE_RANGES,
            'teamCategories' => Activity::TEAM_CATEGORIES,
            'primaryThemes' => Activity::PRIMARY_THEMES,
            'bookingConfirmationTypes' => Activity::BOOKING_CONFIRMATION_TYPES,
        ]);
    }

    /**
     * Save Step 1: Basic Information
     */
    public function saveStep1Basic(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        if ($activity->operator_id !== $operator->id) {
            abort(403);
        }

        if ($request->has('whats_included')) {
            $request->merge(['whats_included' => trim($request->input('whats_included')) ?: null]);
        }

        $data = $request->validate([
            'service_type' => 'required|in:' . implode(',', Activity::SERVICE_TYPES),
            'activity_name' => 'required|string|min:5|max:120',
            'short_title' => 'nullable|string|max:60',
            'team_categories' => 'required|array|min:1',
            'team_categories.*' => 'in:' . implode(',', Activity::TEAM_CATEGORIES),
            'physical_level' => 'required|in:' . implode(',', Activity::PHYSICAL_LEVELS),
            'price_range' => 'required|in:' . implode(',', Activity::PRICE_RANGES),
            'primary_themes' => 'nullable|array',
            'primary_themes.*' => 'in:' . implode(',', Activity::PRIMARY_THEMES),
            'destination' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'town' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'meeting_point_details' => 'required|string|min:10',
            'overview' => 'required|string|min:20',
            'whats_included' => 'nullable|string',
            'itinerary' => 'required|string|min:20',
            'duration' => 'required|string|max:50',
            'suitable_for_age' => 'nullable|string|max:100',
            'languages_offered' => 'nullable|array',
            'booking_confirmation_type' => 'required|in:' . implode(',', Activity::BOOKING_CONFIRMATION_TYPES),
            'add_ons_available' => 'nullable|boolean',
            'private_exclusive_option' => 'nullable|boolean',
            'allow_adults' => 'nullable|boolean',
            'allow_children' => 'nullable|boolean',
            'allow_infants' => 'nullable|boolean',
        ]);

        try {
            $activity->service_type = $data['service_type'];
            $activity->activity_name = $data['activity_name'];
            $activity->short_title = $data['short_title'] ?? null;
            $activity->team_categories = $data['team_categories'];
            $activity->physical_level = $data['physical_level'];
            $activity->price_range = $data['price_range'];
            $activity->primary_themes = $data['primary_themes'] ?? null;
            $activity->destination = $data['destination'] ?? null;
            $activity->region = $data['region'] ?? null;
            $activity->town = $data['town'] ?? null;
            $activity->latitude = $data['latitude'];
            $activity->longitude = $data['longitude'];
            $activity->meeting_point_details = $data['meeting_point_details'];
            $activity->overview = $data['overview'];
            $activity->whats_included = $data['whats_included'] ?? null;
            $activity->itinerary = $data['itinerary'];
            $activity->duration = $data['duration'];
            $activity->suitable_for_age = $data['suitable_for_age'] ?? null;
            $activity->languages_offered = $data['languages_offered'] ?? null;
            $activity->booking_confirmation_type = $data['booking_confirmation_type'];
            $activity->add_ons_available = filter_var($data['add_ons_available'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            $activity->private_exclusive_option = filter_var($data['private_exclusive_option'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            $activity->allow_adults = isset($data['allow_adults']) && $data['allow_adults'] ? true : false;
            $activity->allow_children = isset($data['allow_children']) && $data['allow_children'] ? true : false;
            $activity->allow_infants = isset($data['allow_infants']) && $data['allow_infants'] ? true : false;

            // Mark step 1 as complete
            $activity->completeStep('step1_basic');
            $activity->save();

            \Log::info('Activity Step 1 saved', ['activity_id' => $activity->id, 'operator_id' => $operator->id]);

            return redirect()->route('operator.activity.show', $activity->id)
                ->with('success', 'Activity Step 1: Basic Information saved successfully!');
        } catch (\Exception $e) {
            \Log::error('saveStep1Basic error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('operator.activity.step1.show', $activity->id)
                ->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    /**
     * Step 2: Management & Communication
     */
    public function step2ManagementCommunication($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        if ($activity->operator_id !== $operator->id) {
            abort(403);
        }

        return view('operator.activity.step2_management_communication', [
            'activity' => $activity,
            'operator' => $operator,
        ]);
    }

    /**
     * Save Step 2: Management & Communication
     */
    public function saveStep2ManagementCommunication(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        if ($activity->operator_id !== $operator->id) {
            abort(403);
        }

        $data = $request->validate([
            // Reservation Contact (Mandatory)
            'reservation_contact_name' => 'required|string|max:255',
            'reservation_contact_email' => 'required|email|max:255',
            'reservation_contact_phone' => 'required|string|max:50',
            'reservation_contact_mobile' => 'required|string|max:50',
            
            // Accounting Contact (Optional)
            'accounting_contact_name' => 'nullable|string|max:255',
            'accounting_contact_email' => 'nullable|email|max:255',
            'accounting_contact_phone' => 'nullable|string|max:50',
            'accounting_contact_mobile' => 'nullable|string|max:50',
            
            // Management Contact (Mandatory)
            'management_contact_name' => 'required|string|max:255',
            'management_contact_email' => 'required|email|max:255',
            'management_contact_phone' => 'required|string|max:50',
            'management_contact_mobile' => 'required|string|max:50',
            
            // Operational Manager (Optional)
            'operational_manager_name' => 'nullable|string|max:255',
            'operational_manager_phone' => 'nullable|string|max:50',
            
            // Booking Settings
            'booking_confirmation_type' => 'required|in:Instant,On Request',
        ]);

        try {
            // Save Reservation Contact
            $activity->reservation_contact_name = $data['reservation_contact_name'];
            $activity->reservation_contact_email = $data['reservation_contact_email'];
            $activity->reservation_contact_phone = $data['reservation_contact_phone'];
            $activity->reservation_contact_mobile = $data['reservation_contact_mobile'];
            
            // Save Accounting Contact
            $activity->accounting_contact_name = $data['accounting_contact_name'] ?? null;
            $activity->accounting_contact_email = $data['accounting_contact_email'] ?? null;
            $activity->accounting_contact_phone = $data['accounting_contact_phone'] ?? null;
            $activity->accounting_contact_mobile = $data['accounting_contact_mobile'] ?? null;
            
            // Save Management Contact
            $activity->management_contact_name = $data['management_contact_name'];
            $activity->management_contact_email = $data['management_contact_email'];
            $activity->management_contact_phone = $data['management_contact_phone'];
            $activity->management_contact_mobile = $data['management_contact_mobile'];
            
            // Save Operational Manager
            $activity->operational_manager_name = $data['operational_manager_name'] ?? null;
            $activity->operational_manager_phone = $data['operational_manager_phone'] ?? null;
            
            // Save Booking Settings
            $activity->booking_confirmation_type = $data['booking_confirmation_type'];
            
            // Set booking registration type from operator if not already set
            if (!$activity->booking_registration_type) {
                $activity->booking_registration_type = $operator->booking_registration_type ?? 'Listing';
            }

            // Mark step 2 as complete
            $activity->completeStep('step2_management_communication');
            $activity->save();

            \Log::info('Activity Step 2 saved', ['activity_id' => $activity->id, 'operator_id' => $operator->id]);

            return redirect()->route('operator.activity.show', $activity->id)
                ->with('success', 'Activity Step 2: Management & Communication saved successfully!');
        } catch (\Exception $e) {
            \Log::error('saveStep2ManagementCommunication error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('operator.activity.step2.show', $activity->id)
                ->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    /**
     * Step 3: Photos & Media
     */
    public function step3PhotosMedia($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        if ($activity->operator_id !== $operator->id) {
            abort(403);
        }

        return view('operator.activity.step3_photos_media', [
            'activity' => $activity,
            'operator' => $operator,
        ]);
    }

    /**
     * Save Step 3: Photos & Media
     */
    public function saveStep3PhotosMedia(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        if ($activity->operator_id !== $operator->id) {
            abort(403);
        }

        $data = $request->validate([
            'hero_banner_image' => $activity->hero_banner_image ? 'nullable|image|max:10240' : 'required|image|max:10240', // 10MB max
            'gallery_images.*' => 'nullable|image|max:10240',
            'vehicle_types.*' => 'required|string',
            'vehicle_images.*' => 'required|image|max:10240',
            'logo' => 'nullable|image|max:5120', // 5MB max
            'video' => 'nullable|mimes:mp4,mov,avi,wmv|max:51200', // 50MB max
        ]);

        try {
            // Handle Hero/Banner Image
            if ($request->hasFile('hero_banner_image')) {
                $heroPath = $request->file('hero_banner_image')->store('activities/hero', 'public');
                $activity->hero_banner_image = $heroPath;
            }

            // Handle Gallery Images
            if ($request->hasFile('gallery_images')) {
                $galleryPaths = [];
                foreach ($request->file('gallery_images') as $image) {
                    $galleryPaths[] = $image->store('activities/gallery', 'public');
                }
                
                // Merge with existing gallery images if any
                $existingGallery = $activity->gallery_images ?? [];
                $activity->gallery_images = array_merge($existingGallery, $galleryPaths);
            }

            // Handle Vehicle/Equipment Images
            if ($request->hasFile('vehicle_images')) {
                $vehicleData = [];
                $vehicleTypes = $request->input('vehicle_types', []);
                $vehicleImages = $request->file('vehicle_images');

                foreach ($vehicleImages as $index => $image) {
                    if (isset($vehicleTypes[$index])) {
                        $imagePath = $image->store('activities/vehicles', 'public');
                        $vehicleData[] = [
                            'type' => $vehicleTypes[$index],
                            'image' => $imagePath,
                        ];
                    }
                }

                // Merge with existing vehicle images if any
                $existingVehicles = $activity->vehicle_images ?? [];
                $activity->vehicle_images = array_merge($existingVehicles, $vehicleData);
            }

            // Handle Logo
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('activities/logos', 'public');
                $activity->logo = $logoPath;
            }

            // Handle Video
            if ($request->hasFile('video')) {
                $videoPath = $request->file('video')->store('activities/videos', 'public');
                $activity->video = $videoPath;
            }

            // Validate minimum requirements
            $galleryCount = count($activity->gallery_images ?? []);
            $vehicleCount = count($activity->vehicle_images ?? []);

            if (!$activity->hero_banner_image) {
                return back()->with('error', 'Hero/Banner image is required.');
            }

            if ($galleryCount < 3) {
                return back()->with('error', 'At least 3 gallery images are required.');
            }

            if ($vehicleCount < 1) {
                return back()->with('error', 'At least 1 vehicle/equipment image is required.');
            }

            // Mark step 3 as complete
            $activity->completeStep('step3_photos_media');
            $activity->save();

            \Log::info('Activity Step 3 saved', ['activity_id' => $activity->id, 'operator_id' => $operator->id]);

            return redirect()->route('operator.activity.show', $activity->id)
                ->with('success', 'Activity Step 3: Photos & Media uploaded successfully!');
        } catch (\Exception $e) {
            \Log::error('saveStep3PhotosMedia error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('operator.activity.step3.show', $activity->id)
                ->with('error', 'Failed to upload media: ' . $e->getMessage());
        }
    }

    /**
     * Step 4: Legal & Compliance
     */
    public function step4LegalCompliance($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        // Load or create compliance record
        $compliance = $activity->compliance;
        if (!$compliance) {
            $compliance = new \App\Models\ActivityCompliance();
        }

        $operatorBusinessRegistrationNumber = '';
        if ($operator->business) {
            $operatorBusinessRegistrationNumber = $operator->business->registration_number;
        } else {
            $operatorProfile = \App\Models\OperatorProfile::where('operator_id', $operator->operator_id)->first();
            if ($operatorProfile) {
                $operatorBusinessRegistrationNumber = $operatorProfile->business_registration_number;
            }
        }

        return view('operator.activity.step4_legal_compliance', compact('activity', 'compliance', 'operator', 'operatorBusinessRegistrationNumber'));
    }

    /**
     * Save Step 4: Legal & Compliance
     */
    public function saveStep4LegalCompliance(Request $request, $id)
    {
        try {
            $activity = Activity::findOrFail($id);
            $operator = auth()->user();

            // Check ownership
            if ($activity->operator_id !== $operator->id) {
                abort(403, 'Unauthorized action.');
            }

            // Validation
            $request->validate([
                'business_registration_number' => 'required|string|max:255',
                'tourism_activity_permit' => 'required|string|max:255',
                'public_liability_insurance' => 'required|string|max:255',
                'insurance_expiration' => 'nullable|date',
                'parent_service_id' => 'nullable|string|max:50',
                'equipment_registration_serial' => 'nullable|string|max:255',
                'tourism_permit_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'insurance_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'operational_assessment_doc' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'emergency_plan_doc' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'equipment_compliance_doc' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'permits_authorisations.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'other_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            ]);

            // Load or create compliance record
            $compliance = $activity->compliance;
            if (!$compliance) {
                $compliance = new \App\Models\ActivityCompliance();
                $compliance->activity_id = $activity->id;
                $compliance->compliance_id = \App\Models\ActivityCompliance::generateComplianceId();
            }

            // Update fields
            $compliance->parent_service_id = $request->input('parent_service_id');
            $compliance->business_registration_number = $request->input('business_registration_number');
            $compliance->tourism_activity_permit = $request->input('tourism_activity_permit');
            $compliance->public_liability_insurance = $request->input('public_liability_insurance');
            $compliance->insurance_expiration = $request->input('insurance_expiration');
            $compliance->equipment_registration_serial = $request->input('equipment_registration_serial');

            // Handle tourism permit file upload
            if ($request->hasFile('tourism_permit_file')) {
                $file = $request->file('tourism_permit_file');
                $filename = time() . '_permit_' . $file->getClientOriginalName();
                $path = $file->storeAs('activities/compliance/permits', $filename, 'public');
                $compliance->tourism_permit_file = $path;
            }

            // Handle insurance file upload
            if ($request->hasFile('insurance_file')) {
                $file = $request->file('insurance_file');
                $filename = time() . '_insurance_' . $file->getClientOriginalName();
                $path = $file->storeAs('activities/compliance/insurance', $filename, 'public');
                $compliance->insurance_file = $path;
            }

            // Handle operational assessment document upload
            if ($request->hasFile('operational_assessment_doc')) {
                $file = $request->file('operational_assessment_doc');
                $filename = time() . '_assessment_' . $file->getClientOriginalName();
                $path = $file->storeAs('activities/compliance/assessments', $filename, 'public');
                $compliance->operational_assessment_doc = $path;
            }

            // Handle emergency plan document upload
            if ($request->hasFile('emergency_plan_doc')) {
                $file = $request->file('emergency_plan_doc');
                $filename = time() . '_emergency_' . $file->getClientOriginalName();
                $path = $file->storeAs('activities/compliance/emergency', $filename, 'public');
                $compliance->emergency_plan_doc = $path;
            }

            // Handle equipment compliance document upload
            if ($request->hasFile('equipment_compliance_doc')) {
                $file = $request->file('equipment_compliance_doc');
                $filename = time() . '_equipment_' . $file->getClientOriginalName();
                $path = $file->storeAs('activities/compliance/equipment', $filename, 'public');
                $compliance->equipment_compliance_doc = $path;
            }

            // Handle permits/authorisations files
            if ($request->hasFile('permits_authorisations')) {
                $permitFiles = [];
                foreach ($request->file('permits_authorisations') as $file) {
                    $filename = time() . '_auth_' . uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('activities/compliance/permits', $filename, 'public');
                    $permitFiles[] = $path;
                }
                // Merge with existing files
                $existingPermits = $compliance->permits_authorisations_files ?? [];
                $compliance->permits_authorisations_files = array_merge($existingPermits, $permitFiles);
            }

            // Handle other compliance documents
            if ($request->hasFile('other_documents')) {
                $otherFiles = [];
                foreach ($request->file('other_documents') as $file) {
                    $filename = time() . '_other_' . uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('activities/compliance/other', $filename, 'public');
                    $otherFiles[] = $path;
                }
                // Merge with existing files
                $existingOther = $compliance->other_permit_files ?? [];
                $compliance->other_permit_files = array_merge($existingOther, $otherFiles);
            }

            $compliance->save();

            // Mark step 4 as complete
            $activity->step4_legal_compliance = 1;

            // Auto-transition status if all required steps are complete
            if ($activity->step1_basic && $activity->step2_management_communication && 
                $activity->step3_photos_media && $activity->step4_legal_compliance) {
                if ($activity->status === 'Draft') {
                    $activity->status = 'In Review';
                }
            }

            $activity->save();

            \Log::info('Activity Step 4 saved', ['activity_id' => $activity->id, 'compliance_id' => $compliance->compliance_id]);

            return redirect()->route('operator.activity.show', $activity->id)
                ->with('success', 'Activity Step 4: Legal & Compliance saved successfully!');
        } catch (\Exception $e) {
            \Log::error('saveStep4LegalCompliance error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('operator.activity.step4.show', $activity->id)
                ->with('error', 'Failed to save compliance: ' . $e->getMessage());
        }
    }

    /**
     * Show Step 5: Accounting & Transaction
     */
    public function step5AccountingTransaction($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        // Load or create accounting record
        $accounting = $activity->accounting;
        if (!$accounting) {
            $accounting = new \App\Models\ActivityAccounting();
        }

        return view('operator.activity.step5_accounting_transaction', compact('activity', 'operator', 'accounting'));
    }

    /**
     * Save Step 5: Accounting & Transaction
     */
    public function saveStep5AccountingTransaction(Request $request, $id)
    {
        try {
            $activity = Activity::findOrFail($id);
            $operator = auth()->user();

            // Check ownership
            if ($activity->operator_id !== $operator->id) {
                abort(403, 'Unauthorized action.');
            }

            // Custom validation rules based on conditions
            $rules = [
                'bank_account_holder_name' => 'required|string|max:255',
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:100',
                'iban' => 'nullable|string|max:100',
                'swift_code' => 'nullable|string|max:50',
                'agreement_name' => 'nullable|string',
                'tax_type' => 'nullable|in:Tourism,City,Environmental,None',
                'tax_payment_collection' => 'nullable|in:Operator,MPO',
                'commission_type' => 'nullable|string',
                'commission_value' => 'nullable|numeric',
                'currency_net' => 'nullable|string|max:10',
            ];

            // VAT number is required only if not exempted
            if (!$request->has('vat_exempted') || !$request->input('vat_exempted')) {
                $rules['vat_number'] = 'required|string|max:100';
            } else {
                $rules['vat_number'] = 'nullable|string|max:100';
            }

            // Tax charge fields are required if  tax type is not "None"
            if ($request->input('tax_type') && $request->input('tax_type') !== 'None') {
                $rules['tax_charges_basis'] = 'required|string';
                $rules['tax_charges_type'] = 'required|string';
                $rules['tax_charges_value'] = 'required|numeric|min:0';
            }

            $request->validate($rules);

            // Load or create accounting record
            $accounting = $activity->accounting;
            if (!$accounting) {
                $accounting = new \App\Models\ActivityAccounting();
                $accounting->activity_id = $activity->id;
            }

            // Update accounting fields
            $accounting->bank_account_holder_name = $request->input('bank_account_holder_name');
            $accounting->bank_name = $request->input('bank_name');
            $accounting->account_number = $request->input('account_number');
            $accounting->iban = $request->input('iban');
            $accounting->swift_code = $request->input('swift_code');
            $accounting->vat_number = $request->input('vat_number');
            $accounting->vat_exempted = $request->has('vat_exempted') ? 1 : 0;
            $accounting->agreement_name = $request->input('agreement_name');
            $accounting->commission_type = $request->input('commission_type');
            $accounting->commission_value = $request->input('commission_value');
            $accounting->currency_net = $request->input('currency_net');
            $accounting->tax_type = $request->input('tax_type');
            $accounting->tax_charges_basis = $request->input('tax_charges_basis');
            $accounting->tax_charges_type = $request->input('tax_charges_type');
            $accounting->tax_charges_value = $request->input('tax_charges_value');
            $accounting->tax_payment_collection = $request->input('tax_payment_collection');

            $accounting->save();

            // Mark step 5 as complete
            $activity->step5_accounting_transaction = 1;

            // Auto-transition status if all required steps are complete
            if ($activity->step1_basic && $activity->step2_management_communication && 
                $activity->step3_photos_media && $activity->step4_legal_compliance && 
                $activity->step5_accounting_transaction) {
                if ($activity->status === 'Draft' || $activity->status === 'In Review') {
                    $activity->status = 'In Review';
                }
            }

            $activity->save();

            \Log::info('Activity Step 5 saved', ['activity_id' => $activity->id, 'accounting_id' => $accounting->id]);

            return redirect()->route('operator.activity.show', $activity->id)
                ->with('success', 'Activity Step 5: Accounting & Transaction saved successfully!');
        } catch (\Exception $e) {
            \Log::error('saveStep5AccountingTransaction error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()
                ->with('error', 'Failed to save accounting & transaction data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show Step 6: Policies & Rules
     */
    public function step6PoliciesRules($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        // Load or create policy record
        $policy = $activity->policy;
        if (!$policy) {
            $policy = new \App\Models\ActivityPolicy();
        }

        return view('operator.activity.step6_policies_rules', compact('activity', 'policy'));
    }

    /**
     * Save Step 6: Policies & Rules
     */
    public function saveStep6PoliciesRules(Request $request, $id)
    {
        try {
            $activity = Activity::findOrFail($id);
            $operator = auth()->user();

            // Check ownership
            if ($activity->operator_id !== $operator->id) {
                abort(403, 'Unauthorized action.');
            }

            // Custom validation rules
            $rules = [
                'service_id' => 'nullable|string|max:50',
                'booking_window_rules' => 'nullable|string',
                'no_show_policy' => 'nullable|string',
                'amendment_policy' => 'nullable|string',
                'amendment_policy_type' => 'required|in:Custom,Template',
                'amendment_policy_template_id' => 'nullable|string',
                'cancellation_policy' => 'nullable|string',
                'cancellation_policy_type' => 'required|in:Custom,Template',
                'cancellation_policy_template_id' => 'nullable|string',
                'cancellation_penalties_enabled' => 'required|in:Yes,No',
                'child_policy_age' => 'nullable|integer|min:0|max:17',
                'infant_policy_age' => 'nullable|integer|min:0|max:5',
                'safety_requirements' => 'required|string',
                'health_requirements_type' => 'required|in:None,Upload,Generate',
                'health_requirements_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            ];

            // Conditional validation for policies
            if ($request->input('amendment_policy_type') === 'Custom') {
                $rules['amendment_policy'] = 'nullable|string';
            } else {
                $rules['amendment_policy_template_id'] = 'required|string';
            }

            if ($request->input('cancellation_policy_type') === 'Custom') {
                $rules['cancellation_policy'] = 'required|string';
            } else {
                $rules['cancellation_policy_template_id'] = 'required|string';
            }

            // Conditional validation for penalties
            if ($request->input('cancellation_penalties_enabled') === 'Yes') {
                $rules['cancellation_penalties_type'] = 'required|in:Person(s),Percentage,Amount';
                $rules['cancellation_penalties_value'] = 'required|numeric|min:0';
            } else {
                $rules['cancellation_penalties_type'] = 'nullable|in:Person(s),Percentage,Amount';
                $rules['cancellation_penalties_value'] = 'nullable|numeric|min:0';
            }

            $request->validate($rules);

            // Load or create policy record
            $policy = $activity->policy;
            if (!$policy) {
                $policy = new \App\Models\ActivityPolicy();
                $policy->activity_id = $activity->id;
            }

            // Save basic fields
            $policy->service_id = $request->input('service_id');
            $policy->booking_window_rules = $request->input('booking_window_rules');
            $policy->no_show_policy = $request->input('no_show_policy');
            $policy->amendment_policy = $request->input('amendment_policy');
            $policy->amendment_policy_type = $request->input('amendment_policy_type');
            $policy->amendment_policy_template_id = $request->input('amendment_policy_template_id');
            $policy->cancellation_policy = $request->input('cancellation_policy');
            $policy->cancellation_policy_type = $request->input('cancellation_policy_type');
            $policy->cancellation_policy_template_id = $request->input('cancellation_policy_template_id');
            $policy->cancellation_penalties_enabled = $request->input('cancellation_penalties_enabled');
            $policy->cancellation_penalties_type = $request->input('cancellation_penalties_type');
            $policy->cancellation_penalties_value = $request->input('cancellation_penalties_value');
            $policy->child_policy_age = $request->input('child_policy_age');
            $policy->infant_policy_age = $request->input('infant_policy_age');
            $policy->safety_requirements = $request->input('safety_requirements');
            $policy->health_requirements_type = $request->input('health_requirements_type');

            // Handle health requirements file upload
            if ($request->hasFile('health_requirements_file')) {
                $file = $request->file('health_requirements_file');
                $filename = time() . '_health_requirements_' . $file->getClientOriginalName();
                $path = $file->storeAs('activity_policies', $filename, 'public');
                $policy->health_requirements_file = $path;
            }

            $policy->save();

            // Mark step 6 as complete
            $activity->step6_policies_rules = 1;

            // Auto-transition status if all required steps are complete
            if ($activity->step1_basic && $activity->step2_management_communication && 
                $activity->step3_photos_media && $activity->step4_legal_compliance && 
                $activity->step5_accounting_transaction && $activity->step6_policies_rules) {
                if ($activity->status === 'Draft') {
                    $activity->status = 'In Review';
                }
            }

            $activity->save();

            \Log::info('Activity Step 6 saved', ['activity_id' => $activity->id, 'policy_id' => $policy->policy_id]);

            return redirect()->route('operator.activity.show', $activity->id)
                ->with('success', 'Activity Step 6: Policies & Rules saved successfully!');
        } catch (\Exception $e) {
            \Log::error('saveStep6PoliciesRules error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('operator.activity.step6.show', $activity->id)
                ->with('error', 'Failed to save policies & rules: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show Step 7: Variants & Equipment
     */
    public function step7VariantsEquipment($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $variants = $activity->variants;
        $variant = null;
        $operationsStaffing = $activity->operationsStaffing ?? new \App\Models\ActivityOperationsStaffing();
        
        // Get all operations & staffing records for all variants
        $operationsRecords = \App\Models\ActivityOperationsStaffing::where('activity_id', $activity->id)
                                                                    ->with('variant')
                                                                    ->get();

        return view('operator.activity.step7_variants_equipment', compact('activity', 'operator', 'variants', 'variant', 'operationsStaffing', 'operationsRecords'));
    }

    /**
     * Store new variant
     */
    public function storeVariant(Request $request, $id)
    {
        try {
            $activity = Activity::findOrFail($id);
            $operator = auth()->user();

            // Check ownership
            if ($activity->operator_id !== $operator->id) {
                abort(403, 'Unauthorized action.');
            }

            // Validation
            $request->validate([
                'variant_name' => 'required|string|max:255',
                'quality_tier' => 'required|in:Standard,Premium,Luxury',
                'max_pax' => 'required|integer|min:1',
                'min_participants' => 'required|integer|min:1',
                'max_participants' => 'required|integer|min:1',
                'allotment' => 'required|integer|min:0',
                'amenities' => 'nullable|array',
                'safety_equipment' => 'nullable|array',
                'private_exclusive' => 'required|in:Yes,No',
                'equipment_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
            ]);

            // Create variant
            $variant = new \App\Models\ActivityVariant();
            $variant->activity_id = $activity->id;
            $variant->service_id = $activity->service_id;
            $variant->variant_equipment_id = \App\Models\ActivityVariant::generateVariantEquipmentId($activity->id);
            $variant->variant_name = $request->input('variant_name');
            $variant->quality_tier = $request->input('quality_tier');
            $variant->max_pax = $request->input('max_pax');
            $variant->min_participants = $request->input('min_participants');
            $variant->max_participants = $request->input('max_participants');
            $variant->allotment = $request->input('allotment');
            $variant->amenities = $request->input('amenities', []);
            $variant->safety_equipment = $request->input('safety_equipment', []);
            $variant->private_exclusive = $request->input('private_exclusive');

            // Handle image upload
            if ($request->hasFile('equipment_image')) {
                $file = $request->file('equipment_image');
                $filename = time() . '_variant_' . $file->getClientOriginalName();
                $path = $file->storeAs('activity_variants', $filename, 'public');
                $variant->equipment_image = $path;
            }

            $variant->save();

            // Mark step 7 as complete if not already
            if (!$activity->step7_variants_equipment) {
                $activity->step7_variants_equipment = 1;
                
                // Auto-transition status if all required steps are complete
                if ($activity->step1_basic && $activity->step2_management_communication && 
                    $activity->step3_photos_media && $activity->step4_legal_compliance && 
                    $activity->step5_accounting_transaction && $activity->step6_policies_rules && 
                    $activity->step7_variants_equipment) {
                    if ($activity->status === 'Draft') {
                        $activity->status = 'In Review';
                    }
                }
                
                $activity->save();
            }

            \Log::info('Activity variant created', ['activity_id' => $activity->id, 'variant_id' => $variant->variant_id]);

            return redirect()->route('operator.activity.step7.show', $activity->id)
                ->with('success', 'Variant added successfully!');
        } catch (\Exception $e) {
            \Log::error('storeVariant error', ['error' => $e->getMessage()]);
            return redirect()->route('operator.activity.step7.show', $activity->id)
                ->with('error', 'Failed to add variant: ' . $e->getMessage());
        }
    }

    /**
     * Show edit variant form
     */
    public function editVariant($id, $variantId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $variant = \App\Models\ActivityVariant::findOrFail($variantId);
        $variants = $activity->variants;
        $operationsStaffing = $activity->operationsStaffing ?? new \App\Models\ActivityOperationsStaffing();
        
        // Get all operations & staffing records for all variants
        $operationsRecords = \App\Models\ActivityOperationsStaffing::where('activity_id', $activity->id)
                                                                    ->with('variant')
                                                                    ->get();

        return view('operator.activity.step7_variants_equipment', compact('activity', 'operator', 'variant', 'variants', 'operationsStaffing', 'operationsRecords'));
    }

    /**
     * Update variant
     */
    public function updateVariant(Request $request, $id, $variantId)
    {
        try {
            $activity = Activity::findOrFail($id);
            $operator = auth()->user();

            // Check ownership
            if ($activity->operator_id !== $operator->id) {
                abort(403, 'Unauthorized action.');
            }

            $variant = \App\Models\ActivityVariant::findOrFail($variantId);

            // Validation
            $request->validate([
                'variant_name' => 'required|string|max:255',
                'quality_tier' => 'required|in:Standard,Premium,Luxury',
                'max_pax' => 'required|integer|min:1',
                'min_participants' => 'required|integer|min:1',
                'max_participants' => 'required|integer|min:1',
                'allotment' => 'required|integer|min:0',
                'amenities' => 'nullable|array',
                'safety_equipment' => 'nullable|array',
                'private_exclusive' => 'required|in:Yes,No',
                'equipment_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
            ]);

            // Update variant
            $variant->variant_name = $request->input('variant_name');
            $variant->quality_tier = $request->input('quality_tier');
            $variant->max_pax = $request->input('max_pax');
            $variant->min_participants = $request->input('min_participants');
            $variant->max_participants = $request->input('max_participants');
            $variant->allotment = $request->input('allotment');
            $variant->amenities = $request->input('amenities', []);
            $variant->safety_equipment = $request->input('safety_equipment', []);
            $variant->private_exclusive = $request->input('private_exclusive');

            // Handle image upload
            if ($request->hasFile('equipment_image')) {
                $file = $request->file('equipment_image');
                $filename = time() . '_variant_' . $file->getClientOriginalName();
                $path = $file->storeAs('activity_variants', $filename, 'public');
                $variant->equipment_image = $path;
            }

            $variant->save();

            \Log::info('Activity variant updated', ['activity_id' => $activity->id, 'variant_id' => $variant->variant_id]);

            return redirect()->route('operator.activity.step7.show', $activity->id)
                ->with('success', 'Variant updated successfully!');
        } catch (\Exception $e) {
            \Log::error('updateVariant error', ['error' => $e->getMessage()]);
            return redirect()->route('operator.activity.step7.show', $activity->id)
                ->with('error', 'Failed to update variant: ' . $e->getMessage());
        }
    }

    /**
     * Delete variant
     */
    public function deleteVariant($id, $variantId)
    {
        try {
            $activity = Activity::findOrFail($id);
            $operator = auth()->user();

            // Check ownership
            if ($activity->operator_id !== $operator->id) {
                abort(403, 'Unauthorized action.');
            }

            $variant = \App\Models\ActivityVariant::findOrFail($variantId);
            $variant->delete();

            \Log::info('Activity variant deleted', ['activity_id' => $activity->id, 'variant_id' => $variantId]);

            return redirect()->route('operator.activity.step7.show', $activity->id)
                ->with('success', 'Variant deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('deleteVariant error', ['error' => $e->getMessage()]);
            return redirect()->route('operator.activity.step7.show', $activity->id)
                ->with('error', 'Failed to delete variant: ' . $e->getMessage());
        }
    }

    /**
     * Delete activity (soft delete)
     */
    public function destroy($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        if ($activity->operator_id !== $operator->id) {
            abort(403);
        }


        try {
            $activity->delete();
            \Log::info('Activity deleted', ['activity_id' => $activity->id, 'operator_id' => $operator->id]);

            return redirect()->route('operator.activity.index')
                ->with('success', 'Activity deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Activity delete error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete activity: ' . $e->getMessage());
        }
    }

    /**
     * Save Operations & Staffing
     */
    public function saveOperationsStaffing(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'variant_id' => 'required|exists:activity_variants,variant_id',
            'age_groups' => 'required|array|min:1',
            'age_groups.*' => 'string',
            'pickup_options' => 'nullable|string',
            'dropoff_options' => 'nullable|string',
            'accessibility_features' => 'nullable|array',
            'accessibility_features.*' => 'string',
            'crew_guide_count' => 'nullable|integer|min:1',
            'crew_guide_requirements' => 'nullable|string',
            'special_equipment_notes' => 'nullable|string',
        ]);

        try {
            $variant = \App\Models\ActivityVariant::findOrFail($validated['variant_id']);
            
            // Get ops contact from Step 2 (Management & Communication)
            $opsContactName = $activity->management_contact_name ?? '';
            $opsContactMobile = $activity->management_contact_mobile ?? '';

            $operationsStaffing = \App\Models\ActivityOperationsStaffing::where('activity_id', $activity->id)
                                                                         ->where('variant_id', $validated['variant_id'])
                                                                         ->first();

            if (!$operationsStaffing) {
                $operationsStaffing = new \App\Models\ActivityOperationsStaffing();
                $operationsStaffing->activity_id = $activity->id;
                $operationsStaffing->service_id = $activity->service_id;
                $operationsStaffing->variant_id = $validated['variant_id'];
                $operationsStaffing->variant_equipment_id = $variant->variant_equipment_id;
            }

            $operationsStaffing->age_groups = $validated['age_groups'];
            $operationsStaffing->pickup_options = $validated['pickup_options'] ?? null;
            $operationsStaffing->dropoff_options = $validated['dropoff_options'] ?? null;
            $operationsStaffing->accessibility_features = $validated['accessibility_features'] ?? [];
            $operationsStaffing->crew_guide_count = $validated['crew_guide_count'] ?? null;
            $operationsStaffing->ops_contact_name = $opsContactName;
            $operationsStaffing->ops_contact_mobile = $opsContactMobile;
            $operationsStaffing->crew_guide_requirements = $validated['crew_guide_requirements'] ?? null;
            $operationsStaffing->special_equipment_notes = $validated['special_equipment_notes'] ?? null;
            $operationsStaffing->save();

            return back()->with('success', 'Operations & Staffing saved successfully.');
        } catch (\Exception $e) {
            \Log::error('Operations & Staffing save error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to save operations & staffing: ' . $e->getMessage());
        }
    }

    /**
     * Delete Operations & Staffing
     */
    public function deleteOperationsStaffing(Request $request, $id, $operationId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $operationsStaffing = \App\Models\ActivityOperationsStaffing::findOrFail($operationId);
            
            if ($operationsStaffing->activity_id !== $activity->id) {
                abort(403, 'Unauthorized action.');
            }

            $operationsStaffing->delete();

            return back()->with('success', 'Operations & Staffing deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Operations & Staffing delete error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete operations & staffing: ' . $e->getMessage());
        }
    }

    /**
     * Update Operations & Staffing
     */
    public function updateOperationsStaffing(Request $request, $id, $operationId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'variant_id' => 'required|exists:activity_variants,variant_id',
            'age_groups' => 'required|array|min:1',
            'age_groups.*' => 'string',
            'pickup_options' => 'nullable|string',
            'dropoff_options' => 'nullable|string',
            'accessibility_features' => 'nullable|array',
            'accessibility_features.*' => 'string',
            'crew_guide_count' => 'nullable|integer|min:1',
            'crew_guide_requirements' => 'nullable|string',
            'special_equipment_notes' => 'nullable|string',
        ]);

        try {
            $operationsStaffing = \App\Models\ActivityOperationsStaffing::findOrFail($operationId);
            
            // Verify ownership
            if ($operationsStaffing->activity_id !== $activity->id) {
                abort(403, 'Unauthorized action.');
            }

            $variant = \App\Models\ActivityVariant::findOrFail($validated['variant_id']);
            
            // Get ops contact from Step 2 (Management & Communication)
            $opsContactName = $activity->management_contact_name ?? '';
            $opsContactMobile = $activity->management_contact_mobile ?? '';

            // Update fields
            $operationsStaffing->age_groups = $validated['age_groups'];
            $operationsStaffing->pickup_options = $validated['pickup_options'] ?? null;
            $operationsStaffing->dropoff_options = $validated['dropoff_options'] ?? null;
            $operationsStaffing->accessibility_features = $validated['accessibility_features'] ?? [];
            $operationsStaffing->crew_guide_count = $validated['crew_guide_count'] ?? null;
            $operationsStaffing->ops_contact_name = $opsContactName;
            $operationsStaffing->ops_contact_mobile = $opsContactMobile;
            $operationsStaffing->crew_guide_requirements = $validated['crew_guide_requirements'] ?? null;
            $operationsStaffing->special_equipment_notes = $validated['special_equipment_notes'] ?? null;
            $operationsStaffing->save();

            return back()->with('success', 'Operations & Staffing updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Operations & Staffing update error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update operations & staffing: ' . $e->getMessage());
        }
    }

    /**
     * Step 8: Show Scheduling TimeSlots
     */
    public function step8SchedulingTimeSlots(Request $request, $id)
    {
        $activity = Activity::with(['variants', 'schedulingTimeSlots'])->findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if previous steps are complete
        $previousStepsComplete = !is_null($activity->step7_status) && $activity->step7_status === 'Completed';

        $timeSlots = $activity->schedulingTimeSlots()->get();
        $variants = $activity->variants()->get();

        return view('operator.activity.step8_scheduling_timeslots', [
            'activity' => $activity,
            'timeSlots' => $timeSlots,
            'variants' => $variants,
            'previousStepsComplete' => $previousStepsComplete,
        ]);
    }

    /**
     * Store Scheduling TimeSlot
     */
    public function storeTimeSlot(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'variant_id' => 'required|exists:activity_variants,variant_id',
            'participant_equipment_id' => 'required|in:Per Person,Per Equipment',
            'capacity_per_slot' => 'required|integer|min:1',
            'schedule_type' => 'required|in:Fixed Slots,Interval-Based,Open Booking,Group Events',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'duration' => 'required|string',
            'recurring' => 'nullable|integer|min:1',
            'lead_time_minutes' => 'nullable|integer|min:1',
            'days_of_week' => 'nullable|array',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        $validator->after(function ($validator) use ($request, $activity) {
            $variantId = $request->input('variant_id');
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');

            $existingTimeSlots = \App\Models\ActivitySchedulingTimeSlot::where('activity_id', $activity->id)
                ->where('variant_id', $variantId)
                ->get();

            foreach ($existingTimeSlots as $slot) {
                if ($slot->start_time === $startTime && $slot->end_time === $endTime) {
                    $validator->errors()->add('start_time', 'An identical timeslot already exists for this variant.');
                    break;
                }

                if ($startTime < $slot->end_time && $endTime > $slot->start_time) {
                    $validator->errors()->add('start_time', 'This timeslot overlaps with an existing timeslot for the selected variant.');
                    break;
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        try {
            $variant = \App\Models\ActivityVariant::findOrFail($validated['variant_id']);

            $timeSlot = new \App\Models\ActivitySchedulingTimeSlot();
            $timeSlot->service_id = $activity->service_id;
            $timeSlot->activity_id = $activity->id;
            $timeSlot->variant_id = $validated['variant_id'];
            $timeSlot->service_name = $activity->service_name ?? '';
            $timeSlot->variant_name = $variant->variant_name ?? '';
            $timeSlot->participant_equipment_id = $validated['participant_equipment_id'];
            $timeSlot->capacity_per_slot = $validated['capacity_per_slot'];
            $timeSlot->schedule_type = $validated['schedule_type'];
            $timeSlot->start_time = $validated['start_time'];
            $timeSlot->end_time = $validated['end_time'];
            $timeSlot->duration = $validated['duration'];
            $timeSlot->recurring = $validated['recurring'] ?? null;
            $timeSlot->lead_time_minutes = $validated['lead_time_minutes'] ?? null;
            $timeSlot->days_of_week = $validated['days_of_week'] ?? [];
            $timeSlot->discount_value = $validated['discount_value'] ?? null;
            $timeSlot->save();

            // Reload activity and mark Step 8 as complete
            $activity->refresh();
            $activity->update(['step8_scheduling_timeslots' => 1]);

            return back()->with('success', 'TimeSlot saved successfully.');
        } catch (\Exception $e) {
            \Log::error('TimeSlot save error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to save timeslot: ' . $e->getMessage());
        }
    }

    /**
     * Edit Scheduling TimeSlot
     */
    public function editTimeSlot(Request $request, $id, $timeslotId)
    {
        $activity = Activity::with(['variants', 'schedulingTimeSlots'])->findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $timeSlot = \App\Models\ActivitySchedulingTimeSlot::findOrFail($timeslotId);
        
        if ($timeSlot->activity_id !== $activity->id) {
            abort(403, 'Unauthorized action.');
        }

        $timeSlots = $activity->schedulingTimeSlots()->get();
        $variants = $activity->variants()->get();

        return view('operator.activity.step8_scheduling_timeslots', [
            'activity' => $activity,
            'timeSlots' => $timeSlots,
            'variants' => $variants,
            'editingTimeSlot' => $timeSlot,
        ]);
    }

    /**
     * Update Scheduling TimeSlot
     */
    public function updateTimeSlot(Request $request, $id, $timeslotId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'variant_id' => 'required|exists:activity_variants,variant_id',
            'participant_equipment_id' => 'required|in:Per Person,Per Equipment',
            'capacity_per_slot' => 'required|integer|min:1',
            'schedule_type' => 'required|in:Fixed Slots,Interval-Based,Open Booking,Group Events',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'duration' => 'required|string',
            'recurring' => 'nullable|integer|min:1',
            'lead_time_minutes' => 'nullable|integer|min:1',
            'days_of_week' => 'nullable|array',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        $validator->after(function ($validator) use ($request, $activity, $timeslotId) {
            $variantId = $request->input('variant_id');
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');

            $existingTimeSlots = \App\Models\ActivitySchedulingTimeSlot::where('activity_id', $activity->id)
                ->where('variant_id', $variantId)
                ->where('timeslot_id', '<>', $timeslotId)
                ->get();

            foreach ($existingTimeSlots as $slot) {
                if ($slot->start_time === $startTime && $slot->end_time === $endTime) {
                    $validator->errors()->add('start_time', 'An identical timeslot already exists for this variant.');
                    break;
                }

                if ($startTime < $slot->end_time && $endTime > $slot->start_time) {
                    $validator->errors()->add('start_time', 'This timeslot overlaps with an existing timeslot for the selected variant.');
                    break;
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        try {
            $timeSlot = \App\Models\ActivitySchedulingTimeSlot::findOrFail($timeslotId);
            
            // Verify ownership
            if ($timeSlot->activity_id !== $activity->id) {
                abort(403, 'Unauthorized action.');
            }

            $variant = \App\Models\ActivityVariant::findOrFail($validated['variant_id']);

            $timeSlot->variant_id = $validated['variant_id'];
            $timeSlot->variant_name = $variant->variant_name ?? '';
            $timeSlot->participant_equipment_id = $validated['participant_equipment_id'];
            $timeSlot->capacity_per_slot = $validated['capacity_per_slot'];
            $timeSlot->schedule_type = $validated['schedule_type'];
            $timeSlot->start_time = $validated['start_time'];
            $timeSlot->end_time = $validated['end_time'];
            $timeSlot->duration = $validated['duration'];
            $timeSlot->recurring = $validated['recurring'] ?? null;
            $timeSlot->lead_time_minutes = $validated['lead_time_minutes'] ?? null;
            $timeSlot->days_of_week = $validated['days_of_week'] ?? [];
            $timeSlot->discount_value = $validated['discount_value'] ?? null;
            $timeSlot->save();

            return back()->with('success', 'TimeSlot updated successfully.');
        } catch (\Exception $e) {
            \Log::error('TimeSlot update error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update timeslot: ' . $e->getMessage());
        }
    }

    /**
     * Delete Scheduling TimeSlot
     */
    public function deleteTimeSlot(Request $request, $id, $timeslotId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $timeSlot = \App\Models\ActivitySchedulingTimeSlot::findOrFail($timeslotId);
            
            if ($timeSlot->activity_id !== $activity->id) {
                abort(403, 'Unauthorized action.');
            }

            $timeSlot->delete();

            // Check if any timeslots remain
            $remainingTimeSlots = \App\Models\ActivitySchedulingTimeSlot::where('activity_id', $activity->id)->count();
            
            // If no timeslots left, mark Step 8 as incomplete
            if ($remainingTimeSlots === 0) {
                $activity->update(['step8_scheduling_timeslots' => 0]);
            }

            return back()->with('success', 'TimeSlot deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('TimeSlot delete error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete timeslot: ' . $e->getMessage());
        }
    }

    /**
     * Step 9: Show Rates
     */
    public function step9Rates($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check Step 8 is complete
        if (!$activity->step8_scheduling_timeslots) {
            return redirect()->route('operator.activity.step8.show', $activity->id)
                ->with('error', 'Please complete Step 8 first.');
        }

        // Get variants
        $variants = \App\Models\ActivityVariant::where('activity_id', $activity->id)->get();

        // Get existing rates
        $rates = \App\Models\ActivityRate::where('activity_id', $activity->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('operator.activity.step9_rates', [
            'activity' => $activity,
            'variants' => $variants,
            'rates' => $rates,
        ]);
    }

    /**
     * Store Rate
     */
    public function storeRate(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'variant_id' => 'required|exists:activity_variants,variant_id',
            'season' => 'nullable|string|max:100',
            'valid_from' => 'required|date|date_format:Y-m-d',
            'valid_to' => 'required|date|date_format:Y-m-d|after:valid_from',
            'rate_specificity' => 'required|in:Per Person,Per Equipment',
            'adult_rate' => 'nullable|required_if:rate_specificity,Per Person|numeric|min:0',
           // 'teen_rate' => 'nullable|required_if:rate_specificity,Per Person|numeric|min:0',
            'children_rate' => 'nullable|required_if:rate_specificity,Per Person|numeric|min:0',
            'infant_rate' => 'nullable|required_if:rate_specificity,Per Person|numeric|min:0',
            'equipment_rate' => 'nullable|required_if:rate_specificity,Per Equipment|numeric|min:0',
            'private_exclusive_rate' => 'nullable|numeric|min:0',
        ]);

        try {
            $variant = \App\Models\ActivityVariant::findOrFail($validated['variant_id']);

            $rate = new \App\Models\ActivityRate();
            $rate->service_id = (string) $activity->id;
            $rate->activity_id = $activity->id;
            $rate->variant_id = $validated['variant_id'];
            $rate->variant_name = $variant->variant_name ?? '';
            $rate->season = $validated['season'] ?? 'One Season';
            $rate->valid_from = $validated['valid_from'];
            $rate->valid_to = $validated['valid_to'];
            $rate->rate_specificity = $validated['rate_specificity'];
            $rate->adult_rate = $validated['adult_rate'] ?? null;
            //$rate->teen_rate = $validated['teen_rate'] ?? null;
            $rate->children_rate = $validated['children_rate'] ?? null;
            $rate->infant_rate = $validated['infant_rate'] ?? null;
            $rate->equipment_rate = $validated['equipment_rate'] ?? null;
            $rate->private_exclusive_rate = $validated['private_exclusive_rate'] ?? null;
            $rate->save();

            // Mark Step 9 as complete
            $activity->refresh();
            $activity->update(['step9_rates' => 1]);

            return back()->with('success', 'Rate saved successfully.');
        } catch (\Exception $e) {
            \Log::error('Rate save error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to save rate: ' . $e->getMessage());
        }
    }

    /**
     * Edit Rate
     */
    public function editRate($id, $rateId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $rate = \App\Models\ActivityRate::findOrFail($rateId);
        
        // Verify rate belongs to this activity
        if ($rate->activity_id !== $activity->id) {
            abort(403, 'Unauthorized action.');
        }

        $variants = \App\Models\ActivityVariant::where('activity_id', $activity->id)->get();
        $rates = \App\Models\ActivityRate::where('activity_id', $activity->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('operator.activity.step9_rates', [
            'activity' => $activity,
            'variants' => $variants,
            'rates' => $rates,
            'editingRate' => $rate,
        ]);
    }

    /**
     * Update Rate
     */
    public function updateRate(Request $request, $id, $rateId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'variant_id' => 'required|exists:activity_variants,variant_id',
            'season' => 'nullable|string|max:100',
            'valid_from' => 'required|date|date_format:Y-m-d',
            'valid_to' => 'required|date|date_format:Y-m-d|after:valid_from',
            'rate_specificity' => 'required|in:Per Person,Per Equipment',
            'adult_rate' => 'nullable|required_if:rate_specificity,Per Person|numeric|min:0',
            //'teen_rate' => 'nullable|required_if:rate_specificity,Per Person|numeric|min:0',
            'children_rate' => 'nullable|required_if:rate_specificity,Per Person|numeric|min:0',
            'infant_rate' => 'nullable|required_if:rate_specificity,Per Person|numeric|min:0',
            'equipment_rate' => 'nullable|required_if:rate_specificity,Per Equipment|numeric|min:0',
            'private_exclusive_rate' => 'nullable|numeric|min:0',
        ]);

        try {
            $rate = \App\Models\ActivityRate::findOrFail($rateId);
            
            // Verify rate belongs to this activity
            if ($rate->activity_id !== $activity->id) {
                abort(403, 'Unauthorized action.');
            }

            $variant = \App\Models\ActivityVariant::findOrFail($validated['variant_id']);

            $rate->variant_id = $validated['variant_id'];
            $rate->variant_name = $variant->variant_name ?? '';
            $rate->season = $validated['season'] ?? 'One Season';
            $rate->valid_from = $validated['valid_from'];
            $rate->valid_to = $validated['valid_to'];
            $rate->rate_specificity = $validated['rate_specificity'];
            $rate->adult_rate = $validated['adult_rate'] ?? null;
            //$rate->teen_rate = $validated['teen_rate'] ?? null;
            $rate->children_rate = $validated['children_rate'] ?? null;
            $rate->infant_rate = $validated['infant_rate'] ?? null;
            $rate->equipment_rate = $validated['equipment_rate'] ?? null;
            $rate->private_exclusive_rate = $validated['private_exclusive_rate'] ?? null;
            $rate->save();

            return back()->with('success', 'Rate updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Rate update error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update rate: ' . $e->getMessage());
        }
    }

    /**
     * Delete Rate
     */
    public function deleteRate(Request $request, $id, $rateId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $rate = \App\Models\ActivityRate::findOrFail($rateId);
            
            if ($rate->activity_id !== $activity->id) {
                abort(403, 'Unauthorized action.');
            }

            $rate->delete();

            // Check if any rates remain
            $remainingRates = \App\Models\ActivityRate::where('activity_id', $activity->id)->count();
            
            // If no rates left, mark Step 9 as incomplete
            if ($remainingRates === 0) {
                $activity->update(['step9_rates' => 0]);
            }

            return back()->with('success', 'Rate deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Rate delete error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete rate: ' . $e->getMessage());
        }
    }

    /**
     * Show Fees & Add-Ons Management
     */
    public function step9Addons($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $variants = \App\Models\ActivityVariant::where('activity_id', $activity->id)->get();
        $addons = \App\Models\ActivityAddon::where('activity_id', $activity->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('operator.activity.step9_addons', [
            'activity' => $activity,
            'variants' => $variants,
            'addons' => $addons,
        ]);
    }

    /**
     * Store Add-On
     */
    public function storeAddon(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'addon_name' => 'required|string|max:255',
            'pricing_type' => 'required|in:Per Person,Per Booking',
            'price' => 'required|numeric|min:0',
            'addon_type' => 'required|in:Optional,Compulsory',
            'variant_id' => 'nullable|exists:activity_variants,variant_id',
            'availability_rules' => 'nullable|string|max:1000',
        ]);

        try {
            $variantName = null;
            if (!empty($validated['variant_id'])) {
                $variant = \App\Models\ActivityVariant::find($validated['variant_id']);
                $variantName = $variant ? $variant->variant_name : null;
            }

            $addon = new \App\Models\ActivityAddon();
            $addon->service_id = (string) $activity->id;
            $addon->activity_id = $activity->id;
            $addon->addon_name = $validated['addon_name'];
            $addon->pricing_type = $validated['pricing_type'];
            $addon->price = $validated['price'];
            $addon->addon_type = $validated['addon_type'];
            $addon->variant_id = $validated['variant_id'] ?? null;
            $addon->variant_name = $variantName;
            $addon->availability_rules = $validated['availability_rules'] ?? null;
            $addon->save();

            return back()->with('success', 'Add-on created successfully.');
        } catch (\Exception $e) {
            \Log::error('Add-on save error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to save add-on: ' . $e->getMessage());
        }
    }

    /**
     * Update Add-On
     */
    public function updateAddon(Request $request, $id, $addonId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'addon_name' => 'required|string|max:255',
            'pricing_type' => 'required|in:Per Person,Per Booking',
            'price' => 'required|numeric|min:0',
            'addon_type' => 'required|in:Optional,Compulsory',
            'variant_id' => 'nullable|exists:activity_variants,variant_id',
            'availability_rules' => 'nullable|string|max:1000',
        ]);

        try {
            $addon = \App\Models\ActivityAddon::findOrFail($addonId);
            
            // Verify addon belongs to this activity
            if ($addon->activity_id !== $activity->id) {
                abort(403, 'Unauthorized action.');
            }

            $variantName = null;
            if (!empty($validated['variant_id'])) {
                $variant = \App\Models\ActivityVariant::find($validated['variant_id']);
                $variantName = $variant ? $variant->variant_name : null;
            }

            $addon->addon_name = $validated['addon_name'];
            $addon->pricing_type = $validated['pricing_type'];
            $addon->price = $validated['price'];
            $addon->addon_type = $validated['addon_type'];
            $addon->variant_id = $validated['variant_id'] ?? null;
            $addon->variant_name = $variantName;
            $addon->availability_rules = $validated['availability_rules'] ?? null;
            $addon->save();

            return back()->with('success', 'Add-on updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Add-on update error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update add-on: ' . $e->getMessage());
        }
    }

    /**
     * Delete Add-On
     */
    public function deleteAddon(Request $request, $id, $addonId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $addon = \App\Models\ActivityAddon::findOrFail($addonId);
            
            if ($addon->activity_id !== $activity->id) {
                abort(403, 'Unauthorized action.');
            }

            $addon->delete();

            return back()->with('success', 'Add-on deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Add-on delete error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete add-on: ' . $e->getMessage());
        }
    }

    /**
     * Step 10: Show Allotment
     */
    public function step10Allotment($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check Step 9 is complete
        if (!isset($activity->step9_rates) || !$activity->step9_rates) {
            return redirect()->route('operator.activity.step9.show', $activity->id)
                ->with('error', 'Please complete Step 9 first.');
        }

        $variants = \App\Models\ActivityVariant::where('activity_id', $activity->id)->get();
        $timeSlots = \App\Models\ActivitySchedulingTimeSlot::where('activity_id', $activity->id)->get();

        $allotments = \App\Models\ActivityAllotment::where('activity_id', $activity->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $blackouts = \App\Models\ActivityBlackoutDate::where('activity_id', $activity->id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('operator.activity.step10_allotment', [
            'activity' => $activity,
            'variants' => $variants,
            'timeSlots' => $timeSlots,
            'allotments' => $allotments,
            'blackouts' => $blackouts,
        ]);
    }

    /**
     * Store Allotment
     */
    public function storeAllotment(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'variant_id' => 'required|exists:activity_variants,variant_id',
            'participant_equipment_id' => 'required|in:Per Person,Per Equipment',
            'allotment_strategy' => 'required|in:Per Slot,Daily Cap,Equipment-based',
            'slot_times' => 'nullable|required_if:allotment_strategy,Per Slot|array',
            'slot_times.*' => 'string|max:100',
            'allotment' => 'required|integer|min:0',
            'calendar_enabled' => 'required|in:Yes,No',
            'calendar_start' => 'nullable|required_if:calendar_enabled,Yes|date|date_format:Y-m-d',
            'calendar_end' => 'nullable|required_if:calendar_enabled,Yes|date|date_format:Y-m-d|after_or_equal:calendar_start',
            'season' => 'nullable|string|max:100',
        ]);

        try {
            $variant = \App\Models\ActivityVariant::findOrFail($validated['variant_id']);

            $allotment = new \App\Models\ActivityAllotment();
            $allotment->service_id = (string) $activity->id;
            $allotment->activity_id = $activity->id;
            $allotment->service_name = $activity->activity_name;
            $allotment->variant_id = $validated['variant_id'];
            $allotment->variant_name = $variant->variant_name ?? '';
            $allotment->participant_equipment_id = $validated['participant_equipment_id'];
            $allotment->allotment_strategy = $validated['allotment_strategy'];
            $allotment->slot_times = $validated['slot_times'] ?? null;
            $allotment->inventory_date = now()->toDateString();
            $allotment->allotment = $validated['allotment'];
            $allotment->calendar_enabled = $validated['calendar_enabled'] === 'Yes';
            $allotment->calendar_start = $validated['calendar_start'] ?? null;
            $allotment->calendar_end = $validated['calendar_end'] ?? null;
            $allotment->season = $validated['season'] ?? null;
            $allotment->save();

            $activity->refresh();
            $activity->update(['step10_allotment' => 1]);

            return back()->with('success', 'Allotment saved successfully.');
        } catch (\Exception $e) {
            \Log::error('Allotment save error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to save allotment: ' . $e->getMessage());
        }
    }

    /**
     * Update Allotment
     */
    public function updateAllotment(Request $request, $id, $allotmentId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'variant_id' => 'required|exists:activity_variants,variant_id',
            'participant_equipment_id' => 'required|in:Per Person,Per Equipment',
            'allotment_strategy' => 'required|in:Per Slot,Daily Cap,Equipment-based',
            'slot_times' => 'nullable|required_if:allotment_strategy,Per Slot|array',
            'slot_times.*' => 'string|max:100',
            'allotment' => 'required|integer|min:0',
            'calendar_enabled' => 'required|in:Yes,No',
            'calendar_start' => 'nullable|required_if:calendar_enabled,Yes|date|date_format:Y-m-d',
            'calendar_end' => 'nullable|required_if:calendar_enabled,Yes|date|date_format:Y-m-d|after_or_equal:calendar_start',
            'season' => 'nullable|string|max:100',
        ]);

        try {
            $allotment = \App\Models\ActivityAllotment::findOrFail($allotmentId);

            if ($allotment->activity_id !== $activity->id) {
                abort(403, 'Unauthorized action.');
            }

            $variant = \App\Models\ActivityVariant::findOrFail($validated['variant_id']);

            $allotment->variant_id = $validated['variant_id'];
            $allotment->variant_name = $variant->variant_name ?? '';
            $allotment->participant_equipment_id = $validated['participant_equipment_id'];
            $allotment->allotment_strategy = $validated['allotment_strategy'];
            $allotment->slot_times = $validated['slot_times'] ?? null;
            $allotment->inventory_date = now()->toDateString();
            $allotment->allotment = $validated['allotment'];
            $allotment->calendar_enabled = $validated['calendar_enabled'] === 'Yes';
            $allotment->calendar_start = $validated['calendar_start'] ?? null;
            $allotment->calendar_end = $validated['calendar_end'] ?? null;
            $allotment->season = $validated['season'] ?? null;
            $allotment->save();

            return back()->with('success', 'Allotment updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Allotment update error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update allotment: ' . $e->getMessage());
        }
    }

    /**
     * Delete Allotment
     */
    public function deleteAllotment(Request $request, $id, $allotmentId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $allotment = \App\Models\ActivityAllotment::findOrFail($allotmentId);

            if ($allotment->activity_id !== $activity->id) {
                abort(403, 'Unauthorized action.');
            }

            $allotment->delete();

            $remainingAllotments = \App\Models\ActivityAllotment::where('activity_id', $activity->id)->count();
            if ($remainingAllotments === 0) {
                $activity->update(['step10_allotment' => 0]);
            }

            return back()->with('success', 'Allotment deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Allotment delete error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete allotment: ' . $e->getMessage());
        }
    }

    /**
     * Store Blackout Date
     */
    public function storeBlackoutDate(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'variant_id' => 'nullable|exists:activity_variants,variant_id',
            'season' => 'nullable|string|max:100',
            'blackout_dates' => 'required|string', // Comma-separated dates
        ]);

        try {
            $dateString = $validated['blackout_dates'];
            if (empty($dateString)) {
                return back()->with('error', 'Please select at least one date.');
            }

            $selectedDates = array_filter(explode(',', $dateString));
            if (empty($selectedDates)) {
                return back()->with('error', 'Please select at least one date.');
            }

            sort($selectedDates);

            // Group consecutive dates into ranges and create blackout records
            $ranges = $this->groupConsecutiveDates($selectedDates);
            
            foreach ($ranges as $range) {
                $blackout = new \App\Models\ActivityBlackoutDate();
                $blackout->activity_id = $activity->id;
                $blackout->variant_id = $validated['variant_id'] ?? null;
                $blackout->season = $validated['season'] ?? null;
                $blackout->start_date = $range['start'];
                $blackout->end_date = $range['end'];
                $blackout->save();
            }

            return back()->with('success', 'Blackout dates saved successfully.');
        } catch (\Exception $e) {
            \Log::error('Blackout save error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to save blackout dates: ' . $e->getMessage());
        }
    }

    /**
     * Group consecutive dates into ranges
     * @param array $dates Array of date strings in format Y-m-d
     * @return array Array of ranges with 'start' and 'end' keys
     */
    private function groupConsecutiveDates($dates)
    {
        $ranges = [];
        $currentRange = ['start' => $dates[0], 'end' => $dates[0]];

        for ($i = 1; $i < count($dates); $i++) {
            $currentDate = new \DateTime($dates[$i]);
            $lastDate = new \DateTime($currentRange['end']);
            $lastDate->modify('+1 day');

            if ($lastDate->format('Y-m-d') === $dates[$i]) {
                $currentRange['end'] = $dates[$i];
            } else {
                $ranges[] = $currentRange;
                $currentRange = ['start' => $dates[$i], 'end' => $dates[$i]];
            }
        }
        $ranges[] = $currentRange;
        return $ranges;
    }

    /**
     * Delete Blackout Date
     */
    public function deleteBlackoutDate(Request $request, $id, $blackoutId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $blackout = \App\Models\ActivityBlackoutDate::findOrFail($blackoutId);

            if ($blackout->activity_id !== $activity->id) {
                abort(403, 'Unauthorized action.');
            }

            $blackout->delete();

            return back()->with('success', 'Blackout date deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Blackout delete error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete blackout date: ' . $e->getMessage());
        }
    }

    /**
     * Show Step 11: Promotions & Offers
     */
    public function step11PromotionsOffers($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if Step 10 is complete
        if (!$activity->step10_allotment) {
            return redirect()->route('operator.activity.step10.show', $activity->id)
                ->with('error', 'Please complete Step 10: Allotment first.');
        }

        $variants = ActivityVariant::where('activity_id', $activity->id)->get();
        $promotions = ActivityPromotion::where('activity_id', $activity->id)->get();
        
        // Create variant map
        $variantMap = [];
        foreach ($variants as $variant) {
            $variantMap[$variant->variant_id] = $variant->variant_name;
        }

        return view('operator.activity.step11_promotions_offers', compact('activity', 'variants', 'promotions', 'variantMap'));
    }

    /**
     * Store Promotion
     */
    public function storePromotion(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'variant_ids' => 'required|array|min:1',
            'variant_ids.*' => 'exists:activity_variants,variant_id',
            'campaign_name' => 'required|string|max:255',
            'campaign_description' => 'nullable|string|max:250',
            'specifications' => 'required|string',
            'inclusions' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'discount_type' => 'required|in:Amount,Percentage',
            'discount_value' => 'required|numeric|min:0.01',
            'promo_valid_from' => 'required|date|date_format:Y-m-d',
            'promo_valid_to' => 'required|date|date_format:Y-m-d|after_or_equal:promo_valid_from',
            'non_refundable' => 'required|in:Yes,No',
            'approval_status' => 'required|in:Draft,Pending Approval,Published',
        ]);

        try {
            $promotion = new \App\Models\ActivityPromotion();
            $promotion->activity_id = $activity->id;
            $promotion->service_id = $activity->id;
            $promotion->campaign_id = \App\Models\ActivityPromotion::generateCampaignId($activity->id);
            $promotion->campaign_name = $validated['campaign_name'];
            $promotion->campaign_description = $validated['campaign_description'] ?? null;
            $promotion->specifications = $validated['specifications'];
            $promotion->inclusions = $validated['inclusions'] ?? null;
            $promotion->exclusions = $validated['exclusions'] ?? null;
            $promotion->discount_type = $validated['discount_type'];
            $promotion->discount_value = $validated['discount_value'];
            $promotion->promo_valid_from = $validated['promo_valid_from'];
            $promotion->promo_valid_to = $validated['promo_valid_to'];
            $promotion->non_refundable = $validated['non_refundable'];
            $promotion->approval_status = $validated['approval_status'];
            $promotion->variant_ids = $validated['variant_ids'];
            $promotion->save();

            // Mark step 11 as complete
            $activity->update(['step11_promotions_offers' => 1]);

            return back()->with('success', 'Promotion created successfully. Campaign ID: ' . $promotion->campaign_id);
        } catch (\Exception $e) {
            \Log::error('Promotion save error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to save promotion: ' . $e->getMessage());
        }
    }

    /**
     * Update Promotion
     */
    public function updatePromotion(Request $request, $id, $promotionId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'variant_ids' => 'required|array|min:1',
            'variant_ids.*' => 'exists:activity_variants,variant_id',
            'campaign_name' => 'required|string|max:255',
            'campaign_description' => 'nullable|string|max:250',
            'specifications' => 'required|string',
            'inclusions' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'discount_type' => 'required|in:Amount,Percentage',
            'discount_value' => 'required|numeric|min:0.01',
            'promo_valid_from' => 'required|date|date_format:Y-m-d',
            'promo_valid_to' => 'required|date|date_format:Y-m-d|after_or_equal:promo_valid_from',
            'non_refundable' => 'required|in:Yes,No',
            'approval_status' => 'required|in:Draft,Pending Approval,Published',
        ]);

        try {
            $promotion = \App\Models\ActivityPromotion::findOrFail($promotionId);

            if ($promotion->activity_id !== $activity->id) {
                abort(403, 'Unauthorized action.');
            }

            $promotion->campaign_name = $validated['campaign_name'];
            $promotion->campaign_description = $validated['campaign_description'] ?? null;
            $promotion->specifications = $validated['specifications'];
            $promotion->inclusions = $validated['inclusions'] ?? null;
            $promotion->exclusions = $validated['exclusions'] ?? null;
            $promotion->discount_type = $validated['discount_type'];
            $promotion->discount_value = $validated['discount_value'];
            $promotion->promo_valid_from = $validated['promo_valid_from'];
            $promotion->promo_valid_to = $validated['promo_valid_to'];
            $promotion->non_refundable = $validated['non_refundable'];
            $promotion->approval_status = $validated['approval_status'];
            $promotion->variant_ids = $validated['variant_ids'];
            $promotion->save();

            // Ensure step 11 is marked as complete
            if (!$activity->step11_promotions_offers) {
                $activity->update(['step11_promotions_offers' => 1]);
            }

            return back()->with('success', 'Promotion updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Promotion update error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update promotion: ' . $e->getMessage());
        }
    }

    /**
     * Delete Promotion
     */
    public function deletePromotion(Request $request, $id, $promotionId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $promotion = \App\Models\ActivityPromotion::findOrFail($promotionId);

            if ($promotion->activity_id !== $activity->id) {
                abort(403, 'Unauthorized action.');
            }

            $promotion->delete();

            // Check if any promotions remain, if not, mark step as incomplete
            if ($activity->promotions()->count() === 0) {
                $activity->update(['step11_promotions_offers' => 0]);
            }

            return back()->with('success', 'Promotion deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Promotion delete error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete promotion: ' . $e->getMessage());
        }
    }

    /**
     * Show Step 12: SEO & Social
     */
    public function step12SeoSocial($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if Step 11 is complete
        if (!$activity->step11_promotions_offers) {
            return redirect()->route('operator.activity.step11.show', $activity->id)
                ->with('error', 'Please complete Step 11: Promotions & Offers first.');
        }

        $seoSocial = $activity->seoSocial;

        // Get keywords as array or empty array
        $keywords = $seoSocial ? ($seoSocial->keywords_tags ?? []) : [];

        return view('operator.activity.step12_seo_social', compact('activity', 'seoSocial', 'keywords'));
    }

    /**
     * Store SEO & Social
     */
    public function storeSeoSocial(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'short_description' => 'required|string|max:500',
            'full_description' => 'required|string',
            'highlights' => 'nullable|string',
            'seo_title' => 'nullable|string|max:500',
            'seo_description' => 'nullable|string|max:500',
            'keywords_tags' => 'nullable|array',
            'keywords_tags.*' => 'string|max:500',
            'og_title' => 'nullable|string|max:500',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $ogImagePath = null;
            
            // Handle OG Image upload
            if ($request->hasFile('og_image')) {
                $file = $request->file('og_image');
                $filename = 'og-image-' . $activity->id . '-' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('activities/seo', $filename, 'public');
                $ogImagePath = $path;
            }

            // Create or update SEO/Social record
            $seoSocial = $activity->seoSocial ?? new \App\Models\ActivitySeoSocial();
            $seoSocial->activity_id = $activity->id;
            $seoSocial->service_id = $activity->id;
            $seoSocial->short_description = $validated['short_description'];
            $seoSocial->full_description = $validated['full_description'];
            $seoSocial->highlights = $validated['highlights'] ?? null;
            $seoSocial->seo_title = $validated['seo_title'] ?? null;
            $seoSocial->seo_description = $validated['seo_description'] ?? null;
            $seoSocial->keywords_tags = $validated['keywords_tags'] ?? null;
            $seoSocial->og_title = $validated['og_title'] ?? null;
            $seoSocial->og_description = $validated['og_description'] ?? null;
            
            if ($ogImagePath) {
                $seoSocial->og_image_path = $ogImagePath;
            }
            
            $seoSocial->save();

            // Mark step as complete
            $activity->update(['step12_seo_social' => 1]);

            return back()->with('success', 'SEO & Social information saved successfully.');
        } catch (\Exception $e) {
            \Log::error('SEO Social save error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to save SEO & Social information: ' . $e->getMessage());
        }
    }

    /**
     * Show Step 13: Publish
     */
    public function step13Publish($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if Step 12 is complete
        if (!$activity->step12_seo_social) {
            return redirect()->route('operator.activity.step12.show', $activity->id)
                ->with('error', 'Please complete Step 12: SEO & Social first.');
        }

        return view('operator.activity.step13_publish', compact('activity'));
    }

    /**
     * Submit for Approval
     */
    public function submitForApproval(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        // Verify all steps are complete
        $requiredSteps = [
            'step1_basic', 'step2_management_communication', 'step3_photos_media',
            'step4_legal_compliance', 'step5_accounting_transaction', 'step6_policies_rules',
            'step7_variants_equipment', 'step8_scheduling_timeslots', 'step9_rates',
            'step10_allotment', 'step11_promotions_offers', 'step12_seo_social'
        ];

        $incompleteSteps = [];
        foreach ($requiredSteps as $step) {
            if (!$activity->$step) {
                $incompleteSteps[] = str_replace('_', ' ', ucwords(str_replace('step', 'Step ', $step)));
            }
        }

        if (!empty($incompleteSteps)) {
            return back()->with('error', 'Please complete all steps before submitting for approval. Incomplete: ' . implode(', ', $incompleteSteps));
        }

        try {
            $activity->update([
                'approval_status' => 'Pending',
                'submitted_for_approval_at' => now(),
                'step13_publish' => 1
            ]);

            // TODO: Send notification to admin
            // Mail::to(config('mail.admin_email'))->send(new ActivityApprovalRequested($activity));

            return back()->with('success', 'Activity submitted for approval successfully! You will be notified once it is reviewed.');
        } catch (\Exception $e) {
            \Log::error('Submit for approval error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to submit for approval: ' . $e->getMessage());
        }
    }

    /**
     * Show booking listing for operator's activities
     */
    public function bookingList(Request $request)
    {
        $operator = auth()->user();

        // Get all activities for this operator
        $activityIds = \App\Models\Activity::where('operator_id', $operator->id)
            ->pluck('id');

        // Get bookings for these activities
        $bookings = \App\Models\ActivityBooking::whereIn('activity_id', $activityIds)
            ->with(['activity', 'guests'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('operator.activity.booking_list', compact('bookings'));
    }

    /**
     * Show booking details for a specific booking
     */
    public function bookingDetails($bookingId)
    {
        $operator = auth()->user();

        // Get all activities for this operator
        $activityIds = \App\Models\Activity::where('operator_id', $operator->id)
            ->pluck('id');

        // Get the booking with relationships
        $booking = \App\Models\ActivityBooking::whereIn('activity_id', $activityIds)
            ->where('id', $bookingId)
            ->with(['activity', 'guests'])
            ->firstOrFail();

        return view('operator.activity.booking_details', compact('booking'));
    }

    /**
     * Update booking status for activity bookings
     */
    public function updateBookingStatus(Request $request, $bookingId)
    {
        $operator = auth()->user();

        $activityIds = Activity::where('operator_id', $operator->id)
            ->pluck('id');

        $booking = \App\Models\ActivityBooking::whereIn('activity_id', $activityIds)
            ->where('id', $bookingId)
            ->firstOrFail();

        $request->validate([
            'booking_status' => 'required|in:Confirmed,Cancelled',
        ]);

        if ($booking->booking_status === 'Cancelled') {
            return back()->with('error', 'Cancelled bookings cannot be updated.');
        }

        $booking->booking_status = $request->input('booking_status');
        $booking->save();

        (new OperatorBookingNotificationService())->notifyBookingStatusChanged(
            $booking,
            'activity',
            $booking->booking_status
        );

        return back()->with('success', 'Booking status updated to ' . $booking->booking_status . '.');
    }
}
