<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationRoom;
use App\Models\AccommodationMedia;
use App\Models\AccommodationRate;
use App\Models\AccommodationCompliance;
use App\Models\Business;
use Illuminate\Http\Request;

class AccommodationController extends Controller
{
    /**
     * Check preconditions before accommodation setup
     * 
     * CRITICAL: Before any accommodation setup, verify:
     * 1. Operator account is onboarded and active
     * 2. Controller has confirmed identity
     * 3. Controller has accepted all required agreements
     * 4. Agreement defines commission structure, communication model, payout rules
     */
    protected function checkPreconditions()
    {
        $operator = auth()->user();
        
        // Check operator account status
        if (!$operator || $operator->account_status !== 'active') {
            return redirect()->route('operator.profile')
                ->with('error', 'Your operator account must be active to manage accommodations.');
        }
        
        // Check if linked to business
        if (!$operator->business_id) {
            return redirect()->route('operator.register.step2')
                ->with('error', 'Please complete your business registration first.');
        }
        
        // Check if business is approved
        $business = Business::find($operator->business_id);
        if (!$business || $business->status !== 'active') {
            return redirect()->back()
                ->with('error', 'Your business must be approved before managing accommodations.');
        }
        
        return null; // All checks passed
    }
    
    /**
     * Show list of accommodations
     */
    public function index()
    {
        if ($redirect = $this->checkPreconditions()) return $redirect;
        
        $operator = auth()->user();
        $accommodations = Accommodation::where('operator_id', $operator->id)
            ->orWhere('business_id', $operator->business_id)
            ->get();
        
        return view('operator.accommodation.index', compact('accommodations'));
    }
    
    /**
     * Show form to create new accommodation
     */
    public function create()
    {
        if ($redirect = $this->checkPreconditions()) return $redirect;
        
        $operator = auth()->user();
        
        return view('operator.accommodation.step1_basics', compact('operator'));
    }
    
    /**
     * Save Step 1: Accommodation Basics
     * 
     * Creates the property entity with:
     * - Property name and type
     * - Address and map pin
     * - Legal holder (may differ from operator)
     * - Reservation contact details
     * - Generates Property ID
     * - Sets status to Draft
     */
    public function store(Request $request)
    {
        return $this->saveStep1Basics($request);
    }

    /**
     * Save Step 1: Accommodation Basics
     * 
     * Creates the property entity with:
     * - Property name and type
     * - Address and map pin
     * - Legal holder (may differ from operator)
     * - Reservation contact details
     * - Generates Property ID
     * - Sets status to Draft
     */
    public function saveStep1Basics(Request $request)
    {
        if ($redirect = $this->checkPreconditions()) return $redirect;
        
        $operator = auth()->user();
        
        $request->validate([
            'property_name' => 'required|string|max:255',
            'property_type' => 'required|string|in:' . implode(',', Accommodation::TYPES),
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'short_description' => 'nullable|string|max:250',
            'property_description' => 'nullable|string',
            'legal_holder_name' => 'nullable|string|max:255',
            'legal_holder_id_type' => 'nullable|string|max:50',
            'legal_holder_id_number' => 'nullable|string|max:100',
            'reservation_contact_name' => 'nullable|string|max:255',
            'reservation_contact_email' => 'nullable|email|max:255',
            'reservation_contact_phone' => 'nullable|string|max:20',
            'management_contact_name' => 'nullable|string|max:255',
            'management_contact_email' => 'nullable|email|max:255',
            'management_contact_phone' => 'nullable|string|max:20',
        ]);
        
        // Create accommodation
        $accommodation = new Accommodation();
        $accommodation->accommodation_id = Accommodation::generateAccommodationId();
        $accommodation->operator_id = $operator->id;
        $accommodation->business_id = $operator->business_id;
        $accommodation->property_name = $request->property_name;
        $accommodation->property_type = $request->property_type;
        $accommodation->address = $request->address;
        $accommodation->city = $request->city;
        $accommodation->country = $request->country;
        $accommodation->region = $request->region;
        $accommodation->postal_code = $request->postal_code;
        $accommodation->latitude = $request->latitude;
        $accommodation->longitude = $request->longitude;
        $accommodation->short_description = $request->short_description;
        $accommodation->property_description = $request->property_description;
        $accommodation->legal_holder_name = $request->legal_holder_name ?? $operator->business_legal_name;
        $accommodation->legal_holder_id_type = $request->legal_holder_id_type;
        $accommodation->legal_holder_id_number = $request->legal_holder_id_number;
        $accommodation->reservation_contact_name = $request->reservation_contact_name;
        $accommodation->reservation_contact_email = $request->reservation_contact_email;
        $accommodation->reservation_contact_phone = $request->reservation_contact_phone;
        $accommodation->management_contact_name = $request->management_contact_name;
        $accommodation->management_contact_email = $request->management_contact_email;
        $accommodation->management_contact_phone = $request->management_contact_phone;
        $accommodation->status = Accommodation::STATUS_DRAFT;
        $accommodation->save();
        
        // Mark step 1 as complete
        $accommodation->completeStep('step1_basics');
        
        // Create compliance record
        AccommodationCompliance::create([
            'compliance_id' => 'CMP' . strtoupper(substr(uniqid(), -8)),
            'accommodation_id' => $accommodation->id,
        ]);
        
        return redirect()->route('operator.accommodation.show', $accommodation->id)
            ->with('success', 'Property basics saved successfully! Next: Complete Step 2 - Legal & Contacts.');
    }
    
    /**
     * Show accommodation details
     */
    public function show($id)
    {
        $accommodation = Accommodation::findOrFail($id);
        
        // Authorization check
        $operator = auth()->user();
        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }
        
        return view('operator.accommodation.show', compact('accommodation'));
    }
    
    /**
     * Show edit form for Step 1
     */
    public function editStep1($id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();
        
        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }
        
        return view('operator.accommodation.step1_basics', compact('accommodation', 'operator'));
    }

    /**
     * Update Step 1: Accommodation Basics (Edit existing property)
     */
    public function update(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();
        
        // Authorization check
        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }
        
        $request->validate([
            'property_name' => 'required|string|max:255',
            'property_type' => 'required|string|in:' . implode(',', Accommodation::TYPES),
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'short_description' => 'nullable|string|max:250',
            'property_description' => 'nullable|string',
            'legal_holder_name' => 'nullable|string|max:255',
            'legal_holder_id_type' => 'nullable|string|max:50',
            'legal_holder_id_number' => 'nullable|string|max:100',
            'reservation_contact_name' => 'nullable|string|max:255',
            'reservation_contact_email' => 'nullable|email|max:255',
            'reservation_contact_phone' => 'nullable|string|max:20',
            'management_contact_name' => 'nullable|string|max:255',
            'management_contact_email' => 'nullable|email|max:255',
            'management_contact_phone' => 'nullable|string|max:20',
        ]);
        
        // Update accommodation
        $accommodation->update([
            'property_name' => $request->property_name,
            'property_type' => $request->property_type,
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
            'region' => $request->region,
            'postal_code' => $request->postal_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'short_description' => $request->short_description,
            'property_description' => $request->property_description,
            'legal_holder_name' => $request->legal_holder_name ?? $accommodation->legal_holder_name,
            'legal_holder_id_type' => $request->legal_holder_id_type,
            'legal_holder_id_number' => $request->legal_holder_id_number,
            'reservation_contact_name' => $request->reservation_contact_name,
            'reservation_contact_email' => $request->reservation_contact_email,
            'reservation_contact_phone' => $request->reservation_contact_phone,
            'management_contact_name' => $request->management_contact_name,
            'management_contact_email' => $request->management_contact_email,
            'management_contact_phone' => $request->management_contact_phone,
        ]);
        
        return redirect()->route('operator.accommodation.show', $accommodation->id)
            ->with('success', 'Property basics updated successfully!');
    }

    /**
     * Show Step 2: Reservation and Communication form
     */
    public function step2Reservation($id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();
        
        // Authorization check
        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }
        
        // Step 1 must be completed before accessing Step 2
        if (!$accommodation->step1_basics) {
            return redirect()->route('operator.accommodation.step1.edit', $accommodation->id)
                ->with('error', 'Please complete Step 1: Accommodation Basics first.');
        }
        
        return view('operator.accommodation.step2_reservation', compact('accommodation', 'operator'));
    }

    /**
     * Show Step 3: Photos & Media
     */
    public function step3Photos($id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        // require step1 and step2 completed
        if (!$accommodation->step1_basics) {
            return redirect()->route('operator.accommodation.step1.edit', $accommodation->id)
                ->with('error', 'Please complete Step 1 first.');
        }
        if (!$accommodation->step2_legal) {
            return redirect()->route('operator.accommodation.step2.show', $accommodation->id)
                ->with('error', 'Please complete Step 2 first.');
        }

        $rooms = $accommodation->rooms()->get();

        return view('operator.accommodation.step3_photos', compact('accommodation', 'operator', 'rooms'));
    }

    /**
     * Save Step 3: Photos & Media
     */
    public function saveStep3Photos(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        // Basic validation
        $request->validate([
            'hero_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'gallery.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
            'video_file' => 'nullable|mimetypes:video/mp4,video/quicktime,video/x-msvideo|max:102400',
        ]);

        // Gallery min count
        $galleryFiles = $request->file('gallery', []);
        if (count($galleryFiles) < 6) {
            return redirect()->back()->withErrors(['gallery' => 'Please upload at least 6 gallery images.'])->withInput();
        }

        // Validate room galleries: if rooms exist, ensure at least one file per room
        $rooms = $accommodation->rooms()->get();
        foreach ($rooms as $room) {
            $files = $request->file('room_gallery.' . $room->id, []);
            if (is_array($files) && count($files) > 0) {
                // ok
            } else {
                return redirect()->back()->withErrors(['room_gallery' => "Please add at least one image for room: {$room->name}"])->withInput();
            }
        }

        // Handle uploads
        $storagePath = 'accommodations/' . $accommodation->id . '/media';

        // Hero image
        if ($file = $request->file('hero_image')) {
            $path = $file->store($storagePath, 'public');
            \App\Models\AccommodationMedia::create([
                'accommodation_id' => $accommodation->id,
                'media_type' => 'hero',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $operator->id,
            ]);
        }

        // Gallery
        foreach ($galleryFiles as $file) {
            if (!$file) continue;
            $path = $file->store($storagePath, 'public');
            \App\Models\AccommodationMedia::create([
                'accommodation_id' => $accommodation->id,
                'media_type' => 'gallery',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $operator->id,
            ]);
        }

        // Room galleries
        foreach ($rooms as $room) {
            $files = $request->file('room_gallery.' . $room->id, []);
            foreach ($files as $file) {
                if (!$file) continue;
                $path = $file->store($storagePath, 'public');
                \App\Models\AccommodationMedia::create([
                    'accommodation_id' => $accommodation->id,
                    'media_type' => 'room',
                    'room_id' => $room->id,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => $operator->id,
                ]);
            }
        }

        // Logo
        if ($file = $request->file('logo')) {
            $path = $file->store($storagePath, 'public');
            \App\Models\AccommodationMedia::create([
                'accommodation_id' => $accommodation->id,
                'media_type' => 'logo',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $operator->id,
            ]);
        }

        // Video file
        if ($file = $request->file('video_file')) {
            $path = $file->store($storagePath, 'public');
            \App\Models\AccommodationMedia::create([
                'accommodation_id' => $accommodation->id,
                'media_type' => 'video',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $operator->id,
            ]);
        }

        // Mark step 3 media complete
        $accommodation->completeStep('step3_media');

        return redirect()->route('operator.accommodation.show', $accommodation->id)
            ->with('success', 'Photos & Media uploaded successfully! They will be reviewed before publishing.');
    }

    /**
     * Show Step 4: Compliance & Legal
     */
    public function step4Compliance($id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        // require previous steps
        if (!$accommodation->step1_basics) {
            return redirect()->route('operator.accommodation.step1.edit', $accommodation->id)
                ->with('error', 'Please complete Step 1 first.');
        }
        if (!$accommodation->step2_legal) {
            return redirect()->route('operator.accommodation.step2.show', $accommodation->id)
                ->with('error', 'Please complete Step 2 first.');
        }

        return view('operator.accommodation.step4_compliance', compact('accommodation', 'operator'));
    }

    /**
     * Save Step 4: Compliance & Legal
     */
    public function saveStep4Compliance(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        $request->validate([
            'business_registration_number' => 'required|string|max:255',
            'tourism_permit_number' => 'required|string|max:255',
            'tourism_permit_expiration' => 'nullable|date',
            'public_liability_insurance_number' => 'required|string|max:255',
            'insurance_expiration' => 'nullable|date',

            'tourism_permit_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'insurance_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'fire_safety_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'health_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'other_docs.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        // Save textual fields
        $accommodation->update([
            'business_registration_number' => $request->business_registration_number,
            'tourism_permit_number' => $request->tourism_permit_number,
            'tourism_permit_expiration' => $request->tourism_permit_expiration,
            'public_liability_insurance_number' => $request->public_liability_insurance_number,
            'insurance_expiration' => $request->insurance_expiration,
        ]);

        $storagePath = 'accommodations/' . $accommodation->id . '/compliance';

        // Upload files and create media records
        if ($file = $request->file('tourism_permit_file')) {
            $path = $file->store($storagePath, 'public');
            \App\Models\AccommodationMedia::create([
                'accommodation_id' => $accommodation->id,
                'media_type' => 'compliance_permit',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $operator->id,
            ]);
        }

        if ($file = $request->file('insurance_file')) {
            $path = $file->store($storagePath, 'public');
            \App\Models\AccommodationMedia::create([
                'accommodation_id' => $accommodation->id,
                'media_type' => 'compliance_insurance',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $operator->id,
            ]);
        }

        if ($file = $request->file('fire_safety_file')) {
            $path = $file->store($storagePath, 'public');
            \App\Models\AccommodationMedia::create([
                'accommodation_id' => $accommodation->id,
                'media_type' => 'compliance_fire',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $operator->id,
            ]);
        }

        if ($file = $request->file('health_file')) {
            $path = $file->store($storagePath, 'public');
            \App\Models\AccommodationMedia::create([
                'accommodation_id' => $accommodation->id,
                'media_type' => 'compliance_health',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $operator->id,
            ]);
        }

        $otherFiles = $request->file('other_docs', []);
        foreach ($otherFiles as $file) {
            if (!$file) continue;
            $path = $file->store($storagePath, 'public');
            \App\Models\AccommodationMedia::create([
                'accommodation_id' => $accommodation->id,
                'media_type' => 'compliance_other',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $operator->id,
            ]);
        }

        // Mark compliance step complete
        $accommodation->completeStep('step7_compliance');

        return redirect()->route('operator.accommodation.show', $accommodation->id)
            ->with('success', 'Compliance details saved. Documents will be reviewed.');
    }

    /**
     * Save Step 2: Reservation and Communication
     */
    public function saveStep2(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();
        
        // Authorization check
        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }
        
        $request->validate([
            'reservation_contact_name' => 'required|string|max:255',
            'reservation_contact_email' => 'required|email|max:255',
            'reservation_contact_phone' => 'required|string|max:20',
            'reservation_contact_mobile' => 'required|string|max:20',

            'accounting_contact_name' => 'nullable|string|max:255',
            'accounting_contact_email' => 'nullable|email|max:255',
            'accounting_contact_phone' => 'nullable|string|max:20',
            'accounting_contact_mobile' => 'nullable|string|max:20',

            'management_contact_name' => 'required|string|max:255',
            'management_contact_email' => 'required|email|max:255',
            'management_contact_phone' => 'required|string|max:20',
            'management_contact_mobile' => 'required|string|max:20',

            'onsite_department' => 'nullable|string|max:255',
            'onsite_phone' => 'nullable|string|max:20',

            'booking_confirmation_type' => 'required|in:Instant,On Request',
        ]);
        
        // Update accommodation with contact details
        $accommodation->update([
            'reservation_contact_name' => $request->reservation_contact_name,
            'reservation_contact_email' => $request->reservation_contact_email,
            'reservation_contact_phone' => $request->reservation_contact_phone,
            'reservation_contact_mobile' => $request->reservation_contact_mobile,

            'accounting_contact_name' => $request->accounting_contact_name,
            'accounting_contact_email' => $request->accounting_contact_email,
            'accounting_contact_phone' => $request->accounting_contact_phone,
            'accounting_contact_mobile' => $request->accounting_contact_mobile,

            'management_contact_name' => $request->management_contact_name,
            'management_contact_email' => $request->management_contact_email,
            'management_contact_phone' => $request->management_contact_phone,
            'management_contact_mobile' => $request->management_contact_mobile,

            'onsite_department' => $request->onsite_department,
            'onsite_phone' => $request->onsite_phone,

            'booking_confirmation_type' => $request->booking_confirmation_type,
            // preserve booking_registration_type from operator/business if set; do not allow arbitrary change here
        ]);
        
        // Mark step 2 as complete
        $accommodation->completeStep('step2_legal');
        
        return redirect()->route('operator.accommodation.show', $accommodation->id)
            ->with('success', 'Reservation and Communication details saved successfully! Next: Step 3 - Photos & Media.');
    }

    /**
     * Show Step 5: Accounting & Transaction
     */
    public function step5Accounting($id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user()->load('accounting');

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        if (!$accommodation->step1_basics) {
            return redirect()->route('operator.accommodation.step1.edit', $accommodation->id)
                ->with('error', 'Please complete Step 1 first.');
        }

        return view('operator.accommodation.step5_accounting', compact('accommodation', 'operator'));
    }

    /**
     * Save Step 5: Accounting & Transaction
     */
    public function saveStep5Accounting(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        // Validate based on VAT exempted status
        $rules = [
            'bank_account_holder_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:100',
            'iban' => 'nullable|string|max:100',
            'swift_code' => 'nullable|string|max:50',
            'tax_type' => 'required|in:Tourism,City Tax,None',
            'tax_collection_method' => 'required|in:Operator,MPO',
        ];

        // VAT validation: required unless exempted
        if (!$request->has('vat_exempted')) {
            $rules['vat_number'] = 'required|string|max:100';
        } else {
            $rules['vat_number'] = 'nullable|string|max:100';
        }

        // Tax charges validation: required if tax_type is not 'None'
        if ($request->tax_type !== 'None') {
            $rules['tax_charges_type'] = 'required|in:Per Unit,Per Person,Per Adult';
            $rules['tax_charges_value_type'] = 'required|in:Amount,Percentage';
            $rules['tax_charges_value'] = 'required|numeric|min:0';
        } else {
            $rules['tax_charges_type'] = 'nullable|in:Per Unit,Per Person,Per Adult';
            $rules['tax_charges_value_type'] = 'nullable|in:Amount,Percentage';
            $rules['tax_charges_value'] = 'nullable|numeric|min:0';
        }

        $request->validate($rules);

        // Update accommodation
        $accommodation->update([
            'bank_account_holder_name' => $request->bank_account_holder_name,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'iban' => $request->iban,
            'swift_code' => $request->swift_code,
            'vat_number' => $request->vat_number,
            'vat_exempted' => $request->has('vat_exempted'),
            'tax_type' => $request->tax_type,
            'tax_charges_type' => $request->tax_charges_type ?? null,
            'tax_charges_value_type' => $request->tax_charges_value_type ?? null,
            'tax_charges_value' => $request->tax_charges_value ?? null,
            'tax_collection_method' => $request->tax_collection_method,
            'currency_code' => $request->currency_code ?? 'MUR',
        ]);

        // Mark step complete
        $accommodation->completeStep('step5_rates');

        return redirect()->route('operator.accommodation.show', $accommodation->id)
            ->with('success', 'Accounting & Transaction details saved successfully!');
    }

    /**
     * Show Step 6: Policies & Rules
     */
    public function step6PoliciesRules($id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        if (!$accommodation->step1_basics) {
            return redirect()->route('operator.accommodation.step1.edit', $accommodation->id)
                ->with('error', 'Please complete Step 1 first.');
        }

        return view('operator.accommodation.step6_policies_rules', compact('accommodation', 'operator'));
    }

    /**
     * Save Step 6: Policies & Rules
     */
    public function saveStep6PoliciesRules(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        // Base validation rules
        $rules = [
            'checkin_time' => 'nullable|date_format:H:i',
            'checkout_time' => 'nullable|date_format:H:i',
            'checkin_checkout_rules' => 'nullable|string|max:1000',
            'booking_window_rules' => 'nullable|string|max:1000',
            'amendment_policy_type' => 'nullable|in:custom,template',
            'amendment_policy' => 'nullable|string|max:5000',
            'amendment_policy_template_id' => 'nullable|string|max:100',
            'cancellation_policy_type' => 'required|in:custom,template',
            'cancellation_policy' => 'nullable|string|max:5000',
            'cancellation_policy_template_id' => 'nullable|string|max:100',
            'cancellation_penalties_enabled' => 'required|in:0,1',
            'cancellation_penalty_type' => 'nullable|in:Night,Percentage,Amount',
            'cancellation_penalty_value' => 'nullable|numeric|min:0',
            'security_deposit_policy_type' => 'nullable|in:custom,template',
            'security_deposit_policy' => 'nullable|string|max:5000',
            'security_deposit_policy_template_id' => 'nullable|string|max:100',
            'deposit_required' => 'required|in:0,1',
            'deposit_type' => 'nullable|in:Night,Percentage,Amount',
            'deposit_value' => 'nullable|numeric|min:0',
            'child_max_age' => 'nullable|integer|min:0|max:18',
            'infant_max_age' => 'nullable|integer|min:0|max:5',
            'house_rules_type' => 'nullable|in:custom,template',
            'house_rules' => 'nullable|string|max:5000',
            'house_rules_template_id' => 'nullable|string|max:100',
        ];

        // Conditional validation for cancellation policy
        if ($request->cancellation_policy_type === 'custom') {
            $rules['cancellation_policy'] = 'required|string|max:5000';
        } else {
            $rules['cancellation_policy_template_id'] = 'required|string|max:100';
        }

        // Conditional validation for cancellation penalties
        if ($request->cancellation_penalties_enabled === '1') {
            $rules['cancellation_penalty_type'] = 'required|in:Night,Percentage,Amount';
            $rules['cancellation_penalty_value'] = 'required|numeric|min:0';
        }

        // Conditional validation for deposit settings
        if ($request->deposit_required === '1') {
            $rules['deposit_type'] = 'required|in:Night,Percentage,Amount';
            $rules['deposit_value'] = 'required|numeric|min:0';
        }

        // Amendment policy validation
        if ($request->amendment_policy_type === 'custom') {
            $rules['amendment_policy'] = 'required|string|max:5000';
        }

        $request->validate($rules);

        // Update accommodation
        $accommodation->update([
            'checkin_time' => $request->checkin_time ?? null,
            'checkout_time' => $request->checkout_time ?? null,
            'checkin_checkout_rules' => $request->checkin_checkout_rules ?? null,
            'booking_window_rules' => $request->booking_window_rules ?? null,
            'amendment_policy_type' => $request->amendment_policy_type ?? 'custom',
            'amendment_policy' => $request->amendment_policy ?? null,
            'amendment_policy_template_id' => $request->amendment_policy_template_id ?? null,
            'cancellation_policy_type' => $request->cancellation_policy_type,
            'cancellation_policy' => $request->cancellation_policy ?? null,
            'cancellation_policy_template_id' => $request->cancellation_policy_template_id ?? null,
            'cancellation_penalties_enabled' => $request->cancellation_penalties_enabled,
            'cancellation_penalty_type' => $request->cancellation_penalty_type ?? null,
            'cancellation_penalty_value' => $request->cancellation_penalty_value ?? null,
            'security_deposit_policy_type' => $request->security_deposit_policy_type ?? 'custom',
            'security_deposit_policy' => $request->security_deposit_policy ?? null,
            'security_deposit_policy_template_id' => $request->security_deposit_policy_template_id ?? null,
            'deposit_required' => $request->deposit_required,
            'deposit_type' => $request->deposit_type ?? null,
            'deposit_value' => $request->deposit_value ?? null,
            'child_max_age' => $request->child_max_age ?? null,
            'infant_max_age' => $request->infant_max_age ?? null,
            'house_rules_type' => $request->house_rules_type ?? 'custom',
            'house_rules' => $request->house_rules ?? null,
            'house_rules_template_id' => $request->house_rules_template_id ?? null,
        ]);

        // Mark step complete
        $accommodation->completeStep('step6_policies');

        return redirect()->route('operator.accommodation.show', $accommodation->id)
            ->with('success', 'Policies & Rules details saved successfully!');
    }

    /**
     * Show Step 7: Rooms & Units
     */
    public function step7RoomsUnits($id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        if (!$accommodation->step1_basics) {
            return redirect()->route('operator.accommodation.step1.edit', $accommodation->id)
                ->with('error', 'Please complete Step 1 first.');
        }

        $rooms = AccommodationRoom::where('accommodation_id', $accommodation->id)->get();

        // Load media for selection
        $accommodation->load('media');

        return view('operator.accommodation.step7_rooms_units', compact('accommodation', 'operator', 'rooms'));
    }

    /**
     * Save/Create a room
     */
    public function saveRoom(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        $rules = [
            'room_name' => 'required|string|max:255',
            'room_type' => 'required|string',
            'size_sqm' => 'nullable|numeric|min:0|max:9999.99',
            'view' => 'nullable|string|max:50',
            'smoking' => 'nullable|in:Smoking,Non-smoking',
            'short_description' => 'required|string|max:250',
            'full_description' => 'nullable|string|max:1000',
            'amenities' => 'required|array|min:1',
            'amenities.*' => 'string',
            'accessibility' => 'nullable|array',
            'accessibility.*' => 'string',
            'occupancy_adults' => 'required|integer|min:1',
            'occupancy_children' => 'required|integer|min:0',
            'occupancy_infant' => 'nullable|integer|min:0',
            'images' => 'required|array|min:1',
            'images.*' => 'integer',
        ];

        $data = $request->validate($rules);

        // Create unique room_id
        $roomId = 'R' . strtoupper(uniqid());

        $room = AccommodationRoom::create([
            'room_id' => $roomId,
            'accommodation_id' => $accommodation->id,
            'room_name' => $data['room_name'],
            'room_type' => $data['room_type'],
            'room_description' => $data['full_description'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'size_sqm' => $data['size_sqm'] ?? null,
            'view' => $data['view'] ?? null,
            'smoking' => $data['smoking'] ?? null,
            'capacity' => max(1, intval($data['occupancy_adults'])),
            'children_capacity' => intval($data['occupancy_children'] ?? 0),
            'infant_capacity' => $data['occupancy_infant'] ?? null,
            'quantity' => 1,
            'is_accessible' => !empty($data['accessibility']),
            'accessibility' => !empty($data['accessibility']) ? json_encode($data['accessibility']) : null,
            'amenities' => json_encode($data['amenities']),
        ]);

        // Attach images: set selected media items' room_id to this room
        if (!empty($data['images'])) {
            AccommodationMedia::whereIn('id', $data['images'])->update(['room_id' => $room->id]);
        }

        // Mark step complete
        $accommodation->completeStep('step4_rooms');

        return redirect()->route('operator.accommodation.step7.show', $accommodation->id)
            ->with('success', 'Room added successfully!');
    }

    /**
     * Edit room form
     */
    public function editRoom($id, AccommodationRoom $room)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        $rooms = AccommodationRoom::where('accommodation_id', $accommodation->id)->get();
        $accommodation->load('media');
        return view('operator.accommodation.step7_rooms_units', compact('accommodation', 'operator', 'room', 'rooms'));
    }

    /**
     * Update room
     */
    public function updateRoom(Request $request, $id, AccommodationRoom $room)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        $rules = [
            'room_name' => 'required|string|max:255',
            'room_type' => 'required|string',
            'size_sqm' => 'nullable|numeric|min:0|max:9999.99',
            'view' => 'nullable|string|max:50',
            'smoking' => 'nullable|in:Smoking,Non-smoking',
            'short_description' => 'required|string|max:250',
            'full_description' => 'nullable|string|max:1000',
            'amenities' => 'required|array|min:1',
            'amenities.*' => 'string',
            'accessibility' => 'nullable|array',
            'accessibility.*' => 'string',
            'occupancy_adults' => 'required|integer|min:1',
            'occupancy_children' => 'required|integer|min:0',
            'occupancy_infant' => 'nullable|integer|min:0',
            'images' => 'required|array|min:1',
            'images.*' => 'integer',
        ];

        $data = $request->validate($rules);

        $room->update([
            'room_name' => $data['room_name'],
            'room_type' => $data['room_type'],
            'room_description' => $data['full_description'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'size_sqm' => $data['size_sqm'] ?? null,
            'view' => $data['view'] ?? null,
            'smoking' => $data['smoking'] ?? null,
            'capacity' => max(1, intval($data['occupancy_adults'])),
            'children_capacity' => intval($data['occupancy_children'] ?? 0),
            'infant_capacity' => $data['occupancy_infant'] ?? null,
            'is_accessible' => !empty($data['accessibility']),
            'accessibility' => !empty($data['accessibility']) ? json_encode($data['accessibility']) : null,
            'amenities' => json_encode($data['amenities']),
        ]);

        // Re-assign images: clear previous assignments and attach new
        AccommodationMedia::where('room_id', $room->id)->update(['room_id' => null]);
        if (!empty($data['images'])) {
            AccommodationMedia::whereIn('id', $data['images'])->update(['room_id' => $room->id]);
        }

        return redirect()->route('operator.accommodation.step7.show', $accommodation->id)
            ->with('success', 'Room updated successfully!');
    }

    /**
     * Delete room
     */
    public function deleteRoom($id, AccommodationRoom $room)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        $room->delete();

        return redirect()->route('operator.accommodation.step7.show', $accommodation->id)
            ->with('success', 'Room deleted successfully!');
    }

    /**
     * Show Step 8: Rate Plans
     */
    public function step8RatePlans($id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        if (!$accommodation->step1_basics) {
            return redirect()->route('operator.accommodation.step1.edit', $accommodation->id)
                ->with('error', 'Please complete Step 1 first.');
        }

        // Load rooms with their plans
        $rooms = AccommodationRoom::where('accommodation_id', $accommodation->id)->get();

        // Load all business-level rate plans (not tied to specific rooms)
        $businessPlans = AccommodationRate::where('accommodation_id', $accommodation->id)
            ->where('is_rate_plan', true)
            ->whereNull('room_id')
            ->get();

        // Prepare room plans data for JavaScript (include plan details, not just IDs)
        $roomPlansData = [];
        foreach ($rooms as $room) {
            $assignedPlans = $room->rates()->where('is_rate_plan', true)->get();
            $roomPlansData[] = [
                'roomId' => $room->id,
                'plans' => $assignedPlans->map(function($p) {
                    return [
                        'id' => $p->id,
                        'rate_name' => $p->rate_name,
                        'meal_plan' => $p->meal_plan,
                        'pricing_setting' => $p->pricing_setting,
                    ];
                })->toArray()
            ];
        }

        return view('operator.accommodation.step8_rate_plans', compact('accommodation', 'operator', 'rooms', 'businessPlans', 'roomPlansData'));
    }

    /**
     * Save/Create a rate plan
     */
    public function saveRatePlan(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        $rules = [
            'rate_name' => 'required|string|max:255',
            'meal_plan' => 'required|in:Room Only,Breakfast,Half Board,Full Board,All Inclusive',
            'pricing_setting' => 'required|in:Per Person/Night,Per Room/Night,Per Property/Night',
            'inclusions' => 'nullable|array',
            'inclusions.*' => 'string',
        ];

        $data = $request->validate($rules);

        // Create unique rate ID
        $rateId = 'RATE' . strtoupper(uniqid());

        AccommodationRate::create([
            'rate_id' => $rateId,
            'accommodation_id' => $accommodation->id,
            'rate_name' => $data['rate_name'],
            'meal_plan' => $data['meal_plan'],
            'pricing_setting' => $data['pricing_setting'],
            'inclusions' => !empty($data['inclusions']) ? json_encode($data['inclusions']) : null,
            'is_rate_plan' => true,
            'base_rate' => 0,
            'final_rate' => 0,
            'valid_from' => now()->toDateString(),
            'valid_to' => now()->addYear()->toDateString(),
            'rate_type' => 'Standard',
        ]);

        // Mark step complete
        $accommodation->completeStep('step8_rates');

        return redirect()->route('operator.accommodation.step8.show', $accommodation->id)
            ->with('success', 'Rate Plan added successfully!');
    }

    /**
     * Edit rate plan form
     */
    public function editRatePlan($id, AccommodationRate $plan)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        $rooms = AccommodationRoom::where('accommodation_id', $accommodation->id)->get();

        return view('operator.accommodation.step8_rate_plans', compact('accommodation', 'operator', 'rooms', 'plan'));
    }

    /**
     * Update rate plan
     */
    public function updateRatePlan(Request $request, $id, AccommodationRate $plan)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        $rules = [
            'rate_name' => 'required|string|max:255',
            'meal_plan' => 'required|in:Room Only,Breakfast,Half Board,Full Board,All Inclusive',
            'pricing_setting' => 'required|in:Per Person/Night,Per Room/Night,Per Property/Night',
            'inclusions' => 'nullable|array',
            'inclusions.*' => 'string',
        ];

        $data = $request->validate($rules);

        $plan->update([
            'rate_name' => $data['rate_name'],
            'meal_plan' => $data['meal_plan'],
            'pricing_setting' => $data['pricing_setting'],
            'inclusions' => !empty($data['inclusions']) ? json_encode($data['inclusions']) : null,
        ]);

        return redirect()->route('operator.accommodation.step8.show', $accommodation->id)
            ->with('success', 'Rate Plan updated successfully!');
    }

    /**
     * Delete rate plan
     */
    public function deleteRatePlan($id, AccommodationRate $plan)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        $plan->delete();

        return redirect()->route('operator.accommodation.step8.show', $accommodation->id)
            ->with('success', 'Rate Plan deleted successfully!');
    }

    /**
     * Assign a plan to a room
     */
    public function assignPlansToRoom(Request $request, $id)
    {
        try {
            \Log::info('assignPlansToRoom called', ['id' => $id, 'request' => $request->all()]);
            
            $accommodation = Accommodation::findOrFail($id);
            \Log::info('Accommodation found', ['accommodation_id' => $accommodation->id]);
            
            $operator = auth()->user();
            \Log::info('Operator found', ['operator_id' => $operator->id ?? null]);

            if ($accommodation->operator_id !== $operator->id && 
                $accommodation->business_id !== $operator->business_id) {
                \Log::info('Unauthorized access');
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            \Log::info('Validation starting');
            $data = $request->validate([
                'room_id' => 'required|exists:accommodation_rooms,id',
                'plan_ids' => 'required|array|min:1',
                'plan_ids.*' => [
                    'required',
                    'exists:accommodation_rates,id',
                    function ($attribute, $value, $fail) use ($accommodation) {
                        \Log::info('Validating plan', ['plan_id' => $value, 'accommodation_id' => $accommodation->id]);
                        $plan = AccommodationRate::find($value);
                        \Log::info('Plan query result', ['plan' => $plan ? $plan->toArray() : null]);
                        if (!$plan) {
                            \Log::info('Plan not found');
                            $fail('Invalid plan selected.');
                            return;
                        }
                        if ($plan->accommodation_id !== $accommodation->id) {
                            \Log::info('Plan accommodation mismatch', ['plan_accommodation' => $plan->accommodation_id, 'expected' => $accommodation->id]);
                            $fail('Invalid plan selected.');
                            return;
                        }
                        if (!$plan->is_rate_plan) {
                            \Log::info('Plan is not rate plan', ['is_rate_plan' => $plan->is_rate_plan]);
                            $fail('Invalid plan selected.');
                            return;
                        }
                        if ($plan->room_id !== null) {
                            \Log::info('Plan has room_id', ['room_id' => $plan->room_id]);
                            $fail('Invalid plan selected.');
                            return;
                        }
                        \Log::info('Plan validation passed', ['plan_id' => $value]);
                    },
                ],
            ]);
            \Log::info('Validation passed', ['data' => $data]);

            $room = AccommodationRoom::findOrFail($data['room_id']);
            \Log::info('Room found', ['room_id' => $room->id]);

            // Verify room belongs to this accommodation
            if ($room->accommodation_id !== $accommodation->id) {
                \Log::info('Room does not belong to accommodation');
                return response()->json(['success' => false, 'message' => 'Room does not belong to this accommodation'], 403);
            }

            // Get current plans for this room
            $currentPlanIds = $room->rates()->where('is_rate_plan', true)->pluck('id')->toArray();
            \Log::info('Current plans', ['currentPlanIds' => $currentPlanIds]);

            // Plans to add (not already assigned)
            $plansToAdd = array_diff($data['plan_ids'], $currentPlanIds);
            \Log::info('Plans to add', ['plansToAdd' => $plansToAdd]);

            // Plans to remove (assigned but not in new selection)
            $plansToRemove = array_diff($currentPlanIds, $data['plan_ids']);
            \Log::info('Plans to remove', ['plansToRemove' => $plansToRemove]);

            // Remove plans that are no longer selected
            if (!empty($plansToRemove)) {
                $room->rates()->whereIn('id', $plansToRemove)->delete();
                \Log::info('Plans removed', ['removed' => $plansToRemove]);
            }

            // Add new plans
            foreach ($plansToAdd as $planId) {
                try {
                    $plan = AccommodationRate::findOrFail($planId);
                    \Log::info('Plan found', ['plan_id' => $plan->id, 'plan_name' => $plan->rate_name]);

                    // Verify plan belongs to this accommodation and is a global rate plan
                    if ($plan->accommodation_id !== $accommodation->id || !$plan->is_rate_plan || $plan->room_id !== null) {
                        \Log::info('Plan validation failed', ['plan_id' => $plan->id]);
                        continue; // Skip invalid plans
                    }

                    $newRateData = [
                        'rate_id' => 'RATE' . strtoupper(uniqid()),
                        'accommodation_id' => $accommodation->id,
                        'room_id' => $room->id,
                        'rate_name' => $plan->rate_name,
                        'meal_plan' => $plan->meal_plan,
                        'pricing_setting' => $plan->pricing_setting,
                        'inclusions' => $plan->inclusions,
                        'is_rate_plan' => true,
                        'base_rate' => $plan->base_rate ?? 0,
                        'final_rate' => $plan->final_rate ?? 0,
                        'valid_from' => $plan->valid_from ?? now()->toDateString(),
                        'valid_to' => $plan->valid_to ?? now()->addYear()->toDateString(),
                        'rate_type' => $plan->rate_type ?? 'Standard',
                        'currency' => $plan->currency ?? 'MUR',
                        'min_nights' => $plan->min_nights ?? 1,
                        'max_nights' => $plan->max_nights,
                        'is_active' => $plan->is_active ?? true,
                    ];
                    
                    \Log::info('Creating new rate', ['data' => $newRateData]);
                    
                    $newRate = AccommodationRate::create($newRateData);
                    \Log::info('Rate created successfully', ['new_rate_id' => $newRate->id]);
                    
                } catch (\Exception $e) {
                    \Log::error('Error creating accommodation rate', ['error' => $e->getMessage(), 'planId' => $planId, 'trace' => $e->getTraceAsString()]);
                    return response()->json(['success' => false, 'message' => 'Error assigning plan: ' . $e->getMessage()], 500);
                }
            }

            \Log::info('All plans processed successfully');
            return response()->json(['success' => true, 'message' => 'Plans assigned to room successfully']);
        } catch (\Exception $e) {
            \Log::error('assignPlansToRoom error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function removePlanFromRoom(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'room_id' => 'required|exists:accommodation_rooms,id',
            'plan_id' => 'required|exists:accommodation_rates,id',
        ]);

        $room = AccommodationRoom::findOrFail($data['room_id']);
        $plan = AccommodationRate::findOrFail($data['plan_id']);

        // Verify room belongs to this accommodation
        if ($room->accommodation_id !== $accommodation->id) {
            return response()->json(['success' => false, 'message' => 'Room does not belong to this accommodation'], 403);
        }

        // Verify plan belongs to this room
        if ($plan->room_id !== $room->id) {
            return response()->json(['success' => false, 'message' => 'Plan does not belong to this room'], 403);
        }

        // Delete the plan assignment
        $plan->delete();

        return response()->json(['success' => true, 'message' => 'Plan removed from room successfully']);
    }

    /**
     * Show Step 9: Season and Pricing
     */
    public function step9SeasonPricing($id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        if (!$accommodation->step1_basics) {
            return redirect()->route('operator.accommodation.step1.edit', $accommodation->id)
                ->with('error', 'Please complete Step 1 first.');
        }

        // Load rooms with their assigned plans
        $rooms = AccommodationRoom::where('accommodation_id', $accommodation->id)->with('rates')->get();

        // Build list of all room + plan combinations
        $roomPlanCombinations = [];
        foreach ($rooms as $room) {
            $assignedPlans = $room->rates()->where('is_rate_plan', true)->get();
            foreach ($assignedPlans as $plan) {
                // Check if default pricing exists for this room+plan combination
                $defaultPricing = AccommodationRate::where('accommodation_id', $accommodation->id)
                    ->where('room_id', $room->id)
                    ->where('rate_name', $plan->rate_name)
                    ->where('meal_plan', $plan->meal_plan)
                    ->where('pricing_setting', $plan->pricing_setting)
                    ->where('is_default', true)
                    ->first();

                $roomPlanCombinations[] = [
                    'room' => $room,
                    'plan' => $plan,
                    'has_default' => $defaultPricing ? true : false,
                    'default_pricing' => $defaultPricing,
                ];
            }
        }

        // Load all seasonal pricing for this accommodation
        $seasonalPricing = AccommodationRate::where('accommodation_id', $accommodation->id)
            ->where('is_rate_plan', false) // Seasonal pricing entries
            ->whereNotNull('room_id')
            ->with(['room'])
            ->orderBy('valid_from')
            ->get();

        return view('operator.accommodation.step9_season_pricing', compact('accommodation', 'operator', 'rooms', 'roomPlanCombinations', 'seasonalPricing'));
    }

    /**
     * Save seasonal pricing
     */
    public function saveSeasonPricing(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        $request->validate([
            'room_id' => 'required|exists:accommodation_rooms,id',
            'rate_plan_id' => 'required|exists:accommodation_rates,id',
            'adult_rate' => 'required|numeric|min:0',
            'extra_adult_rate' => 'required|numeric|min:0',
            'extra_bed' => 'nullable|numeric|min:0',
            'children_rate' => 'required|numeric|min:0',
            'infant_rate' => 'required|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
        ]);

        $room = AccommodationRoom::findOrFail($request->room_id);
        $ratePlan = AccommodationRate::findOrFail($request->rate_plan_id);

        // Verify room belongs to accommodation
        if ($room->accommodation_id !== $accommodation->id) {
            return redirect()->back()->with('error', 'Invalid room selected.');
        }

        // Verify rate plan belongs to accommodation and is a rate plan
        if ($ratePlan->accommodation_id !== $accommodation->id || !$ratePlan->is_rate_plan) {
            return redirect()->back()->with('error', 'Invalid rate plan selected.');
        }

        // Verify room has this plan assigned
        $assignedPlan = $room->rates()->where('id', $ratePlan->id)->where('is_rate_plan', true)->first();
        if (!$assignedPlan) {
            return redirect()->back()->with('error', 'This plan is not assigned to this room.');
        }

        // Check for overlapping dates
        $overlapping = AccommodationRate::where('accommodation_id', $accommodation->id)
            ->where('room_id', $room->id)
            ->where('rate_name', $ratePlan->rate_name) // Same plan
            ->where('meal_plan', $ratePlan->meal_plan)
            ->where('pricing_setting', $ratePlan->pricing_setting)
            ->where('is_rate_plan', false)
            ->where(function($query) use ($request) {
                $query->whereBetween('valid_from', [$request->valid_from, $request->valid_to])
                      ->orWhereBetween('valid_to', [$request->valid_from, $request->valid_to])
                      ->orWhere(function($q) use ($request) {
                          $q->where('valid_from', '<=', $request->valid_from)
                            ->where('valid_to', '>=', $request->valid_to);
                      });
            })
            ->exists();

        if ($overlapping) {
            return redirect()->back()->with('error', 'Pricing dates overlap with existing pricing for this plan.');
        }

        // Create seasonal pricing entry
        AccommodationRate::create([
            'rate_id' => 'RATE' . strtoupper(uniqid()),
            'accommodation_id' => $accommodation->id,
            'room_id' => $room->id,
            'rate_name' => $ratePlan->rate_name,
            'meal_plan' => $ratePlan->meal_plan,
            'pricing_setting' => $ratePlan->pricing_setting,
            'inclusions' => $ratePlan->inclusions,
            'is_rate_plan' => false, // This is seasonal pricing, not a plan definition
            'base_rate' => $request->adult_rate,
            'final_rate' => $request->adult_rate,
            'extra_adult_rate' => $request->extra_adult_rate,
            'extra_bed_rate' => $request->extra_bed,
            'children_rate' => $request->children_rate,
            'infant_rate' => $request->infant_rate,
            'valid_from' => $request->valid_from,
            'valid_to' => $request->valid_to,
            'rate_type' => 'Seasonal',
            'currency' => $ratePlan->currency ?? 'MUR',
            'is_active' => true,
        ]);

        // Mark step 9 as complete
        $accommodation->completeStep('step9_pricing');

        return redirect()->route('operator.accommodation.step9.show', $accommodation->id)
            ->with('success', 'Seasonal pricing added successfully!');
    }

    /**
     * Save accommodation fees (cleaning, resort, early/late) to DB
     */
    public function saveFees(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'cleaning_fee' => 'nullable|numeric|min:0',
            'resort_fee' => 'nullable|numeric|min:0',
            'early_checkin_type' => 'nullable|in:percent,fixed',
            'early_checkin_value' => 'nullable|numeric|min:0',
            'late_checkout_type' => 'nullable|in:percent,fixed',
            'late_checkout_value' => 'nullable|numeric|min:0',
        ]);

        // Save or update property-level fees (room_id left null)
        try {
            \App\Models\AccommodationFee::updateOrCreate(
                ['accommodation_id' => $accommodation->id, 'room_id' => null],
                array_merge($data, ['accommodation_id' => $accommodation->id, 'room_id' => null])
            );

            return response()->json(['success' => true, 'message' => 'Saved']);
        } catch (\Exception $e) {
            \Log::error('saveFees error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to save fees'], 500);
        }
    }

    /**
     * Return saved fees for an accommodation (property-level)
     */
    public function getFees(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $fees = \App\Models\AccommodationFee::where('accommodation_id', $accommodation->id)
            ->whereNull('room_id')
            ->first();

        return response()->json(['success' => true, 'data' => $fees]);
    }

    /**
     * Set default pricing for a room + plan combination
     */
    public function setDefaultPrice(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $request->validate([
                'room_id' => 'required|exists:accommodation_rooms,id',
                'plan_id' => 'required|exists:accommodation_rates,id',
                'adult_rate' => 'required|numeric|min:0',
                'extra_adult_rate' => 'required|numeric|min:0',
                'extra_bed_rate' => 'nullable|numeric|min:0',
                'children_rate' => 'required|numeric|min:0',
                'infant_rate' => 'required|numeric|min:0',
            ]);

            $room = AccommodationRoom::findOrFail($request->room_id);
            $plan = AccommodationRate::findOrFail($request->plan_id);

            // Verify room belongs to accommodation
            if ($room->accommodation_id !== $accommodation->id) {
                return response()->json(['success' => false, 'message' => 'Invalid room selected.'], 400);
            }

            // Verify plan belongs to accommodation and is a rate plan
            if ($plan->accommodation_id !== $accommodation->id || !$plan->is_rate_plan) {
                return response()->json(['success' => false, 'message' => 'Invalid plan selected.'], 400);
            }

            // Verify room has this plan assigned
            $assignedPlan = $room->rates()->where('id', $plan->id)->where('is_rate_plan', true)->first();
            if (!$assignedPlan) {
                return response()->json(['success' => false, 'message' => 'This plan is not assigned to this room.'], 400);
            }

            // Remove any existing default pricing for this room+plan combination
            AccommodationRate::where('accommodation_id', $accommodation->id)
                ->where('room_id', $room->id)
                ->where('rate_name', $plan->rate_name)
                ->where('meal_plan', $plan->meal_plan)
                ->where('pricing_setting', $plan->pricing_setting)
                ->where('is_default', true)
                ->delete();

            // Create default pricing entry
            AccommodationRate::create([
                'rate_id' => 'RATE' . strtoupper(uniqid()),
                'accommodation_id' => $accommodation->id,
                'room_id' => $room->id,
                'rate_name' => $plan->rate_name,
                'meal_plan' => $plan->meal_plan,
                'pricing_setting' => $plan->pricing_setting,
                'inclusions' => $plan->inclusions,
                'is_rate_plan' => false, // This is pricing, not a plan definition
                'is_default' => true,
                'base_rate' => $request->adult_rate,
                'final_rate' => $request->adult_rate,
                'extra_adult_rate' => $request->extra_adult_rate,
                'extra_bed_rate' => $request->extra_bed_rate ?? 0,
                'children_rate' => $request->children_rate,
                'infant_rate' => $request->infant_rate,
                'valid_from' => now()->toDateString(),
                'valid_to' => now()->addYears(10)->toDateString(),
                'rate_type' => 'Standard',
                'currency' => 'MUR',
                'is_active' => true,
            ]);

            return response()->json(['success' => true, 'message' => 'Default price set successfully!']);
        } catch (\Exception $e) {
            \Log::error('setDefaultPrice error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Edit seasonal pricing
     */
    public function editSeasonPricing($id, $pricingId)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        $pricing = AccommodationRate::findOrFail($pricingId);

        if ($pricing->accommodation_id !== $accommodation->id || $pricing->is_rate_plan) {
            abort(403);
        }

        $rooms = AccommodationRoom::where('accommodation_id', $accommodation->id)->get();

        return view('operator.accommodation.step9_season_pricing_edit', compact('accommodation', 'operator', 'pricing', 'rooms'));
    }

    /**
     * Update seasonal pricing
     */
    public function updateSeasonPricing(Request $request, $id, $pricingId)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        $pricing = AccommodationRate::findOrFail($pricingId);

        if ($pricing->accommodation_id !== $accommodation->id || $pricing->is_rate_plan) {
            abort(403);
        }

        $request->validate([
            'adult_rate' => 'required|numeric|min:0',
            'extra_adult_rate' => 'required|numeric|min:0',
            'extra_bed' => 'nullable|numeric|min:0',
            'children_rate' => 'required|numeric|min:0',
            'infant_rate' => 'required|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
        ]);

        // Check for overlapping dates (excluding current pricing)
        $overlapping = AccommodationRate::where('accommodation_id', $accommodation->id)
            ->where('room_id', $pricing->room_id)
            ->where('rate_name', $pricing->rate_name)
            ->where('is_rate_plan', false)
            ->where('id', '!=', $pricingId)
            ->where(function($query) use ($request) {
                $query->whereBetween('valid_from', [$request->valid_from, $request->valid_to])
                      ->orWhereBetween('valid_to', [$request->valid_from, $request->valid_to])
                      ->orWhere(function($q) use ($request) {
                          $q->where('valid_from', '<=', $request->valid_from)
                            ->where('valid_to', '>=', $request->valid_to);
                      });
            })
            ->exists();

        if ($overlapping) {
            return redirect()->back()->with('error', 'Pricing dates overlap with existing pricing for this plan.');
        }

        $pricing->update([
            'base_rate' => $request->adult_rate,
            'final_rate' => $request->adult_rate,
            'extra_adult_rate' => $request->extra_adult_rate,
            'extra_bed_rate' => $request->extra_bed,
            'children_rate' => $request->children_rate,
            'infant_rate' => $request->infant_rate,
            'valid_from' => $request->valid_from,
            'valid_to' => $request->valid_to,
        ]);

        return redirect()->route('operator.accommodation.step9.show', $accommodation->id)
            ->with('success', 'Seasonal pricing updated successfully!');
    }

    /**
     * Delete seasonal pricing
     */
    public function deleteSeasonPricing(Request $request, $id, $pricingId)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            abort(403);
        }

        $pricing = AccommodationRate::findOrFail($pricingId);

        if ($pricing->accommodation_id !== $accommodation->id || $pricing->is_rate_plan) {
            abort(403);
        }

        $pricing->delete();

        return redirect()->route('operator.accommodation.step9.show', $accommodation->id)
            ->with('success', 'Seasonal pricing deleted successfully!');
    }

    /**
     * Add seasonal pricing entry
     */
    public function addSeasonalEntry(Request $request, $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $request->validate([
                'room_id' => 'required|exists:accommodation_rooms,id',
                'plan_id' => 'required|exists:accommodation_rates,id',
                'valid_from' => 'required|date',
                'valid_to' => 'required|date|after:valid_from',
                'adult_rate' => 'required|numeric|min:0',
                'extra_adult_rate' => 'required|numeric|min:0',
                'extra_bed_rate' => 'nullable|numeric|min:0',
                'children_rate' => 'required|numeric|min:0',
                'infant_rate' => 'required|numeric|min:0',
            ]);

            $room = AccommodationRoom::findOrFail($request->room_id);
            $plan = AccommodationRate::findOrFail($request->plan_id);

            // Verify room belongs to accommodation
            if ($room->accommodation_id !== $accommodation->id) {
                return response()->json(['success' => false, 'message' => 'Invalid room selected.'], 400);
            }

            // Verify plan belongs to accommodation and is a rate plan
            if ($plan->accommodation_id !== $accommodation->id || !$plan->is_rate_plan) {
                return response()->json(['success' => false, 'message' => 'Invalid plan selected.'], 400);
            }

            // Verify room has this plan assigned
            $assignedPlan = $room->rates()->where('id', $plan->id)->where('is_rate_plan', true)->first();
            if (!$assignedPlan) {
                return response()->json(['success' => false, 'message' => 'This plan is not assigned to this room.'], 400);
            }

            // Check for overlapping dates
            $overlapping = AccommodationRate::where('accommodation_id', $accommodation->id)
                ->where('room_id', $room->id)
                ->where('rate_name', $plan->rate_name)
                ->where('meal_plan', $plan->meal_plan)
                ->where('pricing_setting', $plan->pricing_setting)
                ->where('is_rate_plan', false)
                ->where('is_default', false)
                ->where(function($query) use ($request) {
                    $query->whereBetween('valid_from', [$request->valid_from, $request->valid_to])
                          ->orWhereBetween('valid_to', [$request->valid_from, $request->valid_to])
                          ->orWhere(function($q) use ($request) {
                              $q->where('valid_from', '<=', $request->valid_from)
                                ->where('valid_to', '>=', $request->valid_to);
                          });
                })
                ->exists();

            if ($overlapping) {
                return response()->json(['success' => false, 'message' => 'Pricing dates overlap with existing seasonal pricing for this plan.'], 400);
            }

            // Create seasonal pricing entry
            AccommodationRate::create([
                'rate_id' => 'RATE' . strtoupper(uniqid()),
                'accommodation_id' => $accommodation->id,
                'room_id' => $room->id,
                'rate_name' => $plan->rate_name,
                'meal_plan' => $plan->meal_plan,
                'pricing_setting' => $plan->pricing_setting,
                'inclusions' => $plan->inclusions,
                'is_rate_plan' => false,
                'is_default' => false,
                'base_rate' => $request->adult_rate,
                'final_rate' => $request->adult_rate,
                'extra_adult_rate' => $request->extra_adult_rate,
                'extra_bed_rate' => $request->extra_bed_rate ?? 0,
                'children_rate' => $request->children_rate,
                'infant_rate' => $request->infant_rate,
                'valid_from' => $request->valid_from,
                'valid_to' => $request->valid_to,
                'rate_type' => 'Seasonal',
                'currency' => 'MUR',
                'is_active' => true,
            ]);

            return response()->json(['success' => true, 'message' => 'Seasonal pricing entry added successfully!']);
        } catch (\Exception $e) {
            \Log::error('addSeasonalEntry error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete seasonal pricing entry
     */
    public function deleteSeasonalEntry(Request $request, $id, $entryId)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $entry = AccommodationRate::findOrFail($entryId);

            // Verify entry belongs to accommodation and is not a rate plan or default pricing
            if ($entry->accommodation_id !== $accommodation->id || $entry->is_rate_plan || $entry->is_default) {
                return response()->json(['success' => false, 'message' => 'Invalid entry.'], 400);
            }

            $entry->delete();

            return response()->json(['success' => true, 'message' => 'Seasonal pricing entry deleted successfully!']);
        } catch (\Exception $e) {
            \Log::error('deleteSeasonalEntry error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update seasonal pricing entry
     */
    public function updateSeasonalEntry(Request $request, $id, $entryId)
    {
        $accommodation = Accommodation::findOrFail($id);
        $operator = auth()->user();

        if ($accommodation->operator_id !== $operator->id && 
            $accommodation->business_id !== $operator->business_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $request->validate([
                'valid_from' => 'required|date',
                'valid_to' => 'required|date|after:valid_from',
                'adult_rate' => 'required|numeric|min:0',
                'extra_adult_rate' => 'required|numeric|min:0',
                'extra_bed_rate' => 'nullable|numeric|min:0',
                'children_rate' => 'required|numeric|min:0',
                'infant_rate' => 'required|numeric|min:0',
            ]);

            $entry = AccommodationRate::findOrFail($entryId);

            // Verify entry belongs to accommodation and is not a rate plan or default pricing
            if ($entry->accommodation_id !== $accommodation->id || $entry->is_rate_plan || $entry->is_default) {
                return response()->json(['success' => false, 'message' => 'Invalid entry.'], 400);
            }

            // Check for overlapping dates with other seasonal entries (excluding current entry)
            $overlapping = AccommodationRate::where('accommodation_id', $accommodation->id)
                ->where('room_id', $entry->room_id)
                ->where('rate_name', $entry->rate_name)
                ->where('meal_plan', $entry->meal_plan)
                ->where('pricing_setting', $entry->pricing_setting)
                ->where('is_rate_plan', false)
                ->where('is_default', false)
                ->where('id', '!=', $entryId)
                ->where(function($query) use ($request) {
                    $query->whereBetween('valid_from', [$request->valid_from, $request->valid_to])
                          ->orWhereBetween('valid_to', [$request->valid_from, $request->valid_to])
                          ->orWhere(function($q) use ($request) {
                              $q->where('valid_from', '<=', $request->valid_from)
                                ->where('valid_to', '>=', $request->valid_to);
                          });
                })
                ->exists();

            if ($overlapping) {
                return response()->json(['success' => false, 'message' => 'Pricing dates overlap with existing seasonal pricing for this plan.'], 400);
            }

            // Update seasonal pricing entry
            $entry->update([
                'base_rate' => $request->adult_rate,
                'final_rate' => $request->adult_rate,
                'extra_adult_rate' => $request->extra_adult_rate,
                'extra_bed_rate' => $request->extra_bed_rate ?? 0,
                'children_rate' => $request->children_rate,
                'infant_rate' => $request->infant_rate,
                'valid_from' => $request->valid_from,
                'valid_to' => $request->valid_to,
            ]);

            return response()->json(['success' => true, 'message' => 'Seasonal pricing entry updated successfully!']);
        } catch (\Exception $e) {
            \Log::error('updateSeasonalEntry error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
