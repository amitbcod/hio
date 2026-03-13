@extends('frontend.layout')

@section('title', $activity['title'] . ' | Activity | Holidays.io')
@section('meta_description', $activity['excerpt'])

@section('content')
    <section class="page-hero">
        <div class="page-hero-media">
            <img src="{{ $activity['image'] }}" alt="{{ $activity['title'] }}">
        </div>
        <div class="wrap page-hero-content">
            <div class="breadcrumbs">
                <a href="{{ url('/') }}">Home</a>
                <span>/</span>
                <a href="{{ url('/#activities-section') }}">Activities</a>
                <span>/</span>
                <span>{{ $activity['title'] }}</span>
            </div>
            <h1>{{ $activity['title'] }}</h1>
            <p>{{ $activity['excerpt'] }}</p>
        </div>
    </section>

    <section class="page-section">
        <div class="wrap detail-shell">
            <div class="detail-card">
                <h2>Overview</h2>
                <div class="detail-text">{{ $activity['overview_text'] ?: $activity['excerpt'] }}</div>
            </div>

            <aside class="detail-side">
                <div class="side-list">
                    <div class="side-item">
                        <span>Type</span>
                        <strong>{{ $activity['meta'] }}</strong>
                    </div>
                    <div class="side-item">
                        <span>Location</span>
                        <div>{{ $activity['location'] }}</div>
                    </div>
                    <div class="side-item">
                        <span>Duration</span>
                        <div>{{ $activity['duration'] ?: 'To be updated' }}</div>
                    </div>
                    <div class="side-item">
                        <span>Booking</span>
                        <div>{{ $activity['booking_confirmation_type'] ?: 'Operator confirmation' }}</div>
                    </div>
                    <div class="side-item">
                        <span>Languages</span>
                        <div>{{ !empty($activity['languages']) ? implode(', ', $activity['languages']) : 'Not specified' }}</div>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    @if(!empty($activity['included_text']) || !empty($activity['itinerary_text']))
        <section class="page-section" style="padding-top:0;">
            <div class="wrap detail-shell">
                <div class="detail-card">
                    <h2>What’s Included</h2>
                    <div class="detail-text">{{ $activity['included_text'] ?: 'Details will be updated soon.' }}</div>
                </div>

                <div class="detail-card">
                    <h2>Itinerary</h2>
                    <div class="detail-text">{{ $activity['itinerary_text'] ?: 'Details will be updated soon.' }}</div>
                </div>
            </div>
        </section>
    @endif

    @if(!empty($activity['gallery']))
        <section class="page-section" style="padding-top:0;">
            <div class="wrap">
                <div class="section-header">
                    <div>
                        <h2>Gallery</h2>
                        <p>Images uploaded by the operator for this activity.</p>
                    </div>
                </div>
                <div class="gallery-grid">
                    @foreach($activity['gallery'] as $image)
                        <img src="{{ $image }}" alt="{{ $activity['title'] }} gallery image">
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
