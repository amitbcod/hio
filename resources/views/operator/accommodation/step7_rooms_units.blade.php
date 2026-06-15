@extends('layouts.app')

@section('content')
    <!-- Quill WYSIWYG Editor -->
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    
    <div class="container mt-5">
        @php $currentStep = 7; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.accommodation._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <h2 style="font-weight:700;margin:0;">Step 7: Rooms & Units</h2>
                        <a href="{{ route('operator.accommodation.step7.show', $accommodation->id) }}#addRoom" class="btn" id="addRoomBtn" style="background:#19b5b5;color:#fff;padding:8px 12px;border-radius:4px;text-decoration:none;">Add Rooms</a>
                    </div>
                </div>

                {{-- Room listings at top --}}
                <div style="margin-bottom:16px;">
                    @if(isset($rooms) && count($rooms))
                        @foreach($rooms as $r)
                            <div style="background:#f5f5f5;padding:10px;border-radius:6px;display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                                <div style="flex:1;">
                                    <strong>{{ $r->room_name }}</strong> - <span style="opacity:0.8;">Bed: {{ $r->room_type }}</span>
                                    <div style="font-size:12px;color:#666;margin-top:6px;">
                                        Max Occupancy: Adults - {{ $r->capacity }} · Children - {{ $r->children_capacity ?? 0 }} · Infant - {{ $r->infant_capacity ?? 0 }}
                                        @if($r->max_person_capacity !== null)
                                            · Max Person Capacity - {{ $r->max_person_capacity }}
                                        @endif
                                    </div>
                                </div>
                                <div style="display:flex;gap:8px;">
                                    <!-- <a href="#" style="padding:6px 8px;background:#e0e0e0;border-radius:4px;color:#333;text-decoration:none;">View</a> -->
                                    <a href="{{ route('operator.accommodation.step7.room.edit', ['id' => $accommodation->id, 'room' => $r->id]) }}" style="padding:6px 8px;background:#fff;border:1px solid #e0e0e0;border-radius:4px;color:#333;text-decoration:none;">Edit</a>
                                    <form method="POST" action="{{ route('operator.accommodation.step7.room.delete', ['id' => $accommodation->id, 'room' => $r->id]) }}" onsubmit="return confirm('Delete this room?');">
                                        @csrf
                                        <button type="submit" style="padding:6px 8px;background:#ff6b6b;border-radius:4px;border:none;color:#fff;">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div style="background:#fff3cd;padding:12px;border-radius:6px;color:#856404;">No rooms added yet. Click "Add Rooms" to create the first room/unit.</div>
                    @endif
                </div>

                {{-- Form section --}}
                <div id="roomFormSection" style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);{{ isset($room) ? '' : 'display:none;' }}">
                    @if(isset($room))
                        <h4 style="margin-top:0;">Edit Room: {{ $room->room_name }}</h4>
                        <form method="POST" action="{{ route('operator.accommodation.step7.room.update', ['id' => $accommodation->id, 'room' => $room->id]) }}">
                    @else
                        <h4 style="margin-top:0;">Add Room / Unit</h4>
                        <form method="POST" action="{{ route('operator.accommodation.saveStep7', $accommodation->id) }}">
                    @endif
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;">Category / Name *</label>
                                <input type="text" name="room_name" class="form-control" required value="{{ old('room_name', $room->room_name ?? '') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;">Bedding / Room Type *</label>
                                <select name="room_type" class="form-control" required>
                                    <option value="">Select</option>
                                    @foreach(
                                        ['Single','Double','Twin','Suite','Deluxe','Family','Studio','Bungalow','Villa','Other'] as $type)
                                        <option value="{{ $type }}" {{ old('room_type', $room->room_type ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label style="font-weight:600;">Size (m²)</label>
                                <input type="number" name="size_sqm" step="0.1" class="form-control" value="{{ old('size_sqm', $room->size_sqm ?? '') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-weight:600;">View</label>
                                <select name="view" class="form-control">
                                    <option value="">Select</option>
                                    @foreach(['Garden','Sea','City','Mountain','Pool'] as $v)
                                        <option value="{{ $v }}" {{ old('view', $room->view ?? '') === $v ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-weight:600;">Smoking</label>
                                <select name="smoking" class="form-control">
                                    <option value="">Select</option>
                                    <option value="Non-smoking" {{ old('smoking', $room->smoking ?? '') === 'Non-smoking' ? 'selected' : '' }}>Non-smoking</option>
                                    <option value="Smoking" {{ old('smoking', $room->smoking ?? '') === 'Smoking' ? 'selected' : '' }}>Smoking</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label style="font-weight:600;">Short Description *</label>
                                <textarea name="short_description" id="short_description" style="display:none;" required>{{ old('short_description', $room->short_description ?? '') }}</textarea>
                                <div id="short_description_editor" style="height:100px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
                                <small style="color:#666;display:block;margin-top:4px;">Character limit: <span id="short_char_count">0</span> / 250</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label style="font-weight:600;">Full Description</label>
                                <textarea name="full_description" id="full_description" style="display:none;">{{ old('full_description', $room->room_description ?? '') }}</textarea>
                                <div id="full_description_editor" style="height:150px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;">Amenities *</label>
                                <select name="amenities[]" class="form-control" multiple required>
                                    @foreach(['Kettle','Mini-bar','AC','Safe','TV','Desk','Hairdryer','Fridge','Balcony','Kitchenette'] as $amen)
                                        <option value="{{ $amen }}" {{ (is_array(old('amenities', isset($room->amenities) ? json_decode($room->amenities, true) : [])) && in_array($amen, old('amenities', isset($room->amenities) ? json_decode($room->amenities, true) : []))) ? 'selected' : '' }}>{{ $amen }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;">Accessibility</label>
                                <select name="accessibility[]" class="form-control" multiple>
                                    @foreach(['Accessible bathroom','Roll-in shower','Grab rails','Visual alarms'] as $acc)
                                        <option value="{{ $acc }}" {{ (is_array(old('accessibility', isset($room->accessibility) ? json_decode($room->accessibility, true) : [])) && in_array($acc, old('accessibility', isset($room->accessibility) ? json_decode($room->accessibility, true) : []))) ? 'selected' : '' }}>{{ $acc }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label style="font-weight:600;">Occupancy – Adults *</label>
                                <input type="number" name="occupancy_adults" class="form-control" min="1" required value="{{ old('occupancy_adults', $room->capacity ?? 1) }}">
                                <small style="display:block;color:#666;margin-top:4px;">Adults are aged 17+ years.</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label style="font-weight:600;">Occupancy – Children *</label>
                                <input type="number" name="occupancy_children" class="form-control" min="0" required value="{{ old('occupancy_children', $room->children_capacity ?? 0) }}">
                                <small style="display:block;color:#666;margin-top:4px;">Children are aged 3-17 years. </small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-weight:600;">Occupancy – Infant</label>
                                <input type="number" name="occupancy_infant" class="form-control" min="0" value="{{ old('occupancy_infant', $room->infant_capacity ?? '') }}">
                                <small style="display:block;color:#666;margin-top:4px;">Infants are aged 0-2 years.</small>
                            </div>
                            </div>
                        </div>

                        {{-- Capacity and Allotment --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;">Capacity (Max Rooms/Units) *</label>
                                <input type="number" id="max_capacity" name="max_capacity" class="form-control" min="0" required value="{{ old('max_capacity', $room->max_capacity ?? '') }}">
                                <small style="color:#666;display:block;margin-top:4px;">Full capacity of this room category</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;">Max Person Capacity *</label>
                                <input type="number" id="max_person_capacity" name="max_person_capacity" class="form-control" min="1" required value="{{ old('max_person_capacity', $room->max_person_capacity ?? '') }}">
                                @error('max_person_capacity')
                                    <div style="color:#d32f2f;font-size:13px;margin-top:4px;">{{ $message }}</div>
                                @enderror
                                <small style="color:#666;display:block;margin-top:4px;">Maximum total persons allowed by this room type (adults + children + infants beyond the first).</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;">Allotment (Sellable Units) *</label>
                                <input type="number" id="allotment" name="allotment" class="form-control" min="0" required value="{{ old('allotment', $room->allotment ?? '') }}">
                                <small style="color:#666;display:block;margin-top:4px;">Number of sellable rooms/units (used in inventory management)</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label style="font-weight:600;">Images (select from uploaded media) *</label>
                                <select name="images[]" class="form-control" multiple required>
                                    @php
                                        $selectedImages = old('images', isset($room) ? ($accommodation->media->where('room_id', $room->id)->pluck('id')->toArray()) : []);
                                    @endphp
                                    @foreach($accommodation->media ?? [] as $m)
                                        <option value="{{ $m->id }}" {{ (is_array($selectedImages) && in_array($m->id, $selectedImages)) ? 'selected' : '' }}>{{ $m->original_name ?? ('Image ' . $m->id) }}</option>
                                    @endforeach
                                </select>
                                <small style="color:#666;display:block;margin-top:6px;">Minimum 4 images recommended (select from Photos & Media)</small>
                            </div>
                        </div>

                        <div style="display:flex;justify-content:space-between;gap:12px;float: right;">
                            <!-- <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">← Back</a> -->
                            <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:8px 14px;border-radius:4px;">Save Room</button>
                        </div>
                    </form>

                   
                </div>
                 <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:16px;">
                        <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">← Back</a>
                    </div>
                <script>
                    (function(){
                        // Initialize Quill editors
                        var shortDescEditor = new Quill('#short_description_editor', {
                            theme: 'snow',
                            placeholder: 'Enter a brief description (max 250 characters)...',
                            modules: {
                                toolbar: [
                                    ['bold', 'italic', 'underline'],
                                    [{'list': 'ordered'}, {'list': 'bullet'}],
                                    ['clean']
                                ]
                            }
                        });

                        var fullDescEditor = new Quill('#full_description_editor', {
                            theme: 'snow',
                            placeholder: 'Enter detailed description...',
                            modules: {
                                toolbar: [
                                    ['bold', 'italic', 'underline', 'strike'],
                                    [{'header': [1, 2, 3, false]}],
                                    [{'list': 'ordered'}, {'list': 'bullet'}],
                                    ['link'],
                                    ['clean']
                                ]
                            }
                        });

                        // Set initial content from textarea
                        var shortTextarea = document.getElementById('short_description');
                        var fullTextarea = document.getElementById('full_description');
                        
                        if(shortTextarea.value){
                            shortDescEditor.root.innerHTML = shortTextarea.value;
                        }
                        if(fullTextarea.value){
                            fullDescEditor.root.innerHTML = fullTextarea.value;
                        }

                        // Sync editors with hidden textarea
                        function syncShortDesc(){
                            var html = shortDescEditor.root.innerHTML;
                            shortTextarea.value = html;
                            
                            // Update character count
                            var text = shortDescEditor.getText().trim();
                            var charCount = text.length;
                            document.getElementById('short_char_count').textContent = charCount;
                            
                            // Show warning if exceeds limit
                            var charCountEl = document.getElementById('short_char_count');
                            if(charCount > 250){
                                charCountEl.style.color = '#ff0000';
                                charCountEl.style.fontWeight = 'bold';
                            } else {
                                charCountEl.style.color = '#666';
                                charCountEl.style.fontWeight = 'normal';
                            }
                        }

                        function syncFullDesc(){
                            fullTextarea.value = fullDescEditor.root.innerHTML;
                        }

                        shortDescEditor.on('text-change', syncShortDesc);
                        fullDescEditor.on('text-change', syncFullDesc);

                        // Sync on form submit
                        var form = document.querySelector('form');
                        if(form){
                            form.addEventListener('submit', function(){
                                syncShortDesc();
                                syncFullDesc();
                            });
                        }

                        // Initialize character count
                        syncShortDesc();

                        var addBtn = document.getElementById('addRoomBtn');
                        var formSection = document.getElementById('roomFormSection');
                        var firstInput = formSection ? formSection.querySelector('input[name="room_name"]') : null;

                        if(addBtn && formSection && !{{ isset($room) ? 'true' : 'false' }}){
                            addBtn.addEventListener('click', function(e){
                                e.preventDefault();
                                // toggle visibility: show and focus
                                formSection.style.display = '';
                                if(firstInput){
                                    firstInput.focus();
                                }
                                // scroll into view
                                formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            });
                        }

                        // If hash is #addRoom and form is hidden, show it
                        if(window.location.hash === '#addRoom' && formSection && formSection.style.display === 'none'){
                            formSection.style.display = '';
                            setTimeout(function(){
                                formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }, 200);
                        }

                        // Auto-populate allotment from capacity
                        var capacityInput = document.getElementById('max_capacity');
                        var allotmentInput = document.getElementById('allotment');
                        
                        if(capacityInput && allotmentInput){
                            capacityInput.addEventListener('input', function(){
                                // Only auto-populate if allotment is empty or not yet set
                                if(!allotmentInput.value || allotmentInput.value === '0'){
                                    allotmentInput.value = this.value;
                                }
                            });
                        }
                    })();
                </script>
                
                <!-- Back Button -->
                <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
                    <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
                        ← Back to Accommodation Overview
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
