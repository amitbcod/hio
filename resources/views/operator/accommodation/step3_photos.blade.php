@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        @php $currentStep = 3; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.accommodation._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 32px;">
                    <div style="margin-bottom: 24px;">
                        <h2 style="font-weight: bold; margin-bottom: 8px;">Step 3: Photos & Media</h2>
                        <p style="color: #666; margin-bottom: 0;">Upload hero image, gallery, room photos, logo and optional video. Minimum 6 gallery images required.</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul style="margin-bottom: 0;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('operator.accommodation.saveStep3', $accommodation->id) }}" enctype="multipart/form-data">
                        @csrf
 <div class="step-3-form form-card">
                        <div class="col-md-6 mb-4">
                            <label style="font-weight: 600; display:block; margin-bottom:8px;">Hero / Banner Image (Primary)</label>
                            <input type="file" name="hero_image" accept="image/*" required>
                            <small style="color:#999; display:block; margin-top:6px;">Mandatory. Will be reviewed before publishing.</small>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label style="font-weight: 600; display:block; margin-bottom:8px;">Gallery Images (min 6)</label>
                            <input type="file" name="gallery[]" accept="image/*" multiple required>
                            <small style="color:#999; display:block; margin-top:6px;">Please upload at least 6 images for the property gallery.</small>
                        </div>

                        @if($rooms && $rooms->count())
                            <div class="col-md-6 mb-4">
                                <h5 style="font-weight:600;">Room Category Images</h5>
                                <p style="color:#666;">Upload at least one image per room category.</p>
                                @foreach($rooms as $room)
                                    <div style="margin-bottom:12px;">
                                        <label style="font-weight:600; display:block;">{{ $room->name }} (min 1)</label>
                                        <input type="file" name="room_gallery[{{ $room->id }}][]" accept="image/*" multiple required>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="col-md-6 mb-4">
                            <label style="font-weight: 600; display:block; margin-bottom:8px;">Logo (Optional)</label>
                            <input type="file" name="logo" accept="image/*">
                            <small style="color:#999; display:block; margin-top:6px;">Optional. Recommended size: 300x300px.</small>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label style="font-weight: 600; display:block; margin-bottom:8px;">Video (Optional)</label>
                            <input type="file" name="video_file" accept="video/*">
                            <small style="color:#999; display:block; margin-top:6px;">Optional. Upload a short promo video. Will be reviewed before publishing.</small>
                        </div>
</div>

                        <div style="display:flex; justify-content:flex-end; gap:8px;">
                            <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0; color:#333; padding:8px 14px; border-radius:4px;">← Back</a>
                            <button type="submit" class="btn" style="background:#19b5b5; color:#fff; padding:8px 18px; border-radius:4px;">Upload & Continue</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

 <script>
      function toggleMenu(element) {
         let submenu = element.nextElementSibling;

         element.classList.toggle("active");
         submenu.classList.toggle("hidden");
      }
   </script>
   <script>
      function toggleSidebar() {
         document.getElementById("sidebar").classList.toggle("active");
      }
   </script>

   <script>
      function toggleSidebar() {
         document.getElementById("sidebar").classList.toggle("active");
      }

      document.addEventListener("click", function (e) {
         let sidebar = document.getElementById("sidebar");
         let hamburger = document.querySelector(".hamburger");

         if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
            sidebar.classList.remove("active");
         }
      });
   </script>