@extends('layouts.admin')

@section('content')
    <!-- Quill WYSIWYG Editor -->
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    
    <div class="container mt-5">
        @php $currentStep = 12; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.accommodation._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <h2 style="font-weight:700;margin:0;">Step 12: SEO & Social</h2>
                </div>

                @if(session('success'))
                <div class="alert alert-success" style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:16px;margin-bottom:16px;color:#2e7d32;">
                    <strong>✓ {{ session('success') }}</strong>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger" style="background:#fff4e5;border:1px solid #ffb74d;border-radius:8px;padding:16px;margin-bottom:16px;color:#e65100;">
                    <strong>Please fix the highlighted fields below.</strong>
                    <ul style="margin:8px 0 0 18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- AJAX Messages --}}
                <div id="ajax-success" class="alert alert-success" style="display:none;background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:16px;margin-bottom:16px;color:#2e7d32;"></div>
                <div id="ajax-error" class="alert alert-danger" style="display:none;background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;"></div>

                <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;">
                    <form method="POST" action="{{ route('operator.accommodation.step12.save', $accommodation->id) }}" enctype="multipart/form-data">
                        @csrf

                        <div style="margin-bottom:16px;">
                            <label style="font-weight:600;">SEO Title (≤60 chars)</label>
                            <input type="text" name="seo_title" class="form-control" maxlength="60" value="{{ old('seo_title', $accommodation->seo_title) }}">
                        </div>

                        <div style="margin-bottom:16px;">
                            <label style="font-weight:600;">SEO Description (≤500 chars)</label>
                            <textarea name="seo_description" id="seo_description" style="display:none;">{{ old('seo_description', $accommodation->seo_description) }}</textarea>
                            <div id="seo_description_editor" style="height:120px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
                                <small id="seo_description_count" style="color:#666;">0 / 500</small>
                                <small id="seo_description_error" style="color:#d93025;display:none;"></small>
                            </div>
                            @error('seo_description')<small style="color:#d93025;display:block;margin-top:4px;">{{ $message }}</small>@enderror
                            <small style="color:#666;display:block;margin-top:4px;">Optimized description for search engines with formatting support</small>
                        </div>

                        <div style="margin-bottom:16px;">
                            <label style="font-weight:600;">Keywords / Tags (comma separated)</label>
                            <input type="text" name="keywords_tags" class="form-control" value="{{ old('keywords_tags', $accommodation->keywords_tags) }}">
                        </div>

                        <hr>

                        <div style="margin-bottom:16px;">
                            <label style="font-weight:600;">OpenGraph Title (≤60 chars)</label>
                            <input type="text" name="og_title" class="form-control" maxlength="60" value="{{ old('og_title', $accommodation->og_title) }}">
                        </div>

                        <div style="margin-bottom:16px;">
                            <label style="font-weight:600;">OpenGraph Description <small style="font-weight:400;color:#666;">(max 500 characters)</small></label>
                            <textarea name="og_description" id="og_description" style="display:none;">{{ old('og_description', $accommodation->og_description) }}</textarea>
                            <div id="og_description_editor" style="height:120px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
                                <small id="og_description_count" style="color:#666;">0 / 500</small>
                                <small id="og_description_error" style="color:#d93025;display:none;"></small>
                            </div>
                            @error('og_description')<small style="color:#d93025;display:block;margin-top:4px;">{{ $message }}</small>@enderror
                            <small style="color:#666;display:block;margin-top:4px;">Description for social media sharing as plain text</small>
                        </div>

                        <div style="margin-bottom:16px;">
                            <label style="font-weight:600;">OpenGraph Image</label>
                            <input type="file" name="og_image" accept="image/*" class="form-control">
                            @if($accommodation->og_image)
                                <div style="margin-top:8px;display:flex;align-items:center;gap:8px;">
                                    <a href="{{ asset('storage/' . $accommodation->og_image) }}" target="_blank" style="display:inline-block;">
                                        <img src="{{ asset('storage/' . $accommodation->og_image) }}" alt="OpenGraph image" style="max-width:120px;max-height:80px;object-fit:cover;border-radius:6px;border:1px solid #e0e0e0;">
                                    </a>
                                    <small style="color:#666;">Click thumbnail to view full image</small>
                                </div>
                            @endif
                        </div>

                        <div style="display:flex;gap:12px;">
                            <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;">Save SEO & Social</button>
                            <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;">Back to Property</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Quill editors
        document.addEventListener('DOMContentLoaded', function() {
            // SEO Description Editor
            var seoDescEditor = new Quill('#seo_description_editor', {
                theme: 'snow',
                placeholder: 'Enter SEO-optimized description...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{'list': 'ordered'}, {'list': 'bullet'}],
                        ['link'],
                        ['clean']
                    ]
                }
            });

            // OpenGraph Description Editor
            var ogDescEditor = new Quill('#og_description_editor', {
                theme: 'snow',
                placeholder: 'Enter description for social media sharing...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{'list': 'ordered'}, {'list': 'bullet'}],
                        ['link'],
                        ['clean']
                    ]
                }
            });

            // Set initial content from textareas
            var seoTextarea = document.getElementById('seo_description');
            var ogTextarea = document.getElementById('og_description');
            var seoDescriptionCount = document.getElementById('seo_description_count');
            var ogDescriptionCount = document.getElementById('og_description_count');
            var seoDescriptionError = document.getElementById('seo_description_error');
            var ogDescriptionError = document.getElementById('og_description_error');
            var seoDescriptionMax = 500;
            var ogDescriptionMax = 500;

            if(seoTextarea.value){
                seoDescEditor.root.innerHTML = seoTextarea.value;
            }
            if(ogTextarea.value){
                ogDescEditor.root.innerHTML = ogTextarea.value;
            }

            function updateDescriptionCounter(editor, countEl, maxLength) {
                if (!countEl) return;
                var currentLength = editor.getText().trim().length;
                countEl.textContent = currentLength + ' / ' + maxLength;
                countEl.style.color = currentLength > maxLength ? '#d93025' : '#666';
            }

            function validateDescriptionLengths() {
                var seoLength = seoDescEditor.getText().trim().length;
                var ogLength = ogDescEditor.getText().trim().length;
                var valid = true;

                if (seoDescriptionError) {
                    if (seoLength > seoDescriptionMax) {
                        seoDescriptionError.style.display = 'block';
                        seoDescriptionError.textContent = 'SEO Description exceeds ' + seoDescriptionMax + ' characters.';
                        valid = false;
                    } else {
                        seoDescriptionError.style.display = 'none';
                        seoDescriptionError.textContent = '';
                    }
                }

                if (ogDescriptionError) {
                    if (ogLength > ogDescriptionMax) {
                        ogDescriptionError.style.display = 'block';
                        ogDescriptionError.textContent = 'OpenGraph description exceeds ' + ogDescriptionMax + ' characters.';
                        valid = false;
                    } else {
                        ogDescriptionError.style.display = 'none';
                        ogDescriptionError.textContent = '';
                    }
                }

                return valid;
            }

            function syncSeoDesc(){
                seoTextarea.value = seoDescEditor.getText().trim();
                updateDescriptionCounter(seoDescEditor, seoDescriptionCount, seoDescriptionMax);
            }

            function syncOgDesc(){
                ogTextarea.value = ogDescEditor.getText().trim();
                updateDescriptionCounter(ogDescEditor, ogDescriptionCount, ogDescriptionMax);
            }

            seoDescEditor.on('text-change', function() {
                syncSeoDesc();
            });
            ogDescEditor.on('text-change', function() {
                syncOgDesc();
            });

            // Sync and validate on form submit only
            var form = document.querySelector('form');
            if(form){
                var submitButton = form.querySelector('button[type="submit"]');
                form.addEventListener('submit', function(event){
                    event.preventDefault(); // Prevent default form submission
                    syncSeoDesc();
                    syncOgDesc();
                    if (!validateDescriptionLengths()) {
                        if (submitButton) {
                            submitButton.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        return; // Don't proceed if validation fails
                    }

                    // If validation passes, send AJAX request
                    var formData = new FormData(form);
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            var successDiv = document.getElementById('ajax-success');
                            if (successDiv) {
                                successDiv.style.display = 'block';
                                successDiv.innerHTML = '<strong>✓ ' + data.message + '</strong>';
                                successDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                            // Optionally reload or update UI
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            // Show error message
                            var errorDiv = document.getElementById('ajax-error');
                            if (errorDiv) {
                                errorDiv.style.display = 'block';
                                errorDiv.innerHTML = '<strong>✗ ' + data.message + '</strong>';
                                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        var errorDiv = document.getElementById('ajax-error');
                        if (errorDiv) {
                            errorDiv.style.display = 'block';
                            errorDiv.innerHTML = '<strong>✗ An error occurred. Please try again.</strong>';
                        }
                    });
                });
            }

            updateDescriptionCounter(seoDescEditor, seoDescriptionCount, seoDescriptionMax);
            updateDescriptionCounter(ogDescEditor, ogDescriptionCount, ogDescriptionMax);
        });
    </script>
@endsection
