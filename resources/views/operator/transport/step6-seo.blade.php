@extends('layouts.app')

@section('title', 'Transport Step 5 | Operator Dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @php $currentStep = 7; @endphp
            @include('operator.transport._steps_sidebar')
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px; margin-top:40px">
                <h2 style="font-weight:700;margin:0;">Step 7: SEO & Social</h2>
                <p style="margin:8px 0 0 0;color:#666;">Add metadata and social sharing details for your transport listing.</p>
            </div>

            <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                <form method="POST" action="{{ route('operator.transport.step6.save', $transport->id) }}">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label style="font-weight:600;">SEO Title</label>
                            <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $transport->seo_title) }}">
                        </div>
                        <div class="col-md-6">
                            <label style="font-weight:600;">Meta Description</label>
                            <input type="text" name="seo_description" class="form-control" value="{{ old('seo_description', $transport->seo_description) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label style="font-weight:600;">SEO Keywords</label>
                        <input type="text" name="seo_keywords" class="form-control" value="{{ old('seo_keywords', $transport->seo_keywords) }}">
                    </div>
                    <div class="mb-3">
                        <label style="font-weight:600;">Short Description</label>
                        <textarea name="short_description" id="short_description" class="form-control" style="display:none;">{{ old('short_description', $transport->short_description) }}</textarea>
                        <div id="short_description_editor" style="background:#fff;border:1px solid #ddd;border-radius:4px;min-height:120px;"></div>
                    </div>
                    <div class="mb-3">
                        <label style="font-weight:600;">Short Description (French)</label>
                        <textarea name="short_description_fr" id="short_description_fr" class="form-control" style="display:none;">{{ old('short_description_fr', $transport->short_description_fr) }}</textarea>
                        <div id="short_description_fr_editor" style="background:#fff;border:1px solid #ddd;border-radius:4px;min-height:120px;"></div>
                    </div>

                    <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save Step 7</button>
                </form>
            </div>
        </div>
    </div>
</div>
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script>
        (function () {
            const form = document.querySelector('form');
            const fields = [
                { textareaId: 'short_description', editorId: 'short_description_editor', placeholder: 'Write a short description for the transport listing...' },
                { textareaId: 'short_description_fr', editorId: 'short_description_fr_editor', placeholder: 'Écrivez une courte description en français...' }
            ];

            const editors = [];

            fields.forEach(function (field) {
                const textarea = document.getElementById(field.textareaId);
                const editorEl = document.getElementById(field.editorId);
                if (!textarea || !editorEl) return;

                const quill = new Quill('#' + field.editorId, {
                    theme: 'snow',
                    placeholder: field.placeholder,
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['clean']
                        ]
                    }
                });

                if (textarea.value) {
                    if (/<[a-z][\s\S]*>/i.test(textarea.value)) {
                        quill.clipboard.dangerouslyPasteHTML(textarea.value);
                    } else {
                        quill.setText(textarea.value);
                    }
                }

                quill.on('text-change', function () {
                    const html = quill.root.innerHTML;
                    textarea.value = html === '<p><br></p>' ? '' : html;
                });

                editors.push(quill);
            });

            form.addEventListener('submit', function () {
                editors.forEach(function (quill) {
                    const textarea = document.getElementById(quill.container.id.replace('_editor', ''));
                    if (textarea) {
                        const html = quill.root.innerHTML;
                        textarea.value = html === '<p><br></p>' ? '' : html;
                    }
                });
            });
        })();
    </script>
@endsection
