@extends('frontend.layout')

@section('title', $accommodation['title'] . ' | Stay | Holidays.io')
@section('meta_description', $accommodation['excerpt'])

@section('content')
    <section class="page-hero">
        <div class="page-hero-media">
            <img src="{{ $accommodation['image'] }}" alt="{{ $accommodation['title'] }}">
        </div>
        <div class="wrap page-hero-content">
            <div class="breadcrumbs">
                <a href="{{ url('/') }}">Home</a>
                <span>/</span>
                <a href="{{ url('/#stays-section') }}">Stays</a>
                <span>/</span>
                <span>{{ $accommodation['title'] }}</span>
            </div>
            <h1>{{ $accommodation['title'] }}</h1>
            <p>{{ $accommodation['excerpt'] }}</p>
        </div>
    </section>

    <section class="page-section">
        <div class="wrap detail-shell">
            <div class="detail-card">
                <h2>Property Overview</h2>
                <div class="detail-text">{{ $accommodation['description_text'] ?: $accommodation['excerpt'] }}</div>
            </div>

            <aside class="detail-side">
                <div class="side-list">
                    <div class="side-item">
                        <span>Property Type</span>
                        <strong>{{ $accommodation['meta'] }}</strong>
                    </div>
                    <div class="side-item">
                        <span>Location</span>
                        <div>{{ $accommodation['location'] }}</div>
                    </div>
                    <div class="side-item">
                        <span>Status</span>
                        <div>{{ $accommodation['status'] }}</div>
                    </div>
                    <div class="side-item">
                        <span>Gallery Items</span>
                        <div>{{ count($accommodation['gallery']) }}</div>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    @if(!empty($accommodation['gallery']))
        <section class="page-section" style="padding-top:0;">
            <div class="wrap">
                <div class="section-header">
                    <div>
                        <h2>Photo Gallery</h2>
                        <p>Media uploaded by the operator for this property.</p>
                    </div>
                </div>
                <div class="gallery-grid">
                    @foreach($accommodation['gallery'] as $image)
                        <img src="{{ $image }}" alt="{{ $accommodation['title'] }} gallery image">
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
