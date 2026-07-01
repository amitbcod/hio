@extends('layouts.admin')

@section('content')
<div class="container">
    <h3>Create Policy Template</h3>
    <form action="{{ route('admin.policy-templates.store') }}" method="POST">
        @csrf
        @include('admin.policy_templates._form', ['template' => null, 'service' => $service])
        <button class="btn btn-primary">Save</button>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
(function initQuill(){
    const textarea = document.getElementById('content');
    const editorDiv = document.getElementById('content_editor');
    if(!textarea || !editorDiv) return;

    const quill = new Quill('#content_editor', {
        theme: 'snow',
        placeholder: 'Write template content (HTML)...',
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
        if(form && !form._quillSyncAttached) {
            form.addEventListener('submit', function(){ textarea.value = quill.root.innerHTML; });
            form._quillSyncAttached = true;
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachSync);
    } else {
        attachSync();
    }
})();
</script>
@endpush
