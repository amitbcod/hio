@extends('frontend.layout')

@section('title', __('package.list.title'))

@section('content')
@php
    $packages = $packages ?? collect();
    $guestSummary = trim((string) (($adults ?? 2) . ' Adults • ' . (($children ?? 0)) . ' Children • ' . (($infants ?? 0)) . ' Infants'));
@endphp

<section class="page-main-search" style="padding: 32px 0 24px; background: #f5f5f3;">
    <div class="wrap2" style="max-width: 1200px; margin: 0 auto;">
        <form method="GET" action="{{ route('frontend.packages.list') }}" style="display:flex; align-items:stretch; gap:0; background:#f4f3f1; border:1px solid #d9d5d0; border-radius:10px; overflow:visible; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
            <div style="flex:1; padding:16px 18px; border-right:1px solid #d9d5d0; background:#f1f0ee;">
                <div style="font-size:12px; color:#475467; margin-bottom:8px;">{{ __('home.search.region_area') }}</div>
                <select name="region" style="width:100%; border:none; background:transparent; font-size:18px; color:#1f2937; font-weight:600; outline:none; appearance:none;">
                    <option value="all" {{ ($region ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                    @foreach($regionOptions ?? [] as $option)
                        <option value="{{ $option }}" {{ ($region ?? 'all') === $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <div style="flex:1; padding:16px 18px; border-right:1px solid #d9d5d0; background:#f1f0ee;">
                <div style="font-size:12px; color:#475467; margin-bottom:8px;">{{ __('package.travelling_date') }}</div>
                <div style="display:flex; flex-direction:column; gap:4px; position:relative;">
                    <div style="display:flex; align-items:center; gap:8px; position:relative;">
                        <input
                            id="traveling-date-display"
                            type="text"
                            value="{{ !empty($travelingDate) ? \Carbon\Carbon::createFromFormat('d/m/Y', $travelingDate)->format('d/m/Y') : '' }}"
                            placeholder="dd/mm/yyyy"
                            readonly
                            style="width:100%; border:none; background:transparent; font-size:18px; color:#1f2937; font-weight:600; outline:none; cursor:pointer;"
                        >
                        <input
                            id="traveling-date-native"
                            type="date"
                            name="traveling_date"
                            value="{{ !empty($travelingDate) ? \Carbon\Carbon::createFromFormat('d/m/Y', $travelingDate)->format('Y-m-d') : '' }}"
                            style="position:absolute; inset:0; width:100%; height:100%; opacity:0; pointer-events:none;"
                        >
                    </div>
                    <small class="date-display-traveling_date" style="font-size:12px; color:#475467; min-height:18px;">
                        {{ !empty($travelingDate) ? \Carbon\Carbon::createFromFormat('d/m/Y', $travelingDate)->format('d/m/Y') : '' }}
                    </small>
                </div>
            </div>

            <div style="flex:2; padding:16px 18px; background:#f1f0ee; display:flex; align-items:center; justify-content:space-between; gap:14px; position:relative;">
                <div style="flex:1;">
                    <div style="font-size:12px; color:#475467; margin-bottom:8px;">{{ __('package.guests') }}</div>
                    <div id="package-guest-summary" style="font-size:18px; color:#1f2937; font-weight:600; cursor:pointer; user-select:none;">
                        {{ $adults ?? 2 }} {{ __('home.search.adults') }} • {{ $children ?? 0 }} {{ __('home.search.children') }} • {{ $infants ?? 0 }} {{ __('home.search.infants') }}
                    </div>
                    <div class="guest-rooms-selector" style="position:absolute; right:160px; top:52px; width:280px; background:#fff; border:1px solid #d9d5d0; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.08); padding:14px 16px; display:none; z-index:20;">
                        <div class="guest-rooms-row" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                            <label style="font-size:14px; color:#334155;">{{ __('home.search.adults') }}</label>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <button type="button" class="count-btn decrement" data-target="adults" style="width:26px; height:26px; border-radius:50%; border:1px solid #d1d5db; background:#fff; cursor:pointer;">−</button>
                                <input type="text" name="adults" value="{{ $adults ?? 2 }}" readonly style="width:32px; text-align:center; border:none; background:transparent; font-weight:700; color:#1f2937;">
                                <button type="button" class="count-btn increment" data-target="adults" style="width:26px; height:26px; border-radius:50%; border:1px solid #d1d5db; background:#fff; cursor:pointer;">+</button>
                            </div>
                        </div>
                        <div class="guest-rooms-row" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                            <label style="font-size:14px; color:#334155;">{{ __('home.search.children') }}</label>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <button type="button" class="count-btn decrement" data-target="children" style="width:26px; height:26px; border-radius:50%; border:1px solid #d1d5db; background:#fff; cursor:pointer;">−</button>
                                <input type="text" name="children" value="{{ $children ?? 0 }}" readonly style="width:32px; text-align:center; border:none; background:transparent; font-weight:700; color:#1f2937;">
                                <button type="button" class="count-btn increment" data-target="children" style="width:26px; height:26px; border-radius:50%; border:1px solid #d1d5db; background:#fff; cursor:pointer;">+</button>
                            </div>
                        </div>
                        <div class="guest-rooms-row" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                            <label style="font-size:14px; color:#334155;">{{ __('home.search.infants') }}</label>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <button type="button" class="count-btn decrement" data-target="infants" style="width:26px; height:26px; border-radius:50%; border:1px solid #d1d5db; background:#fff; cursor:pointer;">−</button>
                                <input type="text" name="infants" value="{{ $infants ?? 0 }}" readonly style="width:32px; text-align:center; border:none; background:transparent; font-weight:700; color:#1f2937;">
                                <button type="button" class="count-btn increment" data-target="infants" style="width:26px; height:26px; border-radius:50%; border:1px solid #d1d5db; background:#fff; cursor:pointer;">+</button>
                            </div>
                        </div>
                        <!-- rooms selector removed per request: only adults/children/infants needed -->
                    </div>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <button type="submit" style="background:#f39b4a; border:none; border-radius:8px; color:#fff; font-weight:800; font-size:18px; padding:12px 24px; cursor:pointer; min-width:150px;">{{ __('home.search.proceed') }}</button>
                </div>
            </div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const displayField = document.getElementById('traveling-date-display');
                const nativeDateField = document.getElementById('traveling-date-native');

                if (displayField && nativeDateField) {
                    const formatDateValue = function (value) {
                        if (!value) {
                            displayField.value = '';
                            return;
                        }

                        const match = value.match(/^\d{4}-\d{2}-\d{2}$/);
                        if (!match) {
                            displayField.value = value;
                            return;
                        }

                        const [year, month, day] = value.split('-');
                        const date = new Date(Number(year), Number(month) - 1, Number(day));

                        if (!Number.isNaN(date.getTime())) {
                            displayField.value = `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;
                        } else {
                            displayField.value = value;
                        }
                    };

                    displayField.addEventListener('click', function () {
                        if (nativeDateField.showPicker) {
                            nativeDateField.showPicker();
                        } else {
                            nativeDateField.click();
                        }
                    });

                    nativeDateField.addEventListener('change', function () {
                        formatDateValue(nativeDateField.value);
                    });

                    formatDateValue(nativeDateField.value);
                }

                const summary = document.getElementById('package-guest-summary');
                const selector = document.querySelector('.guest-rooms-selector');
                const findInput = (key) => document.querySelector(`input[name="${key}"]`);

                const updateSummary = function () {
                    const adults = parseInt(findInput('adults')?.value || 2, 10);
                    const children = parseInt(findInput('children')?.value || 0, 10);
                    const infants = parseInt(findInput('infants')?.value || 0, 10);
                    if (summary) {
                        summary.textContent = `${adults} Adults • ${children} Children • ${infants} Infants`;
                    }
                };

                if (summary) {
                    summary.addEventListener('click', function () {
                        if (selector) {
                            selector.style.display = selector.style.display === 'none' ? 'block' : 'none';
                        }
                    });
                }

                document.addEventListener('click', function (event) {
                    if (!selector) return;
                    if (!summary?.contains(event.target) && !selector.contains(event.target)) {
                        selector.style.display = 'none';
                    }
                });

                document.querySelectorAll('.count-btn').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const target = button.getAttribute('data-target');
                        const input = findInput(target);
                        if (!input) return;

                        let value = parseInt(input.value || '0', 10);
                        if (button.classList.contains('increment')) {
                            value += 1;
                        } else {
                            value = Math.max(target === 'adults' ? 1 : 0, value - 1);
                        }

                        input.value = value;
                        updateSummary();
                    });
                });

                updateSummary();
            });
        </script>

        <div class="package-list-shell" style="display:flex; gap:24px; align-items:flex-start; margin-top:24px;">
            <!-- <aside class="package-filter-panel" style="width: 290px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                <h3 style="margin:0 0 18px; font-size: 18px; font-weight: 700; color:#273246;">{{ __('filters.by') }}</h3>
                <div style="margin-bottom:22px;">
                    <div style="font-weight:700; color:#2d3748; margin-bottom:10px;">{{ __('category.filter.property_type') }}</div>
                    @forelse($packageFilterOptions['property_types'] ?? [] as $option)
                        <label style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; color:#4a5568;">
                            <span style="display:flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="property_type[]" value="{{ $option['value'] }}" {{ in_array((string) $option['value'], $selectedPropertyTypes ?? [], true) ? 'checked' : '' }}>
                                {{ $option['value'] }}
                            </span>
                            <span style="color:#6b7280;">({{ $option['count'] }})</span>
                        </label>
                    @empty
                        <div style="color:#6b7280; font-size:14px;">No property types available.</div>
                    @endforelse
                </div>

                <div style="margin-bottom:22px;">
                    <div style="font-weight:700; color:#2d3748; margin-bottom:10px;">{{ __('category.filter.meal_plan') }}</div>
                    @forelse($packageFilterOptions['meal_plans'] ?? [] as $option)
                        <label style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; color:#4a5568;">
                            <span style="display:flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="meal_plan[]" value="{{ $option['value'] }}" {{ in_array((string) $option['value'], $selectedMealPlans ?? [], true) ? 'checked' : '' }}>
                                {{ $option['value'] }}
                            </span>
                            <span style="color:#6b7280;">({{ $option['count'] }})</span>
                        </label>
                    @empty
                        <div style="color:#6b7280; font-size:14px;">No meal plans available.</div>
                    @endforelse
                </div>

                <div style="display:flex; flex-direction:column; gap:10px; margin-top:18px;">
                    <button type="button" style="background:#f39b4a; border:none; border-radius:8px; color:#fff; font-weight:700; padding:12px 16px; cursor:pointer;">{{ __('filters.apply') }}</button>
                    <button type="button" style="background:none; border:none; color:#4a5568; text-decoration:underline; cursor:pointer;">{{ __('filters.clear') }}</button>
                </div>
            </aside> -->

            <div style="flex:1; min-width:0;">
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px 22px; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                    <h2 style="margin:0; font-size: 26px; color:#1f2a37; font-weight:800;">{{ __('package.list.title') }}</h2>
                    <div style="margin-top:8px; color:#667085; font-size:14px;">{{ trans_choice('package.listings_found', $packages->count(), ['count' => $packages->count()]) }}</div>
                </div>

                    @if($packages->isEmpty())
                        <div style="margin-top:12px; padding:12px; background:#fff7ed; border:1px solid #fde3c6; color:#7a4a00; border-radius:8px;">
                            No packages match the selected number of travellers (Adults + Children). Try adjusting the guest counts or travel date.
                        </div>
                    @endif

                @forelse($packages as $package)
                    @php
                        $content = $package->itinerary['content'] ?? [];
                        $gallery = $content['gallery'] ?? [];
                        $mainImage = !empty($gallery) ? asset('storage/' . $gallery[0]) : asset('images/holidays-io-logo.png');
                        $title = $package->name ?: 'Package';
                        $days = max(0, (int) ($package->no_of_days ?? 0));
                        $nights = max(0, (int) ($package->no_of_nights ?? max(0, $days - 1)));
                        $daysNightsLabel = ($days > 0 || $nights > 0) ? sprintf('%dD%dN', $days, $nights) : '';
                    @endphp

                    <article style="margin-top:20px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                        <div style="display:flex; gap:20px; padding:16px;">
                            <div style="width:328px; height:228px; border-radius:12px; overflow:hidden; flex-shrink:0; background:#e8ecef;">
                                <img src="{{ $mainImage }}" alt="{{ $title }}" style="width:100%; height:100%; object-fit:cover; display:block;">
                            </div>

                            <div style="flex:1; min-width:0; display:flex; flex-direction:column; justify-content:space-between;">
                                <div>
                                    <div style="display:flex; justify-content:space-between; gap:16px; align-items:start;">
                                        <div style="font-size:13px; color:#4d5a66; margin-bottom:8px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                            <span style="display:inline-flex; align-items:center; gap:6px; font-weight:600;">
                                                <span style="width:10px; height:10px; display:inline-block; border-radius:50%; background:#f39b4a;"></span>
                                                @php
                                                    $regionText = '';
                                                    $itinerary = $package->itinerary ?? [];
                                                    foreach ($itinerary as $day) {
                                                        if (is_array($day) && !empty($day['accommodation'])) {
                                                            $acc = \App\Models\Accommodation::find($day['accommodation']);
                                                            if ($acc && !empty($acc->region)) {
                                                                $regionText = $acc->region;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                {{ $regionText ?: 'Mauritius' }}
                                            </span>
                                        </div>
                                        <div style="font-size:13px; color:#4d5a66; white-space:nowrap;">{{ trans_choice('category.available_label', $roomsRequired ?? 1, ['count' => $roomsRequired ?? 1]) }}</div>
                                    </div>

                                    <h3 style="margin:0; font-size: 20px; line-height: 1.3; color:#f39b4a; font-weight:800;">
                                        {{ $title }}
                                    </h3>

                                    @if(!empty($daysNightsLabel))
                                        <div style="margin-top:10px; display:inline-block; padding:6px 10px; border-radius:999px; background:#fff7ed; color:#b45309; font-size:12px; font-weight:700; letter-spacing:0.04em;">
                                            {{ $daysNightsLabel }}
                                        </div>
                                    @endif

                                    <div style="margin-top:10px; color:#4d5a66; font-size:15px;">
                                        {{ trans_choice('cart.nights', $days, ['count' => $days]) }}
                                    </div>
                                </div>

                                <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; margin-top:16px;">
                                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                        <span style="display:inline-block; padding:7px 12px; border-radius:16px; background:#dfeaf3; color:#2d4b5f; font-size:12px; font-weight:600;">{{ __('package.badge.accommodation') }}</span>
                                        <span style="display:inline-block; padding:7px 12px; border-radius:16px; background:#dfeaf3; color:#2d4b5f; font-size:12px; font-weight:600;">{{ __('package.badge.activity') }}</span>
                                        <span style="display:inline-block; padding:7px 12px; border-radius:16px; background:#dfeaf3; color:#2d4b5f; font-size:12px; font-weight:600;">{{ __('package.badge.transfer') }}</span>
                                    </div>

                                    @php
                                        $query = [];
                                        if (!empty($travelingDate)) {
                                            try {
                                                $query['traveling_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $travelingDate)->format('Y-m-d');
                                            } catch (\Exception $e) {
                                                $query['traveling_date'] = $travelingDate;
                                            }
                                        }
                                        $query['adults'] = $adults ?? 2;
                                        $query['children'] = $children ?? 0;
                                        $query['infants'] = $infants ?? 0;
                                        $packageUrl = route('frontend.packages.show', $package->id) . (!empty($query) ? ('?' . http_build_query($query)) : '');
                                    @endphp
                                    <a href="{{ $packageUrl }}" style="display:inline-block; background:#f39b4a; color:#fff; border-radius:8px; padding:10px 18px; text-decoration:none; font-weight:700;">{{ __('home.view_details') }}</a>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div style="padding:30px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; text-align:center; color:#667085;">{{ __('package.no_published') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
