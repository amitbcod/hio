@extends('layouts.app')

@section('title', 'Transport Step 6 | Operator Dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @php $currentStep = 6; @endphp
            @include('operator.transport._steps_sidebar')
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px; margin-top:40px">
                <h2 style="font-weight:700;margin:0;">Step 6: Service Description</h2>
                <p style="margin:8px 0 0 0;color:#666;">Add the full service description and key service details for the transport listing.</p>
            </div>

            <form method="POST" action="{{ route('operator.transport.step6-service-description.save', $transport->id) }}">
                @csrf
                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    <div class="mb-3">
                        <label class="form-label">Long Description</label>
                        <textarea name="long_description" id="long_description" class="form-control" style="display:none;">{{ old('long_description', $transport->long_description) }}</textarea>
                        <div id="long_description_editor" style="background:#fff;border:1px solid #ddd;border-radius:4px;min-height:160px;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Long Description (Français)</label>
                        <textarea name="long_description_fr" id="long_description_fr" class="form-control" style="display:none;">{{ old('long_description_fr', $transport->long_description_fr) }}</textarea>
                        <div id="long_description_fr_editor" style="background:#fff;border:1px solid #ddd;border-radius:4px;min-height:160px;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Inclusions</label>
                        <textarea name="inclusions" id="inclusions" class="form-control" style="display:none;">{{ old('inclusions', $transport->inclusions) }}</textarea>
                        <div id="inclusions_editor" style="background:#fff;border:1px solid #ddd;border-radius:4px;min-height:120px;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Inclusions (Français)</label>
                        <textarea name="inclusions_fr" id="inclusions_fr" class="form-control" style="display:none;">{{ old('inclusions_fr', $transport->inclusions_fr) }}</textarea>
                        <div id="inclusions_fr_editor" style="background:#fff;border:1px solid #ddd;border-radius:4px;min-height:120px;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Exclusions</label>
                        <textarea name="exclusions" id="exclusions" class="form-control" style="display:none;">{{ old('exclusions', $transport->exclusions) }}</textarea>
                        <div id="exclusions_editor" style="background:#fff;border:1px solid #ddd;border-radius:4px;min-height:120px;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Exclusions (Français)</label>
                        <textarea name="exclusions_fr" id="exclusions_fr" class="form-control" style="display:none;">{{ old('exclusions_fr', $transport->exclusions_fr) }}</textarea>
                        <div id="exclusions_fr_editor" style="background:#fff;border:1px solid #ddd;border-radius:4px;min-height:120px;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pickup Instructions</label>
                        <textarea name="pickup_instructions" id="pickup_instructions" class="form-control" style="display:none;">{{ old('pickup_instructions', $transport->pickup_instructions) }}</textarea>
                        <div id="pickup_instructions_editor" style="background:#fff;border:1px solid #ddd;border-radius:4px;min-height:120px;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pickup Instructions (Français)</label>
                        <textarea name="pickup_instructions_fr" id="pickup_instructions_fr" class="form-control" style="display:none;">{{ old('pickup_instructions_fr', $transport->pickup_instructions_fr) }}</textarea>
                        <div id="pickup_instructions_fr_editor" style="background:#fff;border:1px solid #ddd;border-radius:4px;min-height:120px;"></div>
                    </div>
                </div>
                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save & Continue</button>
            </form>
        </div>
    </div>
</div>
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script>
        (function () {
            const form = document.querySelector('form');
            const fields = [
                { textareaId: 'long_description', editorId: 'long_description_editor', placeholder: 'Write the full description of the transport service...' },
                { textareaId: 'long_description_fr', editorId: 'long_description_fr_editor', placeholder: 'Écrivez la description complète du service en français...' },
                { textareaId: 'inclusions', editorId: 'inclusions_editor', placeholder: 'List what is included...' },
                { textareaId: 'inclusions_fr', editorId: 'inclusions_fr_editor', placeholder: 'Listez ce qui est inclus (français)...' },
                { textareaId: 'exclusions', editorId: 'exclusions_editor', placeholder: 'List what is not included...' },
                { textareaId: 'exclusions_fr', editorId: 'exclusions_fr_editor', placeholder: 'Listez ce qui n\'est pas inclus (français)...' },
                { textareaId: 'pickup_instructions', editorId: 'pickup_instructions_editor', placeholder: 'Provide pickup instructions...' },
                { textareaId: 'pickup_instructions_fr', editorId: 'pickup_instructions_fr_editor', placeholder: 'Instructions de prise en charge (français)...' }
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
                            [{ header: [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['link', 'blockquote'],
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
                    const html = quill.root.innerHTML;
                    const textarea = document.querySelector('textarea[id="' + quill.container.id.replace('_editor', '') + '"]');
                    if (textarea) {
                        textarea.value = html === '<p><br></p>' ? '' : html;
                    }
                });
            });
        })();
    </script>
@endsection
