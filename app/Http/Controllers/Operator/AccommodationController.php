<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationRoom;
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
}
