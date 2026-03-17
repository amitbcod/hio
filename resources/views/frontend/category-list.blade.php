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
            'type' => $filters['type'],
            'name' => $filters['name'],
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    <section class="page-hero">
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

    <section class="main-search" style="background-color: #E9E6E0;">
        <div class="wrap">
            <form method="GET"
                action="{{ route('frontend.category.list') }}"
                class="category-search-form category-search-form--detailed"
                id="category-search-form"
                data-search-options='@json($searchOptions)'>
                <div class="category-search-cell category-search-cell--what">
                    <h5><span>01</span> What?</h5>
                    <div class="category-radio-group">
                        <label class="category-radio-item">
                            <input type="radio" name="category" value="accommodation" {{ $category === 'accommodation' ? 'checked' : '' }}>
                            <span>Accommodation</span>
                        </label>
                        <label class="category-radio-item">
                            <input type="radio" name="category" value="tours" {{ $category === 'tours' ? 'checked' : '' }}>
                            <span>Tours - Activity</span>
                        </label>
                        <label class="category-radio-item">
                            <input type="radio" name="category" value="transport" {{ $category === 'transport' ? 'checked' : '' }}>
                            <span>Transport</span>
                        </label>
                    </div>
                </div>

                <div class="category-search-cell">
                    <h5><span>02</span> Region/Area ?</h5>
                    <select name="region" class="category-search-select" data-search-region data-selected="{{ $filters['region'] }}">
                        <option value="">Please select</option>
                        @foreach($searchOptions[$category]['regions'] ?? [] as $region)
                            <option value="{{ $region }}" {{ $filters['region'] === $region ? 'selected' : '' }}>{{ $region }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="category-search-cell">
                    <h5><span>03</span> Check-in and Check-Out</h5>
                    <div class="category-search-dates">
                        <input type="date" name="check_in" class="category-search-input" value="{{ $filters['check_in'] }}">
                        <input type="date" name="check_out" class="category-search-input" value="{{ $filters['check_out'] }}">
                    </div>
                </div>

                <div class="category-search-cell category-search-cell--type">
                    <h5><span>04</span> <span data-type-label>Type</span></h5>
                    <div class="category-search-stack">
                        <select name="type" class="category-search-select" data-search-type data-selected="{{ $filters['type'] }}">
                            <option value="">Any</option>
                            @foreach($searchOptions[$category]['types'] ?? [] as $type)
                                <option value="{{ $type }}" {{ $filters['type'] === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        <div class="category-search-field-group">
                            <label for="category-name-search">Name Search</label>
                            <input id="category-name-search" type="text" name="name" class="category-search-input" placeholder="Optional" value="{{ $filters['name'] }}">
                        </div>
                    </div>
                </div>

                <div class="category-search-submit">
                    <button type="submit" class="btn-primary">Proceed to results</button>
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
                        <input type="hidden" name="type" value="{{ $filters['type'] }}">
                        <input type="hidden" name="name" value="{{ $filters['name'] }}">

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
                                $itemUrl = $item['url'] ?? '#';
                                $startingRate = $item['starting_rate'] ?? null;
                                $secondaryLabel = $item['booking_confirmation_type'] ?? null;
                            @endphp
                            <article class="category-result-card">
                                <a href="{{ $itemUrl }}" class="category-result-media">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                                </a>
                                <div class="category-result-body">
                                    <span class="listing-location"><i class="fa-solid fa-location-dot"></i> {{ $item['location'] }}</span>
                                    <h3><a href="{{ $itemUrl }}">{{ $item['title'] }}</a></h3>
                                    <p>{{ $item['excerpt'] }}</p>
                                    <div class="category-result-footer">
                                        <span class="chip">{{ $metaLabel }}</span>
                                        @if($secondaryLabel)
                                            <strong>{{ $secondaryLabel }}</strong>
                                        @endif
                                        @if($startingRate !== null)
                                            <span class="listing-price">From Rs {{ number_format((float) $startingRate, 0) }}</span>
                                        @endif
                                        <a href="{{ $itemUrl }}">View details</a>
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
@endsection
