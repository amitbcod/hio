@extends('frontend.layout')

@section('title', 'Holidays.io | Dynamic Homepage')
@section('meta_description', 'Browse live accommodation and activity listings entered by operators on Holidays.io.')

@section('content')
    <section class="hero" data-hero-slider>
        <div class="hero-slides">
            @foreach($heroSlides as $index => $slide)
                <article class="hero-slide {{ $index === 0 ? 'is-active' : '' }}" data-hero-slide>
                    <img src="{{ $slide['image'] }}" alt="{{ $slide['title'] }}">
                    <div class="hero-overlay"></div>
                    <div class="wrap hero-content">
                        <span class="hero-badge">{{ $slide['badge'] }}</span>
                        <h1>{{ $slide['title'] }}</h1>
                        <p>{{ $slide['subtitle'] }}</p>
                        <div class="hero-actions">
                            <a href="{{ $slide['url'] }}" class="btn-primary">Explore now</a>
                            <a href="#discover-mauritius" class="btn-outline">Browse homepage listings</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if(count($heroSlides) > 1)
            <div class="hero-dots">
                @foreach($heroSlides as $index => $slide)
                    <button type="button" class="hero-dot {{ $index === 0 ? 'is-active' : '' }}" data-hero-dot="{{ $index }}"
                        aria-label="Show slide {{ $index + 1 }}"></button>
                @endforeach
            </div>
        @endif


        <div class="main-search home-main-search">
            <div class="wrap2">
                <form method="GET" action="{{ route('frontend.category.list') }}"
                    class="category-search-form category-search-form--detailed" id="home-category-search-form"
                    data-search-options='@json($searchOptions)'>
                    <div class="category-search-cell category-search-cell--what">
                        <!-- <h5><span>01</span> What?</h5> -->
                        <div class="category-radio-group">
                            <label class="category-radio-item">
                                <input type="radio" name="category" value="accommodation" {{ $selectedCategory === 'accommodation' ? 'checked' : '' }}>
                                <div class="cat-radio-tab">
                                    <div class="main-icon accommodation"><img src="images/accommodation.svg"></div>
                                    <span>Accommodation</span>
                                </div>
                            </label>
                            <label class="category-radio-item">
                                <input type="radio" name="category" value="tours" {{ $selectedCategory === 'tours' ? 'checked' : '' }}>
                                <div class="cat-radio-tab">
                                    <div class="main-icon activity"><img src="images/activity.svg"></div>
                                    <span>Tours - Activity</span>
                                </div>
                            </label>
                            <label class="category-radio-item">
                                <input type="radio" name="category" value="transport" {{ $selectedCategory === 'transport' ? 'checked' : '' }}>
                                <div class="cat-radio-tab">
                                    <div class="main-icon transport"><img src="images/transport.svg"></div>
                                    <span>Transport</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="category-input-group">
                        <div class="category-input-group-inner">
                            <div class="category-search-cell">
                                <h5>Region/Area ?</h5>
                                <select name="region" class="category-search-select" data-search-region
                                    data-selected="{{ $filters['region'] }}">
                                    <option value="">Please select</option>
                                    @foreach($searchOptions[$selectedCategory]['regions'] ?? [] as $region)
                                        <option value="{{ $region }}" {{ $filters['region'] === $region ? 'selected' : '' }}>
                                            {{ $region }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="category-search-cell">
                                <h5>Check-In</h5>
                                <div class="category-search-dates">
                                    <input type="date" name="check_in" class="category-search-input"
                                        value="{{ $filters['check_in'] }}" min="{{ date('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="category-search-cell">
                                <h5>Check-Out</h5>
                                <div class="category-search-dates">
                                    <input type="date" name="check_out" class="category-search-input"
                                        value="{{ $filters['check_out'] }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                </div>
                            </div>

                            <div class="category-search-cell category-search-cell--type">
                                <!-- <h5><span data-type-label>Type</span></h5> -->
                                <h5>Type</h5>
                                <div class="category-search-stack">
                                    <select name="type" class="category-search-select" data-search-type
                                        data-selected="{{ $filters['type'] }}">
                                        <option value="">Any</option>
                                        @foreach($searchOptions[$selectedCategory]['types'] ?? [] as $type)
                                            <option value="{{ $type }}" {{ $filters['type'] === $type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="category-search-field-group">
                                        <!-- <label for="home-name-search">Name Search</label> -->
                                        <input id="home-name-search" type="text" name="name" class="category-search-input"
                                            placeholder="Optional" value="{{ $filters['name'] }}">
                                    </div>
                                </div>
                            </div>

                            <div class="category-search-submit">
                                <button type="submit" class="btn-primary">Proceed to results</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </section>



    <section class="quick-search">
        <div class="wrap quick-search-grid">
            <div class="quick-tile">
                <strong>Activities</strong>
                <p>Adventure tours, ocean trips, guided experiences, and operator-created activities.</p>
                <a href="#activities-section" class="btn-primary">Browse activities</a>
            </div>
            <div class="quick-tile">
                <strong>Holiday Rentals</strong>
                <p>Discover villas, apartments, cottages, and holiday rentals entered by accommodation operators.</p>
                <a href="#accommodations-section" class="btn-primary">See rentals</a>
            </div>
            <div class="quick-tile">
                <strong>Hotels</strong>
                <p>Explore hotel and resort stays using the same public-facing homepage design.</p>
                <a href="#discover-mauritius" class="btn-primary">See hotels</a>
            </div>
            <div class="quick-tile">
                <strong>Operator Data Live</strong>
                <p>This frontend now reads your accommodation and activity data directly from the existing system.</p>
                <a href="{{ route('operator.login') }}" class="btn-secondary">Operator login</a>
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="wrap split-highlight">
            <div class="highlight-copy">
                <h3>Your Holidays in Mauritius</h3>
                <p>
                    Mauritius is more than just a destination — it is now a dynamic homepage connected to your real operator
                    entries.
                    Accommodation and activity content below is loaded from your existing database so the public frontend
                    starts feeling alive
                    without changing any operator or admin workflow.
                </p>
                <div class="stats-bar">
                    <div class="stat-pill">
                        <strong>{{ $stats['activities'] }}</strong>
                        <span>Activities loaded</span>
                    </div>
                    <div class="stat-pill">
                        <strong>{{ $stats['holidayRentals'] }}</strong>
                        <span>Holiday rentals loaded</span>
                    </div>
                    <div class="stat-pill">
                        <strong>{{ $stats['hotels'] }}</strong>
                        <span>Hotels loaded</span>
                    </div>
                </div>
            </div>
            <div class="highlight-card">
                <img src="{{ $heroSlides[0]['image'] ?? asset('images/holidays-io-logo.png') }}"
                    alt="Featured Mauritius experience">
            </div>
        </div>
    </section>

    <section class="page-section" id="discover-mauritius">
        <div class="wrap">
            <div class="section-header">
                <div>
                    <h2>Discover Mauritius</h2>
                    <p>
                        The homepage tabs below use live data entered by operators for activities and accommodation.
                        Services, transport, and wedding tabs are kept ready for later frontend expansion.
                    </p>
                </div>
            </div>

            <div class="tabs-shell">
                <div class="tab-buttons">
                    <button type="button" class="tab-button is-active" data-tab-target="activities">Activities</button>
                    <button type="button" class="tab-button" data-tab-target="holiday-rentals">Holiday Rentals</button>
                    <button type="button" class="tab-button" data-tab-target="hotels">Hotels</button>
                    <button type="button" class="tab-button" data-tab-target="services">Services</button>
                    <button type="button" class="tab-button" data-tab-target="transport">Transport</button>
                    <button type="button" class="tab-button" data-tab-target="wedding">Wedding</button>
                </div>

                <div class="tab-panel is-active" data-tab-panel="activities" id="activities-section">
                    @if($activities->isEmpty())
                        <div class="empty-state">No activity data is available yet.</div>
                    @else
                        <div class="cards-grid">
                            @foreach($activities as $activity)
                                <article class="listing-card">
                                    <a href="{{ $activity['url'] }}" class="listing-media">
                                        <img src="{{ $activity['image'] }}" alt="{{ $activity['title'] }}">
                                        <span class="listing-badge">{{ $activity['meta'] }}</span>
                                    </a>
                                    <div class="listing-body">
                                        <span class="listing-location"><i class="fa-solid fa-location-dot"></i>
                                            {{ $activity['location'] }}</span>
                                        <h3><a href="{{ $activity['url'] }}">{{ $activity['title'] }}</a></h3>
                                        <p>{{ $activity['excerpt'] }}</p>
                                        <div class="listing-footer">
                                            <strong>{{ $activity['booking_confirmation_type'] ?: 'Operator listing' }}</strong>
                                            <a href="{{ $activity['url'] }}">View details</a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="tab-panel" data-tab-panel="holiday-rentals" id="accommodations-section">
                    @if($holidayRentals->isEmpty())
                        <div class="empty-state">No holiday rental data is available yet.</div>
                    @else
                        <div class="cards-grid">
                            @foreach($holidayRentals as $accommodation)
                                <article class="listing-card">
                                    <a href="{{ $accommodation['url'] }}" class="listing-media">
                                        <img src="{{ $accommodation['image'] }}" alt="{{ $accommodation['title'] }}">
                                        <span class="listing-badge">{{ $accommodation['property_type'] }}</span>
                                    </a>
                                    <div class="listing-body">
                                        <span class="listing-location"><i class="fa-solid fa-location-dot"></i>
                                            {{ $accommodation['location'] }}</span>
                                        <h3><a href="{{ $accommodation['url'] }}">{{ $accommodation['title'] }}</a></h3>
                                        <p>{{ $accommodation['excerpt'] }}</p>
                                        <div class="listing-footer">
                                            <a href="{{ $accommodation['url'] }}">View details</a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="tab-panel" data-tab-panel="hotels">
                    @if($hotels->isEmpty())
                        <div class="empty-state">No hotel data is available yet.</div>
                    @else
                        <div class="cards-grid">
                            @foreach($hotels as $accommodation)
                                <article class="listing-card">
                                    <a href="{{ $accommodation['url'] }}" class="listing-media">
                                        <img src="{{ $accommodation['image'] }}" alt="{{ $accommodation['title'] }}">
                                        <span class="listing-badge">{{ $accommodation['property_type'] }}</span>
                                    </a>
                                    <div class="listing-body">
                                        <span class="listing-location"><i class="fa-solid fa-location-dot"></i>
                                            {{ $accommodation['location'] }}</span>
                                        <h3><a href="{{ $accommodation['url'] }}">{{ $accommodation['title'] }}</a></h3>
                                        <p>{{ $accommodation['excerpt'] }}</p>
                                        <div class="listing-footer">
                                            <a href="{{ $accommodation['url'] }}">View details</a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="tab-panel" data-tab-panel="services">
                    <div class="empty-state">Frontend service cards are ready to be connected when service data becomes
                        available.</div>
                </div>
                <div class="tab-panel" data-tab-panel="transport">
                    <div class="empty-state">Frontend transport cards are ready to be connected when transport data becomes
                        available.</div>
                </div>
                <div class="tab-panel" data-tab-panel="wedding">
                    <div class="empty-state">Frontend wedding cards are ready to be connected when wedding data becomes
                        available.</div>
                </div>
            </div>
        </div>
    </section>
@endsection