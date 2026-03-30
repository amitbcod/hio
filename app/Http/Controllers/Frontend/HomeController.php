<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationRate;
use App\Models\Activity;
use App\Models\OperatorStatusReview;
use Carbon\Carbon;
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

        $accommodations = $this->approvedAccommodationQuery()->with(['media' => function ($query) {
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

        $accommodations = $this->approvedAccommodationQuery()->with([
                'media' => function ($query) {
                    $query->orderBy('order')->orderBy('id');
                },
                'rates' => function ($query) {
                    $query
                        ->where(function ($rateQuery) {
                            $rateQuery->where('is_active', true)
                                ->orWhereNull('is_active');
                        })
                        ->orderBy('final_rate')
                        ->orderBy('base_rate');
                },
                'rooms' => function ($query) {
                    $query->orderBy('room_name')->orderBy('id');
                },
                'inventory' => function ($query) use ($filters) {
                    if (!empty($filters['check_in']) && !empty($filters['check_out'])) {
                        $query->whereBetween('date', [$filters['check_in'], \Carbon\Carbon::parse($filters['check_out'])->subDay()->format('Y-m-d')]);
                    }
                },
                'bookings' => function ($query) use ($filters) {
                    if (!empty($filters['check_in']) && !empty($filters['check_out'])) {
                        $query->where('booking_status', 'Confirmed')
                            ->where(function ($bookingQuery) use ($filters) {
                                $bookingQuery->where(function ($q) use ($filters) {
                                    $q->where('check_in_date', '<=', $filters['check_out'])
                                      ->where('check_out_date', '>', $filters['check_in']);
                                });
                            });
                    }
                },
            ])
            ->whereNotNull('property_name')
            ->latest('updated_at')
            ->take(120)
            ->get()
            ->map(function (Accommodation $accommodation) use ($filters) {
                $mapped = $this->mapAccommodation($accommodation);
                
                // Calculate available rooms for selected dates
                if (!empty($filters['check_in']) && !empty($filters['check_out'])) {
                    $availableRooms = $this->calculateAvailableRooms($accommodation, $filters['check_in'], $filters['check_out']);
                    $mapped['available_rooms_count'] = $availableRooms;
                } else {
                    $mapped['available_rooms_count'] = null; // No dates selected
                }
                
                return $mapped;
            })
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

    public function showActivity(Request $request, Activity $activity)
    {
        abort_if(blank($activity->activity_name), 404);

        $bookingContext = $this->buildDetailBookingContext($request);
        $stayEnd = Carbon::parse($bookingContext['check_out'])->subDay()->toDateString();

        return view('frontend.activity-show', [
            'activity' => $this->mapActivity($activity->load([
                'seoSocial',
                'policy',
                'variants',
                'allotments' => function ($query) use ($bookingContext, $stayEnd) {
                    $query->whereBetween('inventory_date', [$bookingContext['check_in'], $stayEnd]);
                },
                'rates' => function ($query) use ($bookingContext, $stayEnd) {
                    $query
                        ->whereDate('valid_from', '<=', $stayEnd)
                        ->whereDate('valid_to', '>=', $bookingContext['check_in'])
                        ->orderBy('adult_rate')
                        ->orderBy('equipment_rate')
                        ->orderBy('private_exclusive_rate');
                },
            ]), true, $bookingContext),
        ]);
    }

    public function showAccommodation(Request $request, Accommodation $accommodation)
    {
        abort_if(blank($accommodation->property_name), 404);
        abort_if(!$this->isAccommodationApprovedForFrontend($accommodation), 404);

        $bookingContext = $this->buildDetailBookingContext($request);
        $stayEnd = Carbon::parse($bookingContext['check_out'])->subDay()->toDateString();
        $accommodation = $accommodation->load([
            'media' => function ($query) {
                $query->orderBy('order')->orderBy('id');
            },
            'rooms' => function ($query) {
                $query->orderBy('room_name')->orderBy('id');
            },
            'rates' => function ($query) use ($bookingContext, $stayEnd) {
                $query
                    ->where('is_active', true)
                    ->whereDate('valid_from', '<=', $stayEnd)
                    ->whereDate('valid_to', '>=', $bookingContext['check_in'])
                    ->orderBy('final_rate')
                    ->orderBy('base_rate');
            },
            'inventory' => function ($query) use ($bookingContext, $stayEnd) {
                $query
                    ->whereBetween('date', [$bookingContext['check_in'], $stayEnd])
                    ->orderBy('date');
            },
            'bookings' => function ($query) use ($bookingContext) {
                $query
                    ->whereDate('check_in_date', '<', $bookingContext['check_out'])
                    ->whereDate('check_out_date', '>', $bookingContext['check_in'])
                    ->whereIn('booking_status', ['Pending', 'Confirmed']);
            },
            'operator',
        ]);

        $ratingSummary = $this->resolveAccommodationRating(
            $accommodation->operator?->operator_id
        );

        $similarAccommodations = $this->buildSimilarAccommodations($accommodation);

        return view('frontend.accommodation-show', [
            'accommodation' => $this->mapAccommodation($accommodation, true, $bookingContext),
            'ratingSummary' => $ratingSummary,
            'similarAccommodations' => $similarAccommodations,
        ]);
    }

    private function mapActivity(Activity $activity, bool $detailed = false, ?array $bookingContext = null): array
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
        $bookingContext = $bookingContext ?? $this->defaultDetailBookingContext();
        $rates = collect($activity->relationLoaded('rates') ? $activity->rates : []);
        $policy = $activity->relationLoaded('policy') ? $activity->policy : null;
        $variants = collect($activity->relationLoaded('variants') ? $activity->variants : []);
        $allotments = collect($activity->relationLoaded('allotments') ? $activity->allotments : []);

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

        $mapData = $this->buildMapData(
            $activity->latitude !== null ? (float) $activity->latitude : null,
            $activity->longitude !== null ? (float) $activity->longitude : null,
            implode(', ', array_filter([
                $activity->town,
                $activity->region,
                $activity->destination,
                'Mauritius',
            ]))
        );

        $availableRooms = $detailed
            ? $this->buildActivityAvailability($variants, $rates, $allotments, $bookingContext)
            : [];

        $bookingNotesText = $policy
            ? $this->plainText($policy->booking_window_rules ?: $policy->no_show_policy)
            : '';

        if (blank($bookingNotesText) && !blank($activity->booking_confirmation_type)) {
            $bookingNotesText = 'Booking confirmation: ' . $activity->booking_confirmation_type;
        }

        $checkoutPolicyText = $policy
            ? $this->plainText($policy->cancellation_policy ?: $policy->amendment_policy)
            : '';

        if (blank($checkoutPolicyText) && $policy && !blank($policy->cancellation_policy_template_id)) {
            $checkoutPolicyText = 'Cancellation policy template: ' . $policy->cancellation_policy_template_id;
        }

        $termsConditionsText = $policy
            ? $this->plainText($policy->safety_requirements)
            : '';

        if (blank($termsConditionsText) && $policy && !blank($policy->health_requirements_type) && $policy->health_requirements_type !== 'None') {
            $termsConditionsText = 'Health requirements: ' . $policy->health_requirements_type;
        }

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
            'meeting_point' => $this->plainText($activity->meeting_point_details),
            'booking' => $detailed ? $bookingContext : [],
            'available_rooms' => $availableRooms,
            'map_embed_url' => $mapData['embed_url'],
            'map_link' => $mapData['link'],
            'booking_notes_text' => $bookingNotesText,
            'checkout_policy_text' => $checkoutPolicyText,
            'terms_conditions_text' => $termsConditionsText,
            'gallery' => $detailed
                ? $galleryImages->merge($vehicleImages)->prepend($primaryImage)->unique()->values()->all()
                : [],
        ];
    }

    private function mapAccommodation(Accommodation $accommodation, bool $detailed = false, ?array $bookingContext = null): array
    {
        $media = collect($accommodation->media ?? []);
        $rates = collect($accommodation->relationLoaded('rates') ? $accommodation->rates : []);
        $rooms = collect($accommodation->relationLoaded('rooms') ? $accommodation->rooms : []);
        $inventoryRows = collect($accommodation->relationLoaded('inventory') ? $accommodation->inventory : []);
        $bookings = collect($accommodation->relationLoaded('bookings') ? $accommodation->bookings : []);
        $bookingContext = $bookingContext ?? $this->defaultDetailBookingContext();

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

        $addressLine = implode(', ', array_filter([
            $accommodation->address,
            $accommodation->city,
            $accommodation->region,
            $accommodation->country,
        ]));

        $mapData = $this->buildMapData(
            $accommodation->latitude !== null ? (float) $accommodation->latitude : null,
            $accommodation->longitude !== null ? (float) $accommodation->longitude : null,
            $addressLine ?: $location
        );

        $mealPlans = $rates
            ->pluck('meal_plan')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $roomCatalog = $rooms
            ->map(function ($room) {
                $description = $this->plainText($room->short_description ?: $room->room_description);
                $amenities = $this->parseFlexibleList($room->amenities);

                return [
                    'room_id' => (int) $room->id,
                    'room_name' => (string) ($room->room_name ?: ($room->room_type ?: 'Room')),
                    'room_type' => $room->room_type,
                    'description' => $description,
                    'capacity' => (int) ($room->capacity ?? 0),
                    'children_capacity' => (int) ($room->children_capacity ?? 0),
                    'infant_capacity' => (int) ($room->infant_capacity ?? 0),
                    'quantity' => !is_null($room->allotment)
                        ? (int) $room->allotment
                        : (int) ($room->quantity ?? 0),
                    'max_capacity' => $room->max_capacity !== null ? (int) $room->max_capacity : null,
                    'size_sqm' => $room->size_sqm !== null ? (float) $room->size_sqm : null,
                    'view' => $room->view,
                    'smoking' => $room->smoking,
                    'is_accessible' => (bool) ($room->is_accessible ?? false),
                    'accessibility' => $this->plainText($room->accessibility),
                    'amenities' => $amenities,
                ];
            })
            ->values();

        $amenityList = $roomCatalog
            ->flatMap(fn (array $room) => $room['amenities'])
            ->merge($mealPlans->map(fn ($plan) => $plan . ' meal plan'))
            ->filter()
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique(fn ($item) => Str::lower($item))
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

        if ($startingRate === null) {
            $startingRate = $rooms
                ->pluck('base_price')
                ->filter(fn ($value) => $value !== null)
                ->map(fn ($value) => (float) $value)
                ->filter(fn (float $value) => $value > 0)
                ->sort()
                ->values()
                ->first();
        }

        $availableRooms = $detailed
            ? $this->buildAccommodationAvailability($rooms, $rates, $inventoryRows, $bookings, $bookingContext, $accommodation)
            : [];

        $bookingNotesText = $this->plainText($accommodation->booking_window_rules);

        if (blank($bookingNotesText) && !blank($accommodation->booking_confirmation_type)) {
            $bookingNotesText = 'Booking confirmation: ' . $accommodation->booking_confirmation_type;
        }

        $checkoutPolicyParts = array_values(array_filter([
            $accommodation->checkin_time ? 'Check-in time: ' . substr((string) $accommodation->checkin_time, 0, 5) : null,
            $accommodation->checkout_time ? 'Check-out time: ' . substr((string) $accommodation->checkout_time, 0, 5) : null,
            $this->plainText($accommodation->checkin_checkout_rules),
        ]));
        $checkoutPolicyText = implode("\n\n", $checkoutPolicyParts);

        $termsParts = array_values(array_filter([
            $this->plainText($accommodation->house_rules),
            $this->plainText($accommodation->cancellation_policy),
            $this->plainText($accommodation->amendment_policy),
            $this->plainText($accommodation->security_deposit_policy),
        ]));
        $termsConditionsText = implode("\n\n", $termsParts);

        $policyHighlights = array_values(array_filter([
            !blank($accommodation->booking_confirmation_type)
                ? 'Booking confirmation: ' . $accommodation->booking_confirmation_type
                : null,
            $this->formatDepositPolicySummary($accommodation),
            $this->formatCancellationPenaltySummary($accommodation),
            $accommodation->child_max_age
                ? 'Child policy: up to ' . (int) $accommodation->child_max_age . ' years'
                : null,
            $accommodation->infant_max_age
                ? 'Infant policy: up to ' . (int) $accommodation->infant_max_age . ' years'
                : null,
        ]));

        $contactPhone = $accommodation->reservation_contact_mobile
            ?: $accommodation->reservation_contact_phone
            ?: $accommodation->management_contact_mobile
            ?: $accommodation->management_contact_phone
            ?: $accommodation->onsite_phone;

        $contactEmail = $accommodation->reservation_contact_email
            ?: $accommodation->management_contact_email;

        if (blank($termsConditionsText)) {
            $templateParts = array_values(array_filter([
                $accommodation->house_rules_template_id ? 'House rules template: ' . $accommodation->house_rules_template_id : null,
                $accommodation->cancellation_policy_template_id ? 'Cancellation policy template: ' . $accommodation->cancellation_policy_template_id : null,
                $accommodation->amendment_policy_template_id ? 'Amendment policy template: ' . $accommodation->amendment_policy_template_id : null,
            ]));
            $termsConditionsText = implode("\n", $templateParts);
        }

        return [
            'id' => $accommodation->id,
            'kind' => 'Accommodation',
            'title' => $accommodation->property_name,
            'property_type' => $accommodation->property_type,
            'meta' => $accommodation->property_type,
            'location' => $location ?: 'Mauritius',
            'address' => $addressLine,
            'region' => $accommodation->region,
            'city' => $accommodation->city,
            'country' => $accommodation->country,
            'booking_confirmation_type' => $accommodation->booking_confirmation_type,
            'excerpt' => Str::limit($shortDescription ?: 'New accommodation listing added by operator.', 130),
            'image' => $primaryImage,
            'url' => route('frontend.accommodations.show', $accommodation),
            'meal_plans' => $mealPlans->all(),
            'amenity_list' => $amenityList->all(),
            'room_catalog' => $roomCatalog->all(),
            'starting_rate' => $startingRate,
            'budget_range' => $this->mapAccommodationBudgetRange($startingRate),
            'description_text' => $fullDescription,
            'booking' => $detailed ? $bookingContext : [],
            'available_rooms' => $availableRooms,
            'checkin_time' => $accommodation->checkin_time ? substr((string) $accommodation->checkin_time, 0, 5) : null,
            'checkout_time' => $accommodation->checkout_time ? substr((string) $accommodation->checkout_time, 0, 5) : null,
            'policy_highlights' => $policyHighlights,
            'contact_phone' => $contactPhone,
            'contact_email' => $contactEmail,
            'map_embed_url' => $mapData['embed_url'],
            'map_link' => $mapData['link'],
            'booking_notes_text' => $bookingNotesText,
            'checkout_policy_text' => $checkoutPolicyText,
            'terms_conditions_text' => $termsConditionsText,
            'gallery' => $detailed ? $gallery->all() : [],
        ];
    }

    private function buildSimilarAccommodations(Accommodation $accommodation): array
    {
        $query = $this->approvedAccommodationQuery()->with([
            'media' => function ($q) {
                $q->orderBy('order')->orderBy('id');
            },
            'rates' => function ($q) {
                $q->where('is_active', true)->orderBy('final_rate')->orderBy('base_rate');
            },
        ])
            ->whereNotNull('property_name')
            ->where('id', '!=', $accommodation->id)
            ->latest('updated_at');

        $query->where(function ($q) use ($accommodation) {
            $hasCondition = false;

            if (!blank($accommodation->region)) {
                $q->where('region', $accommodation->region);
                $hasCondition = true;
            }

            if (!blank($accommodation->property_type)) {
                if ($hasCondition) {
                    $q->orWhere('property_type', $accommodation->property_type);
                } else {
                    $q->where('property_type', $accommodation->property_type);
                }

                $hasCondition = true;
            }

            if (!$hasCondition) {
                $q->whereNotNull('id');
            }
        });

        $items = $query
            ->take(6)
            ->get()
            ->map(fn (Accommodation $item) => $this->mapAccommodation($item))
            ->take(3)
            ->values();

        if ($items->isNotEmpty()) {
            return $items->all();
        }

        return $this->approvedAccommodationQuery()->with([
                'media' => function ($q) {
                    $q->orderBy('order')->orderBy('id');
                },
                'rates' => function ($q) {
                    $q->where('is_active', true)->orderBy('final_rate')->orderBy('base_rate');
                },
            ])
            ->whereNotNull('property_name')
            ->where('id', '!=', $accommodation->id)
            ->latest('updated_at')
            ->take(3)
            ->get()
            ->map(fn (Accommodation $item) => $this->mapAccommodation($item))
            ->values()
            ->all();
    }

    private function resolveAccommodationRating(?string $operatorExternalId): array
    {
        if (blank($operatorExternalId)) {
            return [
                'score' => null,
                'score_display' => null,
                'count' => 0,
            ];
        }

        $review = OperatorStatusReview::query()
            ->where('operator_id', $operatorExternalId)
            ->first();

        if (!$review) {
            return [
                'score' => null,
                'score_display' => null,
                'count' => 0,
            ];
        }

        $score = (float) ($review->average_rating ?: $review->operator_rating ?: 0);
        $score = $score > 0 ? $score : null;

        return [
            'score' => $score,
            'score_display' => $score !== null ? number_format($score, 1) : null,
            'count' => (int) ($review->testimonials_count ?? 0),
        ];
    }

    private function parseFlexibleList($value): array
    {
        if (blank($value)) {
            return [];
        }

        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->unique(fn ($item) => Str::lower($item))
                ->values()
                ->all();
        }

        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return [];
        }

        if (Str::startsWith($stringValue, ['[', '{'])) {
            $decoded = json_decode($stringValue, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return collect($decoded)
                    ->flatten()
                    ->map(fn ($item) => trim((string) $item))
                    ->filter()
                    ->unique(fn ($item) => Str::lower($item))
                    ->values()
                    ->all();
            }
        }

        $normalized = preg_replace('/[\r\n|;]+/', ',', strip_tags($stringValue));

        return collect(explode(',', (string) $normalized))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique(fn ($item) => Str::lower($item))
            ->values()
            ->all();
    }

    private function formatDepositPolicySummary(Accommodation $accommodation): ?string
    {
        if (!$accommodation->deposit_required) {
            return null;
        }

        $value = $accommodation->deposit_value !== null
            ? (float) $accommodation->deposit_value
            : null;

        if ($value === null || $value <= 0) {
            return 'Deposit required';
        }

        return match ((string) $accommodation->deposit_type) {
            'Percentage' => 'Deposit required: ' . rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . '%',
            'Night' => 'Deposit required: ' . rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . ' night(s)',
            default => 'Deposit required: ' . rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.'),
        };
    }

    private function formatCancellationPenaltySummary(Accommodation $accommodation): ?string
    {
        if (!$accommodation->cancellation_penalties_enabled) {
            return null;
        }

        $value = $accommodation->cancellation_penalty_value !== null
            ? (float) $accommodation->cancellation_penalty_value
            : null;

        if ($value === null || $value <= 0) {
            return 'Cancellation penalties apply';
        }

        return match ((string) $accommodation->cancellation_penalty_type) {
            'Percentage' => 'Cancellation penalty: ' . rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . '%',
            'Night' => 'Cancellation penalty: ' . rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . ' night(s)',
            default => 'Cancellation penalty: ' . rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.'),
        };
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

    private function defaultDetailBookingContext(): array
    {
        $checkIn = now()->startOfDay();
        $checkOut = $checkIn->copy()->addDays(2);

        return [
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'check_in_display' => $checkIn->format('d-m-Y'),
            'check_out_display' => $checkOut->format('d-m-Y'),
            'adults' => 2,
            'children' => 0,
            'total_guests' => 2,
            'nights' => 2,
        ];
    }

    private function buildDetailBookingContext(Request $request): array
    {
        $defaults = $this->defaultDetailBookingContext();

        $checkIn = $this->parseDateInput(
            (string) $request->query('check_in', $defaults['check_in']),
            Carbon::parse($defaults['check_in'])
        );

        $checkOut = $this->parseDateInput(
            (string) $request->query('check_out', $defaults['check_out']),
            Carbon::parse($defaults['check_out'])
        );

        if ($checkOut->lte($checkIn)) {
            $checkOut = $checkIn->copy()->addDays(2);
        }

        $adults = max(1, (int) $request->query('adults', 2));
        $children = max(0, (int) $request->query('children', 0));
        $nights = max(1, $checkIn->diffInDays($checkOut));

        return [
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'check_in_display' => $checkIn->format('d-m-Y'),
            'check_out_display' => $checkOut->format('d-m-Y'),
            'adults' => $adults,
            'children' => $children,
            'total_guests' => $adults + $children,
            'nights' => $nights,
        ];
    }

    private function parseDateInput(string $value, Carbon $fallback): Carbon
    {
        $normalized = trim($value);

        if ($normalized === '') {
            return $fallback->copy()->startOfDay();
        }

        $timestamp = strtotime($normalized);
        if ($timestamp === false) {
            return $fallback->copy()->startOfDay();
        }

        return Carbon::createFromTimestamp($timestamp)->startOfDay();
    }

    private function buildMapData(?float $latitude, ?float $longitude, string $fallbackQuery = ''): array
    {
        $query = '';

        if ($latitude !== null && $longitude !== null) {
            $query = $latitude . ',' . $longitude;
        } elseif (trim($fallbackQuery) !== '') {
            $query = trim($fallbackQuery);
        }

        if ($query === '') {
            return [
                'embed_url' => null,
                'link' => null,
            ];
        }

        $encoded = urlencode($query);

        return [
            'embed_url' => 'https://www.google.com/maps?q=' . $encoded . '&z=14&output=embed',
            'link' => 'https://www.google.com/maps?q=' . $encoded,
        ];
    }

    private function buildStayDateKeys(Carbon $checkIn, Carbon $checkOut): array
    {
        $dates = [];
        $cursor = $checkIn->copy();

        while ($cursor->lt($checkOut)) {
            $dates[] = $cursor->toDateString();
            $cursor->addDay();
        }

        if (empty($dates)) {
            $dates[] = $checkIn->toDateString();
        }

        return $dates;
    }

    private function rateOverlapsStay($rate, Carbon $checkIn, Carbon $checkOut): bool
    {
        $stayEnd = $checkOut->copy()->subDay();

        $validFrom = $rate->valid_from ? Carbon::parse($rate->valid_from)->startOfDay() : null;
        $validTo = $rate->valid_to ? Carbon::parse($rate->valid_to)->startOfDay() : null;

        if ($validFrom && $validFrom->gt($stayEnd)) {
            return false;
        }

        if ($validTo && $validTo->lt($checkIn)) {
            return false;
        }

        return true;
    }

    private function buildAccommodationAvailability($rooms, $rates, $inventoryRows, $bookings, array $bookingContext, Accommodation $accommodation): array
    {
        $checkIn = Carbon::parse($bookingContext['check_in'])->startOfDay();
        $checkOut = Carbon::parse($bookingContext['check_out'])->startOfDay();
        $days = $this->buildStayDateKeys($checkIn, $checkOut);
        $activeRooms = $rooms->filter(function ($room) {
            $status = trim((string) ($room->status ?? ''));

            return $status === '' || strcasecmp($status, 'Active') === 0;
        });
        $allowGlobalFallback = $activeRooms->count() <= 1;
        $globalRates = $rates->filter(function ($rate) {
            return empty($rate->room_id);
        });

        $inventoryByRoomDate = [];
        foreach ($inventoryRows as $row) {
            $roomKey = (int) ($row->room_id ?? 0);
            $dayKey = Carbon::parse($row->date)->toDateString();

            $availableUnitsRaw = $row->available_units;
            $availableUnits = $availableUnitsRaw !== null
                ? (int) $availableUnitsRaw
                : ((int) ($row->sellable_units ?? 0) - (int) ($row->sold_units ?? 0));

            $inventoryByRoomDate[$roomKey][$dayKey] = [
                'sellable_units' => max((int) ($row->sellable_units ?? 0), 0),
                'sold_units' => max((int) ($row->sold_units ?? 0), 0),
                'available_units' => max($availableUnits, 0),
                'is_blocked' => (bool) ($row->stop_sell ?? false) || (bool) ($row->is_blocked ?? false),
            ];
        }

        $bookingsByRoomDate = [];
        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->check_in_date)->startOfDay();
            $end = Carbon::parse($booking->check_out_date)->startOfDay()->subDay();

            if ($end->lt($start)) {
                continue;
            }

            $roomKey = (int) ($booking->room_id ?? 0);
            $units = max(1, (int) ($booking->rooms_booked ?? 1));
            $cursor = $start->copy();

            while ($cursor->lte($end)) {
                if ($cursor->gte($checkIn) && $cursor->lt($checkOut)) {
                    $dayKey = $cursor->toDateString();
                    if (!isset($bookingsByRoomDate[$roomKey][$dayKey])) {
                        $bookingsByRoomDate[$roomKey][$dayKey] = 0;
                    }
                    $bookingsByRoomDate[$roomKey][$dayKey] += $units;
                }

                $cursor->addDay();
            }
        }

        $results = [];

        foreach ($rooms as $room) {
            $roomStatus = trim((string) ($room->status ?? ''));
            if ($roomStatus !== '' && strcasecmp($roomStatus, 'Active') !== 0) {
                continue;
            }

            $roomRates = $rates->filter(function ($rate) use ($room) {
                return (int) ($rate->room_id ?? 0) === (int) $room->id;
            });

            $candidateRates = $roomRates->isNotEmpty() ? $roomRates : $globalRates;

            $selectedRateData = $candidateRates
                ->filter(fn ($rate) => $this->rateOverlapsStay($rate, $checkIn, $checkOut))
                ->map(function ($rate) use ($bookingContext) {
                    $nightly = $this->calculateAccommodationNightlyTotal(
                        $rate,
                        (int) $bookingContext['adults'],
                        (int) $bookingContext['children']
                    );

                    return [
                        'rate' => $rate,
                        'nightly' => $nightly,
                    ];
                })
                ->filter(fn (array $item) => $item['nightly'] !== null)
                ->sortBy('nightly')
                ->first();

            if (!$selectedRateData && $roomRates->isNotEmpty() && $globalRates->isNotEmpty()) {
                $selectedRateData = $globalRates
                    ->filter(fn ($rate) => $this->rateOverlapsStay($rate, $checkIn, $checkOut))
                    ->map(function ($rate) use ($bookingContext) {
                        $nightly = $this->calculateAccommodationNightlyTotal(
                            $rate,
                            (int) $bookingContext['adults'],
                            (int) $bookingContext['children']
                        );

                        return [
                            'rate' => $rate,
                            'nightly' => $nightly,
                        ];
                    })
                    ->filter(fn (array $item) => $item['nightly'] !== null)
                    ->sortBy('nightly')
                    ->first();
            }

            $selectedRate = $selectedRateData['rate'] ?? null;
            $nightlyPrice = $selectedRateData['nightly'] ?? null;

            if ($nightlyPrice === null) {
                $roomBasePrice = $room->base_price !== null ? (float) $room->base_price : null;
                if ($roomBasePrice !== null && $roomBasePrice > 0) {
                    $nightlyPrice = round($roomBasePrice, 2);
                }
            }

            $baseUnits = !is_null($room->allotment)
                ? (int) $room->allotment
                : (int) ($room->quantity ?? 0);
            $baseUnits = $baseUnits > 0 ? $baseUnits : 1;

            $minimumAvailable = null;

            foreach ($days as $dayKey) {
                $inventory = $inventoryByRoomDate[(int) $room->id][$dayKey]
                    ?? ($allowGlobalFallback ? ($inventoryByRoomDate[0][$dayKey] ?? null) : null);

                if ($inventory) {
                    $hasExplicitInventory = $inventory['is_blocked']
                        || $inventory['sellable_units'] > 0
                        || $inventory['sold_units'] > 0
                        || $inventory['available_units'] > 0;

                    if (!$hasExplicitInventory) {
                        $inventory = null;
                    }
                }

                $bookedUnits = (int) ($bookingsByRoomDate[(int) $room->id][$dayKey] ?? 0);

                if ($allowGlobalFallback) {
                    $bookedUnits += (int) ($bookingsByRoomDate[0][$dayKey] ?? 0);
                }

                if ($inventory) {
                    $sellableUnits = $inventory['sellable_units'] > 0
                        ? $inventory['sellable_units']
                        : $baseUnits;

                    $usedUnits = max($inventory['sold_units'], $bookedUnits);

                    $availableDay = $inventory['is_blocked']
                        ? 0
                        : max(min($inventory['available_units'], $sellableUnits - $usedUnits), 0);
                } else {
                    $availableDay = max($baseUnits - $bookedUnits, 0);
                }

                $minimumAvailable = $minimumAvailable === null
                    ? $availableDay
                    : min($minimumAvailable, $availableDay);
            }

            $quantity = max((int) ($minimumAvailable ?? $baseUnits), 0);

            if ($quantity <= 0) {
                continue;
            }

            $totalPrice = $nightlyPrice !== null
                ? round($nightlyPrice * (int) $bookingContext['nights'], 2)
                : null;

            $results[] = [
                'room_id' => (int) $room->id,
                'room_name' => (string) ($room->room_name ?: ($room->room_type ?: 'Room')),
                'room_type' => $room->room_type,
                'quantity' => $quantity,
                'nightly_price' => $nightlyPrice,
                'total_price' => $totalPrice,
                'currency' => $selectedRate->currency ?? $accommodation->currency_code ?? 'MUR',
            ];
        }

        usort($results, function (array $left, array $right) {
            $leftValue = $left['total_price'] ?? PHP_INT_MAX;
            $rightValue = $right['total_price'] ?? PHP_INT_MAX;

            return $leftValue <=> $rightValue;
        });

        return $results;
    }

    private function calculateAccommodationNightlyTotal($rate, int $adults, int $children): ?float
    {
        if (!$rate) {
            return null;
        }

        $baseRate = $rate->final_rate ?? $rate->base_rate;
        $baseRate = $baseRate !== null ? (float) $baseRate : null;

        if ($baseRate === null || $baseRate <= 0) {
            return null;
        }

        $adults = max(1, $adults);
        $children = max(0, $children);
        $pricingSetting = (string) ($rate->pricing_setting ?? 'Per Room/Night');

        if ($pricingSetting === 'Per Person/Night') {
            $total = $baseRate * $adults;
            $childRate = $rate->children_rate !== null ? (float) $rate->children_rate : $baseRate;
            if ($children > 0) {
                $total += $childRate * $children;
            }

            return round($total, 2);
        }

        $includedAdults = 2;
        $extraAdults = max($adults - $includedAdults, 0);
        $extraAdultRate = (float) ($rate->extra_adult_rate ?? 0);
        $childrenRate = (float) ($rate->children_rate ?? 0);

        $total = $baseRate;
        if ($extraAdults > 0 && $extraAdultRate > 0) {
            $total += $extraAdults * $extraAdultRate;
        }

        if ($children > 0 && $childrenRate > 0) {
            $total += $children * $childrenRate;
        }

        return round($total, 2);
    }

    private function buildActivityAvailability($variants, $rates, $allotments, array $bookingContext): array
    {
        $checkIn = Carbon::parse($bookingContext['check_in'])->startOfDay();
        $checkOut = Carbon::parse($bookingContext['check_out'])->startOfDay();
        $days = $this->buildStayDateKeys($checkIn, $checkOut);

        $variantItems = $variants;
        if ($variantItems->isEmpty()) {
            $variantItems = $rates
                ->map(function ($rate) {
                    return (object) [
                        'variant_id' => $rate->variant_id,
                        'variant_name' => $rate->variant_name ?: 'Standard Option',
                        'allotment' => null,
                        'max_participants' => null,
                        'max_pax' => null,
                        'quality_tier' => null,
                    ];
                })
                ->filter(fn ($variant) => !blank($variant->variant_id))
                ->unique('variant_id')
                ->values();
        }

        $allotmentByVariantDate = [];
        foreach ($allotments as $allotment) {
            if (blank($allotment->variant_id) || blank($allotment->inventory_date)) {
                continue;
            }

            $variantKey = (int) $allotment->variant_id;
            $dayKey = Carbon::parse($allotment->inventory_date)->toDateString();
            $value = max((int) ($allotment->allotment ?? 0), 0);

            if (!isset($allotmentByVariantDate[$variantKey][$dayKey])) {
                $allotmentByVariantDate[$variantKey][$dayKey] = 0;
            }

            $allotmentByVariantDate[$variantKey][$dayKey] += $value;
        }

        $results = [];

        foreach ($variantItems as $variant) {
            $variantId = (int) ($variant->variant_id ?? 0);

            $baseUnits = !is_null($variant->allotment)
                ? (int) $variant->allotment
                : max((int) ($variant->max_participants ?? 0), (int) ($variant->max_pax ?? 0));
            $baseUnits = $baseUnits > 0 ? $baseUnits : 1;

            $minimumAvailable = null;
            foreach ($days as $dayKey) {
                $availableDay = $allotmentByVariantDate[$variantId][$dayKey] ?? $baseUnits;

                $minimumAvailable = $minimumAvailable === null
                    ? $availableDay
                    : min($minimumAvailable, $availableDay);
            }

            $quantity = max((int) ($minimumAvailable ?? $baseUnits), 0);

            $selectedRate = $rates
                ->filter(function ($rate) use ($variantId) {
                    return (int) ($rate->variant_id ?? 0) === $variantId;
                })
                ->filter(fn ($rate) => $this->rateOverlapsStay($rate, $checkIn, $checkOut))
                ->sortBy(function ($rate) use ($bookingContext) {
                    $value = $this->calculateActivityRateTotal(
                        $rate,
                        (int) $bookingContext['adults'],
                        (int) $bookingContext['children'],
                        (int) $bookingContext['nights']
                    );

                    return $value !== null ? $value : PHP_INT_MAX;
                })
                ->first();

            $totalPrice = $this->calculateActivityRateTotal(
                $selectedRate,
                (int) $bookingContext['adults'],
                (int) $bookingContext['children'],
                (int) $bookingContext['nights']
            );

            $results[] = [
                'room_id' => $variantId,
                'room_name' => (string) ($variant->variant_name ?: 'Standard Option'),
                'room_type' => $variant->quality_tier,
                'quantity' => $quantity,
                'nightly_price' => null,
                'total_price' => $totalPrice,
                'currency' => 'MUR',
            ];
        }

        usort($results, function (array $left, array $right) {
            $leftValue = $left['total_price'] ?? PHP_INT_MAX;
            $rightValue = $right['total_price'] ?? PHP_INT_MAX;

            return $leftValue <=> $rightValue;
        });

        return $results;
    }

    private function calculateActivityRateTotal($rate, int $adults, int $children, int $nights): ?float
    {
        if (!$rate) {
            return null;
        }

        $adults = max(1, $adults);
        $children = max(0, $children);
        $nights = max(1, $nights);
        $participants = max(1, $adults + $children);

        $rateSpecificity = (string) ($rate->rate_specificity ?? 'Per Person');

        if ($rateSpecificity === 'Per Equipment') {
            $equipmentRate = $rate->equipment_rate !== null ? (float) $rate->equipment_rate : null;
            $base = $equipmentRate !== null && $equipmentRate > 0
                ? $equipmentRate * $participants
                : null;
        } else {
            $adultRate = $rate->adult_rate !== null ? (float) $rate->adult_rate : null;
            $childRate = $rate->children_rate !== null
                ? (float) $rate->children_rate
                : (float) ($rate->teen_rate ?? 0);

            $base = $adultRate !== null && $adultRate > 0
                ? ($adultRate * $adults) + ($childRate * $children)
                : null;
        }

        $privateRate = $rate->private_exclusive_rate !== null ? (float) $rate->private_exclusive_rate : null;
        if (($base === null || $base <= 0) && $privateRate !== null && $privateRate > 0) {
            $base = $privateRate;
        }

        if ($base === null || $base <= 0) {
            return null;
        }

        return round($base * $nights, 2);
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
            'adults' => max(1, (int) $request->query('adults', 2)),
            'children' => max(0, (int) $request->query('children', 0)),
            'rooms' => max(1, (int) $request->query('rooms', 1)),
        ];
    }

    private function buildSearchOptions(): array
    {
        $accommodationRegions = $this->approvedAccommodationQuery()
            ->whereNotNull('property_name')
            ->pluck('region')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $accommodationTypes = $this->approvedAccommodationQuery()
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

    private function approvedAccommodationQuery()
    {
        return Accommodation::query()
            ->where('approval_status', 'Approved')
            ->where('status', Accommodation::STATUS_ACTIVE);
    }

    private function isAccommodationApprovedForFrontend(Accommodation $accommodation): bool
    {
        return (string) $accommodation->approval_status === 'Approved'
            && (string) $accommodation->status === Accommodation::STATUS_ACTIVE;
    }

    private function calculateAvailableRooms(Accommodation $accommodation, string $checkIn, string $checkOut): ?int
    {
        $rooms = collect($accommodation->rooms ?? []);
        if ($rooms->isEmpty()) {
            return null;
        }

        $checkInDate = \Carbon\Carbon::parse($checkIn);
        $checkOutDate = \Carbon\Carbon::parse($checkOut);
        $stayDates = [];

        // Generate all dates in the stay period
        for ($date = $checkInDate->copy(); $date->lt($checkOutDate); $date->addDay()) {
            $stayDates[] = $date->format('Y-m-d');
        }

        $totalAvailable = 0;

        foreach ($rooms as $room) {
            $roomQuantity = $room->allotment ?? $room->quantity ?? 0;
            if ($roomQuantity <= 0) {
                continue;
            }

            // Check inventory for each date
            $availableForRoom = $roomQuantity;

            foreach ($stayDates as $date) {
                $inventory = $accommodation->inventory->firstWhere('date', $date);
                if ($inventory) {
                    $availableForRoom = min($availableForRoom, $inventory->available_rooms ?? $roomQuantity);
                }

                // Check for conflicting bookings
                $conflictingBookings = $accommodation->bookings->filter(function ($booking) use ($date, $room) {
                    return $booking->room_id == $room->id &&
                           $booking->booking_status === 'Confirmed' &&
                           $booking->check_in_date <= $date &&
                           $booking->check_out_date > $date;
                });

                $bookedRooms = $conflictingBookings->sum('rooms_booked');
                $availableForRoom = max(0, $availableForRoom - $bookedRooms);
            }

            $totalAvailable += $availableForRoom;
        }

        return $totalAvailable > 0 ? $totalAvailable : 0;
    }
}
