@extends('layouts.admin')

@php $sidebar = 'admin.packages._steps_sidebar'; $currentStep = 5; @endphp

@section('content')
<div class="container mt-4 mb-5">
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px; overflow:hidden;">
        <div class="card-body p-4">
            <h2 class="mb-1 fw-bold" style="font-size:2rem; color:#1f2a37;">Step 5: Content & CMS</h2>
            <p class="mb-0 text-muted">Add descriptive content, terms and SEO details for this package</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.packages.step5.store', $package->id) }}" enctype="multipart/form-data">
        @csrf
        @php
            $c = $content ?? [];
        @endphp

        <div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden;">
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Short Description *</label>
                    <input type="text" name="short_description" class="form-control" value="{{ old('short_description', $c['short_description'] ?? '') }}" placeholder="A brief one or two line summary shown on listing cards">
                    <div class="form-text">Recommended: under 160 characters</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Description *</label>
                    <textarea name="full_description" rows="6" class="form-control" placeholder="Detailed description of the package, highlights and experience">{{ old('full_description', $c['full_description'] ?? '') }}</textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Inclusion *</label>
                        <textarea name="inclusions" class="form-control" rows="3" placeholder="e.g., Breakfast, Airport transfers, Guided safari">{{ old('inclusions', $c['inclusions'] ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Exclusion</label>
                        <textarea name="exclusions" class="form-control" rows="3" placeholder="e.g., Personal expenses, Travel insurance">{{ old('exclusions', $c['exclusions'] ?? '') }}</textarea>
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label fw-semibold">Traveller Requirements</label>
                    <textarea name="traveller_requirements" class="form-control" rows="3" placeholder="e.g., Valid ID proof, minimum age, health advisories">{{ old('traveller_requirements', $c['traveller_requirements'] ?? '') }}</textarea>
                </div>

                <hr>
                <h5 class="fw-semibold">Image Gallery</h5>
                <p class="text-muted small">Click to upload or drag and drop package images PNG, JPG up to 5MB each</p>

                <div class="mb-3">
                    @php $existingGallery = $c['gallery'] ?? []; @endphp
                    @if(!empty($existingGallery))
                        <div class="mb-2">Existing Images</div>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            @foreach($existingGallery as $g)
                                <div style="position:relative; width:140px;">
                                    <img src="{{ asset('storage/' . $g) }}" style="width:140px;height:90px;object-fit:cover;border-radius:6px;border:1px solid #e9ecef;" />
                                    <div class="form-check" style="position:absolute;left:6px;top:6px;">
                                        <input class="form-check-input remove-gallery-check" type="checkbox" name="remove_gallery[]" value="{{ $g }}" id="rm_{{ md5($g) }}">
                                        <label class="form-check-label small text-white" for="rm_{{ md5($g) }}">Remove</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <label class="form-label fw-semibold">Upload Gallery Images</label>
                    <input type="file" name="gallery[]" multiple accept="image/*" class="form-control">
                    <div class="form-text">You may upload multiple images. Existing images remain unless removed.</div>
                </div>

                <div class="mb-3 row g-2 align-items-center">
                    <div class="col-md-9">
                        <label class="form-label fw-semibold">OG Share Image URL</label>
                        <input type="text" name="og_image_url" class="form-control" value="{{ old('og_image_url', $c['og_image_url'] ?? '') }}" placeholder="e.g., https://example.com/images/coco-verde-og.jpg">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">OG Image Upload</label>
                        <input type="file" name="og_image" accept="image/*" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tags</label>
                    <div id="tags-root" class="border rounded p-2" style="min-height:44px;">
                        <div id="tags-list" class="d-flex flex-wrap gap-2">
                            @foreach($c['tags'] ?? [] as $t)
                                <span class="badge bg-secondary tag-item">{{ $t }} <button type="button" class="btn-close btn-close-white btn-sm ms-1 remove-tag" aria-label="Remove"></button></span>
                            @endforeach
                        </div>
                        <input id="tags-input" type="text" class="form-control mt-2" placeholder="Add a tag and press Enter">
                        <input type="hidden" name="tags" id="tags-hidden" value="{{ old('tags', is_array($c['tags'] ?? null) ? implode(',', $c['tags']) : ($c['tags'] ?? '')) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">SEO Title</label>
                    <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $c['seo_title'] ?? '') }}" placeholder="e.g., 4 Nights Coco Verde Escape | Holidays.io">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">SEO Description</label>
                    <textarea name="seo_description" class="form-control" rows="3">{{ old('seo_description', $c['seo_description'] ?? '') }}</textarea>
                </div>

                <div class="card p-3 mb-3" style="background:#f8f9fb;border-radius:8px;">
                    <h6 class="mb-2">Social Media Custom Share Settings (Open Graph)</h6>
                    <div class="mb-2">
                        <label class="form-label">OG Share Title</label>
                        <input type="text" name="og_title" class="form-control" value="{{ old('og_title', $c['og_title'] ?? '') }}" placeholder="e.g., Experience 4 Nights of Pure Bliss at Coco Verde Resorts!">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">OG Share Description</label>
                        <input type="text" name="og_description" class="form-control" value="{{ old('og_description', $c['og_description'] ?? '') }}" placeholder="Custom text when sharing on social media platforms...">
                    </div>
                    <div class="mb-2 row g-2 align-items-center">
                        <div class="col-md-9">
                            <label class="form-label">OG Share Image URL</label>
                            <input type="text" name="og_image_url" class="form-control" value="{{ old('og_image_url', $c['og_image_url'] ?? '') }}" placeholder="e.g., https://example.com/images/coco-verde-og.jpg">
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-secondary mt-4">Upload Image</button>
                        </div>
                    </div>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Listing Category</label>
                        <select name="listing_category" class="form-select">
                            <option value="">Select category</option>
                            <option value="beach" {{ (old('listing_category', $c['listing_category'] ?? '') === 'beach') ? 'selected' : '' }}>Beach Getaway</option>
                            <option value="adventure" {{ (old('listing_category', $c['listing_category'] ?? '') === 'adventure') ? 'selected' : '' }}>Adventure</option>
                            <option value="family" {{ (old('listing_category', $c['listing_category'] ?? '') === 'family') ? 'selected' : '' }}>Family</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tags</label>
                        <div class="form-text">Manage tags using the tag widget above. They will be saved as a list.</div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.packages.step4', $package->id) }}" class="btn btn-outline-secondary">Back</a>
                    <div>
                        <button type="submit" class="btn btn-success">Next: Day-wise Itinerary</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const input = document.getElementById('tags-input');
    const list = document.getElementById('tags-list');
    const hidden = document.getElementById('tags-hidden');

    function updateHidden(){
        const tags = Array.from(list.querySelectorAll('.tag-item')).map(el => el.textContent.trim());
        hidden.value = tags.join(',');
    }

    input && input.addEventListener('keydown', function(e){
        if(e.key === 'Enter'){
            e.preventDefault();
            const v = input.value.trim();
            if(!v) return;
            const span = document.createElement('span');
            span.className = 'badge bg-secondary tag-item';
            span.innerHTML = v + ' <button type="button" class="btn-close btn-close-white btn-sm ms-1 remove-tag" aria-label="Remove"></button>';
            list.appendChild(span);
            input.value = '';
            updateHidden();
        }
    });

    document.body.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('remove-tag')){
            const parent = e.target.closest('.tag-item');
            if(parent) parent.remove();
            updateHidden();
        }
    });
});
</script>
@endsection
