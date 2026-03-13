@extends('layouts.app')

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
                <div style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:16px;margin-bottom:16px;color:#2e7d32;">
                    <strong>✓ {{ session('success') }}</strong>
                </div>
                @endif

                @if(session('error'))
                <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;">
                    <strong>✗ {{ session('error') }}</strong>
                </div>
                @endif

                <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;">
                    <form method="POST" action="{{ route('operator.accommodation.step12.save', $accommodation->id) }}" enctype="multipart/form-data">
                        @csrf

                        <div style="margin-bottom:16px;">
                            <label style="font-weight:600;">SEO Title (≤60 chars)</label>
                            <input type="text" name="seo_title" class="form-control" maxlength="60" value="{{ old('seo_title', $accommodation->seo_title) }}">
                        </div>

                        <div style="margin-bottom:16px;">
                            <label style="font-weight:600;">SEO Description</label>
                            <textarea name="seo_description" id="seo_description" style="display:none;">{{ old('seo_description', $accommodation->seo_description) }}</textarea>
                            <div id="seo_description_editor" style="height:120px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
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
                            <label style="font-weight:600;">OpenGraph Description</label>
                            <textarea name="og_description" id="og_description" style="display:none;">{{ old('og_description', $accommodation->og_description) }}</textarea>
                            <div id="og_description_editor" style="height:120px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
                            <small style="color:#666;display:block;margin-top:4px;">Description for social media sharing with formatting support</small>
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
            
            if(seoTextarea.value){
                seoDescEditor.root.innerHTML = seoTextarea.value;
            }
            if(ogTextarea.value){
                ogDescEditor.root.innerHTML = ogTextarea.value;
            }

            // Sync editors with hidden textareas
            function syncSeoDesc(){
                seoTextarea.value = seoDescEditor.root.innerHTML;
            }

            function syncOgDesc(){
                ogTextarea.value = ogDescEditor.root.innerHTML;
            }

            seoDescEditor.on('text-change', syncSeoDesc);
            ogDescEditor.on('text-change', syncOgDesc);

            // Sync on form submit
            var form = document.querySelector('form');
            if(form){
                form.addEventListener('submit', function(){
                    syncSeoDesc();
                    syncOgDesc();
                });
            }
        });
    </script>
@endsection
