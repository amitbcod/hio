@extends('layouts.app')

@section('content')
    <div class="container mt-0">
        <div class="row">
            <div id="sidebar" class="col-md-3 net-section">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9 my-pro">
                <div style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 40px;margin-top: 40px;">
                    
                    {{-- Header --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                        <div>
                            <h2 style="font-weight: bold; margin-bottom: 8px;">My Properties</h2>
                            <p style="color: #666; margin-bottom: 0;">Manage and set up your properties</p>
                        </div>
                        <div style="display: flex; gap: 12px;">
                            <a href="{{ route('operator.accommodation.bookings') }}" class="btn" style="background: #17a2b8; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">
                                📅 View Bookings
                            </a>
                            <a href="{{ route('operator.accommodation.create') }}" class="btn" style="background: #19b5b5; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600;">
                                + Add New Property
                            </a>
                        </div>
                    </div>

                    {{-- Alerts --}}
                    @if(session('success'))
                        <div class="alert alert-success" style="margin-bottom: 20px;">{{ session('success') }}</div>
                    @endif

                    {{-- Accommodations List --}}
                    @if($accommodations->isEmpty())
                        <div style="background: #f8f8f8; padding: 40px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 16px;">🏠</div>
                            <h5 style="font-weight: 600; margin-bottom: 8px;">No Properties Yet</h5>
                            <p style="color: #666; margin-bottom: 16px;">Start by adding your first property to begin setting it up on HolidaysIO</p>
                            <a href="{{ route('operator.accommodation.create') }}" class="btn" style="background: #19b5b5; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600; display: inline-block;">
                                Create First Property
                            </a>
                        </div>
                    @else
                        <div class="row">
                            @foreach($accommodations as $accommodation)
                                <div class="col-md-6 mb-4">
                                    <div style="background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; transition: all 0.3s;">
                                        {{-- Card Header with Status --}}
                                        <div style="background: linear-gradient(135deg, #19b5b5, #139999); color: #fff; padding: 16px;">
                                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                                <div>
                                                    <h5 style="font-weight: 600; margin-bottom: 4px;">{{ $accommodation->property_name }}</h5>
                                                    <p style="margin-bottom: 0; font-size: 12px; opacity: 0.9;">ID: {{ $accommodation->accommodation_id }}</p>
                                                </div>
                                                <span class="badge" style="background: rgba(255,255,255,0.3); color: #fff; font-weight: 600; padding: 4px 8px; border-radius: 4px;">
                                                    {{ $accommodation->status }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Card Body --}}
                                        <div style="padding: 16px;">
                                            {{-- Type and Location --}}
                                            <p style="margin-bottom: 12px; color: #666;">
                                                <strong>{{ $accommodation->property_type }}</strong> in <strong>{{ $accommodation->city }}, {{ $accommodation->country }}</strong>
                                            </p>

                                            {{-- Completion Progress --}}
                                            <div style="margin-bottom: 16px;">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                                    <span style="font-weight: 600; font-size: 12px;">Setup Progress</span>
                                                    <span style="font-weight: bold; color: #19b5b5; font-size: 12px;">{{ $accommodation->getCompletionPercentage() }}%</span>
                                                </div>
                                                <div style="height: 6px; background: #e0e0e0; border-radius: 3px; overflow: hidden;">
                                                    <div style="height: 100%; background: #19b5b5; width: {{ $accommodation->getCompletionPercentage() }}%; transition: width 0.3s;"></div>
                                                </div>
                                            </div>

                                            {{-- Status Info --}}
                                            <div style="background: #f5f5f5; padding: 12px; border-radius: 4px; margin-bottom: 16px; font-size: 12px;">
                                                @if($accommodation->is_published)
                                                    <span style="color: #28a745; font-weight: 600;">✓ Published</span>
                                                @elseif($accommodation->compliance_documents_submitted)
                                                    <span style="color: #17a2b8; font-weight: 600;">⏳ Awaiting Approval</span>
                                                @else
                                                    <span style="color: #ffc107; font-weight: 600;">⚠ Incomplete Setup</span>
                                                @endif
                                                <br>
                                                <small style="color: #999;">Last updated: {{ $accommodation->updated_at->diffForHumans() }}</small>
                                            </div>

                                            {{-- Quick Steps Status --}}
                                            <div style="margin-bottom: 16px;">
                                                <p style="font-weight: 600; font-size: 12px; margin-bottom: 8px;">Essential Steps:</p>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 11px;">
                                                    <div style="background: {{ $accommodation->step1_basics ? '#e8f5e9' : '#ffebee' }}; padding: 8px; border-radius: 4px; text-align: center;">
                                                        {{ $accommodation->step1_basics ? '✓' : '○' }} Basics
                                                    </div>
                                                    <div style="background: {{ $accommodation->step2_legal ? '#e8f5e9' : '#ffebee' }}; padding: 8px; border-radius: 4px; text-align: center;">
                                                        {{ $accommodation->step2_legal ? '✓' : '○' }} Legal
                                                    </div>
                                                    <div style="background: {{ $accommodation->step3_media ? '#e8f5e9' : '#ffebee' }}; padding: 8px; border-radius: 4px; text-align: center;">
                                                        {{ $accommodation->step3_media ? '✓' : '○' }} Media
                                                    </div>
                                                    <div style="background: {{ $accommodation->step4_rooms ? '#e8f5e9' : '#ffebee' }}; padding: 8px; border-radius: 4px; text-align: center;">
                                                        {{ $accommodation->step4_rooms ? '✓' : '○' }} Rooms
                                                    </div>
                                                    <div style="background: {{ $accommodation->step5_rates ? '#e8f5e9' : '#ffebee' }}; padding: 8px; border-radius: 4px; text-align: center;">
                                                        {{ $accommodation->step5_rates ? '✓' : '○' }} Rates
                                                    </div>
                                                    <div style="background: {{ $accommodation->step6_policies ? '#e8f5e9' : '#ffebee' }}; padding: 8px; border-radius: 4px; text-align: center;">
                                                        {{ $accommodation->step6_policies ? '✓' : '○' }} Policies
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Actions --}}
                                            <div style="display: flex; gap: 8px;">
                                                <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="flex: 1; background: #19b5b5; color: #fff; border: none; padding: 8px; border-radius: 4px; font-weight: 600; font-size: 12px; text-align: center; text-decoration: none;">
                                                    Continue Setup
                                                </a>
                                                <a href="{{ route('operator.accommodation.step1.edit', $accommodation->id) }}" class="btn" style="flex: 1; background: #f0f0f0; color: #333; border: none; padding: 8px; border-radius: 4px; font-weight: 600; font-size: 12px; text-align: center; text-decoration: none;">
                                                    Edit Basics
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
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
