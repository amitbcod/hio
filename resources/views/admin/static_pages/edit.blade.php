@extends('layouts.admin')

@section('content')
<div class="container">
    <h3>Edit Static Page</h3>
    <form action="{{ route('admin.static-pages.update', $staticPage) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $staticPage->title) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control" value="{{ old('slug', $staticPage->slug) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Meta Description</label>
            <textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $staticPage->meta_description) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">English Content</label>
            <textarea name="content_en" id="content_en" class="form-control" rows="10" style="display:none;">{{ old('content_en', $staticPage->content_en) }}</textarea>
            <div id="content_en_editor" class="wysiwyg-editor" style="min-height:160px;background:#fff;border:1px solid #ddd;border-radius:4px;margin-bottom:8px;"></div>
        </div>
        <div class="mb-3">
            <label class="form-label">French Content</label>
            <textarea name="content_fr" id="content_fr" class="form-control" rows="10" style="display:none;">{{ old('content_fr', $staticPage->content_fr) }}</textarea>
            <div id="content_fr_editor" class="wysiwyg-editor" style="min-height:160px;background:#fff;border:1px solid #ddd;border-radius:4px;margin-bottom:8px;"></div>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', $staticPage->is_active) ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('admin.static-pages.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
(function initQuillField(textareaId, editorId, placeholder) {
    const textarea = document.getElementById(textareaId);
    const editorDiv = document.getElementById(editorId);
    if(!textarea || !editorDiv) return;

    const quill = new Quill('#' + editorId, {
        theme: 'snow',
        placeholder: placeholder,
        modules: { toolbar: [[{ header: [1,2,3,false] }], ['bold','italic','underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link','blockquote'], ['clean']] }
    });

    if(textarea.value) {
        if(/<[a-z][\s\S]*>/i.test(textarea.value)) {
            quill.clipboard.dangerouslyPasteHTML(textarea.value);
        } else {
            quill.setText(textarea.value);
        }
    }

    const attachSync = function() {
        const form = textarea.closest('form');
        if(!form) return;
        form._quillSyncAttachedEditors = form._quillSyncAttachedEditors || {};
        if(!form._quillSyncAttachedEditors[textareaId]) {
            form.addEventListener('submit', function(){ textarea.value = quill.root.innerHTML; });
            form._quillSyncAttachedEditors[textareaId] = true;
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachSync);
    } else {
        attachSync();
    }
})('content_en', 'content_en_editor', 'Write English page content (HTML)...');

(function initQuillField(textareaId, editorId, placeholder) {
    const textarea = document.getElementById(textareaId);
    const editorDiv = document.getElementById(editorId);
    if(!textarea || !editorDiv) return;

    const quill = new Quill('#' + editorId, {
        theme: 'snow',
        placeholder: placeholder,
        modules: { toolbar: [[{ header: [1,2,3,false] }], ['bold','italic','underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link','blockquote'], ['clean']] }
    });

    if(textarea.value) {
        if(/<[a-z][\s\S]*>/i.test(textarea.value)) {
            quill.clipboard.dangerouslyPasteHTML(textarea.value);
        } else {
            quill.setText(textarea.value);
        }
    }

    const attachSync = function() {
        const form = textarea.closest('form');
        if(!form) return;
        form._quillSyncAttachedEditors = form._quillSyncAttachedEditors || {};
        if(!form._quillSyncAttachedEditors[textareaId]) {
            form.addEventListener('submit', function(){ textarea.value = quill.root.innerHTML; });
            form._quillSyncAttachedEditors[textareaId] = true;
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachSync);
    } else {
        attachSync();
    }
})('content_fr', 'content_fr_editor', 'Write French page content (HTML)...');
</script>
@endpush
