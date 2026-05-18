@extends('layouts.admin')

@section('content')
    <!-- Quill WYSIWYG Editor -->
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    <div class="container mt-5">
        @php $currentStep = 1; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.activity._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <h2 style="font-weight:700;margin:0;">Step 1: Basic Information</h2>
                    <p style="margin:8px 0 0 0;color:#666;">Service ID: <strong>{{ $activity->service_id }}</strong></p>
                </div>

                @if($errors->any())
                <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;">
                    <h5 style="margin-top:0;color:#c62828;">❌ Validation Errors:</h5>
                    @foreach($errors->all() as $error)
                        <div style="margin-bottom:4px;">• {{ $error }}</div>
                    @endforeach
                </div>
                @endif

                @if(session('success'))
                <div style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:16px;margin-bottom:16px;color:#2e7d32;">
                    <strong>✓ {{ session('success') }}</strong>
                </div>
                @endif

                <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;">
                    <form method="POST" action="{{ route('operator.activity.step1.save', $activity->id) }}" enctype="multipart/form-data">
                        @csrf

                        {{-- Service Type & Activity Name --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Service Details</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Service Type *</label>
                                    <select name="service_type" class="form-control" required>
                                        <option value="">-- Select Type --</option>
                                        @foreach($serviceTypes as $type)
                                            <option value="{{ $type }}" {{ old('service_type', $activity->service_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Activity Name (5–120 chars) *</label>
                                    <input type="text" name="activity_name" class="form-control" maxlength="120" required value="{{ old('activity_name', $activity->activity_name) }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Short Title (≤60 chars)</label>
                                    <input type="text" name="short_title" class="form-control" maxlength="60" value="{{ old('short_title', $activity->short_title) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Categorisation --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Categorisation</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Team Categories *</label>
                                    <small style="display:block;color:#666;margin-bottom:8px;">Select at least one</small>
                                    <div style="border:1px solid #ddd;padding:8px;border-radius:4px;max-height:150px;overflow-y:auto;">
                                        @foreach($teamCategories as $cat)
                                            <label style="display:flex;align-items:center;gap:8px;margin-bottom:6px;cursor:pointer;">
                                                <input type="checkbox" name="team_categories[]" value="{{ $cat }}" {{ in_array($cat, old('team_categories', $activity->team_categories ?? [])) ? 'checked' : '' }}>
                                                <span>{{ $cat }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Primary Themes</label>
                                    <small style="display:block;color:#666;margin-bottom:8px;">Optional</small>
                                    <div style="border:1px solid #ddd;padding:8px;border-radius:4px;max-height:150px;overflow-y:auto;">
                                        @foreach($primaryThemes as $theme)
                                            <label style="display:flex;align-items:center;gap:8px;margin-bottom:6px;cursor:pointer;">
                                                <input type="checkbox" name="primary_themes[]" value="{{ $theme }}" {{ in_array($theme, old('primary_themes', $activity->primary_themes ?? [])) ? 'checked' : '' }}>
                                                <span>{{ $theme }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Physical & Pricing --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Activity Level & Pricing</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Physical Level *</label>
                                    <select name="physical_level" class="form-control" required>
                                        <option value="">-- Select Level --</option>
                                        @foreach($physicalLevels as $level)
                                            <option value="{{ $level }}" {{ old('physical_level', $activity->physical_level) === $level ? 'selected' : '' }}>{{ $level }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Price Range *</label>
                                    <select name="price_range" class="form-control" required>
                                        <option value="">-- Select Range --</option>
                                        @foreach($priceRanges as $range)
                                            <option value="{{ $range }}" {{ old('price_range', $activity->price_range) === $range ? 'selected' : '' }}>{{ $range }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Location --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Location</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label style="font-weight:600;">Destination</label>
                                    <input type="text" name="destination" class="form-control" value="{{ old('destination', $activity->destination) }}">
                                </div>
                                <div class="col-md-4">
                                    <label style="font-weight:600;">Region</label>
                                    <input type="text" name="region" class="form-control" value="{{ old('region', $activity->region) }}">
                                </div>
                                <div class="col-md-4">
                                    <label style="font-weight:600;">Town</label>
                                    <input type="text" name="town" class="form-control" value="{{ old('town', $activity->town) }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Latitude *</label>
                                    <input type="number" name="latitude" class="form-control" step="0.00000001" min="-90" max="90" required value="{{ old('latitude', $activity->latitude) }}">
                                    <small style="color:#999;">e.g., -20.3484</small>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Longitude *</label>
                                    <input type="number" name="longitude" class="form-control" step="0.00000001" min="-180" max="180" required value="{{ old('longitude', $activity->longitude) }}">
                                    <small style="color:#999;">e.g., 57.5522</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label style="font-weight:600;">Meeting Point Details * (min 10 chars)</label>
                                    <textarea name="meeting_point_details" id="meeting_point_details" style="display:none;" required>{{ old('meeting_point_details', $activity->meeting_point_details) }}</textarea>
                                    <div id="meeting_point_details_editor" style="height:140px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
                                    <div id="error_meeting_point_details" style="display:none;color:#c62828;font-size:12px;margin-top:4px;padding:6px;background:#ffebee;border-radius:4px;"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Activity Content</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label style="font-weight:600;">Overview * (min 20 chars)</label>
                                    <textarea name="overview" id="overview" style="display:none;" required>{{ old('overview', $activity->overview) }}</textarea>
                                    <div id="overview_editor" style="height:170px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
                                    <div id="error_overview" style="display:none;color:#c62828;font-size:12px;margin-top:4px;padding:6px;background:#ffebee;border-radius:4px;"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label style="font-weight:600;">What are included in the activity? </label>
                                    <textarea name="whats_included" id="whats_included" style="display:none;" >{{ old('whats_included', $activity->whats_included) }}</textarea>
                                    <div id="whats_included_editor" style="height:170px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
                                    <div id="error_whats_included" style="display:none;color:#c62828;font-size:12px;margin-top:4px;padding:6px;background:#ffebee;border-radius:4px;"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label style="font-weight:600;">Itinerary * (min 20 chars)</label>
                                    <textarea name="itinerary" id="itinerary" style="display:none;" required>{{ old('itinerary', $activity->itinerary) }}</textarea>
                                    <div id="itinerary_editor" style="height:170px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
                                    <div id="error_itinerary" style="display:none;color:#c62828;font-size:12px;margin-top:4px;padding:6px;background:#ffebee;border-radius:4px;"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Duration (e.g., 7h 30m) *</label>
                                    <input type="text" name="duration" class="form-control" maxlength="50" required value="{{ old('duration', $activity->duration) }}">
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Suitable For Age (e.g., 5-65)</label>
                                    <input type="text" name="suitable_for_age" class="form-control" value="{{ old('suitable_for_age', $activity->suitable_for_age) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Languages & Booking --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Languages & Booking</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Languages Offered</label>
                                    <small style="display:block;color:#666;margin-bottom:8px;">Optional - select languages available</small>
                                    <div style="border:1px solid #ddd;padding:8px;border-radius:4px;max-height:120px;overflow-y:auto;">
                                        @foreach(['English', 'French', 'Mandarin', 'Spanish', 'German', 'Russian'] as $lang)
                                            <label style="display:flex;align-items:center;gap:8px;margin-bottom:4px;cursor:pointer;">
                                                <input type="checkbox" name="languages_offered[]" value="{{ $lang }}" {{ in_array($lang, old('languages_offered', $activity->languages_offered ?? [])) ? 'checked' : '' }}>
                                                <span>{{ $lang }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:600;">Booking Confirmation Type *</label>
                                    <select name="booking_confirmation_type" class="form-control" required>
                                        <option value="">-- Select Type --</option>
                                        @foreach($bookingConfirmationTypes as $type)
                                            <option value="{{ $type }}" {{ old('booking_confirmation_type', $activity->booking_confirmation_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    <small style="color:#999;">Instant: Automatically confirmed. On Request: Requires operator approval.</small>
                                </div>
                            </div>
                        </div>

                        {{-- Options --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Activity Options</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                        <input type="checkbox" name="add_ons_available" value="1" {{ old('add_ons_available', $activity->add_ons_available) ? 'checked' : '' }}>
                                        <span style="font-weight:600;">Add-ons Available (extras/options)</span>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                        <input type="checkbox" name="private_exclusive_option" value="1" {{ old('private_exclusive_option', $activity->private_exclusive_option) ? 'checked' : '' }}>
                                        <span style="font-weight:600;">Private/Exclusive Option Available</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div style="display:flex;gap:12px;">
                            <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;">
                                Save Step 1 & Continue
                            </button>
                            <a href="{{ route('operator.activity.show', $activity->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const validationRules = {
                'meeting_point_details': { min: 10, label: 'Meeting point details' },
                'overview': { min: 20, label: 'Overview' },
                'whats_included': { min: 20, label: 'Whats included' },
                'itinerary': { min: 20, label: 'Itinerary' }
            };

            const fieldConfigs = {
                'meeting_point_details': { placeholder: 'Enter meeting point details...' },
                'overview': { placeholder: 'Enter activity overview...' },
                'whats_included': { placeholder: 'Enter what is included in the activity...' },
                'itinerary': { placeholder: 'Enter itinerary details...' }
            };

            const editors = {};

            function getFieldTextLength(fieldName) {
                if (editors[fieldName]) {
                    return editors[fieldName].getText().trim().length;
                }
                const field = document.getElementById(fieldName);
                return field ? field.value.trim().length : 0;
            }

            function syncEditorToTextarea(fieldName) {
                const field = document.getElementById(fieldName);
                if (field && editors[fieldName]) {
                    field.value = editors[fieldName].root.innerHTML;
                }
            }

            function validateField(fieldName) {
                const errorDiv = document.getElementById('error_' + fieldName);
                const rule = validationRules[fieldName];
                const length = getFieldTextLength(fieldName);
                const editorContainer = editors[fieldName] ? editors[fieldName].container : null;

                if (!errorDiv || !rule) {
                    return true;
                }

                if (length > 0 && length < rule.min) {
                    errorDiv.textContent = '❌ The ' + rule.label + ' field must be at least ' + rule.min + ' characters. (Current: ' + length + ' chars)';
                    errorDiv.style.display = 'block';
                    if (editorContainer) {
                        editorContainer.style.borderColor = '#ef5350';
                    }
                    return false;
                }

                errorDiv.style.display = 'none';
                if (editorContainer) {
                    editorContainer.style.borderColor = '';
                }
                return true;
            }

            Object.keys(fieldConfigs).forEach(fieldName => {
                const editorElement = document.getElementById(fieldName + '_editor');
                const textarea = document.getElementById(fieldName);

                if (!editorElement || !textarea) {
                    return;
                }

                editors[fieldName] = new Quill('#' + fieldName + '_editor', {
                    theme: 'snow',
                    placeholder: fieldConfigs[fieldName].placeholder,
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            ['link'],
                            ['clean']
                        ]
                    }
                });

                if (textarea.value) {
                    editors[fieldName].root.innerHTML = textarea.value;
                }

                editors[fieldName].on('text-change', function() {
                    syncEditorToTextarea(fieldName);
                    const length = getFieldTextLength(fieldName);
                    const rule = validationRules[fieldName];
                    if (rule && (length >= rule.min || length === 0)) {
                        const errorDiv = document.getElementById('error_' + fieldName);
                        if (errorDiv) {
                            errorDiv.style.display = 'none';
                        }
                        editors[fieldName].container.style.borderColor = '';
                    }
                });

                editors[fieldName].on('selection-change', function(range, oldRange) {
                    if (!range && oldRange) {
                        syncEditorToTextarea(fieldName);
                        validateField(fieldName);
                    }
                });
            });

            // Validate on form submit
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    let hasError = false;

                    Object.keys(validationRules).forEach(fieldName => {
                        syncEditorToTextarea(fieldName);
                        const isValid = validateField(fieldName);
                        if (!isValid) {
                            hasError = true;
                        }
                    });

                    if (hasError) {
                        e.preventDefault();
                        // Scroll to first error
                        const firstError = document.querySelector('[id^="error_"][style*="display: block"]');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                });
            }
        });
    </script>
    
    <!-- Back Button -->
    <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
        <a href="{{ route('operator.activity.show', $activity->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
            ← Back to Activity Overview
        </a>
    </div>
    </div>
@endsection
