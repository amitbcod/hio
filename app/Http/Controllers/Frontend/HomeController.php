<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationRate;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $selectedCategory = $this->normalizeCategory($request->query('category'));
        $filters = $this->collectSearchFilters($request);
        $searchOptions = $this->buildSearchOptions();

        $activities = Activity::with('seoSocial')
            ->whereNotNull('activity_name')
            ->latest('updated_at')
            ->take(8)
            ->get()
            ->map(fn (Activity $activity) => $this->mapActivity($activity));

        $accommodations = Accommodation::with(['media' => function ($query) {
                $query->orderBy('order')->orderBy('id');
            }])
            ->whereNotNull('property_name')
            ->latest('updated_at')
            ->take(12)
            ->get()
            ->map(fn (Accommodation $accommodation) => $this->mapAccommodation($accommodation));

        $holidayRentalTypes = ['Holiday Rental', 'Apartment', 'Villa', 'Cottage'];
        $hotelTypes = ['Hotel', 'Lodge', 'Guesthouse', 'Resort'];

        $holidayRentals = $accommodations
            ->filter(fn (array $item) => in_array($item['property_type'], $holidayRentalTypes, true))
            ->values();

        $hotels = $accommodations
            ->filter(fn (array $item) => in_array($item['property_type'], $hotelTypes, true))
            ->values();

        if ($holidayRentals->isEmpty()) {
            $holidayRentals = $accommodations->take(4)->values();
        }

        if ($hotels->isEmpty()) {
            $holidayIds = $holidayRentals->pluck('id');
            $hotels = $accommodations
                ->reject(fn (array $item) => $holidayIds->contains($item['id']))
                ->take(4)
                ->values();

            if ($hotels->isEmpty()) {
                $hotels = $accommodations->take(4)->values();
            }
        }

        $heroSlides = collect()
            ->merge($activities->take(2))
            ->merge($hotels->take(1))
            ->merge($holidayRentals->take(1))
            ->take(4)
            ->map(function (array $item) {
                return [
                    'title' => $item['title'],
                    'subtitle' => $item['excerpt'],
                    'image' => $item['image'],
                    'url' => $item['url'],
                    'badge' => $item['kind'],
                ];
            })
            ->values();

        if ($heroSlides->isEmpty()) {
            $heroSlides = collect([
                [
                    'title' => 'Welcome to Holidays.io',
                    'subtitle' => 'Discover live accommodation and activity listings entered by your operators.',
                    'image' => asset('images/holidays-io-logo.png'),
                    'url' => '#discover-mauritius',
                    'badge' => 'Home',
                ],
            ]);
        }

        $stats = [
            'activities' => $activities->count(),
            'holidayRentals' => $holidayRentals->count(),
            'hotels' => $hotels->count(),
        ];

        return view('frontend.home', [
            'heroSlides' => $heroSlides,
            'activities' => $activities,
            'holidayRentals' => $holidayRentals,
            'hotels' => $hotels,
            'stats' => $stats,
            'selectedCategory' => $selectedCategory,
            'filters' => $filters,
            'searchOptions' => $searchOptions,
        ]);
    }

    public function categoryList(Request $request)
    {
        $category = $this->normalizeCategory($request->query('category'));
        $filters = $this->collectSearchFilters($request);
        $sidebarSelections = $this->collectSidebarFilters($request, $category);
        $searchOptions = $this->buildSearchOptions();

        $accommodations = Accommodation::with([
                'media' => function ($query) {
                    $query->orderBy('order')->orderBy('id');
                },
                'rates' => function ($query) {
                    $query->where('is_active', true)->orderBy('final_rate')->orderBy('base_rate');
                },
            ])
            ->whereNotNull('property_name')
            ->latest('updated_at')
            ->take(120)
            ->get()
            ->map(fn (Accommodation $accommodation) => $this->mapAccommodation($accommodation))
            ->values();

        $activities = Activity::with([
                'seoSocial',
                'rates' => function ($query) {
                    $query->orderBy('adult_rate')->orderBy('equipment_rate')->orderBy('private_exclusive_rate');
                },
            ])
            ->whereNotNull('activity_name')
            ->latest('updated_at')
            ->take(120)
            ->get()
            ->map(fn (Activity $activity) => $this->mapActivity($activity))
            ->values();

        $items = match ($category) {
            'tours' => $activities,
            'transport' => collect(),
            default => $accommodations,
        };

        $items = $this->applySearchFilters($items, $category, $filters);
        $sidebarDefinitions = $this->buildSidebarDefinitions($items, $category);
        $items = $this->applySidebarFilters($items, $category, $sidebarSelections);

        $categoryTitle = match ($category) {
            'tours' => 'Tours - Activity',
            'transport' => 'Transport',
            default => 'Accommodation',
        };

        $currentPage = max(1, (int) $request->query('page', 1));
        $perPage = 8;
        $paginator = new LengthAwarePaginator(
            $items->forPage($currentPage, $perPage)->values(),
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('frontend.category-list', [
            'category' => $category,
            'categoryTitle' => $categoryTitle,
            'results' => $paginator,
            'filters' => $filters,
            'searchOptions' => $searchOptions,
            'sidebarDefinitions' => $sidebarDefinitions,
            'sidebarSelections' => $sidebarSelections,
        ]);
    }

    public function showActivity(Activity $activity)
    {
        abort_if(blank($activity->activity_name), 404);

        return view('frontend.activity-show', [
            'activity' => $this->mapActivity($activity->load('seoSocial'), true),
        ]);
    }

    public function showAccommodation(Accommodation $accommodation)
    {
        abort_if(blank($accommodation->property_name), 404);

        return view('frontend.accommodation-show', [
            'accommodation' => $this->mapAccommodation($accommodation->load(['media' => function ($query) {
                $query->orderBy('order')->orderBy('id');
            }]), true),
        ]);
    }

    private function mapActivity(Activity $activity, bool $detailed = false): array
    {
        $galleryImages = collect($activity->gallery_images ?? [])
            ->filter()
            ->map(fn ($path) => $this->storageAsset($path))
            ->filter()
            ->values();

        $vehicleImages = collect($activity->vehicle_images ?? [])
            ->map(function ($item) {
                if (is_array($item) && !empty($item['image'])) {
                    return $this->storageAsset($item['image']);
                }

                return null;
            })
            ->filter()
            ->values();

        $primaryImage = $this->storageAsset($activity->hero_banner_image)
            ?? $galleryImages->first()
            ?? $vehicleImages->first()
            ?? asset('images/holidays-io-logo.png');

        $shortDescription = $this->plainText(
            $activity->seoSocial->short_description
                ?? $activity->short_title
                ?? $activity->overview
        );

        $overviewText = $this->plainText($activity->overview);
        $includedText = $this->plainText($activity->whats_included);
        $itineraryText = $this->plainText($activity->itinerary);
        $rates = collect($activity->relationLoaded('rates') ? $activity->rates : []);
        $startingRate = $rates
            ->flatMap(function ($rate) {
                return [
                    $rate->adult_rate,
                    $rate->teen_rate,
                    $rate->children_rate,
                    $rate->infant_rate,
                    $rate->equipment_rate,
                    $rate->private_exclusive_rate,
                ];
            })
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => (float) $value)
            ->filter(fn (float $value) => $value > 0)
            ->min();

        $location = implode(' • ', array_filter([
            $activity->destination,
            $activity->region,
            $activity->town,
        ]));

        return [
            'id' => $activity->id,
            'kind' => 'Activity',
            'title' => $activity->activity_name,
            'service_type' => $activity->service_type,
            'meta' => $activity->service_type ?: 'Experience',
            'location' => $location ?: 'Mauritius',
            'region' => $activity->region,
            'destination' => $activity->destination,
            'town' => $activity->town,
            'physical_level' => $activity->physical_level,
            'price_range' => $activity->price_range,
            'excerpt' => Str::limit($shortDescription ?: $overviewText ?: 'New activity listing added by operator.', 130),
            'image' => $primaryImage,
            'url' => route('frontend.activities.show', $activity),
            'duration' => $activity->duration,
            'booking_confirmation_type' => $activity->booking_confirmation_type,
            'team_categories' => array_values($activity->team_categories ?? []),
            'primary_themes' => array_values($activity->primary_themes ?? []),
            'languages' => array_values($activity->languages_offered ?? []),
            'starting_rate' => $startingRate,
            'overview_text' => $overviewText,
            'included_text' => $includedText,
            'itinerary_text' => $itineraryText,
            'gallery' => $detailed
                ? $galleryImages->merge($vehicleImages)->prepend($primaryImage)->unique()->values()->all()
                : [],
        ];
    }

    private function mapAccommodation(Accommodation $accommodation, bool $detailed = false): array
    {
        $media = collect($accommodation->media ?? []);
        $rates = collect($accommodation->relationLoaded('rates') ? $accommodation->rates : []);

        $heroMedia = $media->firstWhere('media_type', 'hero');
        $primaryImage = $this->storageAsset($heroMedia->path ?? null);

        if (!$primaryImage) {
            $fallbackMedia = $media->first(function ($item) {
                return in_array($item->media_type, ['gallery', 'room', 'logo'], true) && !empty($item->path);
            });

            $primaryImage = $this->storageAsset($fallbackMedia->path ?? null);
        }

        $primaryImage = $primaryImage ?? asset('images/holidays-io-logo.png');

        $gallery = $media
            ->filter(fn ($item) => in_array($item->media_type, ['hero', 'gallery', 'room'], true) && !empty($item->path))
            ->map(fn ($item) => $this->storageAsset($item->path))
            ->filter()
            ->prepend($primaryImage)
            ->unique()
            ->values();

        $shortDescription = $this->plainText($accommodation->short_description ?: $accommodation->property_description);
        $fullDescription = $this->plainText($accommodation->property_description ?: $accommodation->short_description);

        $location = implode(' • ', array_filter([
            $accommodation->region,
            $accommodation->city,
            $accommodation->country,
        ]));

        $mealPlans = $rates
            ->pluck('meal_plan')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $startingRate = $rates
            ->map(function ($rate) {
                $value = $rate->final_rate ?? $rate->base_rate;

                return $value !== null ? (float) $value : null;
            })
            ->filter(fn ($value) => $value !== null && $value > 0)
            ->sort()
            ->values()
            ->first();

        return [
            'id' => $accommodation->id,
            'kind' => 'Accommodation',
            'title' => $accommodation->property_name,
            'property_type' => $accommodation->property_type,
            'meta' => $accommodation->property_type,
            'location' => $location ?: 'Mauritius',
            'region' => $accommodation->region,
            'city' => $accommodation->city,
            'country' => $accommodation->country,
            'excerpt' => Str::limit($shortDescription ?: 'New accommodation listing added by operator.', 130),
            'image' => $primaryImage,
            'url' => route('frontend.accommodations.show', $accommodation),
            'meal_plans' => $mealPlans->all(),
            'starting_rate' => $startingRate,
            'budget_range' => $this->mapAccommodationBudgetRange($startingRate),
            'description_text' => $fullDescription,
            'gallery' => $detailed ? $gallery->all() : [],
        ];
    }

    private function storageAsset(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (!Storage::disk('public')->exists($normalized)) {
            return null;
        }

        return asset('storage/' . $normalized);
    }

    private function plainText(?string $value): string
    {
        if (blank($value)) {
            return '';
        }

        $value = preg_replace('/<\s*br\s*\/?>/i', "\n", $value);
        $value = preg_replace('/<\/p>/i', "\n\n", $value);
        $value = strip_tags($value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace("/\r\n|\r/", "\n", $value);
        $value = preg_replace("/\n{3,}/", "\n\n", $value);

        return trim((string) $value);
    }

    private function normalizeCategory(?string $category): string
    {
        $category = strtolower(trim((string) $category));

        return in_array($category, ['accommodation', 'tours', 'transport'], true)
            ? $category
            : 'accommodation';
    }

    private function collectSearchFilters(Request $request): array
    {
        return [
            'region' => trim((string) $request->query('region', '')),
            'check_in' => (string) $request->query('check_in', now()->format('Y-m-d')),
            'check_out' => (string) $request->query('check_out', now()->addDays(2)->format('Y-m-d')),
            'type' => trim((string) $request->query('type', '')),
            'name' => trim((string) $request->query('name', '')),
        ];
    }

    private function buildSearchOptions(): array
    {
        $accommodationRegions = Accommodation::query()
            ->whereNotNull('property_name')
            ->pluck('region')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $accommodationTypes = Accommodation::query()
            ->whereNotNull('property_name')
            ->pluck('property_type')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $activityRegions = Activity::query()
            ->whereNotNull('activity_name')
            ->pluck('region')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $activityTypes = Activity::query()
            ->whereNotNull('activity_name')
            ->pluck('service_type')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return [
            'accommodation' => [
                'regions' => $accommodationRegions,
                'types' => $accommodationTypes,
            ],
            'tours' => [
                'regions' => $activityRegions,
                'types' => $activityTypes,
            ],
            'transport' => [
                'regions' => [],
                'types' => [],
            ],
        ];
    }

    private function applySearchFilters($items, string $category, array $filters)
    {
        if ($filters['region'] !== '') {
            $region = Str::lower($filters['region']);
            $items = $items->filter(function (array $item) use ($region) {
                return Str::contains(Str::lower((string) ($item['location'] ?? '')), $region);
            });
        }

        if ($filters['type'] !== '') {
            $type = Str::lower($filters['type']);
            $items = $items->filter(function (array $item) use ($type, $category) {
                $value = $category === 'accommodation'
                    ? (string) ($item['property_type'] ?? '')
                    : (string) ($item['meta'] ?? '');

                return Str::lower($value) === $type;
            });
        }

        if ($filters['name'] !== '') {
            $name = Str::lower($filters['name']);
            $items = $items->filter(function (array $item) use ($name) {
                return Str::contains(Str::lower((string) ($item['title'] ?? '')), $name);
            });
        }

        return $items->values();
    }

    private function collectSidebarFilters(Request $request, string $category): array
    {
        return match ($category) {
            'tours' => [
                'service_type' => $this->normalizeFilterValues($request->query('service_type', [])),
                'physical_level' => $this->normalizeFilterValues($request->query('physical_level', [])),
                'price_range' => $this->normalizeFilterValues($request->query('price_range', [])),
                'primary_theme' => $this->normalizeFilterValues($request->query('primary_theme', [])),
                'team_category' => $this->normalizeFilterValues($request->query('team_category', [])),
                'booking_confirmation_type' => $this->normalizeFilterValues($request->query('booking_confirmation_type', [])),
            ],
            'accommodation' => [
                'property_type' => $this->normalizeFilterValues($request->query('property_type', [])),
                'meal_plan' => $this->normalizeFilterValues($request->query('meal_plan', [])),
                'budget' => $this->normalizeFilterValues($request->query('budget', [])),
            ],
            default => [],
        };
    }

    private function applySidebarFilters($items, string $category, array $sidebarSelections)
    {
        if ($category === 'accommodation') {
            if (!empty($sidebarSelections['property_type'])) {
                $selected = $sidebarSelections['property_type'];
                $items = $items->filter(fn (array $item) => in_array((string) ($item['property_type'] ?? ''), $selected, true));
            }

            if (!empty($sidebarSelections['meal_plan'])) {
                $selected = $sidebarSelections['meal_plan'];
                $items = $items->filter(function (array $item) use ($selected) {
                    return count(array_intersect($selected, array_values($item['meal_plans'] ?? []))) > 0;
                });
            }

            if (!empty($sidebarSelections['budget'])) {
                $selected = $sidebarSelections['budget'];
                $items = $items->filter(fn (array $item) => in_array((string) ($item['budget_range'] ?? ''), $selected, true));
            }
        }

        if ($category === 'tours') {
            if (!empty($sidebarSelections['service_type'])) {
                $selected = $sidebarSelections['service_type'];
                $items = $items->filter(fn (array $item) => in_array((string) ($item['service_type'] ?? ''), $selected, true));
            }

            if (!empty($sidebarSelections['physical_level'])) {
                $selected = $sidebarSelections['physical_level'];
                $items = $items->filter(fn (array $item) => in_array((string) ($item['physical_level'] ?? ''), $selected, true));
            }

            if (!empty($sidebarSelections['price_range'])) {
                $selected = $sidebarSelections['price_range'];
                $items = $items->filter(fn (array $item) => in_array((string) ($item['price_range'] ?? ''), $selected, true));
            }

            if (!empty($sidebarSelections['primary_theme'])) {
                $selected = $sidebarSelections['primary_theme'];
                $items = $items->filter(function (array $item) use ($selected) {
                    return count(array_intersect($selected, array_values($item['primary_themes'] ?? []))) > 0;
                });
            }

            if (!empty($sidebarSelections['team_category'])) {
                $selected = $sidebarSelections['team_category'];
                $items = $items->filter(function (array $item) use ($selected) {
                    return count(array_intersect($selected, array_values($item['team_categories'] ?? []))) > 0;
                });
            }

            if (!empty($sidebarSelections['booking_confirmation_type'])) {
                $selected = $sidebarSelections['booking_confirmation_type'];
                $items = $items->filter(fn (array $item) => in_array((string) ($item['booking_confirmation_type'] ?? ''), $selected, true));
            }
        }

        return $items->values();
    }

    private function buildSidebarDefinitions($items, string $category): array
    {
        if ($category === 'accommodation') {
            return array_values(array_filter([
                [
                    'key' => 'property_type',
                    'label' => 'Property Type',
                    'options' => $this->buildCountOptions($items->pluck('property_type'), Accommodation::TYPES),
                ],
                [
                    'key' => 'meal_plan',
                    'label' => 'Meal Plan',
                    'options' => $this->buildCountOptions($items->pluck('meal_plans'), AccommodationRate::MEAL_PLANS),
                ],
                [
                    'key' => 'budget',
                    'label' => 'Budget Range',
                    'options' => $this->buildCountOptions($items->pluck('budget_range'), ['Budget', 'Mid Range', 'Top End']),
                ],
            ], fn (array $definition) => !empty($definition['options'])));
        }

        if ($category === 'tours') {
            return array_values(array_filter([
                [
                    'key' => 'service_type',
                    'label' => 'Service Type',
                    'options' => $this->buildCountOptions($items->pluck('service_type'), Activity::SERVICE_TYPES),
                ],
                [
                    'key' => 'physical_level',
                    'label' => 'Physical Level',
                    'options' => $this->buildCountOptions($items->pluck('physical_level'), Activity::PHYSICAL_LEVELS),
                ],
                [
                    'key' => 'price_range',
                    'label' => 'Price Range',
                    'options' => $this->buildCountOptions($items->pluck('price_range'), Activity::PRICE_RANGES),
                ],
                [
                    'key' => 'primary_theme',
                    'label' => 'Primary Theme',
                    'options' => $this->buildCountOptions($items->pluck('primary_themes'), Activity::PRIMARY_THEMES),
                ],
                [
                    'key' => 'team_category',
                    'label' => 'Team Category',
                    'options' => $this->buildCountOptions($items->pluck('team_categories'), Activity::TEAM_CATEGORIES),
                ],
                [
                    'key' => 'booking_confirmation_type',
                    'label' => 'Confirmation',
                    'options' => $this->buildCountOptions($items->pluck('booking_confirmation_type'), Activity::BOOKING_CONFIRMATION_TYPES),
                ],
            ], fn (array $definition) => !empty($definition['options'])));
        }

        return [];
    }

    private function buildCountOptions($values, array $preferredOrder = []): array
    {
        $counts = collect($values)
            ->flatMap(function ($value) {
                if (is_array($value)) {
                    return $value;
                }

                return [$value];
            })
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->countBy();

        $options = $counts
            ->map(fn ($count, $value) => [
                'value' => $value,
                'label' => $value,
                'count' => $count,
            ])
            ->values();

        if (!empty($preferredOrder)) {
            $orderMap = array_flip($preferredOrder);
            $options = $options->sortBy(function (array $option) use ($orderMap) {
                return $orderMap[$option['value']] ?? (count($orderMap) + 1000 + crc32($option['value']));
            });
        } else {
            $options = $options->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE);
        }

        return $options->values()->all();
    }

    private function normalizeFilterValues($value): array
    {
        return collect(is_array($value) ? $value : [$value])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function mapAccommodationBudgetRange(?float $rate): ?string
    {
        if ($rate === null) {
            return null;
        }

        if ($rate <= 1000) {
            return 'Budget';
        }

        if ($rate <= 3000) {
            return 'Mid Range';
        }

        return 'Top End';
    }
}
