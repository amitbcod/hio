@extends('layouts.admin')

@section('content')
    <!-- Quill WYSIWYG Editor -->
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    <div class="container mt-5">
        <div class="row">
            <!-- Sidebar -->
            @php $currentStep = 12; @endphp
            <div class="col-md-3">
                @include('operator.activity._steps_sidebar')
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Header -->
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:start;">
                        <div>
                            <h4 style="font-weight:600;color:#333;margin:0;">Step 12: SEO & Social</h4>
                            <p style="margin:4px 0 0 0;font-size:13px;color:#666;">{{ $activity->activity_name }}</p>
                        </div>
                        <div style="text-align:right;">
                            <p style="margin:0;font-size:12px;color:#999;">Service ID: {{ $activity->id }}</p>
                            <p style="margin:4px 0 0 0;font-size:12px;color:{{ $seoSocial ? '#28a745' : '#999' }};font-weight:600;">{{ $seoSocial ? 'Configured ✓' : 'Not Configured' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
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

                <!-- Info Box -->
                <div style="background:#e3f2fd;border-left:4px solid #2196f3;border-radius:6px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#1565c0;">
                    <i class="fas fa-info-circle"></i> <strong>Note:</strong> Optimize your activity for search engines and social sharing. These fields improve discoverability and engagement.
                </div>

                <!-- SEO & Social Form -->
                <div style="background:#fff;border-radius:12px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;">
                    <form method="POST" action="{{ route('operator.activity.step12.store', $activity->id) }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Short Description -->
                        <div class="mb-3">
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:8px;">
                                Short Description (up to 500 chars) *
                                <span style="color:#666;font-weight:normal;font-size:12px;">
                                    (Character count: <span id="shortDescCount" style="color:#666;">{{ strlen(old('short_description', $seoSocial->short_description ?? '')) }}</span>/500)
                                </span>
                            </label>
                            <textarea name="short_description" id="shortDescInput" required maxlength="500" style="display:none;">{{ old('short_description', $seoSocial->short_description ?? '') }}</textarea>
                            <div id="shortDescEditor" style="height:90px;background:#fff;border-radius:4px;"></div>
                            <small style="color:#666;">Used in search results and overview pages</small>
                        </div>

                        <!-- Full Description -->
                        <div class="mb-3">
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:8px;">
                                Full Description *
                                <span style="color:#666;font-weight:normal;font-size:12px;">
                                    (Character count: <span id="fullDescCount" style="color:#666;">{{ strlen(old('full_description', $seoSocial->full_description ?? '')) }}</span>)
                                </span>
                            </label>
                            <textarea name="full_description" id="fullDescInput" required style="display:none;">{{ old('full_description', $seoSocial->full_description ?? '') }}</textarea>
                            <div id="fullDescEditor" style="height:160px;background:#fff;border-radius:4px;"></div>
                            <small style="color:#666;">Comprehensive description for the activity details page</small>
                        </div>

                        <!-- Highlights -->
                        <div class="mb-3">
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:8px;">
                                Highlights (Up to 8 key points, one per line)
                            </label>
                            <textarea name="highlights" id="highlightsInput" style="display:none;">{{ old('highlights', $seoSocial->highlights ?? '') }}</textarea>
                            <div id="highlightsEditor" style="height:110px;background:#fff;border-radius:4px;"></div>
                            <small style="color:#666;">Key selling points separated by line breaks</small>
                        </div>

                        <hr style="margin:24px 0;">

                        <!-- SEO Section Header -->
                        <h6 style="font-weight:600;color:#333;margin:16px 0 12px 0;">
                            <i class="fas fa-search"></i> Search Engine Optimization (SEO)
                        </h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;font-size:13px;">
                                    SEO Title (≤60 chars)
                                    <span style="color:#666;font-weight:normal;">(<span id="seoTitleCount">{{ strlen(old('seo_title', $seoSocial->seo_title ?? '')) }}</span>/60)</span>
                                </label>
                                <input type="text" name="seo_title" class="form-control" maxlength="60" style="font-size:13px;" placeholder="Optimized title for search results" value="{{ old('seo_title', $seoSocial->seo_title ?? '') }}">
                                <small style="color:#666;">Title shown in Google search results</small>
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;font-size:13px;">
                                    SEO Description (≤500 chars)
                                    <span style="color:#666;font-weight:normal;">(</span><span id="seoDescCount">{{ strlen(old('seo_description', $seoSocial->seo_description ?? '')) }}</span>/500)</span>
                                </label>
                                <textarea name="seo_description" id="seoDescInput" maxlength="500" style="display:none;">{{ old('seo_description', $seoSocial->seo_description ?? '') }}</textarea>
                                <div id="seoDescEditor" style="height:80px;background:#fff;border-radius:4px;"></div>
                                <small style="color:#666;">Description shown below title in search</small>
                            </div>
                        </div>

                        <!-- Keywords/Tags -->
                        <div class="mb-3">
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:8px;">Keywords/Tags (separated by commas)</label>
                            <input type="text" id="keywordsInput" class="form-control" style="font-size:13px;" placeholder="kayaking, adventure, beach, water sports, snorkeling" value="{{ old('keywords_tags') ? implode(', ', old('keywords_tags')) : implode(', ', $keywords) }}">
                            <small style="color:#666;">Press Enter or comma to add each keyword</small>
                            
                            <div id="keywordsList" style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px;">
                                @php $displayKeywords = old('keywords_tags', $keywords) @endphp
                                @foreach($displayKeywords as $keyword)
                                    <span style="background:#e3f2fd;color:#2196f3;padding:6px 12px;border-radius:20px;font-size:12px;display:flex;align-items:center;gap:6px;">
                                        {{ $keyword }}
                                        <button type="button" onclick="removeKeyword(this)" style="background:none;border:none;color:#2196f3;cursor:pointer;font-weight:bold;">×</button>
                                    </span>
                                @endforeach
                            </div>
                            <div id="keywordsHiddenInputs">
                                @foreach($displayKeywords as $keyword)
                                    <input type="hidden" name="keywords_tags[]" value="{{ $keyword }}">
                                @endforeach
                            </div>
                        </div>

                        <hr style="margin:24px 0;">

                        <!-- Social Media Section Header -->
                        <h6 style="font-weight:600;color:#333;margin:16px 0 12px 0;">
                            <i class="fas fa-share-alt"></i> Open Graph (Social Sharing)
                        </h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;font-size:13px;">
                                    OG Title (≤60 chars)
                                    <span style="color:#666;font-weight:normal;">(<span id="ogTitleCount">{{ strlen(old('og_title', $seoSocial->og_title ?? '')) }}</span>/60)</span>
                                </label>
                                <input type="text" name="og_title" class="form-control" maxlength="60" style="font-size:13px;" placeholder="Title when shared on social media" value="{{ old('og_title', $seoSocial->og_title ?? '') }}">
                                <small style="color:#666;">Title shown when shared on Facebook, LinkedIn, etc.</small>
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;font-size:13px;">
                                    OG Description (≤500 chars)
                                    <span style="color:#666;font-weight:normal;">(</span><span id="ogDescCount">{{ strlen(old('og_description', $seoSocial->og_description ?? '')) }}</span>/500)</span>
                                </label>
                                <textarea name="og_description" id="ogDescInput" maxlength="500" style="display:none;">{{ old('og_description', $seoSocial->og_description ?? '') }}</textarea>
                                <div id="ogDescEditor" style="height:80px;background:#fff;border-radius:4px;"></div>
                                <small style="color:#666;">Description shown when link is shared</small>
                            </div>
                        </div>

                        <!-- OG Image -->
                        <div class="mb-3">
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:8px;">
                                OpenGraph Image (for social sharing)
                            </label>
                            <div style="display:flex;gap:12px;align-items:flex-start;">
                                <div style="flex:1;">
                                    <input type="file" name="og_image" class="form-control" accept="image/*" style="font-size:13px;">
                                    <small style="color:#666;">Recommended size: 1200×630px, max 2MB. Formats: JPG, PNG, GIF</small>
                                </div>
                                @if($seoSocial && $seoSocial->og_image_path)
                                <div style="flex:0 0 150px;">
                                    <img src="{{ asset('storage/' . $seoSocial->og_image_path) }}" style="width:100%;border-radius:4px;border:1px solid #ddd;" alt="OG Image">
                                    <small style="color:#666;display:block;margin-top:4px;">Current image</small>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:24px;padding-top:16px;border-top:1px solid #e0e0e0;">
                            <a href="{{ route('operator.activity.step11.show', $activity->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;">← Back to Step 11</a>
                            <button type="submit" id="submitBtn" style="padding:10px 20px;background:#19b5b5;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;">Save SEO & Social</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let keywordsList = {!! json_encode($keywords) !!};
        const quillEditors = {};

        function getEditorContainer(editorKey) {
            const editor = quillEditors[editorKey];
            return editor ? editor.container : null;
        }

        function setEditorBorder(editorKey, color) {
            const container = getEditorContainer(editorKey);
            if (container) {
                container.style.borderColor = color;
            }
        }

        function getEditorTextLength(editorKey) {
            const editor = quillEditors[editorKey];
            if (!editor) {
                return 0;
            }
            return editor.getText().replace(/\n$/, '').length;
        }

        function syncEditorToTextarea(editorKey, textareaId) {
            const editor = quillEditors[editorKey];
            const textarea = document.getElementById(textareaId);
            if (editor && textarea) {
                textarea.value = editor.root.innerHTML;
            }
        }

        function updateKeywordsList() {
            const list = document.getElementById('keywordsList');
            list.innerHTML = keywordsList.map(keyword => `
                <span style="background:#e3f2fd;color:#2196f3;padding:6px 12px;border-radius:20px;font-size:12px;display:flex;align-items:center;gap:6px;">
                    ${keyword}
                    <button type="button" onclick="removeKeyword(this)" style="background:none;border:none;color:#2196f3;cursor:pointer;font-weight:bold;">×</button>
                </span>
            `).join('');

            const hiddenContainer = document.getElementById('keywordsHiddenInputs');
            hiddenContainer.innerHTML = keywordsList.map(keyword =>
                `<input type="hidden" name="keywords_tags[]" value="${keyword}">`
            ).join('');
        }

        function removeKeyword(btn) {
            const keyword = btn.parentElement.textContent.trim().slice(0, -1).trim();
            keywordsList = keywordsList.filter(k => k !== keyword);
            updateKeywordsList();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitBtn = document.getElementById('submitBtn');

            const editorConfigs = [
                {
                    key: 'shortDesc',
                    editorId: 'shortDescEditor',
                    textareaId: 'shortDescInput',
                    placeholder: 'Brief, compelling summary of the activity...',
                    maxLength: 500,
                    height: '90px',
                    borderWidth: '2px'
                },
                {
                    key: 'fullDesc',
                    editorId: 'fullDescEditor',
                    textareaId: 'fullDescInput',
                    placeholder: 'Detailed description of the activity, experience, and what travelers can expect...',
                    height: '160px',
                    borderWidth: '2px'
                },
                {
                    key: 'highlights',
                    editorId: 'highlightsEditor',
                    textareaId: 'highlightsInput',
                    placeholder: '• Professional guide\n• Small group experience\n• Equipment included',
                    height: '110px'
                },
                {
                    key: 'seoDesc',
                    editorId: 'seoDescEditor',
                    textareaId: 'seoDescInput',
                    placeholder: 'Meta description for search results',
                    maxLength: 500,
                    height: '80px'
                },
                {
                    key: 'ogDesc',
                    editorId: 'ogDescEditor',
                    textareaId: 'ogDescInput',
                    placeholder: 'Description when shared on social platforms',
                    maxLength: 500,
                    height: '80px'
                }
            ];

            editorConfigs.forEach(function(cfg) {
                const textarea = document.getElementById(cfg.textareaId);
                const editorDiv = document.getElementById(cfg.editorId);
                if (!textarea || !editorDiv) {
                    return;
                }

                const editor = new Quill('#' + cfg.editorId, {
                    theme: 'snow',
                    placeholder: cfg.placeholder,
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            ['link'],
                            ['clean']
                        ]
                    }
                });

                quillEditors[cfg.key] = editor;

                if (textarea.value) {
                    editor.root.innerHTML = textarea.value;
                }

                const qlContainer = getEditorContainer(cfg.key);
                if (qlContainer) {
                    qlContainer.style.height = cfg.height;
                    if (cfg.borderWidth) {
                        qlContainer.style.borderWidth = cfg.borderWidth;
                    }
                }

                editor.on('text-change', function() {
                    if (cfg.maxLength) {
                        const length = getEditorTextLength(cfg.key);
                        if (length > cfg.maxLength) {
                            editor.deleteText(cfg.maxLength, editor.getLength());
                        }
                    }

                    syncEditorToTextarea(cfg.key, cfg.textareaId);

                    if (cfg.key === 'shortDesc') {
                        updateShortDescState();
                    }
                    if (cfg.key === 'fullDesc') {
                        updateFullDescState();
                    }
                    if (cfg.key === 'seoDesc') {
                        document.getElementById('seoDescCount').textContent = getEditorTextLength('seoDesc');
                    }
                    if (cfg.key === 'ogDesc') {
                        document.getElementById('ogDescCount').textContent = getEditorTextLength('ogDesc');
                    }
                });

                syncEditorToTextarea(cfg.key, cfg.textareaId);
            });

            function updateShortDescState() {
                const count = getEditorTextLength('shortDesc');
                const counter = document.getElementById('shortDescCount');

                counter.textContent = count;
                counter.style.color = '#666';
                setEditorBorder('shortDesc', '#ddd');
                return true;
            }

            function updateFullDescState() {
                const count = getEditorTextLength('fullDesc');
                const counter = document.getElementById('fullDescCount');

                counter.textContent = count;
                counter.style.color = '#666';
                setEditorBorder('fullDesc', '#ddd');
                return true;
            }

            updateShortDescState();
            updateFullDescState();
            document.getElementById('seoDescCount').textContent = getEditorTextLength('seoDesc');
            document.getElementById('ogDescCount').textContent = getEditorTextLength('ogDesc');

            form.addEventListener('submit', function(e) {
                syncEditorToTextarea('shortDesc', 'shortDescInput');
                syncEditorToTextarea('fullDesc', 'fullDescInput');
                syncEditorToTextarea('highlights', 'highlightsInput');
                syncEditorToTextarea('seoDesc', 'seoDescInput');
                syncEditorToTextarea('ogDesc', 'ogDescInput');

                updateShortDescState();
                updateFullDescState();

                submitBtn.disabled = true;
                submitBtn.textContent = 'Saving...';
            });

            document.querySelector('input[name="seo_title"]').addEventListener('input', function() {
                document.getElementById('seoTitleCount').textContent = this.value.length;
            });

            document.querySelector('input[name="og_title"]').addEventListener('input', function() {
                document.getElementById('ogTitleCount').textContent = this.value.length;
            });

            document.getElementById('keywordsInput').addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    const keyword = this.value.trim().replace(/,+$/, '').trim();
                    if (keyword && keyword.length > 0 && !keywordsList.includes(keyword)) {
                        keywordsList.push(keyword);
                        updateKeywordsList();
                        this.value = '';
                    }
                }
            });

            document.getElementById('keywordsInput').addEventListener('blur', function() {
                const keywords = this.value.split(',').map(k => k.trim()).filter(k => k.length > 0);
                keywords.forEach(keyword => {
                    if (!keywordsList.includes(keyword)) {
                        keywordsList.push(keyword);
                    }
                });
                if (keywords.length > 0) {
                    updateKeywordsList();
                    this.value = '';
                }
            });
        });
    </script>

<!-- Back Button -->
<div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
    <a href="{{ route('operator.activity.show', $activity->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
        ← Back to Activity Overview
    </a>
</div>
@endsection
