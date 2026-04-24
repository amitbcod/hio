@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        @php $currentStep = 3; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.accommodation._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 32px;">
                    <div style="margin-bottom: 24px;">
                        <h2 style="font-weight: bold; margin-bottom: 8px;">Step 3: Photos & Media</h2>
                        <p style="color: #666; margin-bottom: 0;">Upload hero image, gallery, room photos, logo and optional video. Minimum 6 gallery images required.</p>
                    </div> 

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul style="margin-bottom: 0;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

<<<<<<< HEAD
                    <form method="POST" action="{{ route('operator.accommodation.saveStep3', $accommodation->id) }}" enctype="multipart/form-data" class="form-card">
=======
                    @php
                        $media = $media ?? collect();
                        $heroMedia = $media->firstWhere('media_type', 'hero') ?? null;
                        $galleryMedia = $media->where('media_type', 'gallery')->values() ?? collect();
                        $logoMedia = $media->firstWhere('media_type', 'logo') ?? null;
                        $videoMedia = $media->firstWhere('media_type', 'video') ?? null;
                        $roomMedia = $media->whereNotNull('room_id')->groupBy('room_id') ?? collect();
                    @endphp

                    <form method="POST" action="{{ route('operator.accommodation.saveStep3', $accommodation->id) }}" enctype="multipart/form-data">
>>>>>>> 188e8ee752843816eafa98e191afdcdecad17c08
                        @csrf

                        <div style="margin-bottom: 24px;">
                            <h4 style="font-weight: 600; margin-bottom: 16px;">Add or Update Media</h4>
                        </div>

                        {{-- Hero / Banner Image --}}
                        <div style="margin-bottom: 28px; padding: 20px; background: #f9fafb; border-radius: 12px; border: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; flex-wrap: wrap;">
                                <div style="flex: 1; min-width: 260px;">
                                    <label style="font-weight: 600; display:block; margin-bottom:8px;">Hero / Banner Image (Primary)</label>
                                    <input id="hero_image_input" type="file" name="hero_image" accept="image/*" {{ !$heroMedia ? 'required' : '' }} style="margin-bottom: 8px;">
                                    <div id="hero_image_preview" style="display: none; margin-top: 12px;"></div>
                                    <small style="color:#999; display:block; margin-top:6px;">{{ $heroMedia ? 'Already uploaded. ' : '' }}Mandatory. Will be reviewed before publishing.</small>
                                </div>
                                <div style="min-width: 140px; text-align: center;">
                                    @if($heroMedia)
                                        <div style="position: relative; width: 140px; height: 100px; border-radius: 8px; overflow: hidden; border: 2px solid #19b5b5; margin-bottom: 8px;">
                                            <img src="{{ asset('storage/' . $heroMedia->path) }}" alt="Hero" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                        <button type="button" onclick="submitDeleteMedia({{ $accommodation->id }}, {{ $heroMedia->id }})" style="background: #ef4444; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">
                                            ✕ Remove
                                        </button>
                                    @else
                                        <div style="width: 140px; height: 100px; border-radius: 8px; border: 1px dashed #cbd5e1; display:flex; align-items:center; justify-content:center; color:#6b7280; font-size:12px;">
                                            No hero image yet
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Gallery Images --}}
                        <div style="margin-bottom: 28px; padding: 20px; background: #f9fafb; border-radius: 12px; border: 1px solid #e5e7eb;">
                            <label style="font-weight: 600; display:block; margin-bottom:8px;">Gallery Images (min 6)</label>
                            <input id="gallery_images_input" type="file" name="gallery[]" accept="image/*" multiple {{ ($galleryMedia && $galleryMedia->count() >= 6) ? '' : 'required' }} style="margin-bottom: 12px;">
                            <small style="color:#999; display:block; margin-bottom:16px;">{{ $galleryMedia ? 'Currently have ' . $galleryMedia->count() . ' images. ' : '' }}Upload at least 6 images total for the property gallery.</small>

                            <div id="gallery_images_preview" style="display:none; margin-bottom: 12px;"></div>

                            @if($galleryMedia->count() > 0)
                                <div style="border-top: 1px solid #e5e7eb; padding-top: 12px;">
                                    <p style="font-weight: 600; margin-bottom: 12px; font-size: 13px; color: #333;">Current Gallery ({{ $galleryMedia->count() }} images):</p>
                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px;">
                                        @foreach($galleryMedia as $item)
                                            <div style="position: relative; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0;">
                                                <img src="{{ asset('storage/' . $item->path) }}" alt="Gallery" style="width: 100%; height: 100px; object-fit: cover;">
                                                <button type="button" onclick="submitDeleteMedia({{ $accommodation->id }}, {{ $item->id }})" style="position: absolute; top: 4px; right: 4px; background: rgba(0,0,0,0.7); color: #fff; border: none; padding: 3px 6px; border-radius: 3px; cursor: pointer; font-size: 11px; font-weight: bold;">✕</button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Room Category Images --}}
                        @if($rooms && $rooms->count())
                            <div style="margin-bottom: 28px; padding: 20px; background: #f9fafb; border-radius: 12px; border: 1px solid #e5e7eb;">
                                <h5 style="font-weight:600; margin-bottom: 4px;">Room Category Images</h5>
                                <p style="color:#666; margin-bottom: 16px; font-size: 13px;">Upload at least one image per room category.</p>
                                
                                @foreach($rooms as $room)
                                    @php $roomImages = $media->where('room_id', $room->id); @endphp
                                    <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                            <div style="flex: 1;">
                                                <label style="font-weight:600; display:block; margin-bottom:4px;">{{ $room->name }} (min 1) {{ $roomImages->count() > 0 ? '- ' . $roomImages->count() . ' image(s)' : '' }}</label>
                                                <input id="room_gallery_{{ $room->id }}_input" class="room-gallery-input" data-room-id="{{ $room->id }}" type="file" name="room_gallery[{{ $room->id }}][]" accept="image/*" multiple {{ $roomImages->count() === 0 ? 'required' : '' }}>
                                                <div id="room_gallery_preview_{{ $room->id }}" style="display:none; margin-top: 12px;"></div>
                                            </div>
                                        </div>
                                        
                                        @if($roomImages->count() > 0)
                                            <div style="margin-top: 12px; padding: 12px; background: #fff; border-radius: 6px; border: 1px solid #e5e7eb;">
                                                <p style="font-weight: 500; font-size: 12px; margin-bottom: 8px; color: #555;">Current images:</p>
                                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                                    @foreach($roomImages as $item)
                                                        <div style="position: relative; width: 90px; height: 70px; border-radius: 6px; overflow: hidden; border: 1px solid #ddd; group;">
                                                            <img src="{{ asset('storage/' . $item->path) }}" alt="{{ $room->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                            <button type="button" onclick="submitDeleteMedia({{ $accommodation->id }}, {{ $item->id }})" style="position: absolute; top: 2px; right: 2px; background: rgba(239, 68, 68, 0.9); color: #fff; border: none; padding: 2px 5px; border-radius: 3px; cursor: pointer; font-size: 10px; font-weight: bold;">✕</button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Logo (Optional) --}}
                        <div style="margin-bottom: 28px; padding: 20px; background: #f9fafb; border-radius: 12px; border: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="flex: 1;">
                                    <label style="font-weight: 600; display:block; margin-bottom:8px;">Logo (Optional)</label>
                                    <input type="file" name="logo" accept="image/*" style="margin-bottom: 8px;">
                                    <small style="color:#999; display:block; margin-top:6px;">{{ $logoMedia ? 'Already uploaded. ' : '' }}Optional. Recommended size: 300x300px.</small>
                                </div>
                                @if($logoMedia)
                                    <div style="margin-left: 20px; text-align: center;">
                                        <div style="width: 110px; height: 110px; border-radius: 8px; overflow: hidden; border: 2px solid #e5e7eb; background: #f5f5f5; display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                            <img src="{{ asset('storage/' . $logoMedia->path) }}" alt="Logo" style="max-width: 95%; max-height: 95%; object-fit: contain;">
                                        </div>
                                        <button type="button" onclick="submitDeleteMedia({{ $accommodation->id }}, {{ $logoMedia->id }})" style="background: #ef4444; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">
                                            ✕ Remove
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Video (Optional) --}}
                        <div style="margin-bottom: 28px; padding: 20px; background: #f9fafb; border-radius: 12px; border: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="flex: 1;">
                                    <label style="font-weight: 600; display:block; margin-bottom:8px;">Video (Optional)</label>
                                    <input type="file" name="video_file" accept="video/*" style="margin-bottom: 8px;">
                                    <small style="color:#999; display:block; margin-top:6px;">{{ $videoMedia ? 'Already uploaded. ' : '' }}Optional. Upload a short promo video. Will be reviewed before publishing.</small>
                                </div>
                                @if($videoMedia)
                                    <div style="margin-left: 20px; text-align: center;">
                                        <div style="width: 180px; border-radius: 8px; overflow: hidden; border: 2px solid #e5e7eb; background: #000; margin-bottom: 8px;">
                                            <video style="width: 100%; height: auto; max-height: 120px;" controls>
                                                <source src="{{ asset('storage/' . $videoMedia->path) }}" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                        <button type="button" onclick="submitDeleteMedia({{ $accommodation->id }}, {{ $videoMedia->id }})" style="background: #ef4444; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">
                                            ✕ Remove
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div style="display:flex; justify-content:flex-end; gap:8px;">
                            <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0; color:#333; padding:8px 14px; border-radius:4px;">← Back</a>
                            <button type="submit" class="btn" style="background:#19b5b5; color:#fff; padding:8px 18px; border-radius:4px;">Upload & Continue</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function submitDeleteMedia(accommodationId, mediaId) {
            if (!confirm('Are you sure you want to delete this media item?')) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/operator/accommodation/' + accommodationId + '/media/' + mediaId + '/delete';
            form.style.display = 'none';

            const token = document.querySelector('input[name="_token"]');
            if (token) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_token';
                input.value = token.value;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }

        function createImageThumbnail(file) {
            const wrapper = document.createElement('div');
            wrapper.style.width = '100px';
            wrapper.style.height = '100px';
            wrapper.style.borderRadius = '8px';
            wrapper.style.overflow = 'hidden';
            wrapper.style.border = '1px solid #d1d5db';
            wrapper.style.background = '#fff';
            wrapper.style.display = 'inline-flex';
            wrapper.style.alignItems = 'center';
            wrapper.style.justifyContent = 'center';
            wrapper.style.position = 'relative';
            wrapper.style.marginRight = '8px';
            wrapper.style.marginBottom = '8px';

            const img = document.createElement('img');
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.src = URL.createObjectURL(file);
            img.onload = function() {
                URL.revokeObjectURL(this.src);
            };

            wrapper.appendChild(img);
            return wrapper;
        }

        function renderPreview(files, containerId) {
            const container = document.getElementById(containerId);
            if (!container) {
                return;
            }
            container.innerHTML = '';
            if (!files || files.length === 0) {
                container.style.display = 'none';
                return;
            }

            const heading = document.createElement('p');
            heading.style.margin = '0 0 10px 0';
            heading.style.fontSize = '13px';
            heading.style.fontWeight = '600';
            heading.style.color = '#374151';
            heading.textContent = 'Selected preview:';
            container.appendChild(heading);

            const grid = document.createElement('div');
            grid.style.display = 'flex';
            grid.style.flexWrap = 'wrap';
            grid.style.gap = '8px';

            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) {
                    return;
                }
                grid.appendChild(createImageThumbnail(file));
            });

            container.appendChild(grid);
            container.style.display = 'block';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const heroInput = document.getElementById('hero_image_input');
            const galleryInput = document.getElementById('gallery_images_input');

            if (heroInput) {
                heroInput.addEventListener('change', function() {
                    renderPreview(this.files, 'hero_image_preview');
                });
            }

            if (galleryInput) {
                galleryInput.addEventListener('change', function() {
                    renderPreview(this.files, 'gallery_images_preview');
                });
            }

            document.querySelectorAll('.room-gallery-input').forEach(function(input) {
                input.addEventListener('change', function() {
                    renderPreview(this.files, 'room_gallery_preview_' + this.dataset.roomId);
                });
            });
        });
    </script>

    <style>
        .group:hover form {
            display: block !important;
        }
    </style>
@endsection
