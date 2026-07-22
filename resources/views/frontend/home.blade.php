@extends('frontend.layout')

@section('title', 'Holidays.io | Dynamic Homepage')
@section('meta_description', 'Browse live accommodation and activity listings entered by operators on Holidays.io.')

@section('content')
    <section class="hero" data-hero-slider>
        <!-- <div class="hero-slides">
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
        @endif -->

        <div class="hero-slides">
    <article class="hero-slide is-active">
        <img src="{{ asset('storage/logos/mauritius.jpg') }}" alt="Mauritius">
        
        <div class="hero-overlay"></div>

        <div class="wrap hero-content">
            <span class="hero-badge">{{ __('home.hero.badge') }}</span>
            <h1>{{ __('home.hero.title') }}</h1>
            <p>{{ __('home.hero.subtitle') }}</p>

            <div class="hero-actions">
                <a href="#" class="btn-primary">{{ __('home.hero.explore_now') }}</a>
                <a href="#discover-mauritius" class="btn-outline">{{ __('home.hero.browse_listings') }}</a>
            </div>
        </div>
    </article>
</div>


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
                                    <span>{{ __('home.search.accommodation') }}</span>
                                </div>
                                @push('styles')
                                    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
                                @endpush
                                @push('scripts')
                                    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
                                @endpush
                            </label>
                            <label class="category-radio-item">
                                <input type="radio" name="category" value="tours" {{ $selectedCategory === 'tours' ? 'checked' : '' }}>
                                <div class="cat-radio-tab">
                                    <div class="main-icon activity"><img src="images/activity.svg"></div>
                                    <span>{{ __('home.search.tours_activity') }}</span>
                                </div>
                            </label>
                            <label class="category-radio-item">
                                <input type="radio" name="category" value="transport" {{ $selectedCategory === 'transport' ? 'checked' : '' }}>
                                <div class="cat-radio-tab">
                                    <div class="main-icon transport"><img src="images/transport.svg"></div>
                                    <span>{{ __('home.search.transport') }}</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="category-input-group">
                        <div class="category-input-group-inner">
                            <div class="category-search-cell category-search-cell--region" style="{{ $selectedCategory === 'transport' ? 'display:none;' : '' }}; flex: 0 1 280px; min-width: 280px">
                                <h5>{{ __('home.search.region_area') }}</h5>
                                <select name="region" class="category-search-select" data-search-region
                                    data-selected="{{ $filters['region'] }}">
                                    <option value="all" {{ $filters['region'] === 'all' || $filters['region'] === '' ? 'selected' : '' }}>All</option>
                                    @foreach($searchOptions[$selectedCategory]['regions'] ?? [] as $region)
                                        <option value="{{ $region }}" {{ $filters['region'] === $region ? 'selected' : '' }}>
                                            {{ $region }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Accommodation/Transport: Check-In and Check-Out -->
                            <div class="category-search-cell category-search-cell--accommodation category-search-cell--check-in" style="display: none; flex: 0">
                                <h5>{{ __('home.search.check_in') }}</h5>
                                <div class="category-search-dates">
                                    <input type="date" name="check_in" class="category-search-input"
                                        value="{{ $filters['check_in'] }}" min="{{ date('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="category-search-cell category-search-cell--accommodation category-search-cell--check-out" style="display: none; flex: 0">
                                <h5>{{ __('home.search.check_out') }}</h5>
                                <div class="category-search-dates">
                                    <input type="date" name="check_out" class="category-search-input"
                                        value="{{ $filters['check_out'] }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                </div>
                            </div>

                            <!-- Tours/Activity: Activity Date -->
                            <div class="category-search-cell category-search-cell--tours category-search-cell--activity-date" style="display: none;">
                                <h5>{{ __('home.search.select_date') }}</h5>
                                <div class="category-search-dates">
                                    <input type="date" name="activity_date" class="category-search-input"
                                        value="{{ request()->query('activity_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}">
                                </div>
                            </div>

                            <!-- Accommodation/Transport/Activity: Guest details -->
                            <div class="category-search-cell category-search-cell--guests" style="display: none;">
                                <h5 id="guest-cell-heading">{{ __('home.search.guest_rooms') }}</h5>
                                <div class="guest-rooms-summary">
                                    <span id="guest-rooms-summary-text">{{ (int) request()->query('adults',2) }} {{ __('home.search.adults') }} &middot; {{ (int) request()->query('children',0) }} {{ __('home.search.children') }} &middot; {{ (int) request()->query('infants',0) }} {{ __('home.search.infants') }} &middot; {{ (int) request()->query('rooms',1) }} {{ __('home.search.rooms') }}</span> 
                                </div>
                                <div class="guest-rooms-selector">
                                    <div class="guest-rooms-row">
                                        <label for="adults-field">{{ __('home.search.adults') }} <span>({{ __('home.search.adult_age_label') }})</span></label>
                                        <div class="guest-rooms-counter">
                                            <button type="button" class="count-btn decrement" data-target="adults">−</button>
                                            <input id="adults-field" type="text" name="adults" value="{{ request()->query('adults', 2) }}" readonly>
                                            <button type="button" class="count-btn increment" data-target="adults">+</button>
                                        </div>
                                    </div>
                                    <div class="guest-rooms-row">
                                        <label for="children-field">{{ __('home.search.children') }} <span>({{ __('home.search.children_age_label') }})</span></label>
                                        <div class="guest-rooms-counter">
                                            <button type="button" class="count-btn decrement" data-target="children">−</button>
                                            <input id="children-field" type="text" name="children" value="{{ request()->query('children', 0) }}" readonly>
                                            <button type="button" class="count-btn increment" data-target="children">+</button> 
                                        </div>
                                    </div>
                                    <div class="guest-rooms-row">
                                        <label for="infants-field">{{ __('home.search.infants') }} <span>({{ __('home.search.infants_age_label') }})</span></label>
                                        <div class="guest-rooms-counter">
                                            <button type="button" class="count-btn decrement" data-target="infants">−</button>
                                            <input id="infants-field" type="text" name="infants" value="{{ request()->query('infants', 0) }}" readonly>
                                            <button type="button" class="count-btn increment" data-target="infants">+</button>
                                        </div>
                                    </div>
                                    <div class="guest-rooms-row" id="rooms-row">
                                        <label for="rooms-field">{{ __('home.search.rooms') }} <span>({{ __('home.search.rooms_limit_label') }})</span></label>
                                        <div class="guest-rooms-counter">
                                            <button type="button" class="count-btn decrement" data-target="rooms">−</button>
                                            <input id="rooms-field" type="text" name="rooms" value="{{ request()->query('rooms', 1) }}" readonly>
                                            <button type="button" class="count-btn increment" data-target="rooms">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Transport: route, passengers and dates -->
                            <div class="category-search-cell category-search-cell--transport" style="display: none;">
                                <div class="transport-row" style="">
                                    <div class="transport-field" style="flex: 1 1 160px; min-width: 140px;">
                                        <h5>{{ __('home.search.departure_location') }}</h5>
                                        <select name="transport_from" class="category-search-select" data-search-from>
                                            <option value="" disabled hidden>{{ __('home.search.departure_location') }}</option>
                                            @foreach(($searchOptions['transport']['froms'] ?? []) as $from)
                                                <option value="{{ $from }}" {{ request()->query('transport_from', '') === $from ? 'selected' : '' }}>{{ $from }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="transport-field" style="flex: 1 1 160px; min-width: 140px;">
                                        <h5>{{ __('home.search.destination') }}</h5>
                                        <select name="transport_to" class="category-search-select" data-search-to>
                                            <option value="" disabled hidden>{{ __('home.search.destination') }}</option>
                                            @foreach(($searchOptions['transport']['tos'] ?? []) as $to)
                                                <option value="{{ $to }}" {{ request()->query('transport_to', '') === $to ? 'selected' : '' }}>{{ $to }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="transport-field" style="flex: 0 1 110px; min-width: 110px;">
                                        <h5>{{ __('home.search.passengers') }}</h5>
                                        <input type="number" name="passengers" class="category-search-input" min="1" value="{{ request()->query('passengers', 2) }}">
                                    </div>
                                    <div class="transport-field" style="flex: 0 1 140px; min-width: 140px;">
                                        <h5>Service Type</h5>
                                        <select name="service_type" class="category-search-select" id="home-service-type">
                                            <option value="route" {{ request()->query('service_type','route') === 'route' ? 'selected' : '' }}>Route wise</option>
                                            <option value="car_rental" {{ request()->query('service_type') === 'car_rental' ? 'selected' : '' }}>Car rental</option>
                                        </select>
                                    </div>
                                    <div class="transport-row-date">
                                        <div class="transport-field" style="flex: 1 1 160px;">
                                            <h5>{{ __('home.search.arrival_date_time') }}</h5>
                                            <input type="date" name="arrival_date" class="category-search-input" value="{{ request()->query('arrival_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="transport-field" style="flex: 1 1 120px;">
                                            <h5>&nbsp;</h5>
                                            <input type="time" name="arrival_time" class="category-search-input" value="{{ request()->query('arrival_time', '') }}">
                                        </div>
                                    </div>
                                    <div class="transport-row-date"> 
                                        <div class="transport-field" style="flex: 1 1 160px;">
                                            <h5>{{ __('home.search.return_date_time') }}</h5>
                                            <input type="date" name="return_date" class="category-search-input" value="{{ request()->query('return_date', '') }}" min="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="transport-field" style="flex: 1 1 120px">
                                            <h5>&nbsp;</h5>
                                            <input type="time" name="return_time" class="category-search-input" value="{{ request()->query('return_time', '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <div class="category-search-submit">
                                <button type="submit" class="btn-primary">{{ __('home.search.proceed') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </section>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('home-category-search-form');
            if (!form) return;
            form.addEventListener('submit', function (e) {
                const cat = form.querySelector('input[name="category"]:checked')?.value || '';
                if (cat === 'transport') {
                    const st = form.querySelector('select[name="service_type"]')?.value || 'route';
                    if (st === 'car_rental') {
                        const arrivalDate = form.querySelector('input[name="arrival_date"]')?.value || '';
                        const arrivalTime = form.querySelector('input[name="arrival_time"]')?.value || '';
                        const returnDate = form.querySelector('input[name="return_date"]')?.value || '';
                        const returnTime = form.querySelector('input[name="return_time"]')?.value || '';
                        if (!arrivalDate || !arrivalTime || !returnDate || !returnTime) {
                            e.preventDefault();
                            alert('For Car rental please provide pickup and return dates and times.');
                            return false;
                        }
                    }
                }
            });
        });
    </script>
    @endpush



    <section class="quick-search">
        <div class="wrap quick-search-grid">
            <div class="quick-tile">
                <strong>{{ __('home.quick.activities') }}</strong>
                <p>{{ __('home.quick.activities_desc') }}</p>
                <a href="#activities-section" class="btn-primary">{{ __('home.quick.activities_cta') }}</a>
            </div>
            <div class="quick-tile">
                <strong>{{ __('home.quick.holiday_rentals') }}</strong>
                <p>{{ __('home.quick.holiday_rentals_desc') }}</p>
                <a href="#accommodations-section" class="btn-primary">{{ __('home.quick.holiday_rentals_cta') }}</a>
            </div>
            <div class="quick-tile">
                <strong>{{ __('home.quick.hotels') }}</strong>
                <p>{{ __('home.quick.hotels_desc') }}</p>
                <a href="#discover-mauritius" class="btn-primary">{{ __('home.quick.hotels_cta') }}</a>
            </div>
            <!-- <div class="quick-tile">
                <strong>Operator Data Live</strong>
                <p>This frontend now reads your accommodation and activity data directly from the existing system.</p>
                <a href="{{ route('operator.login') }}" class="btn-secondary">Operator login</a>
            </div> -->
        </div>
    </section>

    <section class="page-section">
        <div class="wrap split-highlight">
            <div class="highlight-copy">
                <h3>{{ __('home.about.title') }}</h3>
                <p>{{ __('home.about.description') }}</p>
                <div class="stats-bar">
                    <div class="stat-pill">
                        <strong>{{ $stats['activities'] }}</strong>
                        <span>{{ __('home.stats.activities_loaded') }}</span>
                    </div>
                    <div class="stat-pill">
                        <strong>{{ $stats['holidayRentals'] }}</strong>
                        <span>{{ __('home.stats.holiday_rentals_loaded') }}</span>
                    </div>
                    <div class="stat-pill">
                        <strong>{{ $stats['hotels'] }}</strong>
                        <span>{{ __('home.stats.hotels_loaded') }}</span>
                    </div>
                </div>
            </div>
            <div class="highlight-card">
                <!-- <img src="{{ $heroSlides[0]['image'] ?? asset('images/holidays-io-logo.png') }}"
                    alt="Featured Mauritius experience"> -->
                    <img src="{{ asset('images/Mauritius2.jpg') }}" alt="">
            </div>
        </div>
    </section>

    <section class="page-section" id="discover-mauritius">
        <div class="wrap">
            <div class="section-header">
                <div>
                    <h2>{{ __('home.section.title') }}</h2>
                    <p>
                        {{ __('home.section.description') }}
                    </p>
                </div>
            </div>

            <div class="tabs-shell">
                <div class="tab-buttons">
                    <button type="button" class="tab-button {{ $selectedCategory === 'tours' || $selectedCategory === '' ? 'is-active' : '' }}" data-tab-target="activities">{{ __('home.tabs.activities') }}</button>
                    <!-- <button type="button" class="tab-button" data-tab-target="holiday-rentals">{{ __('home.tabs.holiday_rentals') }}</button> -->
                    <button type="button" class="tab-button {{ $selectedCategory === 'accommodation' ? 'is-active' : '' }}" data-tab-target="hotels">{{ __('home.tabs.hotels') }}</button>
                    <!-- <button type="button" class="tab-button" data-tab-target="services">{{ __('home.tabs.services') }}</button> -->
                    <button type="button" class="tab-button {{ $selectedCategory === 'transport' ? 'is-active' : '' }}" data-tab-target="transport">{{ __('home.tabs.transport') }}</button>
                    <!-- <button type="button" class="tab-button" data-tab-target="wedding">{{ __('home.tabs.wedding') }}</button> -->
                </div>

                <div class="tab-panel {{ $selectedCategory === 'tours' || $selectedCategory === '' ? 'is-active' : '' }}" data-tab-panel="activities" id="activities-section">
                    @if($activities->isEmpty())
                        <div class="empty-state">{{ __('home.empty.activity_data') }}</div>
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
                                        <div class="listing-title-row">
                                            <h3><a href="{{ $activity['url'] }}">{{ $activity['title'] }}</a></h3>
                                            @if(!empty($activity['rating_display']))
                                                @php $activityRating = (int) round($activity['rating_display']); @endphp
                                                <span class="listing-rating-badge" aria-label="{{ $activityRating }} star rating">{!! str_repeat('<i class="fa-solid fa-star"></i>', max(1, min(5, $activityRating))) !!}</span>
                                            @endif
                                        </div>
                                        <p>{{ $activity['excerpt'] }}</p>
                                        <div class="listing-footer">
                                            <strong>{{ $activity['booking_confirmation_type'] ?: 'Operator listing' }}</strong>
                                            <a href="{{ $activity['url'] }}">{{ __('home.view_details') }}</a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="tab-panel" data-tab-panel="holiday-rentals" id="accommodations-section">
                    @if($holidayRentals->isEmpty())
                        <div class="empty-state">{{ __('home.empty.holiday_rentals_data') }}</div>
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
                                        <div class="listing-title-row">
                                            <h3><a href="{{ $accommodation['url'] }}">{{ $accommodation['title'] }}</a></h3>
                                            @if(!empty($accommodation['rating_display']))
                                                @php $accommodationRating = (int) round($accommodation['rating_display']); @endphp
                                                <span class="listing-rating-badge" aria-label="{{ $accommodationRating }} star rating">{!! str_repeat('<i class="fa-solid fa-star"></i>', max(1, min(5, $accommodationRating))) !!}</span>
                                            @endif
                                        </div>
                                        <p>{{ $accommodation['excerpt'] }}</p>
                                        <div class="listing-footer">
                                            <a href="{{ $accommodation['url'] }}">{{ __('home.view_details') }}</a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="tab-panel {{ $selectedCategory === 'accommodation' ? 'is-active' : '' }}" data-tab-panel="hotels">
                    @if($hotels->isEmpty())
                        <div class="empty-state">{{ __('home.empty.hotel_data') }}</div>
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
                                        <div class="listing-title-row">
                                            <h3><a href="{{ $accommodation['url'] }}">{{ $accommodation['title'] }}</a></h3>
                                            @if(!empty($accommodation['rating_display']))
                                                @php $accommodationRating = (int) round($accommodation['rating_display']); @endphp
                                                <span class="listing-rating-badge" aria-label="{{ $accommodationRating }} star rating">{!! str_repeat('<i class="fa-solid fa-star"></i>', max(1, min(5, $accommodationRating))) !!}</span>
                                            @endif
                                        </div>
                                        <p>{{ $accommodation['excerpt'] }}</p>
                                        <div class="listing-footer">
                                            <a href="{{ $accommodation['url'] }}">{{ __('home.view_details') }}</a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="tab-panel" data-tab-panel="services">
                    <div class="empty-state">{{ __('home.empty.service_data') }}</div>
                </div>
                <div class="tab-panel {{ $selectedCategory === 'transport' ? 'is-active' : '' }}" data-tab-panel="transport">
                    @if($transports->isEmpty())
                        <div class="empty-state">{{ __('home.empty.transport_data') }}</div>
                    @else
                        <div class="cards-grid">
                            @foreach($transports as $transport)
                                <article class="listing-card">
                                    <a href="{{ $transport['url'] }}" class="listing-media">
                                        <img src="{{ $transport['image'] }}" alt="{{ $transport['title'] }}">
                                        <span class="listing-badge">{{ $transport['vehicle_type'] ?: __('home.tabs.transport') }}</span>
                                    </a>
                                    <div class="listing-body">
                                        <span class="listing-location"><i class="fa-solid fa-location-dot"></i>
                                            {{ $transport['location'] }}</span>
                                        <div class="listing-title-row">
                                            <h3><a href="{{ $transport['url'] }}">{{ $transport['title'] }}</a></h3>
                                        </div>
                                        <p>{{ $transport['excerpt'] }}</p>
                                        <div class="listing-footer">
                                            <a href="{{ $transport['url'] }}">{{ __('home.view_details') }}</a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="tab-panel" data-tab-panel="wedding">
                    <div class="empty-state">{{ __('home.empty.wedding_data') }}</div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const findInput = (key) => document.querySelector(`[name='${key}']`);
            const searchForm = document.getElementById('home-category-search-form');
            const checkInInput = findInput('check_in');
            const checkOutInput = findInput('check_out');
            const arrivalDateInput = findInput('arrival_date');
            const returnDateInput = findInput('return_date');
            const guestSummary = document.getElementById('guest-rooms-summary-text');
            const guestCellHeading = document.getElementById('guest-cell-heading');
            const guestCell = document.querySelector('.category-search-cell--guests');
            const regionCell = document.querySelector('.category-search-cell--region');
            const accommodationCheckInCell = document.querySelector('.category-search-cell--check-in');
            const accommodationCheckOutCell = document.querySelector('.category-search-cell--check-out');
            const transportCells = document.querySelectorAll('.category-search-cell--transport');
            const toursActivityDateCell = document.querySelector('.category-search-cell--activity-date');
            const roomsRow = document.getElementById('rooms-row');
            const categoryRadios = document.querySelectorAll('input[name="category"]');
            const guestTexts = {
                participants: {!! json_encode(__('home.search.participants')) !!},
                guestRooms: {!! json_encode(__('home.search.guest_rooms')) !!},
                adults: {!! json_encode(__('home.search.adults_label')) !!},
                children: {!! json_encode(__('home.search.children_label')) !!},
                infants: {!! json_encode(__('home.search.infants_label')) !!},
                rooms: {!! json_encode(__('home.search.rooms_label')) !!}
            };
            let transportTomSelectInstances = {
                from: null,
                to: null,
            };

            const syncTomSelectValue = function (select, instance) {
                if (!select || !instance) return;
                instance.setValue(select.value || '');
                if (typeof instance.refreshItems === 'function') {
                    instance.refreshItems();
                }
            };

            const initTransportSearchTomSelects = function () {
                try {
                    const from = document.querySelector('select[data-search-from]');
                    const to = document.querySelector('select[data-search-to]');

                    if (from && !transportTomSelectInstances.from) {
                        transportTomSelectInstances.from = new TomSelect(from, {
                            placeholder: '{{ __('home.search.departure_location') }}',
                            allowEmptyOption: true,
                            create: false,
                            closeAfterSelect: true,
                            onInitialize: function () {
                                if (from.value) {
                                    this.setValue(from.value);
                                }
                            },
                        });
                    }

                    if (to && !transportTomSelectInstances.to) {
                        transportTomSelectInstances.to = new TomSelect(to, {
                            placeholder: '{{ __('home.search.destination') }}',
                            allowEmptyOption: true,
                            create: false,
                            closeAfterSelect: true,
                            onInitialize: function () {
                                if (to.value) {
                                    this.setValue(to.value);
                                }
                            },
                        });
                    }

                    syncTomSelectValue(from, transportTomSelectInstances.from);
                    syncTomSelectValue(to, transportTomSelectInstances.to);
                } catch (e) {
                    console.error('TomSelect init error', e);
                }
            };
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabPanels = document.querySelectorAll('.tab-panel');

            const activateTab = function (tabName) {
                if (!tabName) return;

                tabButtons.forEach((button) => {
                    button.classList.toggle('is-active', button.dataset.tabTarget === tabName);
                });

                tabPanels.forEach((panel) => {
                    panel.classList.toggle('is-active', panel.dataset.tabPanel === tabName);
                });
            };

            const handleHashTab = function () {
                const hash = window.location.hash;
                if (!hash) return;

                const targetPanel = document.querySelector(hash);
                if (!targetPanel || !targetPanel.dataset.tabPanel) return;

                activateTab(targetPanel.dataset.tabPanel);
                setTimeout(() => {
                    targetPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 50);
            };

            const handleCategoryScroll = function () {
                const urlParams = new URLSearchParams(window.location.search);
                if (!urlParams.has('category') || window.location.hash) {
                    return;
                }

                const target = document.getElementById('home-category-search-form');
                if (!target) {
                    return;
                }

                const rect = target.getBoundingClientRect();
                const offsetTop = window.pageYOffset + rect.top - (window.innerHeight / 2) + (rect.height / 2);
                window.scrollTo({ top: Math.max(0, offsetTop), behavior: 'smooth' });
            };

            const activateTabForCategory = function () {
                const selectedCategory = document.querySelector('input[name="category"]:checked')?.value;
                const categoryTabMap = {
                    accommodation: 'hotels',
                    tours: 'activities',
                    transport: 'transport',
                };

                const targetTab = categoryTabMap[selectedCategory];
                if (targetTab) {
                    activateTab(targetTab);
                }
            };

            tabButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    activateTab(this.dataset.tabTarget);
                });
            });

            window.addEventListener('hashchange', function () {
                handleHashTab();
            });

            const showDateError = function (message) {
                let errorNode = document.getElementById('date-validation-error');
                if (!errorNode) {
                    errorNode = document.createElement('div');
                    errorNode.id = 'date-validation-error';
                    errorNode.style.color = '#d93025';
                    errorNode.style.margin = '10px 0 0';
                    errorNode.style.fontSize = '0.95rem';
                    errorNode.style.fontWeight = '500';
                    if (searchForm) {
                        searchForm.querySelector('.category-search-submit')?.before(errorNode);
                    }
                }
                errorNode.textContent = message;
            };

            const clearDateError = function () {
                const errorNode = document.getElementById('date-validation-error');
                if (errorNode) {
                    errorNode.remove();
                }
            };

            const validateCheckInOut = function () {
                const selectedCategory = document.querySelector('input[name="category"]:checked')?.value;
                if (selectedCategory === 'tours') {
                    clearDateError();
                    return true;
                }

                if (!checkInInput || !checkOutInput) {
                    clearDateError();
                    return true;
                }

                const checkIn = checkInInput.value;
                const checkOut = checkOutInput.value;
                if (checkIn && checkOut && checkIn >= checkOut) {
                    showDateError('Check-out date must be after the check-in date.');
                    return false;
                }

                clearDateError();
                return true;
            };

            const updateCheckOutMinDate = function () {
                if (!checkInInput || !checkOutInput) return;

                if (!checkInInput.value) return;

                const selectedCheckIn = new Date(checkInInput.value);
                selectedCheckIn.setDate(selectedCheckIn.getDate() + 1);
                const minDate = selectedCheckIn.toISOString().split('T')[0];
                checkOutInput.min = minDate;

                if (checkOutInput.value && checkOutInput.value <= checkInInput.value) {
                    checkOutInput.value = minDate;
                }
            };

            const updateReturnMinDate = function () {
                if (!arrivalDateInput || !returnDateInput) return;

                if (!arrivalDateInput.value) return;

                const selectedArrival = new Date(arrivalDateInput.value);
                selectedArrival.setDate(selectedArrival.getDate() + 1);
                const minDate = selectedArrival.toISOString().split('T')[0];
                returnDateInput.min = minDate;

                if (returnDateInput.value && returnDateInput.value <= arrivalDateInput.value) {
                    returnDateInput.value = minDate;
                }
            };

            if (checkInInput) {
                checkInInput.addEventListener('change', function () {
                    updateCheckOutMinDate();
                    validateCheckInOut();
                });
            }

            if (checkOutInput) {
                checkOutInput.addEventListener('change', validateCheckInOut);
            }

            if (arrivalDateInput) {
                arrivalDateInput.addEventListener('change', function () {
                    updateReturnMinDate();
                });
            }

            if (searchForm) {
                searchForm.addEventListener('submit', function (event) {
                    if (!validateCheckInOut()) {
                        event.preventDefault();
                    }
                });
            }

            // Update guest rooms summary text
            const updateGuestSummary = function () {
                const adults = parseInt(findInput('adults')?.value || 0, 10);
                const children = parseInt(findInput('children')?.value || 0, 10);
                const infants = parseInt(findInput('infants')?.value || 0, 10);
                const rooms = parseInt(findInput('rooms')?.value || 0, 10);
                const selectedCategory = document.querySelector('input[name="category"]:checked')?.value;
                if (guestSummary) {
                    const parts = [
                        `${adults} ${guestTexts.adults}`,
                        `${children} ${guestTexts.children}`,
                        `${infants} ${guestTexts.infants}`,
                    ];

                    if (selectedCategory === 'accommodation') {
                        parts.push(`${rooms} ${guestTexts.rooms}`);
                    }

                    guestSummary.textContent = parts.join(' · ');
                }
            };

            // Show/hide fields based on selected category
            const updateCategoryFields = function () {
                const selectedCategory = document.querySelector('input[name="category"]:checked')?.value;

                if (selectedCategory === 'tours') {
                    accommodationCheckInCell.style.display = 'none';
                    accommodationCheckOutCell.style.display = 'none';
                    guestCell.style.display = 'block';
                    if (guestCellHeading) guestCellHeading.textContent = guestTexts.participants;
                    if (roomsRow) roomsRow.style.display = 'none';
                    toursActivityDateCell.style.display = 'block';
                    transportCells.forEach(el => el.style.display = 'none');
                    if (regionCell) regionCell.style.display = 'block';
                } else if (selectedCategory === 'transport') {
                    accommodationCheckInCell.style.display = 'none';
                    accommodationCheckOutCell.style.display = 'none';
                    guestCell.style.display = 'none';
                    toursActivityDateCell.style.display = 'none';
                    transportCells.forEach(el => el.style.display = 'block');
                    if (regionCell) regionCell.style.display = 'none';
                    initTransportSearchTomSelects();
                } else {
                    accommodationCheckInCell.style.display = 'block';
                    accommodationCheckOutCell.style.display = 'block';
                    guestCell.style.display = 'block';
                    if (guestCellHeading) guestCellHeading.textContent = guestTexts.guestRooms;
                    if (roomsRow) roomsRow.style.display = 'flex';
                    toursActivityDateCell.style.display = 'none';
                    transportCells.forEach(el => el.style.display = 'none');
                    if (regionCell) regionCell.style.display = 'block';
                }
                updateGuestSummary();
            };

            // Attach change event to category radios
            categoryRadios.forEach(function (radio) {
                radio.addEventListener('change', function () {
                    updateCategoryFields();
                    activateTabForCategory();
                });
            });

            // Handle guest cell expand/collapse
            if (guestCell && guestSummary) {
                guestSummary.addEventListener('click', function () {
                    guestCell.classList.toggle('is-open');
                });

                document.addEventListener('click', function (event) {
                    if (!guestCell.contains(event.target)) {
                        guestCell.classList.remove('is-open');
                    }
                });
            }

            // Handle guest/room counter buttons
            document.querySelectorAll('.guest-rooms-selector .count-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    const target = button.getAttribute('data-target');
                    const input = findInput(target);
                    if (!input) return;

                    let value = parseInt(input.value, 10) || 0;
                    if (button.classList.contains('increment')) {
                        value += 1;
                    } else if (button.classList.contains('decrement')) {
                        value -= 1;
                    }

                    if (target === 'adults' || target === 'rooms') {
                        value = Math.max(1, value);
                    } else {
                        value = Math.max(0, value);
                    }

                    input.value = value;
                    updateGuestSummary();
                });
            });

            // Initialize display on page load
            updateCategoryFields();
            updateGuestSummary();
            updateCheckOutMinDate();
            validateCheckInOut();
            handleHashTab();
            handleCategoryScroll();
        });
    </script>

    @push('styles')
        <style>
            .listing-title-row {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                margin-bottom: 10px;
            }

            .listing-rating-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 6px 10px;
                border-radius: 999px;
                background: rgba(255, 210, 95, 0.16);
                color: #92400e;
                font-size: 0.9rem;
                font-weight: 700;
                border: 1px solid rgba(245, 158, 11, 0.22);
            }

            .category-result-title-row {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                margin-bottom: 10px;
            }

            .listing-rating-badge i {
                color: #f59e0b;
            }

            .category-search-cell--transport .ts-wrapper.category-search-select .ts-control {
                /* min-height: 44px;
                line-height: 1.3;
                padding: 0 12px 0 8px;
                display: flex;
                align-items: center; */
                height: 22px;
                overflow: hidden;
            }

            /* .category-search-cell--transport .ts-wrapper.category-search-select {
                width: 100%;
                min-height: 44px;
                box-sizing: border-box;
            } */

            .category-search-cell--transport .ts-wrapper.category-search-select .ts-dropdown.single {
                background: #fff;
                font-size: 14px;
                font-weight: normal;
            }


            .category-search-input[type="date"],
            .category-search-input[type="time"] {
                appearance: none !important;
                -webkit-appearance: none !important;
                -moz-appearance: none !important;
                background: transparent !important;
                padding-right: 10px !important;
            }

            .category-search-input[type="date"]::-webkit-calendar-picker-indicator,
            .category-search-input[type="date"]::-webkit-clear-button,
            .category-search-input[type="date"]::-webkit-inner-spin-button,
            .category-search-input[type="date"]::-webkit-outer-spin-button,
            .category-search-input[type="time"]::-webkit-calendar-picker-indicator,
            .category-search-input[type="time"]::-webkit-clear-button,
            .category-search-input[type="time"]::-webkit-inner-spin-button,
            .category-search-input[type="time"]::-webkit-outer-spin-button,
            .category-search-input[type="date"]::-ms-clear,
            .category-search-input[type="date"]::-ms-expand,
            .category-search-input[type="time"]::-ms-clear,
            .category-search-input[type="time"]::-ms-expand {
                display: none !important;
                opacity: 0 !important;
                width: 0 !important;
                height: 0 !important;
            }

            .category-search-cell--transport .ts-wrapper:not(.form-control,.form-select).single .ts-control {
                background-position: right 3px center;
            }

        </style>
    @endpush
@endsection