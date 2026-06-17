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
            <span class="hero-badge">Explore</span>
            <h1>Discover Mauritius</h1>
            <p>Experience beaches, resorts, and unforgettable holidays</p>

            <div class="hero-actions">
                <a href="#" class="btn-primary">Explore now</a>
                <a href="#discover-mauritius" class="btn-outline">Browse homepage listings</a>
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
                            <div class="category-search-cell category-search-cell--region" style="{{ $selectedCategory === 'transport' ? 'display:none;' : '' }}; flex: 0 1 280px; min-width: 280px">
                                <h5>Region/Area ?</h5>
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
                                <h5>Check-In</h5>
                                <div class="category-search-dates">
                                    <input type="date" name="check_in" class="category-search-input"
                                        value="{{ $filters['check_in'] }}" min="{{ date('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="category-search-cell category-search-cell--accommodation category-search-cell--check-out" style="display: none; flex: 0">
                                <h5>Check-Out</h5>
                                <div class="category-search-dates">
                                    <input type="date" name="check_out" class="category-search-input"
                                        value="{{ $filters['check_out'] }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                </div>
                            </div>

                            <!-- Tours/Activity: Activity Date -->
                            <div class="category-search-cell category-search-cell--tours category-search-cell--activity-date" style="display: none;">
                                <h5>Select Date</h5>
                                <div class="category-search-dates">
                                    <input type="date" name="activity_date" class="category-search-input"
                                        value="{{ request()->query('activity_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}">
                                </div>
                            </div>

                            <!-- Accommodation/Transport/Activity: Guest details -->
                            <div class="category-search-cell category-search-cell--guests" style="display: none;">
                                <h5 id="guest-cell-heading">Guest & Rooms</h5>
                                <div class="guest-rooms-summary">
                                    <span id="guest-rooms-summary-text">{{ (int) request()->query('adults',2) }} Adults &middot; {{ (int) request()->query('children',0) }} Child &middot; {{ (int) request()->query('infants',0) }} Infant &middot; {{ (int) request()->query('rooms',1) }} Rooms</span> 
                                </div>
                                <div class="guest-rooms-selector">
                                    <div class="guest-rooms-row">
                                        <label for="adults-field">Adults <span>(17+ yr)</span></label>
                                        <div class="guest-rooms-counter">
                                            <button type="button" class="count-btn decrement" data-target="adults">−</button>
                                            <input id="adults-field" type="text" name="adults" value="{{ request()->query('adults', 2) }}" readonly>
                                            <button type="button" class="count-btn increment" data-target="adults">+</button>
                                        </div>
                                    </div>
                                    <div class="guest-rooms-row">
                                        <label for="children-field">Children <span>(3-17 yr)</span></label>
                                        <div class="guest-rooms-counter">
                                            <button type="button" class="count-btn decrement" data-target="children">−</button>
                                            <input id="children-field" type="text" name="children" value="{{ request()->query('children', 0) }}" readonly>
                                            <button type="button" class="count-btn increment" data-target="children">+</button> 
                                        </div>
                                    </div>
                                    <div class="guest-rooms-row">
                                        <label for="infants-field">Infants <span>(0-2 yr)</span></label>
                                        <div class="guest-rooms-counter">
                                            <button type="button" class="count-btn decrement" data-target="infants">−</button>
                                            <input id="infants-field" type="text" name="infants" value="{{ request()->query('infants', 0) }}" readonly>
                                            <button type="button" class="count-btn increment" data-target="infants">+</button>
                                        </div>
                                    </div>
                                    <div class="guest-rooms-row" id="rooms-row">
                                        <label for="rooms-field">Rooms <span>(Max 20)</span></label>
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
                                        <h5>From</h5>
                                        <input type="text" name="transport_from" class="category-search-input" value="{{ request()->query('transport_from', '') }}" placeholder="Departure location">
                                    </div>
                                    <div class="transport-field" style="flex: 1 1 160px; min-width: 140px;">
                                        <h5>To</h5>
                                        <input type="text" name="transport_to" class="category-search-input" value="{{ request()->query('transport_to', '') }}" placeholder="Destination">
                                    </div>
                                    <div class="transport-field" style="flex: 0 1 110px; min-width: 110px;">
                                        <h5>Passengers</h5>
                                        <input type="number" name="passengers" class="category-search-input" min="1" value="{{ request()->query('passengers', 2) }}">
                                    </div>
                                    <div class="transport-row-date">
                                        <div class="transport-field" style="flex: 1 1 160px; min-width: 160px;">
                                            <h5>Arrival date and time</h5>
                                            <input type="date" name="arrival_date" class="category-search-input" value="{{ request()->query('arrival_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="transport-field" style="flex: 1 1 120px; min-width: 120px;">
                                            <h5>&nbsp;</h5>
                                            <input type="time" name="arrival_time" class="category-search-input" value="{{ request()->query('arrival_time', '') }}">
                                        </div>
                                    </div>
                                    <div class="transport-row-date"> 
                                        <div class="transport-field" style="flex: 1 1 160px; min-width: 160px;">
                                            <h5>Return date and time</h5>
                                            <input type="date" name="return_date" class="category-search-input" value="{{ request()->query('return_date', '') }}" min="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="transport-field" style="flex: 1 1 120px; min-width: 120px;">
                                            <h5>&nbsp;</h5>
                                            <input type="time" name="return_time" class="category-search-input" value="{{ request()->query('return_time', '') }}">
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
                <h3>Mauritius Holiday Destination</h3>
              <p>
Discover the beauty of a tropical paradise known for its stunning beaches, 
vibrant culture, and year-round pleasant climate. Whether you're a visitor or a local resident</br> 
    Mauritius offers perfect experiences from relaxation to adventure.</br> 
    The island is safe and tourist-friendly, with modern infrastructure and welcoming communities. </br> 
Basic healthcare facilities and pharmacies are easily accessible across the country. Enjoy your holiday with peace of mind by following simple safety practices and staying protected under the tropical sun.
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
                    <h2>Your Holiday in Mauritius</h2>
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
                    guestSummary.textContent = `${adults} Adults · ${children} Child${children === 1 ? '' : 'ren'} · ${infants} Infant${infants === 1 ? '' : 's'}` + (selectedCategory === 'accommodation' ? ` · ${rooms} Room${rooms === 1 ? '' : 's'}` : '');
                }
            };

            // Show/hide fields based on selected category
            const updateCategoryFields = function () {
                const selectedCategory = document.querySelector('input[name="category"]:checked')?.value;

                if (selectedCategory === 'tours') {
                    accommodationCheckInCell.style.display = 'none';
                    accommodationCheckOutCell.style.display = 'none';
                    guestCell.style.display = 'block';
                    if (guestCellHeading) guestCellHeading.textContent = 'Participants';
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
                } else {
                    accommodationCheckInCell.style.display = 'block';
                    accommodationCheckOutCell.style.display = 'block';
                    guestCell.style.display = 'block';
                    if (guestCellHeading) guestCellHeading.textContent = 'Guest & Rooms';
                    if (roomsRow) roomsRow.style.display = 'flex';
                    toursActivityDateCell.style.display = 'none';
                    transportCells.forEach(el => el.style.display = 'none');
                    if (regionCell) regionCell.style.display = 'block';
                }
                updateGuestSummary();
            };

            // Attach change event to category radios
            categoryRadios.forEach(function (radio) {
                radio.addEventListener('change', updateCategoryFields);
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
@endsection