@extends('frontend.layout')

@section('title', __('category.page_title', ['category' => $categoryTitle]))
@section('meta_description', __('category.meta_description', ['category' => $categoryTitle]))

@section('content')
    @php
        $heroImage = $results->first()['image'] ?? asset('images/holidays-io-logo.png');
        $clearFilterQuery = array_filter([
            'category' => $category,
            'region' => $filters['region'],
            'check_in' => $filters['check_in'],
            'check_out' => $filters['check_out'],
            'activity_date' => $filters['activity_date'],
            'type' => $filters['type'],
            'name' => $filters['name'],
            'transport_from' => $filters['transport_from'] ?? null,
            'transport_to' => $filters['transport_to'] ?? null,
            'service_type' => $filters['service_type'] ?? null,
            'arrival_date' => $filters['arrival_date'] ?? null,
            'arrival_time' => $filters['arrival_time'] ?? null,
            'return_date' => $filters['return_date'] ?? null,
            'return_time' => $filters['return_time'] ?? null,
            'passengers' => $filters['passengers'] ?? null,
            'adults' => $filters['adults'] ?? null,
            'children' => $filters['children'] ?? null,
            'rooms' => $filters['rooms'] ?? null,
            'participants' => $filters['participants'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        $detailQuery = array_filter([
            'check_in' => $filters['check_in'],
            'check_out' => $filters['check_out'],
            'activity_date' => $filters['activity_date'],
            'adults' => $filters['adults'] ?? (int) request()->query('adults', 2),
            'children' => $filters['children'] ?? (int) request()->query('children', 0),
            'infants' => $filters['infants'] ?? (int) request()->query('infants', 0),
            'rooms' => $filters['rooms'] ?? (int) request()->query('rooms', 1),
            'participants' => $filters['participants'] ?? (int) request()->query('participants', 1),
            'transport_from' => $filters['transport_from'] ?? '',
            'transport_to' => $filters['transport_to'] ?? '',
            'service_type' => request()->query('service_type', 'route'),
            'arrival_date' => request()->query('arrival_date', ''),
            'arrival_time' => request()->query('arrival_time', ''),
            'return_date' => request()->query('return_date', ''),
            'return_time' => request()->query('return_time', ''),
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    <section class="page-hero" style="display:none">
        <div class="page-hero-media">
            <img src="{{ $heroImage }}" alt="{{ $categoryTitle }}">
        </div>
        <div class="wrap page-hero-content">
            <div class="breadcrumbs">
                <a href="{{ route('frontend.home') }}">{{ __('site.home') }}</a>
                <span>/</span>
                <span>{{ $categoryTitle }} {{ __('category.listings') }}</span>
            </div>
            <h1>{{ $categoryTitle }} {{ __('category.listings') }}</h1>
            <p>
                {{ __('category.hero_description') }}
            </p>
        </div>
    </section>

    <section class="main-search page-main-search">
        <div class="wrap2">
            <form method="GET"
                action="{{ route('frontend.category.list') }}"
                class="category-search-form category-search-form--detailed"
                id="category-search-form"
                data-search-options='@json($searchOptions)'>
                <div class="category-search-cell category-search-cell--what page-category-search">
                    <!-- <h5><span>01</span> What?</h5> -->
                    <div class="category-radio-group">
                        <label class="category-radio-item">
                            <input type="radio" name="category" value="accommodation" {{ $category === 'accommodation' ? 'checked' : '' }}>
                            <div class="cat-radio-tab">
                            <div class="main-icon accommodation"><img src="images/accommodation.svg"></div>
                            <span>{{ __('home.search.accommodation') }}</span>
                            </div>
                        </label>
                        <label class="category-radio-item">
                            <input type="radio" name="category" value="tours" {{ $category === 'tours' ? 'checked' : '' }}>
                            <div class="cat-radio-tab">
                            <div class="main-icon activity"><img src="images/activity.svg"></div>
                            <span>{{ __('home.search.tours_activity') }}</span>
                            </div>
                        </label>
                        <label class="category-radio-item">
                            <input type="radio" name="category" value="transport" {{ $category === 'transport' ? 'checked' : '' }}>
                            <div class="cat-radio-tab">
                            <div class="main-icon transport"><img src="images/transport.svg"></div>
                            <span>{{ __('home.search.transport') }}</span>
                            </div> 
                        </label>
                    </div>
                </div>

              <div class="category-input-group">
                 <div class="category-input-group-inner">
                        <div class="category-search-cell category-search-cell--region" style="flex: 0 1 280px; min-width: 280px">
                            <h5>{{ __('home.search.region_area') }}</h5>
                            <select name="region" class="category-search-select" data-search-region data-selected="{{ $filters['region'] }}">
                                <option value="all" {{ $filters['region'] === 'all' || $filters['region'] === '' ? 'selected' : '' }}>{{ __('home.search.all') }}</option>
                                @foreach($searchOptions[$category]['regions'] ?? [] as $region)
                                    <option value="{{ $region }}" {{ $filters['region'] === $region ? 'selected' : '' }}>{{ $region }}</option>
                                @endforeach
                            </select>
                        </div>
                        @push('styles')
                            <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
                        @endpush
                        @push('scripts')
                            <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    try {
                                        const from = document.querySelector('select[data-search-from]');
                                        const to = document.querySelector('select[data-search-to]');
                                        if (from) new TomSelect(from, {allowEmptyOption: true, create: false});
                                        if (to) new TomSelect(to, {allowEmptyOption: true, create: false});
                                    } catch (e) {
                                        console.error('TomSelect init error', e);
                                    }
                                });
                            </script>
                        @endpush
                        <div class="category-search-cell category-search-cell--transport" style="display: none;">
                            <div class="transport-row">
                                <div class="transport-field" style="flex: 1 1 160px; min-width: 140px;">
                                    <h5>{{ __('home.search.from') }}</h5>
                                    <select name="transport_from" class="category-search-select" data-search-from>
                                        <option value="">{{ __('home.search.departure_location') }}</option>
                                        @foreach($searchOptions['transport']['froms'] ?? [] as $from)
                                            <option value="{{ $from }}" {{ ($filters['transport_from'] ?? '') === $from ? 'selected' : '' }}>{{ $from }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="transport-field" style="flex: 1 1 160px; min-width: 140px;">
                                    <h5>{{ __('home.search.to') }}</h5>
                                    <select name="transport_to" class="category-search-select" data-search-to>
                                        <option value="">{{ __('home.search.destination') }}</option>
                                        @foreach($searchOptions['transport']['tos'] ?? [] as $to)
                                            <option value="{{ $to }}" {{ ($filters['transport_to'] ?? '') === $to ? 'selected' : '' }}>{{ $to }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="transport-field" style="flex: 0 1 110px; min-width: 110px;">
                                    <h5>{{ __('home.search.passengers') }}</h5>
                                    <input type="number" name="passengers" class="category-search-input" min="1" value="{{ $filters['passengers'] ?? 2 }}">
                                </div>
                                <div class="transport-field" style="flex: 0 1 140px; min-width: 140px;">
                                    <h5>{{ __('home.search.service_type') }}</h5>
                                    <select name="service_type" class="category-search-select">
                                        <option value="route" {{ ($filters['service_type'] ?? 'route') === 'route' ? 'selected' : '' }}>{{ __('home.search.route_wise') }}</option>
                                        <option value="car_rental" {{ ($filters['service_type'] ?? '') === 'car_rental' ? 'selected' : '' }}>{{ __('home.search.car_rental') }}</option>
                                    </select>
                                </div>
                                <div class="transport-row-date">
                                    <div class="transport-field" style="flex: 1 1 160px;">
                                        <h5>{{ __('home.search.arrival_date_time') }}</h5>
                                        <input type="date" name="arrival_date" class="category-search-input" value="{{ $filters['arrival_date'] ?? now()->format('Y-m-d') }}">
                                    </div>
                                    <div class="transport-field" style="flex: 1 1 120px;">
                                        <h5>&nbsp;</h5>
                                        <input type="time" name="arrival_time" class="category-search-input" value="{{ $filters['arrival_time'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="transport-row-date">
                                    <div class="transport-field" style="flex: 1 1 160px;">
                                        <h5>{{ __('home.search.return_date_time') }}</h5>
                                        <input type="date" name="return_date" class="category-search-input" value="{{ $filters['return_date'] ?? '' }}">
                                    </div>
                                    <div class="transport-field" style="flex: 1 1 120px">
                                        <h5>&nbsp;</h5>
                                        <input type="time" name="return_time" class="category-search-input" value="{{ $filters['return_time'] ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accommodation/Transport: Check-In and Check-Out -->
                        <div class="category-search-cell category-search-cell--accommodation category-search-cell--check-in" style="display: none; flex: 0">
                            <h5>{{ __('home.search.check_in') }}</h5>
                            <div class="category-search-dates">
                                <input type="date" name="check_in" class="category-search-input" value="{{ $filters['check_in'] }}">
                            </div>
                        </div>

                        <div class="category-search-cell category-search-cell--accommodation category-search-cell--check-out" style="display: none; flex: 0 ">
                            <h5>{{ __('home.search.check_out') }}</h5>
                            <div class="category-search-dates">
                                <input type="date" name="check_out" class="category-search-input" value="{{ $filters['check_out'] }}">
                            </div>
                        </div>

                        <!-- Tours/Activity: Activity Date -->
                        <div class="category-search-cell category-search-cell--tours category-search-cell--activity-date" style="display: none;">
                            <h5>{{ __('home.search.select_date') }}</h5>
                            <div class="category-search-dates">
                                <input type="date" name="activity_date" class="category-search-input" value="{{ $filters['activity_date'] }}">
                            </div>
                        </div>

                        <!-- Accommodation/Transport/Activity: Guest details -->
                        <div class="category-search-cell category-search-cell--guests" style="display: none;">
                            <h5 id="guest-cell-heading">{{ __('home.search.guest_rooms') }}</h5>
                            <div class="guest-rooms-summary">
                                <span id="guest-rooms-summary-text">{{ (int) $filters['adults'] }} {{ __('home.search.adults') }} · {{ (int) $filters['children'] }} {{ __('home.search.children') }} · {{ (int) $filters['infants'] ?? 0 }} {{ __('home.search.infants') }} · {{ (int) $filters['rooms'] }} {{ __('home.search.rooms') }}</span>
                            </div>
                            <div class="guest-rooms-selector">
                                <div class="guest-rooms-row">
                                    <label for="category-adults-field">{{ __('home.search.adults') }} <span>({{ __('home.search.adult_age_label') }})</span></label>
                                    <div class="guest-rooms-counter">
                                        <button type="button" class="count-btn decrement" data-target="adults">−</button>
                                        <input id="category-adults-field" type="text" name="adults" value="{{ (int) $filters['adults'] }}" readonly>
                                        <button type="button" class="count-btn increment" data-target="adults">+</button>
                                    </div>
                                </div>
                                <div class="guest-rooms-row">
                                    <label for="category-children-field">{{ __('home.search.children') }} <span>({{ __('home.search.children_age_label') }})</span></label>
                                    <div class="guest-rooms-counter">
                                        <button type="button" class="count-btn decrement" data-target="children">−</button>
                                        <input id="category-children-field" type="text" name="children" value="{{ (int) $filters['children'] }}" readonly>
                                        <button type="button" class="count-btn increment" data-target="children">+</button>
                                    </div>
                                </div>
                                <div class="guest-rooms-row">
                                    <label for="category-infants-field">{{ __('home.search.infants') }} <span>({{ __('home.search.infants_age_label') }})</span></label>
                                    <div class="guest-rooms-counter">
                                        <button type="button" class="count-btn decrement" data-target="infants">−</button>
                                        <input id="category-infants-field" type="text" name="infants" value="{{ (int) $filters['infants'] ?? 0 }}" readonly>
                                        <button type="button" class="count-btn increment" data-target="infants">+</button>
                                    </div>
                                </div>
                                <div class="guest-rooms-row" id="rooms-row">
                                    <label for="category-rooms-field">{{ __('home.search.rooms') }} <span>({{ __('home.search.rooms_limit_label') }})</span></label>
                                    <div class="guest-rooms-counter">
                                        <button type="button" class="count-btn decrement" data-target="rooms">−</button>
                                        <input id="category-rooms-field" type="text" name="rooms" value="{{ (int) $filters['rooms'] }}" readonly>
                                        <button type="button" class="count-btn increment" data-target="rooms">+</button>
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
    </section>

    <section class="page-section category-listing-section">
        <div class="wrap category-layout">
            <aside class="category-filters">
                <h3 class="toggle">{{ __('filters.by') }} <span class="arrow"></span></h3>
                @if(empty($sidebarDefinitions))
                    <p class="filter-note">{{ __('filters.none_available') }}</p>
                @else
                    <form method="GET" action="{{ route('frontend.category.list') }}" class="category-filter-form">
                        <input type="hidden" name="category" value="{{ $category }}">
                        <input type="hidden" name="region" value="{{ $filters['region'] }}">
                        <input type="hidden" name="check_in" value="{{ $filters['check_in'] }}">
                        <input type="hidden" name="check_out" value="{{ $filters['check_out'] }}">
                        <input type="hidden" name="activity_date" value="{{ $filters['activity_date'] }}">
                        <input type="hidden" name="type" value="{{ $filters['type'] }}">
                        <input type="hidden" name="name" value="{{ $filters['name'] }}">
                        <input type="hidden" name="transport_from" value="{{ $filters['transport_from'] ?? '' }}">
                        <input type="hidden" name="transport_to" value="{{ $filters['transport_to'] ?? '' }}">
                        <input type="hidden" name="service_type" value="{{ $filters['service_type'] ?? 'route' }}">
                        <input type="hidden" name="arrival_date" value="{{ $filters['arrival_date'] ?? '' }}">
                        <input type="hidden" name="arrival_time" value="{{ $filters['arrival_time'] ?? '' }}">
                        <input type="hidden" name="return_date" value="{{ $filters['return_date'] ?? '' }}">
                        <input type="hidden" name="return_time" value="{{ $filters['return_time'] ?? '' }}">
                        <input type="hidden" name="passengers" value="{{ $filters['passengers'] ?? 2 }}">
                        <input type="hidden" name="adults" value="{{ $filters['adults'] ?? 2 }}">
                        <input type="hidden" name="children" value="{{ $filters['children'] ?? 0 }}">
                        <input type="hidden" name="infants" value="{{ $filters['infants'] ?? 0 }}">
                        <input type="hidden" name="rooms" value="{{ $filters['rooms'] ?? 1 }}">

                        @foreach($sidebarDefinitions as $definition)
                            @php
                                $selectedValues = $sidebarSelections[$definition['key']] ?? [];
                            @endphp
                            <div class="filter-group">
                                <strong>{{ $definition['label'] }}</strong>
                                @foreach($definition['options'] as $option)
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="{{ $definition['key'] }}[]"
                                            value="{{ $option['value'] }}"
                                            {{ in_array($option['value'], $selectedValues, true) ? 'checked' : '' }}>
                                        <span>{{ $option['label'] }}</span>
                                        <small>({{ $option['count'] }})</small>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach

                        <div class="filter-actions">
                            <button type="submit" class="btn-primary">{{ __('filters.apply') }}</button>
                            <a href="{{ route('frontend.category.list', $clearFilterQuery) }}" class="filter-clear-link">{{ __('filters.clear') }}</a>
                        </div>
                    </form>
                @endif
            </aside>

            <div class="category-results">
                <div class="section-header category-results-head">
                    <div>
                        <h2>{{ $categoryTitle }}</h2>
                        <p>{{ trans_choice('category.results_count', $results->total(), ['count' => $results->total()]) }}</p>
                    </div>
                </div>

                @if($results->isEmpty())
                    <div class="empty-state">{{ __('category.no_results', ['category' => strtolower($categoryTitle)]) }}</div>
                @else
                    <div class="category-result-list">
                        @foreach($results as $item)
                            @php


                                $metaLabel = $item['property_type'] ?? $item['meta'] ?? $item['kind'] ?? $categoryTitle;
                                $baseUrl = $item['url'] ?? '#';
                                $iteUSDl = $baseUrl;
                                if (!empty($detailQuery) && $baseUrl !== '#') {
                                    $iteUSDl .= (str_contains($baseUrl, '?') ? '&' : '?') . http_build_query($detailQuery);
                                }
                                $startingRate = $item['starting_rate_of_adult'] ?? null;
                                $isActivityListing = ($item['kind'] ?? null) === 'Activity' || $category === 'tours';
                                if ($category === 'transports' || $category === 'transport') {
                                    $priceUnit = '';
                                } else {
                                    $priceUnit = $isActivityListing ? '/ person' : '/ room';
                                }
                            @endphp
                            <article class="category-result-card">
                                @if(isset($item['available_rooms_count']) && $item['available_rooms_count'] !== null)
                                    <div class="category-result-availability-badge">
                                        <div class="availability-count">{{ $item['available_rooms_count'] }}</div>
                                        <div class="availability-label">{{ trans_choice('category.available_label', $item['available_rooms_count']) }}</div>
                                    </div>
                                @endif
                                <a href="{{ $iteUSDl }}" class="category-result-media">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                                </a>
                                <div class="category-result-body">
                                    <span class="listing-location"><i class="fa-solid fa-location-dot"></i> {{ $item['location'] ?? 'Mauritius' }}</span>
                                    <div class="category-result-title-row">
                                        <h3><a href="{{ $iteUSDl }}">{{ $item['title'] }}</a></h3>
                                        @if(!empty($item['rating_display']))
                                            @php
                                                $ratingValue = (float) $item['rating_display'];
                                                $fullStars = min(5, max(0, floor($ratingValue)));
                                                $halfStar = ($ratingValue - $fullStars) >= 0.5 ? 1 : 0;
                                                $emptyStars = 5 - $fullStars - $halfStar;
                                            @endphp
                                            <span class="listing-rating-badge" aria-label="{{ number_format($ratingValue, 1) }} out of 5 stars">
                                                @for($i = 0; $i < $fullStars; $i++)
                                                    <i class="fa-solid fa-star"></i>
                                                @endfor
                                                @if($halfStar)
                                                    <i class="fa-solid fa-star-half-stroke"></i>
                                                @endif
                                                @for($i = 0; $i < $emptyStars; $i++)
                                                    <i class="fa-regular fa-star"></i>
                                                @endfor
                                            </span>
                                            @if(!empty($item['rating_count']))
                                                <span class="listing-review-count">{{ trans_choice('reviews.count', $item['rating_count'], ['count' => $item['rating_count']]) }}</span>
                                            @endif
                                        @endif
                                    </div>
                                    <p>{{ $item['excerpt'] }}</p>
                                    <div class="category-result-footer">
                                        <span class="chip">{{ $metaLabel }}</span>
                                        @if(isset($item['available_rooms_count']) && $item['available_rooms_count'] !== null)
                                            <!-- <span class="listing-availability">{{ $item['available_rooms_count'] }} rooms available</span> -->
                                        @endif
                                        @if($startingRate !== null)
                                            <span class="listing-price">From USD {{ number_format((float) $startingRate, 0) }} {{ $priceUnit }}</span>
                                        @endif
                                        <a href="{{ $iteUSDl }}">{{ __('home.view_details') }}</a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="category-pagination">
                        @php
                            $currentPage = $results->currentPage();
                            $lastPage = $results->lastPage();
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($lastPage, $startPage + 4);
                            $startPage = max(1, $endPage - 4);
                        @endphp

                        @if($results->onFirstPage())
                            <span class="is-disabled">&laquo;</span>
                        @else
                            <a href="{{ $results->appends(request()->except('page'))->previousPageUrl() }}">&laquo;</a>
                        @endif

                        @for($page = $startPage; $page <= $endPage; $page++)
                            @if($page === $currentPage)
                                <span class="is-current">{{ $page }}</span>
                            @else
                                <a href="{{ $results->appends(request()->except('page'))->url($page) }}">{{ $page }}</a>
                            @endif
                        @endfor

                        @if($results->hasMorePages())
                            <a href="{{ $results->appends(request()->except('page'))->nextPageUrl() }}">&raquo;</a>
                        @else
                            <span class="is-disabled">&raquo;</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const findInput = (key) => document.querySelector(`[name='${key}']`);
            const guestSummary = document.getElementById('guest-rooms-summary-text');
            const guestCell = document.querySelector('.category-search-cell--guests');
            const guestCellHeading = document.getElementById('guest-cell-heading');
            const roomsRow = document.getElementById('rooms-row');
            const regionCell = document.querySelector('.category-search-cell--region');
            const accommodationCheckInCell = document.querySelector('.category-search-cell--check-in');
            const accommodationCheckOutCell = document.querySelector('.category-search-cell--check-out');
            const toursActivityDateCell = document.querySelector('.category-search-cell--activity-date');
            const transportCells = document.querySelectorAll('.category-search-cell--transport');
            const categoryRadios = document.querySelectorAll('input[name="category"]');
            const guestTexts = {
                participants: {!! json_encode(__('home.search.participants')) !!},
                guestRooms: {!! json_encode(__('home.search.guest_rooms')) !!},
                adults: {!! json_encode(__('home.search.adults_label')) !!},
                children: {!! json_encode(__('home.search.children_label')) !!},
                infants: {!! json_encode(__('home.search.infants_label')) !!},
                rooms: {!! json_encode(__('home.search.rooms_label')) !!}
            };

            // Update guest rooms summary text
            const updateGuestSummary = function () {
                const adults = parseInt(findInput('adults')?.value || 0, 10);
                const children = parseInt(findInput('children')?.value || 0, 10);
                const infants = parseInt(findInput('infants')?.value || 0, 10);
                const rooms = parseInt(findInput('rooms')?.value || 0, 10);

                if (guestSummary) {
                    const selectedCategory = document.querySelector('input[name="category"]:checked')?.value;
                    const parts = [
                        `${adults} ${guestTexts.adults}`,
                        `${children} ${guestTexts.children}`,
                        `${infants} ${guestTexts.infants}`,
                    ];

                    if (selectedCategory !== 'tours') {
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
                    transportCells.forEach((cell) => cell.style.display = 'none');
                    guestCell.style.display = 'block';
                    toursActivityDateCell.style.display = 'block';
                    if (guestCellHeading) guestCellHeading.textContent = guestTexts.participants;
                    if (roomsRow) roomsRow.style.display = 'none';
                } else if (selectedCategory === 'transport') {
                    regionCell.style.display = 'none';
                    accommodationCheckInCell.style.display = 'none';
                    accommodationCheckOutCell.style.display = 'none';
                    transportCells.forEach((cell) => cell.style.display = 'block');
                    guestCell.style.display = 'none';
                    toursActivityDateCell.style.display = 'none';
                    if (roomsRow) roomsRow.style.display = 'none';
                } else {
                    regionCell.style.display = 'block';
                    accommodationCheckInCell.style.display = 'block';
                    accommodationCheckOutCell.style.display = 'block';
                    transportCells.forEach((cell) => cell.style.display = 'none');
                    guestCell.style.display = 'block';
                    toursActivityDateCell.style.display = 'none';
                    if (guestCellHeading) guestCellHeading.textContent = guestTexts.guestRooms;
                    if (roomsRow) roomsRow.style.display = 'flex';
                }
            };

            // Attach change event to category radios
            categoryRadios.forEach(function (radio) {
                radio.addEventListener('change', function () {
                    updateCategoryFields();
                    updateGuestSummary();
                });
            });

            // Initialize on first load
            updateCategoryFields();
            updateGuestSummary();

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
        });
    </script>

    @push('styles')
        <style>
            .category-search-cell--transport .ts-wrapper.category-search-select .ts-control {
                padding: 0;
                padding-right: 20px !important;
            }

            .category-search-cell--transport .ts-wrapper.category-search-select {
                width: 100%;
                height: 22px;
            }

            .category-search-cell--transport .ts-wrapper.category-search-select .ts-dropdown.single {
                background: #fff;
                font-size: 14px;
                font-weight: normal;
            }

            /* .transport-row,
            .transport-row-date {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                align-items: flex-end;
                margin-top: 12px;
            }

            .transport-row-date {
                margin-top: 10px;
            } */

            /* .transport-field {
                display: flex;
                flex-direction: column;
                gap: 6px;
            } */

            /* .transport-field h5 {
                margin: 0;
                font-size: 0.84rem;
                line-height: 1.2;
                color: inherit;
            } */

            /* .category-search-cell--transport .transport-field {
                min-width: 0;
            } */

            /* .transport-row,
            .transport-row-date {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: flex-end;
                margin-top: 10px;
            }

            .transport-row-date {
                margin-top: 0;
            } */

            /* .transport-field {
                display: flex;
                flex-direction: column;
                gap: 4px;
                min-width: 0;
            } */

            /* .transport-field h5 {
                margin: 0;
                font-size: 0.85rem;
                line-height: 1.2;
            } */

            /* .transport-field input,
            .transport-field select {
                min-height: 44px;
                height: 44px;
                width: 100%;
                box-sizing: border-box;
                padding: 0 12px;
            } */

            /* .category-search-cell--transport .category-search-input,
            .category-search-cell--transport .category-search-select {
                width: 100%;
                min-height: 44px;
                height: 44px;
                box-sizing: border-box;
                padding: 0 12px;
            } */

            /* .category-search-cell--transport .category-search-select {
                min-height: 44px;
                height: 44px;
            } */

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
