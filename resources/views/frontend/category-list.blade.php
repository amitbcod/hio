@extends('frontend.layout')

@section('title', $categoryTitle . ' Listings | Holidays.io')
@section('meta_description', 'Browse ' . $categoryTitle . ' listings on Holidays.io.')

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
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    <section class="page-hero" style="display:none">
        <div class="page-hero-media">
            <img src="{{ $heroImage }}" alt="{{ $categoryTitle }}">
        </div>
        <div class="wrap page-hero-content">
            <div class="breadcrumbs">
                <a href="{{ route('frontend.home') }}">Home</a>
                <span>/</span>
                <span>{{ $categoryTitle }} listings</span>
            </div>
            <h1>{{ $categoryTitle }} Listings</h1>
            <p>
                Browse listings by category and open each card to view full details.
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
                            <span>Accommodation</span>
                            </div>
                        </label>
                        <label class="category-radio-item">
                            <input type="radio" name="category" value="tours" {{ $category === 'tours' ? 'checked' : '' }}>
                            <div class="cat-radio-tab">
                            <div class="main-icon activity"><img src="images/activity.svg"></div>
                            <span>Tours - Activity</span>
                            </div>
                        </label>
                        <label class="category-radio-item">
                            <input type="radio" name="category" value="transport" {{ $category === 'transport' ? 'checked' : '' }}>
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
                            <select name="region" class="category-search-select" data-search-region data-selected="{{ $filters['region'] }}">
                                <option value="all" {{ $filters['region'] === 'all' || $filters['region'] === '' ? 'selected' : '' }}>All</option>
                                @foreach($searchOptions[$category]['regions'] ?? [] as $region)
                                    <option value="{{ $region }}" {{ $filters['region'] === $region ? 'selected' : '' }}>{{ $region }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Accommodation/Transport: Check-In and Check-Out -->
                        <div class="category-search-cell category-search-cell--accommodation category-search-cell--check-in" style="display: none;">
                            <h5>Check-In</h5>
                            <div class="category-search-dates">
                                <input type="date" name="check_in" class="category-search-input" value="{{ $filters['check_in'] }}">
                            </div>
                        </div>

                        <div class="category-search-cell category-search-cell--accommodation category-search-cell--check-out" style="display: none;">
                            <h5>Check-Out</h5>
                            <div class="category-search-dates">
                                <input type="date" name="check_out" class="category-search-input" value="{{ $filters['check_out'] }}">
                            </div>
                        </div>

                        <!-- Tours/Activity: Activity Date -->
                        <div class="category-search-cell category-search-cell--tours category-search-cell--activity-date" style="display: none;">
                            <h5>Select Date</h5>
                            <div class="category-search-dates">
                                <input type="date" name="activity_date" class="category-search-input" value="{{ $filters['activity_date'] }}">
                            </div>
                        </div>

                        <!-- Accommodation/Transport/Activity: Guest details -->
                        <div class="category-search-cell category-search-cell--guests" style="display: none;">
                            <h5 id="guest-cell-heading">Guest & Rooms</h5>
                            <div class="guest-rooms-summary">
                                <span id="guest-rooms-summary-text">{{ (int) $filters['adults'] }} Adults · {{ (int) $filters['children'] }} Child{{ (int) $filters['children'] === 1 ? '' : 'ren' }} · {{ (int) $filters['infants'] ?? 0 }} Infant{{ ((int) $filters['infants'] ?? 0) === 1 ? '' : 's' }} · {{ (int) $filters['rooms'] }} Room{{ (int) $filters['rooms'] === 1 ? '' : 's' }}</span>
                            </div>
                            <div class="guest-rooms-selector">
                                <div class="guest-rooms-row">
                                    <label for="category-adults-field">Adults <span>(17+ yr)</span></label>
                                    <div class="guest-rooms-counter">
                                        <button type="button" class="count-btn decrement" data-target="adults">−</button>
                                        <input id="category-adults-field" type="text" name="adults" value="{{ (int) $filters['adults'] }}" readonly>
                                        <button type="button" class="count-btn increment" data-target="adults">+</button>
                                    </div>
                                </div>
                                <div class="guest-rooms-row">
                                    <label for="category-children-field">Children <span>(0-17 yr)</span></label>
                                    <div class="guest-rooms-counter">
                                        <button type="button" class="count-btn decrement" data-target="children">−</button>
                                        <input id="category-children-field" type="text" name="children" value="{{ (int) $filters['children'] }}" readonly>
                                        <button type="button" class="count-btn increment" data-target="children">+</button>
                                    </div>
                                </div>
                                <div class="guest-rooms-row">
                                    <label for="category-infants-field">Infants <span>(0-2 yr)</span></label>
                                    <div class="guest-rooms-counter">
                                        <button type="button" class="count-btn decrement" data-target="infants">−</button>
                                        <input id="category-infants-field" type="text" name="infants" value="{{ (int) $filters['infants'] ?? 0 }}" readonly>
                                        <button type="button" class="count-btn increment" data-target="infants">+</button>
                                    </div>
                                </div>
                                <div class="guest-rooms-row" id="rooms-row">
                                    <label for="category-rooms-field">Rooms <span>(Max 20)</span></label>
                                    <div class="guest-rooms-counter">
                                        <button type="button" class="count-btn decrement" data-target="rooms">−</button>
                                        <input id="category-rooms-field" type="text" name="rooms" value="{{ (int) $filters['rooms'] }}" readonly>
                                        <button type="button" class="count-btn increment" data-target="rooms">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="category-search-submit">
                            <button type="submit" class="btn-primary">Proceed</button>
                        </div>
                    </div>
                </div>

                
            </form>
        </div>
    </section>

    <section class="page-section category-listing-section">
        <div class="wrap category-layout">
            <aside class="category-filters">
                <h3>Filter by</h3>
                @if(empty($sidebarDefinitions))
                    <p class="filter-note">No extra filters are available for this category yet.</p>
                @else
                    <form method="GET" action="{{ route('frontend.category.list') }}" class="category-filter-form">
                        <input type="hidden" name="category" value="{{ $category }}">
                        <input type="hidden" name="region" value="{{ $filters['region'] }}">
                        <input type="hidden" name="check_in" value="{{ $filters['check_in'] }}">
                        <input type="hidden" name="check_out" value="{{ $filters['check_out'] }}">
                        <input type="hidden" name="activity_date" value="{{ $filters['activity_date'] }}">
                        <input type="hidden" name="type" value="{{ $filters['type'] }}">
                        <input type="hidden" name="name" value="{{ $filters['name'] }}">
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
                            <button type="submit" class="btn-primary">Apply filters</button>
                            <a href="{{ route('frontend.category.list', $clearFilterQuery) }}" class="filter-clear-link">Clear filters</a>
                        </div>
                    </form>
                @endif
            </aside>

            <div class="category-results">
                <div class="section-header category-results-head">
                    <div>
                        <h2>{{ $categoryTitle }}</h2>
                        <p>{{ $results->total() }} listing(s) found.</p>
                    </div>
                </div>

                @if($results->isEmpty())
                    <div class="empty-state">No {{ strtolower($categoryTitle) }} listings matched the selected search and filter options.</div>
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
                                $startingRate = $item['starting_rate'] ?? null;
                                $isActivityListing = ($item['kind'] ?? null) === 'Activity' || $category === 'tours';
                                $priceUnit = $isActivityListing ? '/ person' : '/ room';
                            @endphp
                            <article class="category-result-card">
                                @if(isset($item['available_rooms_count']) && $item['available_rooms_count'] !== null)
                                    <div class="category-result-availability-badge">
                                        <div class="availability-count">{{ $item['available_rooms_count'] }}</div>
                                        <div class="availability-label">available</div>
                                    </div>
                                @endif
                                <a href="{{ $iteUSDl }}" class="category-result-media">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                                </a>
                                <div class="category-result-body">
                                    <span class="listing-location"><i class="fa-solid fa-location-dot"></i> {{ $item['location'] }}</span>
                                    <h3><a href="{{ $iteUSDl }}">{{ $item['title'] }}</a></h3>
                                    <p>{{ $item['excerpt'] }}</p>
                                    <div class="category-result-footer">
                                        <span class="chip">{{ $metaLabel }}</span>
                                        @if(isset($item['available_rooms_count']) && $item['available_rooms_count'] !== null)
                                            <!-- <span class="listing-availability">{{ $item['available_rooms_count'] }} rooms available</span> -->
                                        @endif
                                        @if($startingRate !== null)
                                            <span class="listing-price">From USD {{ number_format((float) $startingRate, 0) }} {{ $priceUnit }}</span>
                                        @endif
                                        <a href="{{ $iteUSDl }}">View details</a>
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
            const accommodationCheckInCell = document.querySelector('.category-search-cell--check-in');
            const accommodationCheckOutCell = document.querySelector('.category-search-cell--check-out');
            const toursActivityDateCell = document.querySelector('.category-search-cell--activity-date');
            const categoryRadios = document.querySelectorAll('input[name="category"]');

            // Update guest rooms summary text
            const updateGuestSummary = function () {
                const adults = parseInt(findInput('adults')?.value || 0, 10);
                const children = parseInt(findInput('children')?.value || 0, 10);
                const infants = parseInt(findInput('infants')?.value || 0, 10);
                const rooms = parseInt(findInput('rooms')?.value || 0, 10);

                if (guestSummary) {
                    const selectedCategory = document.querySelector('input[name="category"]:checked')?.value;
                    const parts = [
                        `${adults} Adults`,
                        `${children} Child${children === 1 ? '' : 'ren'}`,
                        `${infants} Infant${infants === 1 ? '' : 's'}`,
                    ];

                    if (selectedCategory !== 'tours') {
                        parts.push(`${rooms} Room${rooms === 1 ? '' : 's'}`);
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
                    toursActivityDateCell.style.display = 'block';
                    if (guestCellHeading) guestCellHeading.textContent = 'Participants';
                    if (roomsRow) roomsRow.style.display = 'none';
                } else {
                    accommodationCheckInCell.style.display = 'block';
                    accommodationCheckOutCell.style.display = 'block';
                    guestCell.style.display = 'block';
                    toursActivityDateCell.style.display = 'none';
                    if (guestCellHeading) guestCellHeading.textContent = 'Guest & Rooms';
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
        });
    </script>
@endsection
