@extends('layouts.admin')

@section('content')
    <div class="container mt-5">
        @php $currentStep = 3; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.activity._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <div style="margin-bottom:24px;">
                        <h2 style="font-weight:700;margin-bottom:8px;">Step 3: Photos & Media</h2>
                        <p style="color:#666;margin-bottom:0;">Service ID: <strong>{{ $activity->service_id }}</strong></p>
                        <p style="color:#666;margin-top:8px;margin-bottom:0;">Upload hero image, gallery images (min 3), vehicle/equipment photos, logo and optional video.</p>
                    </div>

                    {{-- Success/Error Messages --}}
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

                    @if(session('error'))
                    <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;">
                        <strong>✗ {{ session('error') }}</strong>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('operator.activity.step3.save', $activity->id) }}" enctype="multipart/form-data">
                        @csrf

                        {{-- Hero/Banner Image --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Hero / Banner Image *</h6>
                            <p style="color:#666;font-size:14px;margin-bottom:12px;">Primary banner image for the activity. This is the main image visitors will see first.</p>
                            
                            @if($activity->hero_banner_image)
                            <div style="margin-bottom:12px;">
                                <img src="{{ asset('storage/' . $activity->hero_banner_image) }}" alt="Hero Banner" style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid #ddd;">
                                <p style="font-size:13px;color:#666;margin-top:4px;">Current hero image</p>
                            </div>
                            @endif
                            
                            <input type="file" name="hero_banner_image" class="form-control" accept="image/*" {{ $activity->hero_banner_image ? '' : 'required' }}>
                            <small style="color:#999;display:block;margin-top:6px;">
                                {{ $activity->hero_banner_image ? 'Upload new image to replace current one (optional)' : 'Mandatory. Recommended size: 1920x1080px or similar ratio' }}
                            </small>
                        </div>

                        {{-- Gallery Images --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Gallery Images *</h6>
                            <p style="color:#666;font-size:14px;margin-bottom:12px;">Service image set - minimum 3 images required. Include at least <strong>two images of the activity</strong> and <strong>one image of equipment/gear</strong>.</p>
                            
                            @if($activity->gallery_images && count($activity->gallery_images) > 0)
                            <div style="margin-bottom:12px;">
                                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;margin-bottom:8px;">
                                    @foreach($activity->gallery_images as $image)
                                        <img src="{{ asset('storage/' . $image) }}" alt="Gallery" style="width:100%;height:100px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                                    @endforeach
                                </div>
                                <p style="font-size:13px;color:#666;">Current gallery: {{ count($activity->gallery_images) }} image(s)</p>
                            </div>
                            @endif
                            
                            <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple {{ ($activity->gallery_images && count($activity->gallery_images) >= 3) ? '' : 'required' }}>
                            <small style="color:#999;display:block;margin-top:6px;">
                                {{ ($activity->gallery_images && count($activity->gallery_images) >= 3) ? 'Upload additional images (optional) or replace existing gallery' : 'Select at least 3 images. Hold Ctrl/Cmd to select multiple files' }}
                            </small>
                        </div>

                        {{-- Vehicle/Equipment Images --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Vehicle / Equipment Images *</h6>
                            <p style="color:#666;font-size:14px;margin-bottom:12px;">Upload one photo for each vehicle or equipment used in this activity. Used in detailed description page.</p>
                            
                            @if($activity->vehicle_images && count($activity->vehicle_images) > 0)
                            <div style="margin-bottom:12px;background:#fff;padding:12px;border-radius:4px;border:1px solid #ddd;">
                                <p style="font-weight:600;margin-bottom:8px;font-size:13px;">Current Vehicle/Equipment Images:</p>
                                @foreach($activity->vehicle_images as $vehicle)
                                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;padding:8px;background:#f9f9f9;border-radius:4px;">
                                        <img src="{{ asset('storage/' . $vehicle['image']) }}" alt="{{ $vehicle['type'] }}" style="width:80px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                                        <span style="font-size:13px;font-weight:600;">{{ $vehicle['type'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                            @endif

                            <div id="vehicleImagesContainer">
                                <div class="vehicle-image-item" style="display:flex;gap:12px;align-items:end;margin-bottom:12px;">
                                    <div style="flex:1;">
                                        <label style="font-weight:600;display:block;margin-bottom:4px;">Vehicle/Equipment Type *</label>
                                        <select name="vehicle_types[]" class="form-control" required>
                                            <option value="">-- Select Type --</option>
                                            <option value="Boat">Boat</option>
                                            <option value="Bus/Coach">Bus/Coach</option>
                                            <option value="4x4 Vehicle">4x4 Vehicle</option>
                                            <option value="Bicycle">Bicycle</option>
                                            <option value="Kayak/Canoe">Kayak/Canoe</option>
                                            <option value="Diving Equipment">Diving Equipment</option>
                                            <option value="Hiking Gear">Hiking Gear</option>
                                            <option value="Safety Equipment">Safety Equipment</option>
                                            <option value="Other Equipment">Other Equipment</option>
                                        </select>
                                    </div>
                                    <div style="flex:1;">
                                        <label style="font-weight:600;display:block;margin-bottom:4px;">Image *</label>
                                        <input type="file" name="vehicle_images[]" class="form-control" accept="image/*" required>
                                    </div>
                                    <button type="button" class="btn btn-danger btn-sm remove-vehicle" style="display:none;padding:8px 12px;">Remove</button>
                                </div>
                            </div>
                            
                            <button type="button" id="addVehicleImage" class="btn" style="background:#19b5b5;color:#fff;padding:8px 16px;border-radius:4px;border:none;cursor:pointer;font-size:13px;margin-top:8px;">
                                + Add Another Vehicle/Equipment
                            </button>
                            <small style="color:#999;display:block;margin-top:8px;">At least one vehicle/equipment image is required</small>
                        </div>

                        {{-- Logo --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Logo (Optional)</h6>
                            <p style="color:#666;font-size:14px;margin-bottom:12px;">Upload your activity or operator logo.</p>
                            
                            @if($activity->logo)
                            <div style="margin-bottom:12px;">
                                <img src="{{ asset('storage/' . $activity->logo) }}" alt="Logo" style="max-width:150px;max-height:150px;border-radius:8px;border:1px solid #ddd;">
                                <p style="font-size:13px;color:#666;margin-top:4px;">Current logo</p>
                            </div>
                            @endif
                            
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <small style="color:#999;display:block;margin-top:6px;">Optional. Recommended size: 300x300px. PNG with transparent background preferred.</small>
                        </div>

                        {{-- Video --}}
                        <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                            <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Video (Optional)</h6>
                            <p style="color:#666;font-size:14px;margin-bottom:12px;">Upload a short promotional video of your activity.</p>
                            
                            @if($activity->video)
                            <div style="margin-bottom:12px;">
                                <video controls style="max-width:100%;max-height:300px;border-radius:8px;border:1px solid #ddd;">
                                    <source src="{{ asset('storage/' . $activity->video) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                                <p style="font-size:13px;color:#666;margin-top:4px;">Current video</p>
                            </div>
                            @endif
                            
                            <input type="file" name="video" class="form-control" accept="video/*">
                            <small style="color:#999;display:block;margin-top:6px;">Optional. Max file size: 50MB. Will be reviewed before publishing.</small>
                        </div>

                        {{-- Submit Buttons --}}
                        <div style="display:flex;justify-content:space-between;gap:12px;">
                            <a href="{{ route('operator.activity.show', $activity->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:4px;">
                                ← Back
                            </a>
                            <div style="display:flex;gap:8px;">
                                <a href="{{ route('operator.activity.show', $activity->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:4px;">
                                    Skip
                                </a>
                                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 24px;border-radius:4px;font-weight:600;">
                                    Upload & Continue
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let vehicleCount = 1;
            const container = document.getElementById('vehicleImagesContainer');
            const addButton = document.getElementById('addVehicleImage');

            // Show remove buttons if there's more than one item
            function updateRemoveButtons() {
                const items = container.querySelectorAll('.vehicle-image-item');
                items.forEach((item, index) => {
                    const removeBtn = item.querySelector('.remove-vehicle');
                    if (items.length > 1) {
                        removeBtn.style.display = 'inline-block';
                    } else {
                        removeBtn.style.display = 'none';
                    }
                });
            }

            // Add new vehicle/equipment row
            addButton.addEventListener('click', function() {
                vehicleCount++;
                const newItem = document.createElement('div');
                newItem.className = 'vehicle-image-item';
                newItem.style.cssText = 'display:flex;gap:12px;align-items:end;margin-bottom:12px;';
                newItem.innerHTML = `
                    <div style="flex:1;">
                        <label style="font-weight:600;display:block;margin-bottom:4px;">Vehicle/Equipment Type *</label>
                        <select name="vehicle_types[]" class="form-control" required>
                            <option value="">-- Select Type --</option>
                            <option value="Boat">Boat</option>
                            <option value="Bus/Coach">Bus/Coach</option>
                            <option value="4x4 Vehicle">4x4 Vehicle</option>
                            <option value="Bicycle">Bicycle</option>
                            <option value="Kayak/Canoe">Kayak/Canoe</option>
                            <option value="Diving Equipment">Diving Equipment</option>
                            <option value="Hiking Gear">Hiking Gear</option>
                            <option value="Safety Equipment">Safety Equipment</option>
                            <option value="Other Equipment">Other Equipment</option>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label style="font-weight:600;display:block;margin-bottom:4px;">Image *</label>
                        <input type="file" name="vehicle_images[]" class="form-control" accept="image/*" required>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm remove-vehicle" style="padding:8px 12px;">Remove</button>
                `;
                
                container.appendChild(newItem);
                updateRemoveButtons();

                // Add event listener to new remove button
                newItem.querySelector('.remove-vehicle').addEventListener('click', function() {
                    newItem.remove();
                    updateRemoveButtons();
                });
            });

            // Handle remove buttons for existing items
            container.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-vehicle')) {
                    e.target.closest('.vehicle-image-item').remove();
                    updateRemoveButtons();
                }
            });

            updateRemoveButtons();
        });
    </script>
    
    <!-- Back Button -->
    <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
        <a href="{{ route('operator.activity.show', $activity->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
            ← Back to Activity Overview
        </a>
    </div>
@endsection
