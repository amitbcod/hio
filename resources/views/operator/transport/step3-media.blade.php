@extends('layouts.app')

@section('title', 'Transport Step 3 | Operator Dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-3 net-section">
            @php $currentStep = 3; @endphp
            @include('operator.transport._steps_sidebar')
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px; margin-top:40px">
                <h2 style="font-weight:700;margin:0;">Step 3: Media</h2>
                <p style="margin:8px 0 0 0;color:#666;">Upload photos and media for your transport service.</p>
            </div>

            <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    @if($transport->gallery_images && count($transport->gallery_images) > 0)
                    <div style="margin-bottom:12px;">
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;margin-bottom:8px;">
                            @foreach($transport->gallery_images as $index => $img)
                                <div style="position:relative;overflow:hidden;border-radius:4px;border:1px solid #ddd;">
                                    <img src="{{ asset('storage/' . $img) }}" alt="Media" style="width:100%;height:100px;object-fit:cover;display:block;">
                                    <form method="POST" action="{{ route('operator.transport.step3.delete-image', [$transport->id, $index]) }}" style="position:absolute;top:8px;right:8px;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn" style="background:rgba(0,0,0,0.55);color:#fff;border:none;border-radius:999px;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;" title="Delete image">&times;</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                        <p style="font-size:13px;color:#666;">Current media: {{ count($transport->gallery_images) }} file(s)</p>
                    </div>
                    @endif

                <form method="POST" action="{{ route('operator.transport.step3.save', $transport->id) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label style="font-weight:600;">Upload Photos</label>
                        <input type="file" name="media_files[]" class="form-control" multiple accept="image/*">
                    </div>

                    <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save Step 3</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
