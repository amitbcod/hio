@extends('layouts.app')

@section('title', 'Edit Transport | Operator Dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @php $currentStep = 1; @endphp
            @include('operator.transport._steps_sidebar')
        </div>
        <div class="col-md-9 transport-rightside">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px; margin-top:40px">
                <h2 style="font-weight:700;margin:0;">Edit Transport</h2>
                <p style="margin:8px 0 0 0;color:#666;">Update transport service details.</p>
            </div>

            @if ($errors->any())
                <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;">
                    <h5 style="margin-top:0;color:#c62828;">❌ Validation Errors:</h5>
                    @foreach ($errors->all() as $error)
                        <div style="margin-bottom:4px;">• {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('operator.transport.update', $transport->id) }}">
                @csrf
                @method('PUT')

                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label style="font-weight:600;">Vehicle Name <span style="color:#d32f2f">*</span></label>
                            <input type="text" name="vehicle_name" class="form-control @error('vehicle_name') is-invalid @enderror" value="{{ old('vehicle_name', $transport->vehicle_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label style="font-weight:600;">Vehicle Type <span style="color:#d32f2f">*</span></label>
                            <input type="text" class="form-control" value="{{ old('vehicle_type', $transport->vehicle_type) }}" readonly>
                            <input type="hidden" name="vehicle_type" value="{{ old('vehicle_type', $transport->vehicle_type) }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label style="font-weight:600;">Seating Capacity <span style="color:#d32f2f">*</span></label>
                            <input type="number" name="seating_capacity" class="form-control @error('seating_capacity') is-invalid @enderror" value="{{ old('seating_capacity', $transport->seating_capacity) }}" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label style="font-weight:600;">Registration Number</label>
                            <input type="text" name="registration_number" class="form-control @error('registration_number') is-invalid @enderror" value="{{ old('registration_number', $transport->registration_number) }}">
                        </div>
                        <div class="col-md-4">
                            <label style="font-weight:600;">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror" value="{{ old('contact_email', $transport->contact_email) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label style="font-weight:600;">Service Description</label>
                        <textarea name="service_description" id="service_description" class="form-control @error('service_description') is-invalid @enderror" style="display:none;">{{ old('service_description', $transport->service_description) }}</textarea>
                        <div id="service_description_editor" style="background:#fff;border:1px solid #ddd;border-radius:4px;min-height:140px;"></div>
                    </div>
                </div>

                <div style="display:flex;gap:12px;flex-wrap:wrap; padding:18px">
                    <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save Changes</button>
                    <a href="{{ route('operator.transport.show', $transport->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:4px;border:none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script>
        (function () {
            const form = document.querySelector('form');
            const textarea = document.getElementById('service_description');
            const editorEl = document.getElementById('service_description_editor');

            if (!textarea || !editorEl) return;

            const quill = new Quill('#service_description_editor', {
                theme: 'snow',
                placeholder: 'Provide a detailed service description...',
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

            form.addEventListener('submit', function () {
                const html = quill.root.innerHTML;
                textarea.value = html === '<p><br></p>' ? '' : html;
            });
        })();
    </script>
@endsection
