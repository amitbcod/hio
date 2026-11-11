@extends('layouts.app')

@section('title', 'Transport Step 5 | Operator Dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @php $currentStep = 5; @endphp
            @include('operator.transport._steps_sidebar')
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px; margin-top:40px">
                <h2 style="font-weight:700;margin:0;">Step 5: Service Description</h2>
                <p style="margin:8px 0 0 0;color:#666;">Add the full service description and key service details for the transport listing.</p>
            </div>

            @php
                $descriptionSections = [
                    'descriptions' => [
                        'title' => 'Long Description',
                        'button' => 'Save Descriptions',
                        'route' => 'operator.transport.step6-service-description.descriptions.save',
                        'fields' => [
                            ['name' => 'long_description', 'label' => 'Long Description', 'placeholder' => 'Write the full description of the transport service...', 'height' => 160],
                            ['name' => 'long_description_fr', 'label' => 'Long Description (Français)', 'placeholder' => 'Écrivez la description complète du service en français...', 'height' => 160],
                        ],
                    ],
                    'inclusions' => [
                        'title' => 'Inclusions',
                        'button' => 'Save Inclusions',
                        'route' => 'operator.transport.step6-service-description.inclusions.save',
                        'fields' => [
                            ['name' => 'inclusions', 'label' => 'Inclusions', 'placeholder' => 'List what is included...', 'height' => 120],
                            ['name' => 'inclusions_fr', 'label' => 'Inclusions (Français)', 'placeholder' => 'Listez ce qui est inclus (français)...', 'height' => 120],
                        ],
                    ],
                    'exclusions' => [
                        'title' => 'Exclusions',
                        'button' => 'Save Exclusions',
                        'route' => 'operator.transport.step6-service-description.exclusions.save',
                        'fields' => [
                            ['name' => 'exclusions', 'label' => 'Exclusions', 'placeholder' => 'List what is not included...', 'height' => 120],
                            ['name' => 'exclusions_fr', 'label' => 'Exclusions (Français)', 'placeholder' => 'Listez ce qui n\'est pas inclus (français)...', 'height' => 120],
                        ],
                    ],
                    'pickup-instructions' => [
                        'title' => 'Pickup Instructions',
                        'button' => 'Save Pickup Instructions',
                        'route' => 'operator.transport.step6-service-description.pickup-instructions.save',
                        'fields' => [
                            ['name' => 'pickup_instructions', 'label' => 'Pickup Instructions', 'placeholder' => 'Provide pickup instructions...', 'height' => 120],
                            ['name' => 'pickup_instructions_fr', 'label' => 'Pickup Instructions (Français)', 'placeholder' => 'Instructions de prise en charge (français)...', 'height' => 120],
                        ],
                    ],
                ];
            @endphp

            @foreach($descriptionSections as $sectionKey => $section)
                <form id="transport-service-description-{{ $sectionKey }}-form"
                      data-description-form
                      method="POST"
                      action="{{ route($section['route'], $transport->id) }}"
                      style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    @csrf
                    <h3 style="font-size:1.2rem;font-weight:600;margin-bottom:16px;">{{ $section['title'] }}</h3>

                    @if(session('transport_description_saved_section') === $sectionKey)
                        <div class="alert alert-success" role="status">{{ session('transport_description_success') }}</div>
                    @endif

                    @foreach($section['fields'] as $field)
                        <div class="mb-3">
                            <label class="form-label" for="{{ $field['name'] }}_editor">{{ $field['label'] }}</label>
                            <textarea name="{{ $field['name'] }}"
                                      id="{{ $field['name'] }}"
                                      class="form-control"
                                      data-quill-textarea
                                      data-editor-id="{{ $field['name'] }}_editor"
                                      data-label="{{ $field['label'] }}"
                                      style="display:none;">{{ old($field['name'], $transport->{$field['name']}) }}</textarea>
                            <div id="{{ $field['name'] }}_editor"
                                 data-quill-editor
                                 data-placeholder="{{ $field['placeholder'] }}"
                                 style="background:#fff;border:1px solid #ddd;border-radius:4px;min-height:{{ $field['height'] }}px;"></div>
                            @error($field['name'])
                                <div class="text-danger small mt-1" role="alert">{{ $message }}</div>
                            @enderror
                            <div class="text-danger small mt-1" data-client-error role="alert" style="display:none;"></div>
                        </div>
                    @endforeach

                    <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">
                        {{ $section['button'] }}
                    </button>
                </form>
            @endforeach
        </div>
    </div>
</div>
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
    (function () {
        document.querySelectorAll('[data-description-form]').forEach(function (form) {
            const editors = [];

            form.querySelectorAll('[data-quill-textarea]').forEach(function (textarea) {
                const editorElement = form.querySelector('#' + textarea.dataset.editorId);
                const quill = new Quill(editorElement, {
                    theme: 'snow',
                    placeholder: editorElement.dataset.placeholder,
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

                editors.push({ quill: quill, textarea: textarea });
            });

            form.addEventListener('submit', function (event) {
                if (form.dataset.submitting === '1') {
                    event.preventDefault();
                    return;
                }

                let firstInvalidField = null;
                editors.forEach(function (editor) {
                    const html = editor.quill.root.innerHTML;
                    editor.textarea.value = html === '<p><br></p>' ? '' : html;

                    const error = editor.textarea.parentElement.querySelector('[data-client-error]');
                    if (new TextEncoder().encode(editor.textarea.value).length > 65535) {
                        error.textContent = editor.textarea.dataset.label + ' exceeds the 65,535-byte database limit. Please shorten it before saving.';
                        error.style.display = 'block';
                        firstInvalidField = firstInvalidField || editor.textarea;
                    } else {
                        error.textContent = '';
                        error.style.display = 'none';
                    }
                });

                if (firstInvalidField) {
                    event.preventDefault();
                    firstInvalidField.parentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                form.dataset.submitting = '1';
                const submitButton = form.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.textContent = 'Saving...';
            });
        });
    })();
</script>
@endsection
