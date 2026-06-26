@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        @php $currentStep = 1; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.accommodation._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 32px;">
                    
                    {{-- Header --}}
                    <div style="margin-bottom: 24px;">
                        @if(isset($accommodation))
                            <h2 style="font-weight: bold; margin-bottom: 8px;">Edit Property: {{ $accommodation->property_name }}</h2>
                            <p style="color: #666; margin-bottom: 0;">ID: {{ $accommodation->accommodation_id }}</p>
                        @else
                            <h2 style="font-weight: bold; margin-bottom: 8px;">Add New Property</h2>
                            <p style="color: #666; margin-bottom: 0;">Start by providing your property's basic information</p>
                        @endif
                    </div>

                    {{-- Property Completion --}}
                    @if(isset($accommodation))
                        <div style="background: #f8f8f8; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-weight: 600;">Property Completion</span>
                                <span style="font-weight: bold; color: #19b5b5;">{{ $accommodation->getCompletionPercentage() }}%</span>
                            </div>
                            <div style="height: 8px; background: #e0e0e0; border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; background: #19b5b5; width: {{ $accommodation->getCompletionPercentage() }}%; transition: width 0.3s;"></div>
                            </div>
                        </div>
                    @endif

                    {{-- Form --}}
                    <form method="POST" action="{{ isset($accommodation) ? route('operator.accommodation.update', $accommodation->id) : route('operator.accommodation.store') }}">
                        @csrf
                        @if(isset($accommodation))
                            @method('PUT')
                        @endif

                        {{-- Step Header --}}
                        <div style="background: #19b5b5; color: #fff; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; font-weight: bold;">
                            Step 1: Accommodation Basics
                        </div>

                        {{-- Alerts --}}
                        @if(session('success'))
                            <div class="alert alert-success" style="margin-bottom: 20px;">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger" style="margin-bottom: 20px;">
                                <strong>Please fix the following errors:</strong>
                                <ul style="margin-bottom: 0;">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Property Name & Type --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Property Name *</label>
                                <input type="text" name="property_name" class="form-control" required
                                    value="{{ old('property_name', $accommodation->property_name ?? '') }}"
                                    placeholder="e.g., Sunset Beach Resort">
                                @error('property_name')
                                    <small style="color: #dc3545;">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Property Name (French)</label>
                                <input type="text" name="property_name_fr" class="form-control"
                                    value="{{ old('property_name_fr', $accommodation->property_name_fr ?? '') }}"
                                    placeholder="e.g., Résidence Plage du Soleil">
                                @error('property_name_fr')
                                    <small style="color: #dc3545;">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Property Type *</label>
                                <select name="property_type" class="form-control" required>
                                    <option value="">Select Property Type</option>
                                    @foreach(\App\Models\Accommodation::TYPES as $type)
                                        <option value="{{ $type }}" {{ old('property_type', $accommodation->property_type ?? '') === $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('property_type')
                                    <small style="color: #dc3545;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="mb-4">
                            <label style="font-weight: 600; margin-bottom: 8px; display: block;">Short Description</label>
                            <textarea name="short_description" id="short_description" style="display:none;">{{ old('short_description', $accommodation->short_description ?? '') }}</textarea>
                            <div id="short_description_editor" style="background:#fff;"></div>
                            <div style="margin-top:12px;">
                                <label style="font-weight:600;">Short Description (French)</label>
                                <textarea name="short_description_fr" id="short_description_fr" style="display:none;">{{ old('short_description_fr', $accommodation->short_description_fr ?? '') }}</textarea>
                                <div id="short_description_fr_editor" style="background:#fff;"></div>
                            </div>
                            <small style="color: #999;">Character countdown: <span id="charCount">250</span></small>
                            @error('short_description')
                                <small style="color: #dc3545;">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Full Description --}}
                        <div class="mb-4">
                            <label style="font-weight: 600; margin-bottom: 8px; display: block;">Full Description</label>
                            <textarea name="property_description" id="property_description" style="display:none;">{{ old('property_description', $accommodation->property_description ?? '') }}</textarea>
                            <div id="property_description_editor" style="background:#fff;"></div>
                            <div style="margin-top:12px;">
                                <label style="font-weight:600;">Full Description (French)</label>
                                <textarea name="property_description_fr" id="property_description_fr" style="display:none;">{{ old('property_description_fr', $accommodation->property_description_fr ?? '') }}</textarea>
                                <div id="property_description_fr_editor" style="background:#fff;"></div>
                            </div>
                            <small style="color: #999;">Use the editor toolbar to format your content.</small>
                            @error('property_description')
                                <small style="color: #dc3545;">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Address Section --}}
                        <hr style="margin: 32px 0;">
                        <h5 style="font-weight: 600; margin-bottom: 20px;">Location Information</h5>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Street Address *</label>
                                <input type="text" name="address" class="form-control" required
                                    value="{{ old('address', $accommodation->address ?? '') }}"
                                    placeholder="Building number, street name">
                                @error('address')
                                    <small style="color: #dc3545;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block;">City *</label>
                                <input type="text" name="city" class="form-control" required
                                    value="{{ old('city', $accommodation->city ?? '') }}"
                                    placeholder="e.g., Port Louis">
                                @error('city')
                                    <small style="color: #dc3545;">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Region/State</label>
                                <input type="text" name="region" class="form-control"
                                    value="{{ old('region', $accommodation->region ?? '') }}"
                                    placeholder="e.g., Northern">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Country *</label>
                                <input type="text" name="country" class="form-control" required
                                    value="{{ old('country', $accommodation->country ?? 'Mauritius') }}"
                                    placeholder="e.g., Mauritius">
                                @error('country')
                                    <small style="color: #dc3545;">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Postal Code</label>
                                <input type="text" name="postal_code" class="form-control"
                                    value="{{ old('postal_code', $accommodation->postal_code ?? '') }}"
                                    placeholder="ZIP/Postal code">
                            </div>
                        </div>

                        {{-- Map Location --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Latitude</label>
                                <input type="number" name="latitude" class="form-control" step="0.00000001"
                                    value="{{ old('latitude', $accommodation->latitude ?? '') }}"
                                    placeholder="-20.1609">
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Longitude</label>
                                <input type="number" name="longitude" class="form-control" step="0.00000001"
                                    value="{{ old('longitude', $accommodation->longitude ?? '') }}"
                                    placeholder="57.5012">
                            </div>
                        </div>

                        <div class="mb-4">
                            <small style="color: #999;">Map Location: <span style="color: #dc3545;">Add interactive map picker here</span></small>
                        </div>

                        {{-- Legal Holder Section --}}
                        <hr style="margin: 32px 0;">
                        <h5 style="font-weight: 600; margin-bottom: 20px;">Legal Information</h5>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Legal Holder Name</label>
                                <input type="text" name="legal_holder_name" class="form-control"
                                    value="{{ old('legal_holder_name', $accommodation->legal_holder_name ?? $operator->business_legal_name) }}"
                                    placeholder="Leave empty to use business legal name">
                                <small style="color: #999;">May differ from operator. Used for tax and legal documents.</small>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block;">ID Type</label>
                                <select name="legal_holder_id_type" class="form-control">
                                    <option value="">Select ID Type</option>
                                    <option value="BRN" {{ old('legal_holder_id_type', $accommodation->legal_holder_id_type ?? '') === 'BRN' ? 'selected' : '' }}>Business Registration Number</option>
                                    <option value="NIC" {{ old('legal_holder_id_type', $accommodation->legal_holder_id_type ?? '') === 'NIC' ? 'selected' : '' }}>National ID Card</option>
                                    <option value="Passport" {{ old('legal_holder_id_type', $accommodation->legal_holder_id_type ?? '') === 'Passport' ? 'selected' : '' }}>Passport</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block;">ID Number</label>
                                <input type="text" name="legal_holder_id_number" class="form-control"
                                    value="{{ old('legal_holder_id_number', $accommodation->legal_holder_id_number ?? '') }}"
                                    placeholder="e.g., BR123456789">
                            </div>
                        </div>

                        {{-- Contacts Section --}}
                        <hr style="margin: 32px 0;">
                        <h5 style="font-weight: 600; margin-bottom: 20px;">Contact Information</h5>

                        <div class="alert alert-info" style="margin-bottom: 20px;">
                            Contact information will be configured in <strong>Step 2: Reservation and Communication</strong>
                        </div>

                        {{-- Buttons --}}
                        <hr style="margin: 32px 0;">
                        <div style="display: flex; justify-content: space-between; gap: 12px;">
                            @if(isset($accommodation->id) )
                            <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:16px;">
                                <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">← Back</a>
                            </div>
                            @else
                             <a href="{{ route('operator.accommodation.index') }}" class="btn" style="background: #f0f0f0; color: #333; padding: 8px 12px; border-radius: 4px;">← Back</a> 
                            @endif 
                            <button type="submit" class="btn" style="background: #19b5b5; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">
                                Save and Continue
                            </button>
                        </div>
                    </form>

                    <!-- Back Button -->
                    @if($accommodation)
                        <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
                            <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
                                ← Back to Accommodation Overview
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <style>
        #short_description_editor .ql-editor {
            min-height: 90px;
        }
        #property_description_editor .ql-editor {
            min-height: 150px;
        }
        #short_description_fr_editor .ql-editor {
            min-height: 90px;
        }
        #property_description_fr_editor .ql-editor {
            min-height: 150px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script>
        (function () {
            const shortInput = document.getElementById('short_description');
                    const shortInputFr = document.getElementById('short_description_fr');
            const fullInput = document.getElementById('property_description');
                    const fullInputFr = document.getElementById('property_description_fr');
            const charCount = document.getElementById('charCount');
            const form = document.querySelector('form');

            const shortQuill = new Quill('#short_description_editor', {
                theme: 'snow',
                placeholder: 'Brief description (max 250 characters)',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ list: 'bullet' }],
                        ['clean']
                    ]
                }
            });

            const fullQuill = new Quill('#property_description_editor', {
                theme: 'snow',
                placeholder: 'Detailed description of your property...',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link', 'blockquote'],
                        ['clean']
                    ]
                }
            });

                    const shortQuillFr = new Quill('#short_description_fr_editor', {
                        theme: 'snow',
                        placeholder: 'Brève description en français (max 250 caractères)',
                        modules: {
                            toolbar: [
                                ['bold', 'italic', 'underline'],
                                [{ list: 'bullet' }],
                                ['clean']
                            ]
                        }
                    });

                    const fullQuillFr = new Quill('#property_description_fr_editor', {
                        theme: 'snow',
                        placeholder: 'Description complète en français...',
                        modules: {
                            toolbar: [
                                [{ header: [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ list: 'ordered' }, { list: 'bullet' }],
                                ['link', 'blockquote'],
                                ['clean']
                            ]
                        }
                    });

                    // Initialize French editors with existing content
                    if (shortInputFr.value) {
                        if (/<[a-z][\s\S]*>/i.test(shortInputFr.value)) {
                            shortQuillFr.clipboard.dangerouslyPasteHTML(shortInputFr.value);
                        } else {
                            shortQuillFr.setText(shortInputFr.value);
                        }
                    }

                    if (fullInputFr.value) {
                        fullQuillFr.clipboard.dangerouslyPasteHTML(fullInputFr.value);
                    }

                    // Sync French editors on text changes
                    shortQuillFr.on('text-change', function() {
                        const htmlFr = shortQuillFr.root.innerHTML;
                        shortInputFr.value = htmlFr === '<p><br></p>' ? '' : htmlFr;
                    });

                    fullQuillFr.on('text-change', function() {
                        const htmlFr = fullQuillFr.root.innerHTML;
                        fullInputFr.value = htmlFr === '<p><br></p>' ? '' : htmlFr;
                    });

            function getShortText() {
                return (shortQuill.getText() || '').replace(/\n$/, '');
            }

            function syncShortDescription() {
                let text = getShortText();

                if (text.length > 250) {
                    shortQuill.deleteText(250, text.length);
                    text = getShortText();
                }

                const html = shortQuill.root.innerHTML;
                shortInput.value = html === '<p><br></p>' ? '' : html;
                const remaining = 250 - text.length;
                charCount.textContent = remaining;
            }

            function syncFullDescription() {
                const html = fullQuill.root.innerHTML;
                fullInput.value = html === '<p><br></p>' ? '' : html;
            }

            if (shortInput.value) {
                if (/<[a-z][\s\S]*>/i.test(shortInput.value)) {
                    shortQuill.clipboard.dangerouslyPasteHTML(shortInput.value);
                } else {
                    shortQuill.setText(shortInput.value);
                }
            }

            if (fullInput.value) {
                fullQuill.clipboard.dangerouslyPasteHTML(fullInput.value);
            }

            shortQuill.on('text-change', syncShortDescription);
            fullQuill.on('text-change', syncFullDescription);

            form.addEventListener('submit', function () {
                syncShortDescription();
                syncFullDescription();
                // sync french editors - ensure they're persisted before submit
                const htmlShortFr = shortQuillFr.root.innerHTML;
                shortInputFr.value = htmlShortFr === '<p><br></p>' ? '' : htmlShortFr;
                const htmlFullFr = fullQuillFr.root.innerHTML;
                fullInputFr.value = htmlFullFr === '<p><br></p>' ? '' : htmlFullFr;
            });

            syncShortDescription();
            syncFullDescription();
        })();
    </script>
@endsection
