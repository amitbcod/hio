<?php

namespace App\Http\Controllers\Operator;

use App\Models\Activity;
use App\Models\Region;
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
            'regions' => Region::orderBy('name')->pluck('name', 'id')->all(),
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
            'activity_name_fr' => 'nullable|string|max:120',
            'short_title' => 'nullable|string|max:60',
            'short_title_fr' => 'nullable|string|max:60',
            'team_categories' => 'required|array|min:1',
            'team_categories.*' => 'in:' . implode(',', Activity::TEAM_CATEGORIES),
            'physical_level' => 'required|in:' . implode(',', Activity::PHYSICAL_LEVELS),
            'price_range' => 'required|in:' . implode(',', Activity::PRICE_RANGES),
            'primary_themes' => 'nullable|array',
            'primary_themes.*' => 'in:' . implode(',', Activity::PRIMARY_THEMES),
            'destination' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'regions' => 'nullable|string|max:255',
            'town' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'meeting_point_details' => 'required|string|min:10',
            'overview' => 'required|string|min:20',
            'overview_fr' => 'nullable|string',
            'whats_included' => 'nullable|string',
            'whats_included_fr' => 'nullable|string',
            'itinerary' => 'required|string|min:20',
            'itinerary_fr' => 'nullable|string',
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
            $activity->activity_name_fr = $data['activity_name_fr'] ?? null;
            $activity->short_title = $data['short_title'] ?? null;
            $activity->short_title_fr = $data['short_title_fr'] ?? null;
            $activity->team_categories = $data['team_categories'];
            $activity->physical_level = $data['physical_level'];
            $activity->price_range = $data['price_range'];
            $activity->primary_themes = $data['primary_themes'] ?? null;
            $activity->destination = $data['destination'] ?? null;
            $activity->address = $data['region'] ?? null;
            $regionsId = null;
            if (!empty($data['regions'])) {
                $regionsId = is_numeric($data['regions']) ? (int) $data['regions'] : null;
            } elseif (!empty($data['region'])) {
                $found = Region::where('name', trim($data['region']))->first();
                $regionsId = $found ? (int) $found->id : null;
            }
            $activity->regions = $regionsId;
            $activity->town = $data['town'] ?? null;
            $activity->latitude = $data['latitude'];
            $activity->longitude = $data['longitude'];
            $activity->meeting_point_details = $data['meeting_point_details'];
            $activity->overview = $data['overview'];
            $activity->overview_fr = $data['overview_fr'] ?? null;
            $activity->whats_included = $data['whats_included'] ?? null;
            $activity->whats_included_fr = $data['whats_included_fr'] ?? null;
            $activity->itinerary = $data['itinerary'];
            $activity->itinerary_fr = $data['itinerary_fr'] ?? null;
            $activity->duration = $data['duration'];
            $activity->suitable_for_age = $data['suitable_for_age'] ?? null;
            $activity->languages_offered = $data['languages_offered'] ?? null;
            $activity->booking_confirmation_type = $data['booking_confirmation_type'];
            $activity->add_ons_available = filter_var($data['add_ons_available'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            $activity->private_exclusive_option = filter_var($data['private_exclusive_option'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            $activity->allow_adults = isset($data['allow_adults']) && $data['allow_adults'] ? true : false;
            $activity->allow_children = isset($data['allow_children']) && $data['allow_children'] ? true : false;
            $activity->allow_infants = isset($data['allow_infants']) && $data['allow_infants'] ? true : false;

            $activity->save();
            $activity->completeStep('step1_basic');

            \Log::info('Activity Step 1 saved', ['activity_id' => $activity->id, 'operator_id' => $operator->id, 'regions_saved' => $activity->regions]);

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
            'reservation_contact_name' => 'required|string|max:255',
            'reservation_contact_email' => 'required|email|max:255',
            'reservation_contact_phone' => 'required|string|max:50',
            'reservation_contact_mobile' => 'required|string|max:50',
            'accounting_contact_name' => 'nullable|string|max:255',
            'accounting_contact_email' => 'nullable|email|max:255',
            'accounting_contact_phone' => 'nullable|string|max:50',
            'accounting_contact_mobile' => 'nullable|string|max:50',
            'management_contact_name' => 'required|string|max:255',
            'management_contact_email' => 'required|email|max:255',
            'management_contact_phone' => 'required|string|max:50',
            'management_contact_mobile' => 'required|string|max:50',
            'operational_manager_name' => 'nullable|string|max:255',
            'operational_manager_phone' => 'nullable|string|max:50',
            'booking_confirmation_type' => 'required|in:Instant,On Request',
        ]);

        try {
            $activity->reservation_contact_name = $data['reservation_contact_name'];
            $activity->reservation_contact_email = $data['reservation_contact_email'];
            $activity->reservation_contact_phone = $data['reservation_contact_phone'];
            $activity->reservation_contact_mobile = $data['reservation_contact_mobile'];
            $activity->accounting_contact_name = $data['accounting_contact_name'] ?? null;
            $activity->accounting_contact_email = $data['accounting_contact_email'] ?? null;
            $activity->accounting_contact_phone = $data['accounting_contact_phone'] ?? null;
            $activity->accounting_contact_mobile = $data['accounting_contact_mobile'] ?? null;
            $activity->management_contact_name = $data['management_contact_name'];
            $activity->management_contact_email = $data['management_contact_email'];
            $activity->management_contact_phone = $data['management_contact_phone'];
            $activity->management_contact_mobile = $data['management_contact_mobile'];
            $activity->operational_manager_name = $data['operational_manager_name'] ?? null;
            $activity->operational_manager_phone = $data['operational_manager_phone'] ?? null;
            $activity->booking_confirmation_type = $data['booking_confirmation_type'];

            if (!$activity->booking_registration_type) {
                $activity->booking_registration_type = $operator->agreement_type ?? 'Listing Only';
            }

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

    // ... other methods (photos, media, steps 4-9 etc.) are intentionally left as-is in original file

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

            // Handle logo
            if ($request->hasFile('logo')) {
                $activity->logo = $request->file('logo')->store('activities/logo', 'public');
            }

            // Handle vehicle types/images grouping if present
            if ($request->filled('vehicle_types')) {
                $vehicleTypes = $request->input('vehicle_types', []);
                $vehicleImages = [];
                if ($request->hasFile('vehicle_images')) {
                    foreach ($request->file('vehicle_images') as $img) {
                        $vehicleImages[] = $img->store('activities/vehicles', 'public');
                    }
                }
                // store as combined structure if lengths match
                $grouped = [];
                foreach ($vehicleTypes as $idx => $type) {
                    $grouped[] = ['type' => $type, 'image' => $vehicleImages[$idx] ?? null];
                }
                $activity->vehicle_details = $grouped;
            }

            // Handle video
            if ($request->hasFile('video')) {
                $activity->video = $request->file('video')->store('activities/video', 'public');
            }

            $activity->save();
            $activity->completeStep('step3_photos_media');

            return redirect()->route('operator.activity.show', $activity->id)
                ->with('success', 'Activity Step 3: Photos & Media saved successfully!');
        } catch (\Exception $e) {
            \Log::error('saveStep3PhotosMedia error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('operator.activity.step3.show', $activity->id)
                ->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    /**
     * Step 9: Show Rates
     */
    /**
     * Show Step 7: Variants & Equipment
     */
    public function step7VariantsEquipment($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

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

            if ($activity->operator_id !== $operator->id) {
                abort(403, 'Unauthorized action.');
            }

            $request->validate([
                'variant_name' => 'required|string|max:255',
                'variant_name_fr' => 'nullable|string|max:255',
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

            $variant = new \App\Models\ActivityVariant();
            $variant->activity_id = $activity->id;
            $variant->service_id = $activity->service_id;
            $variant->variant_equipment_id = \App\Models\ActivityVariant::generateVariantEquipmentId($activity->id);
            $variant->variant_name = $request->input('variant_name');
            $variant->variant_name_fr = $request->input('variant_name_fr');
            $variant->quality_tier = $request->input('quality_tier');
            $variant->max_pax = $request->input('max_pax');
            $variant->min_participants = $request->input('min_participants');
            $variant->max_participants = $request->input('max_participants');
            $variant->allotment = $request->input('allotment');
            $variant->amenities = $request->input('amenities', []);
            $variant->safety_equipment = $request->input('safety_equipment', []);
            $variant->private_exclusive = $request->input('private_exclusive');

            if ($request->hasFile('equipment_image')) {
                $file = $request->file('equipment_image');
                $filename = time() . '_variant_' . $file->getClientOriginalName();
                $path = $file->storeAs('activity_variants', $filename, 'public');
                $variant->equipment_image = $path;
            }

            $variant->save();

            if (!$activity->step7_variants_equipment) {
                $activity->step7_variants_equipment = 1;
                if ($activity->step1_basic && $activity->step2_management_communication && $activity->step3_photos_media && $activity->step4_legal_compliance && $activity->step5_accounting_transaction && $activity->step6_policies_rules && $activity->step7_variants_equipment) {
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
    public function editVariant(Request $request, $id, $variantId)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $variant = \App\Models\ActivityVariant::findOrFail($variantId);
        $variants = $activity->variants;
        $operationsStaffing = $activity->operationsStaffing ?? new \App\Models\ActivityOperationsStaffing();
        $operationsRecords = \App\Models\ActivityOperationsStaffing::where('activity_id', $activity->id)->with('variant')->get();

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

            if ($activity->operator_id !== $operator->id) {
                abort(403, 'Unauthorized action.');
            }

            $variant = \App\Models\ActivityVariant::findOrFail($variantId);

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

            $variant->variant_name = $request->input('variant_name');
            $variant->variant_name_fr = $request->input('variant_name_fr');
            $variant->quality_tier = $request->input('quality_tier');
            $variant->max_pax = $request->input('max_pax');
            $variant->min_participants = $request->input('min_participants');
            $variant->max_participants = $request->input('max_participants');
            $variant->allotment = $request->input('allotment');
            $variant->amenities = $request->input('amenities', []);
            $variant->safety_equipment = $request->input('safety_equipment', []);
            $variant->private_exclusive = $request->input('private_exclusive');

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
     * Step 4: Legal & Compliance
     */
    public function step4LegalCompliance($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        if ($activity->operator_id !== $operator->id) {
            abort(403);
        }

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

            if ($activity->operator_id !== $operator->id) {
                abort(403);
            }

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

            $compliance = $activity->compliance;
            if (!$compliance) {
                $compliance = new \App\Models\ActivityCompliance();
                $compliance->activity_id = $activity->id;
                $compliance->compliance_id = \App\Models\ActivityCompliance::generateComplianceId();
            }

            $compliance->parent_service_id = $request->input('parent_service_id');
            $compliance->business_registration_number = $request->input('business_registration_number');
            $compliance->tourism_activity_permit = $request->input('tourism_activity_permit');
            $compliance->public_liability_insurance = $request->input('public_liability_insurance');
            $compliance->insurance_expiration = $request->input('insurance_expiration');
            $compliance->equipment_registration_serial = $request->input('equipment_registration_serial');

            if ($request->hasFile('tourism_permit_file')) {
                $file = $request->file('tourism_permit_file');
                $filename = time() . '_permit_' . $file->getClientOriginalName();
                $path = $file->storeAs('activities/compliance/permits', $filename, 'public');
                $compliance->tourism_permit_file = $path;
            }

            if ($request->hasFile('insurance_file')) {
                $file = $request->file('insurance_file');
                $filename = time() . '_insurance_' . $file->getClientOriginalName();
                $path = $file->storeAs('activities/compliance/insurance', $filename, 'public');
                $compliance->insurance_file = $path;
            }

            if ($request->hasFile('operational_assessment_doc')) {
                $file = $request->file('operational_assessment_doc');
                $filename = time() . '_assessment_' . $file->getClientOriginalName();
                $path = $file->storeAs('activities/compliance/assessments', $filename, 'public');
                $compliance->operational_assessment_doc = $path;
            }

            if ($request->hasFile('emergency_plan_doc')) {
                $file = $request->file('emergency_plan_doc');
                $filename = time() . '_emergency_' . $file->getClientOriginalName();
                $path = $file->storeAs('activities/compliance/emergency', $filename, 'public');
                $compliance->emergency_plan_doc = $path;
            }

            if ($request->hasFile('equipment_compliance_doc')) {
                $file = $request->file('equipment_compliance_doc');
                $filename = time() . '_equipment_' . $file->getClientOriginalName();
                $path = $file->storeAs('activities/compliance/equipment', $filename, 'public');
                $compliance->equipment_compliance_doc = $path;
            }

            if ($request->hasFile('permits_authorisations')) {
                $permitFiles = [];
                foreach ($request->file('permits_authorisations') as $file) {
                    $filename = time() . '_auth_' . uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('activities/compliance/permits', $filename, 'public');
                    $permitFiles[] = $path;
                }
                $existingPermits = $compliance->permits_authorisations_files ?? [];
                $compliance->permits_authorisations_files = array_merge($existingPermits, $permitFiles);
            }

            if ($request->hasFile('other_documents')) {
                $otherFiles = [];
                foreach ($request->file('other_documents') as $file) {
                    $filename = time() . '_other_' . uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('activities/compliance/other', $filename, 'public');
                    $otherFiles[] = $path;
                }
                $existingOther = $compliance->other_permit_files ?? [];
                $compliance->other_permit_files = array_merge($existingOther, $otherFiles);
            }

            $compliance->save();

            $activity->step4_legal_compliance = 1;
            if ($activity->step1_basic && $activity->step2_management_communication && $activity->step3_photos_media && $activity->step4_legal_compliance) {
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

        if ($activity->operator_id !== $operator->id) {
            abort(403);
        }

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

            if ($activity->operator_id !== $operator->id) {
                abort(403);
            }

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

            if (!$request->has('vat_exempted') || !$request->input('vat_exempted')) {
                $rules['vat_number'] = 'required|string|max:100';
            } else {
                $rules['vat_number'] = 'nullable|string|max:100';
            }

            if ($request->input('tax_type') && $request->input('tax_type') !== 'None') {
                $rules['tax_charges_basis'] = 'required|string';
                $rules['tax_charges_type'] = 'required|string';
                $rules['tax_charges_value'] = 'required|numeric|min:0';
            }

            $request->validate($rules);

            $accounting = $activity->accounting;
            if (!$accounting) {
                $accounting = new \App\Models\ActivityAccounting();
                $accounting->activity_id = $activity->id;
            }

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

            $activity->step5_accounting_transaction = 1;
            if ($activity->step1_basic && $activity->step2_management_communication && $activity->step3_photos_media && $activity->step4_legal_compliance && $activity->step5_accounting_transaction) {
                if ($activity->status === 'Draft') {
                    $activity->status = 'In Review';
                }
            }
            $activity->save();

            return redirect()->route('operator.activity.show', $activity->id)
                ->with('success', 'Activity Step 5: Accounting & Transaction saved successfully!');
        } catch (\Exception $e) {
            \Log::error('saveStep5AccountingTransaction error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('operator.activity.step5.show', $activity->id)
                ->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    /**
     * Show Step 6: Policies & Rules
     */
    public function step6PoliciesRules($id)
    {
        $activity = Activity::findOrFail($id);
        $operator = auth()->user();

        if ($activity->operator_id !== $operator->id) {
            abort(403);
        }

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

            if ($activity->operator_id !== $operator->id) {
                abort(403);
            }

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

            if ($request->input('cancellation_penalties_enabled') === 'Yes') {
                $rules['cancellation_penalties_type'] = 'required|in:Person(s),Percentage,Amount';
                $rules['cancellation_penalties_value'] = 'required|numeric|min:0';
            } else {
                $rules['cancellation_penalties_type'] = 'nullable|in:Person(s),Percentage,Amount';
                $rules['cancellation_penalties_value'] = 'nullable|numeric|min:0';
            }

            $request->validate($rules);

            $policy = $activity->policy;
            if (!$policy) {
                $policy = new \App\Models\ActivityPolicy();
                $policy->activity_id = $activity->id;
            }

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

            if ($request->hasFile('health_requirements_file')) {
                $file = $request->file('health_requirements_file');
                $filename = time() . '_health_requirements_' . $file->getClientOriginalName();
                $path = $file->storeAs('activity_policies', $filename, 'public');
                $policy->health_requirements_file = $path;
            }

            $policy->save();

            $activity->step6_policies_rules = 1;
            if ($activity->step1_basic && $activity->step2_management_communication && $activity->step3_photos_media && $activity->step4_legal_compliance && $activity->step5_accounting_transaction && $activity->step6_policies_rules) {
                if ($activity->status === 'Draft') {
                    $activity->status = 'In Review';
                }
            }
            $activity->save();

            \Log::info('Activity Step 6 saved', ['activity_id' => $activity->id, 'policy_id' => $policy->policy_id ?? null]);

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
     * Step 8: Scheduling TimeSlots
     */
    public function step8SchedulingTimeSlots($id)
    {
        $activity = Activity::with(['variants', 'schedulingTimeSlots'])->findOrFail($id);
        $operator = auth()->user();

        // Check ownership
        if ($activity->operator_id !== $operator->id) {
            abort(403, 'Unauthorized action.');
        }

        $timeSlots = $activity->schedulingTimeSlots()->get();
        $variants = $activity->variants()->get();

        return view('operator.activity.step8_scheduling_timeslots', [
            'activity' => $activity,
            'timeSlots' => $timeSlots,
            'variants' => $variants,
        ]);
    }

    /**
     * Store new Scheduling TimeSlot
     */
    public function storeTimeSlot(Request $request, $id)
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
            'capacity_per_slot' => 'required|integer|min:1',
            'schedule_type' => 'required|in:Fixed Slots,Interval-Based,Open Booking,Group Events',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'duration' => 'required|string',
            'recurring' => 'nullable|integer|min:1',
            'lead_time_minutes' => 'nullable|integer|min:1',
            'days_of_week' => 'nullable|array',
        ]);

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

        $validated = $request->validate([
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
        ]);

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
            'step10_allotment', 'step12_seo_social'
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

            return back()->with('success', 'Promotion deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Promotion delete error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete promotion: ' . $e->getMessage());
        }
    }

    /**
     * Show booking listing for operator's activities
     */
    public function bookingList(Request $request)
    {
        $operator = auth()->user();

        $activityIds = Activity::where('operator_id', $operator->id)->pluck('id');

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

        $activityIds = Activity::where('operator_id', $operator->id)->pluck('id');

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

        $activityIds = Activity::where('operator_id', $operator->id)->pluck('id');

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

        return back()->with('success', 'Booking status updated to ' . $booking->booking_status . '.');
    }
}
