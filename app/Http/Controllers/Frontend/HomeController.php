<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationRate;
use App\Models\AccommodationRoom;
use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\BookingWidget;
use App\Models\Package;
use App\Models\Place;
use App\Models\Region;
use App\Models\Transport;
use App\Models\TransportRoute;
use App\Models\OperatorStatusReview;
use App\Models\PolicyTemplate;
use App\Models\Review;
use App\Models\ReviewItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    private array $operatorRatingCache = [];

    public function index(Request $request)
    {
   
        $selectedCategory = $this->normalizeCategory($request->query('category'));
        $filters = $this->collectSearchFilters($request);
        $searchOptions = $this->buildSearchOptions();

        $activities = $this->approvedActivityQuery($filters['activity_date'] ?? null)->with(['seoSocial', 'operator'])
            ->whereNotNull('activity_name')
            ->latest('updated_at')
            ->take(8)
            ->get()
            ->map(fn (Activity $activity) => $this->mapActivity($activity));

        $accommodations = $this->approvedAccommodationQuery()->with(['media' => function ($query) {
                $query->orderBy('order')->orderBy('id');
            }, 'operator'])
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

        $transports = $this->approvedTransportQuery()->with([
                'rates' => function ($query) {
                    $query->where('is_active', true)->orderBy('price_per_person');
                },
                'operator',
                'routes',
            ])
            ->whereNotNull('vehicle_name')
            ->latest('updated_at')
            ->take(8)
            ->get()
            ->map(fn (Transport $transport) => $this->mapTransport(
                $transport,
                false,
                $filters['transport_from'] ?? '',
                $filters['transport_to'] ?? ''
            ));

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
            'transports' => $transports,
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

        $operatorToken = (string) $request->query('operator_token', '');
        $operatorId = null;

        if ($operatorToken !== '') {
            $widget = BookingWidget::where('widget_token', $operatorToken)
                ->where('is_active', true)
                ->first();

            if ($widget) {
                $operatorId = (string) $widget->operator_id;
            }
        }

        $accommodations = $this->approvedAccommodationQuery()
            ->when($operatorId, fn ($query) => $query->where('operator_id', $operatorId))
            ->with([
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
                        $query->whereIn('booking_status', ['Pending', 'Confirmed'])
                            ->where(function ($bookingQuery) use ($filters) {
                                $bookingQuery->where(function ($q) use ($filters) {
                                    $q->where('check_in_date', '<=', $filters['check_out'])
                                      ->where('check_out_date', '>', $filters['check_in']);
                                });
                            });
                    }
                },
                'operator',
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

        $activities = $this->approvedActivityQuery($filters['activity_date'] ?? null)
            ->when($operatorId, fn ($query) => $query->where('operator_id', $operatorId))
            ->with([
                'seoSocial',
                'rates' => function ($query) {
                    $query->orderBy('adult_rate')->orderBy('equipment_rate')->orderBy('private_exclusive_rate');
                },
                'operator',
            ])
            ->whereNotNull('activity_name')
            ->latest('updated_at')
            ->take(120)
            ->get()
            ->map(fn (Activity $activity) => $this->mapActivity($activity))
            ->values();

        $selectedPickupRegion = '';
        $selectedDropoffRegion = '';

        if ($filters['pickup_region_id'] !== '') {
            $selectedPickupRegion = $this->getRegionNameFromId($filters['pickup_region_id']) ?? '';
        }

        if ($filters['dropoff_region_id'] !== '') {
            $selectedDropoffRegion = $this->getRegionNameFromId($filters['dropoff_region_id']) ?? '';
        }

        if ($selectedPickupRegion === '' && $filters['transport_from'] !== '') {
            $selectedPickupRegion = $this->getPlaceRegion($filters['transport_from']) ?? '';
        }

        if ($selectedDropoffRegion === '' && $filters['transport_to'] !== '') {
            $selectedDropoffRegion = $this->getPlaceRegion($filters['transport_to']) ?? '';
        }

        $transports = $this->approvedTransportQuery()->with([
                'rates' => function ($query) {
                    $query->where('is_active', true)->orderBy('price_per_person');
                },
                'operator',
                'routes',
            ])
            ->whereNotNull('vehicle_name')
            ->latest('updated_at')
            ->take(120)
            ->get()
            ->map(fn (Transport $transport) => $this->mapTransport($transport, false, $selectedPickupRegion, $selectedDropoffRegion))
            ->values();

        $items = match ($category) {
            'tours' => $activities,
            'transport' => $transports,
            default => $accommodations,
        };

        $items = $this->applySearchFilters($items, $category, $filters);
        $sidebarDefinitions = $this->buildSidebarDefinitions($items, $category);
        $items = $this->applySidebarFilters($items, $category, $sidebarSelections);

        $categoryTitle = match ($category) {
            'tours' => __('category.title.tours_activity'),
            'transport' => __('category.title.transport'),
            default => __('category.title.accommodation'),
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

    public function packageList(Request $request)
    {
        $region = trim((string) $request->query('region', 'all'));
        $travelingDateRaw = $request->query('traveling_date');
        $travelingDate = null;

        if (!empty($travelingDateRaw)) {
            foreach (['d/m/Y', 'Y-m-d', 'm/d/Y', 'd-m-Y', 'Y/m/d'] as $format) {
                try {
                    $travelingDate = Carbon::createFromFormat($format, $travelingDateRaw)->format('d/m/Y');
                    break;
                } catch (\Exception $e) {
                    $travelingDate = $travelingDateRaw;
                }
            }
        }

        $adults = max(1, (int) $request->query('adults', 2));
        $children = max(0, (int) $request->query('children', 0));
        $infants = max(0, (int) $request->query('infants', 0));
        $roomsRequired = max(1, (int) $request->query('rooms', 1));

        $packages = Package::query()
            ->where('status', 'published')
            ->latest('updated_at')
            ->get()
            ->filter(function (Package $package) use ($region, $travelingDate, $adults, $children, $infants, $roomsRequired) {
                return $this->packageMatchesGuestCriteria($package, $region, $travelingDate, $adults, $children, $infants, $roomsRequired);
            })
            ->values();

        $regionOptions = \App\Models\Region::query()
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return view('frontend.packages-list', [
            'packages' => $packages,
            'regionOptions' => $regionOptions,
            'region' => $region,
            'travelingDate' => $travelingDate,
            'adults' => $adults,
            'children' => $children,
            'infants' => $infants,
            'roomsRequired' => $roomsRequired,
        ]);
    }

    public function showPackage(Package $package)
    {
        abort_if(blank($package->name), 404);
        abort_if((string) ($package->status ?? '') !== 'published', 404);

        $itineraryData = $package->itinerary ?? [];
        $content = $itineraryData['content'] ?? [];
        $dayDescriptions = $itineraryData['day_descriptions'] ?? [];

        $gallery = collect();
        $itineraryDays = [];

        foreach ($itineraryData as $dayIndex => $dayEntry) {
            if (!is_array($dayEntry) || is_string($dayIndex)) {
                continue;
            }

            $dayImages = collect();
            $dayLabel = 'Day ' . ((int) $dayIndex + 1);

            foreach (['accommodation', 'activity', 'transport'] as $itemType) {
                $recordId = $dayEntry[$itemType] ?? null;
                if (blank($recordId)) {
                    continue;
                }

                $model = match ($itemType) {
                    'accommodation' => Accommodation::find((int) $recordId),
                    'activity' => Activity::find((int) $recordId),
                    'transport' => Transport::find((int) $recordId),
                    default => null,
                };

                if (!$model) {
                    continue;
                }

                $modelImages = collect();

                if ($itemType === 'accommodation') {
                    $modelImages = $model->media()->pluck('path');
                } elseif ($itemType === 'activity') {
                    $modelImages = collect(array_merge(
                        (array) ($model->gallery_images ?? []),
                        (array) ($model->vehicle_images ?? []),
                        [$model->hero_banner_image ?? null]
                    ));
                } else {
                    $modelImages = collect(array_merge(
                        (array) ($model->gallery_images ?? []),
                        [$model->hero_banner_image ?? null]
                    ));
                }

                $assetPaths = $modelImages
                    ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                    ->map(function ($value) use ($itemType, $model) {
                        if (is_array($value)) {
                            $value = $value['image'] ?? $value['path'] ?? null;
                        }

                        if (is_string($value) && str_starts_with($value, 'http')) {
                            return $value;
                        }

                        if ($itemType === 'accommodation' && is_string($value)) {
                            return $this->storageAsset($value) ?? $this->storageAsset('storage/' . $value);
                        }

                        return $this->storageAsset($value);
                    })
                    ->filter()
                    ->values();

                $dayImages = $dayImages->merge($assetPaths);
                $gallery = $gallery->merge($assetPaths);
            }

            $itineraryDays[] = [
                'day' => (int) $dayIndex + 1,
                'label' => $dayLabel,
                'description' => $dayDescriptions[$dayIndex] ?? '',
                'images' => $dayImages->unique()->values()->all(),
            ];
        }

        if ($gallery->isEmpty()) {
            $gallery = collect($content['gallery'] ?? [])
                ->map(fn ($path) => $this->storageAsset($path) ?? asset('storage/' . ltrim((string) $path, '/')))
                ->filter()
                ->values();
        }

        $summary = $this->buildPackageSummary($package, $itineraryDays, $content);
        $packagePrice = $this->calculatePackageGuestPrice($package, 2, 0, 0);

        $packageData = [
            'id' => $package->id,
            'name' => $package->name,
            'no_of_days' => (int) ($package->no_of_days ?? max(1, count($itineraryDays))),
            'no_of_nights' => (int) ($package->no_of_nights ?? max(0, (int) ($package->no_of_days ?? max(1, count($itineraryDays))) - 1)),
            'short_description' => $content['short_description'] ?? '',
            'full_description' => $content['full_description'] ?? '',
            'inclusions' => $content['inclusions'] ?? '',
            'exclusions' => $content['exclusions'] ?? '',
            'gallery' => $gallery->unique()->values()->all(),
            'image' => $gallery->first() ?? asset('images/holidays-io-logo.png'),
            'itinerary_days' => $itineraryDays,
            'price' => round($packagePrice, 2),
            'location' => $summary['location'],
            'days_label' => $summary['days_label'],
            'hotel_count' => $summary['hotel_count'],
            'activity_count' => $summary['activity_count'],
            'meal_count' => $summary['meal_count'],
        ];

        return view('frontend.package-show', [
            'package' => $packageData,
        ]);
    }

    private function buildPackageSummary(Package $package, array $itineraryDays, array $content = []): array
    {
        $itinerary = $package->itinerary ?? [];
        $days = max(1, (int) ($package->no_of_days ?? count($itineraryDays)));

        $hotelIds = [];
        $activityIds = [];
        $mealMatches = [];

        foreach ($itinerary as $dayIndex => $dayEntry) {
            if (!is_array($dayEntry) || is_string($dayIndex)) {
                continue;
            }

            $accommodationId = $dayEntry['accommodation'] ?? null;
            if (!blank($accommodationId)) {
                $hotelIds[] = (int) $accommodationId;
            }

            $activityId = $dayEntry['activity'] ?? null;
            if (!blank($activityId)) {
                $activityIds[] = (int) $activityId;
            }

            $mealText = trim((string) ($dayEntry['meal_plan'] ?? ''));
            if ($mealText !== '') {
                $mealMatches[] = $mealText;
            }
        }

        $mealTextSource = trim((string) (($content['inclusions'] ?? '') ?: ($content['full_description'] ?? '')));
        if ($mealTextSource !== '') {
            preg_match_all('/\b(breakfast|brunch|lunch|dinner|snacks|meal|meals)\b/i', $mealTextSource, $mealMatchesFromText);
            $mealMatches = array_merge($mealMatches, $mealMatchesFromText[0]);
        }

        $location = 'Mauritius';
        foreach ($itinerary as $dayEntry) {
            if (!is_array($dayEntry)) {
                continue;
            }

            $accommodationId = $dayEntry['accommodation'] ?? null;
            if (blank($accommodationId)) {
                continue;
            }

            $accommodation = Accommodation::find((int) $accommodationId);
            if (!$accommodation) {
                continue;
            }

            $region = trim((string) ($accommodation->region ?? ''));
            if ($region !== '') {
                $location = $region;
                break;
            }
        }

        $hotelCount = count(array_values(array_unique(array_filter($hotelIds, fn ($id) => (int) $id > 0))));
        $activityCount = count(array_values(array_unique(array_filter($activityIds, fn ($id) => (int) $id > 0))));
        $mealCount = count(array_values(array_unique(array_map('strtolower', array_filter($mealMatches, fn ($value) => is_string($value) && trim($value) !== '')))));

        if ($mealCount < 1) {
            $mealCount = max(1, $days * 2);
        }

        return [
            'location' => $location,
            'days_label' => $days . ' Day Plan',
            'hotel_count' => max(1, $hotelCount ?: 1),
            'activity_count' => max(1, $activityCount ?: 1),
            'meal_count' => $mealCount,
        ];
    }

    private function calculatePackageGuestPrice(Package $package, int $adults = 2, int $children = 0, int $infants = 0): float
    {
        $itinerary = $package->itinerary ?? [];
        $total = 0.0;
        $guestMultiplier = max(1, $adults) + (max(0, $children) * 0.6) + (max(0, $infants) * 0.2);

        foreach ($itinerary as $dayIndex => $dayEntry) {
            if (!is_array($dayEntry) || is_string($dayIndex)) {
                continue;
            }

            $accommodationId = $dayEntry['accommodation'] ?? null;
            if (blank($accommodationId)) {
                continue;
            }

            $selectedRoomIds = array_values(array_filter(array_map('intval', (array) ($dayEntry['rooms'] ?? []))));
            $roomRate = 0.0;

            if (empty($selectedRoomIds)) {
                $selectedRoomIds = AccommodationRoom::where('accommodation_id', (int) $accommodationId)
                    ->pluck('id')
                    ->all();
            }

            foreach ($selectedRoomIds as $roomId) {
                $room = AccommodationRoom::find((int) $roomId);
                if (!$room) {
                    continue;
                }

                $rate = $room->rates()
                    ->where('is_rate_plan', true)
                    ->orderByDesc('updated_at')
                    ->first();

                if (!$rate) {
                    $rate = AccommodationRate::where('accommodation_id', (int) $accommodationId)
                        ->where(function ($query) use ($room) {
                            $query->whereNull('room_id')->orWhere('room_id', $room->id);
                        })
                        ->where('is_rate_plan', true)
                        ->orderByDesc('updated_at')
                        ->first();
                }

                if (!$rate) {
                    continue;
                }

                $candidate = (float) ($rate->final_rate ?? $rate->base_rate ?? 0);
                if ($candidate > $roomRate) {
                    $roomRate = $candidate;
                }
            }

            if ($roomRate > 0) {
                $total += $roomRate * $guestMultiplier;
            }
        }

        if ($total <= 0) {
            $total = max(1500, (float) ($package->no_of_days ?? 3) * 1000);
        }

        return round($total, 2);
    }

    private function matchesPackageGuestCapacity($rooms, int $adults, int $children = 0, int $requiredRooms = 1): bool
    {
        $rooms = collect($rooms);

        if ($rooms->isEmpty() || $requiredRooms < 1) {
            return false;
        }

        $requiredAdultsPerRoom = (int) ceil($adults / max(1, $requiredRooms));
        $requiredChildrenPerRoom = (int) ceil($children / max(1, $requiredRooms));
        $availableRoomUnits = 0;

        foreach ($rooms as $room) {
            if (!$room) {
                continue;
            }

            $roomAdults = max(0, (int) ($room->capacity ?? 0));
            $roomChildren = max(0, (int) ($room->children_capacity ?? 0));
            $quantity = max(1, (int) ($room->quantity ?? 1));

            if ($requiredAdultsPerRoom <= $roomAdults && $requiredChildrenPerRoom <= $roomChildren) {
                $availableRoomUnits += $quantity;
            }
        }

        return $availableRoomUnits >= $requiredRooms;
    }

    private function packageMatchesGuestCriteria(Package $package, string $region, ?string $travelingDate, int $adults, int $children, int $infants, int $roomsRequired): bool
    {
        $itinerary = $package->itinerary ?? [];
        $selectedAccommodations = [];

        foreach ($itinerary as $dayData) {
            if (!is_array($dayData)) {
                continue;
            }

            $accommodationId = $dayData['accommodation'] ?? null;
            if (!empty($accommodationId)) {
                $selectedAccommodations[] = (int) $accommodationId;
            }
        }

        $selectedAccommodations = array_values(array_unique(array_filter($selectedAccommodations, fn ($id) => $id > 0)));
        if (empty($selectedAccommodations)) {
            return false;
        }

        if ($region !== '' && $region !== 'all') {
            $matchesRegion = false;
            foreach ($selectedAccommodations as $accommodationId) {
                $accommodation = \App\Models\Accommodation::find($accommodationId);
                if (!$accommodation) {
                    continue;
                }

                $regionValue = trim((string) ($accommodation->region ?? ''));
                if ($regionValue !== '' && strcasecmp($regionValue, $region) === 0) {
                    $matchesRegion = true;
                    break;
                }
            }

            if (!$matchesRegion) {
                return false;
            }
        }

        foreach ($selectedAccommodations as $accommodationId) {
            $accommodation = \App\Models\Accommodation::with('rooms')->find($accommodationId);
            if (!$accommodation || $accommodation->rooms->isEmpty()) {
                continue;
            }

            if ($this->matchesPackageGuestCapacity($accommodation->rooms, $adults, $children + $infants, $roomsRequired)) {
                return true;
            }
        }

        return false;
    }

    public function showActivity(Request $request, Activity $activity)
    {
        abort_if(blank($activity->activity_name), 404);
        abort_if(!$this->isActivityApprovedForFrontend($activity), 404);

        $bookingContext = $this->buildActivityBookingContext($request);

        // Fetch approved reviews for this activity
        $activityBookingIds = $activity->bookings()->pluck('id')->toArray();
        $approvedActivityReviews = ReviewItem::where('service_type', 'activity')
            ->whereIn('service_id', $activityBookingIds)
            ->where('status', 'approved')
            ->with('parentReview.trip.traveler')
            ->get();

        $activityRatingSummary = $this->resolveReviewRatingSummary($approvedActivityReviews);

        $activityData = $this->mapActivity($activity->load([
                'seoSocial',
                'policy',
                'variants',
                'allotments' => function ($query) use ($bookingContext) {
                    $query->where('inventory_date', $bookingContext['activity_date']);
                },
                'rates' => function ($query) use ($bookingContext) {
                    $query
                        ->whereDate('valid_from', '<=', $bookingContext['activity_date'])
                        ->whereDate('valid_to', '>=', $bookingContext['activity_date'])
                        ->orderBy('adult_rate')
                        ->orderBy('equipment_rate')
                        ->orderBy('private_exclusive_rate');
                },
                'schedulingTimeSlots',
                'operator',
            ]), true, $bookingContext);

        $activityData['rating_score'] = $activityRatingSummary['score'];
        $activityData['rating_display'] = $activityRatingSummary['score_display'];
        $activityData['rating_count'] = $activityRatingSummary['count'];

        return view('frontend.activity-show', [
            'activity' => $activityData,
            'approvedActivityReviews' => $approvedActivityReviews,
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

        // Fetch approved reviews for this accommodation
        $accommodationBookingIds = $accommodation->bookings()->pluck('id')->toArray();
        $approvedAccommodationReviews = ReviewItem::where('service_type', 'accommodation')
            ->whereIn('service_id', $accommodationBookingIds)
            ->where('status', 'approved')
            ->with('parentReview.trip.traveler')
            ->get();

        $reviewRatingSummary = $this->resolveReviewRatingSummary($approvedAccommodationReviews);
        $accommodationData = $this->mapAccommodation($accommodation, true, $bookingContext);

        $accommodationData['rating_score'] = $reviewRatingSummary['score'];
        $accommodationData['rating_display'] = $reviewRatingSummary['score_display'];
        $accommodationData['rating_count'] = $reviewRatingSummary['count'];

        return view('frontend.accommodation-show', [
            'accommodation' => $accommodationData,
            'ratingSummary' => $reviewRatingSummary,
            'similarAccommodations' => $similarAccommodations,
            'approvedAccommodationReviews' => $approvedAccommodationReviews,
        ]);
    }

    public function showTransport(Request $request, Transport $transport)
    {
        abort_if(blank($transport->vehicle_name), 404);
        abort_if(!$this->isTransportApprovedForFrontend($transport), 404);

        $bookingContext = $this->buildTransportBookingContext($request);
        $transport = $transport->load(['operator', 'rates', 'routes']);

        $serviceType = in_array(trim((string) $request->query('service_type', 'airport_transfer')), ['airport_transfer', 'activity_transfer', 'hotel_transfer', 'full_day_sightseeing', 'half_day_sightseeing'], true)
            ? trim((string) $request->query('service_type'))
            : 'airport_transfer';

        $transportData = $this->mapTransport(
            $transport,
            true,
            $request->query('pickup_region_id', '') !== ''
                ? ($this->getRegionNameFromId($request->query('pickup_region_id')) ?? $request->query('transport_from', ''))
                : $request->query('transport_from', ''),
            $request->query('dropoff_region_id', '') !== ''
                ? ($this->getRegionNameFromId($request->query('dropoff_region_id')) ?? $request->query('transport_to', ''))
                : $request->query('transport_to', '')
        );

        $filteredRoutes = collect($transportData['routes_pricing'] ?? [])
            ->filter(fn ($route) => trim((string) ($route['service_type'] ?? 'airport_transfer')) === $serviceType)
            ->values();

        if ($filteredRoutes->isNotEmpty()) {
            $transportData['routes_pricing'] = $filteredRoutes->all();
            $transportData['selected_route'] = $this->findRouteByPlaceSelection(
                $filteredRoutes,
                $transportData['selected_transport_from'] ?? '',
                $transportData['selected_transport_to'] ?? ''
            ) ?? $filteredRoutes->first();
        }

        $transportData['service_type'] = $serviceType;
        $transportData['booking'] = $bookingContext;

        return view('frontend.transport-show', [
            'transport' => $transportData,
        ]);
    }

    private function mapActivity(Activity $activity, bool $detailed = false, ?array $bookingContext = null): array
    {
        $locale = app()->getLocale();
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
            // prefer french when set
            ($locale === 'fr' ? ($activity->seoSocial->short_description_fr ?? null) : null)
                ?? ($locale === 'fr' ? $activity->short_title_fr ?? null : null)
                ?? $activity->seoSocial->short_description
                ?? $activity->short_title
                ?? $activity->overview
        );

        // For detailed views, use HTML content; for lists, use plain text
        // choose french fields when app locale is fr and content exists
        if ($locale === 'fr') {
            $overviewText = $detailed ? ($activity->overview_fr ?: ($activity->overview ?: '')) : $this->plainText($activity->overview_fr ?: $activity->overview);
            $includedText = $detailed ? ($activity->whats_included_fr ?: ($activity->whats_included ?: '')) : $this->plainText($activity->whats_included_fr ?: $activity->whats_included);
            $itineraryText = $detailed ? ($activity->itinerary_fr ?: ($activity->itinerary ?: '')) : $this->plainText($activity->itinerary_fr ?: $activity->itinerary);
        } else {
            $overviewText = $detailed ? ($activity->overview ?: '') : $this->plainText($activity->overview);
            $includedText = $detailed ? ($activity->whats_included ?: '') : $this->plainText($activity->whats_included);
            $itineraryText = $detailed ? ($activity->itinerary ?: '') : $this->plainText($activity->itinerary);
        }
        $meetingPoint = $detailed ? ($activity->meeting_point_details ?: '') : $this->plainText($activity->meeting_point_details);
        
        $bookingContext = $bookingContext ?? $this->defaultDetailBookingContext();
        $rates = collect($activity->relationLoaded('rates') ? $activity->rates : []);
        $policy = $activity->relationLoaded('policy') ? $activity->policy : null;
        $variants = collect($activity->relationLoaded('variants') ? $activity->variants : []);
        $allotments = collect($activity->relationLoaded('allotments') ? $activity->allotments : []);
        $forceTemplatePolicies = $this->shouldForceTemplatePolicies($activity->operator?->agreement_type ?? null);

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

            $startingRateofadult = $rates
            ->pluck('adult_rate')
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => (float) $value)
            ->filter(fn (float $value) => $value > 0)
            ->min();

        // If `regions` stores an id, resolve it to the region name; otherwise use the stored string or address
        if (is_numeric($activity->regions)) {
            $regionModel = \App\Models\Region::find((int) $activity->regions);
            $activityRegionValue = $regionModel?->name ?? $activity->address ?? null;
        } else {
            $activityRegionValue = $activity->regions ?? $activity->address ?? null;
        }
        $location = implode(' • ', array_filter([
            $activity->destination,
            $activityRegionValue,
            $activity->town,
        ]));

        $mapData = $this->buildMapData(
            $activity->latitude !== null ? (float) $activity->latitude : null,
            $activity->longitude !== null ? (float) $activity->longitude : null,
            implode(', ', array_filter([
                $activity->town,
                $activityRegionValue,
                $activity->destination,
                'Mauritius',
            ]))
        );

        $availableRooms = $detailed
            ? $this->buildActivityAvailability($variants, $rates, $allotments, $bookingContext)
            : [];

        $activityBookingIds = $activity->bookings()->pluck('id')->toArray();
        $activityReviewSummary = $this->resolveReviewSummaryByBookingIds($activityBookingIds, 'activity');
        $activityRatingSource = $activityReviewSummary['count'] > 0
            ? $activityReviewSummary
            : $this->resolveAccommodationRating($activity->operator?->operator_id);

        $timeSlots = [];
        if ($detailed && $activity->relationLoaded('schedulingTimeSlots')) {
            $activityBookings = ActivityBooking::where('activity_id', $activity->id)
                ->whereDate('activity_date', $bookingContext['activity_date'])
                ->whereIn('booking_status', ['Pending', 'Confirmed'])
                ->get();

            $bookedParticipantsBySlot = [];
            foreach ($activityBookings as $booking) {
                $participantTimeSlots = $booking->participant_time_slots ?? [];
                if (!is_array($participantTimeSlots)) {
                    continue;
                }
                foreach ($participantTimeSlots as $slotId) {
                    $slotKey = (int) $slotId;
                    if ($slotKey <= 0) {
                        continue;
                    }
                    $bookedParticipantsBySlot[$slotKey] = ($bookedParticipantsBySlot[$slotKey] ?? 0) + 1;
                }
            }

            $timeSlotsByVariant = collect($activity->schedulingTimeSlots)
                ->groupBy(fn ($slot) => (int) ($slot->variant_id ?? 0))
                ->map(function ($slots) use ($bookedParticipantsBySlot) {
                    return $slots->map(function ($slot) use ($bookedParticipantsBySlot) {
                        $capacity = max(0, (int) ($slot->capacity_per_slot ?? 0));
                        $booked = $bookedParticipantsBySlot[(int) $slot->timeslot_id] ?? 0;
                        $available = max(0, $capacity - $booked);

                        return [
                            'id' => $slot->timeslot_id,
                            'start_time' => $slot->start_time,
                            'end_time' => $slot->end_time,
                            'duration' => $slot->duration,
                            'discount_value' => $slot->discount_value ? (float) $slot->discount_value : 0,
                            'capacity_per_slot' => $capacity,
                            'booked' => $booked,
                            'available' => $available,
                            'display' => trim(($slot->start_time ?? '') . ' - ' . ($slot->end_time ?? '') . ($slot->duration ? ' (' . $slot->duration . ')' : '')),
                        ];
                    })->filter(fn ($slot) => !empty($slot['id']))->values()->all();
                });

            foreach ($availableRooms as &$availableRoom) {
                $variantKey = (int) ($availableRoom['room_id'] ?? 0);
                $availableRoom['time_slots'] = $timeSlotsByVariant[$variantKey] ?? [];
            }
            unset($availableRoom);

            $timeSlots = $timeSlotsByVariant->flatten(1)->values()->all();
        }

        // For detailed views, use HTML content; for lists, use plain text
        $bookingNotesText = '';
        $checkoutPolicyText = '';
        $termsConditionsText = '';
        
        if ($policy) {
                $bookingWindowRules = $locale === 'fr' ? ($policy->booking_window_rules_fr ?: $policy->booking_window_rules) : $policy->booking_window_rules;
            $noShowPolicy = $locale === 'fr' ? ($policy->no_show_policy_fr ?: $policy->no_show_policy) : $policy->no_show_policy;
            $cancellationPolicy = $this->resolvePolicyText(
                $policy->cancellation_policy_type,
                $policy->cancellation_policy_template_id,
                $locale === 'fr' ? ($policy->cancellation_policy_fr ?: $policy->cancellation_policy) : $policy->cancellation_policy,
                'activity',
                'Cancellation Policy',
                $forceTemplatePolicies
            );
            $amendmentPolicy = $this->resolvePolicyText(
                $policy->amendment_policy_type,
                $policy->amendment_policy_template_id,
                $locale === 'fr' ? ($policy->amendment_policy_fr ?: $policy->amendment_policy) : $policy->amendment_policy,
                'activity',
                'Amendment Policy',
                $forceTemplatePolicies
            );
            $safetyRequirements = $locale === 'fr' ? ($policy->safety_requirements_fr ?: $policy->safety_requirements) : $policy->safety_requirements;

            if ($detailed) {
                $bookingNotesText = $bookingWindowRules ?: $noShowPolicy ?: '';
                $checkoutPolicyText = trim(implode("\n\n", array_filter([$cancellationPolicy, $amendmentPolicy])));
                $termsConditionsText = trim(implode("\n\n", array_filter([$checkoutPolicyText, $safetyRequirements])));
            } else {
                $bookingNotesText = $this->plainText($bookingWindowRules ?: $noShowPolicy);
                $checkoutPolicyText = $this->plainText(trim(implode("\n\n", array_filter([$cancellationPolicy, $amendmentPolicy]))));
                $termsConditionsText = $this->plainText(trim(implode("\n\n", array_filter([$checkoutPolicyText, $safetyRequirements]))));
            }
        }

        if (blank($bookingNotesText) && !blank($activity->booking_confirmation_type)) {
            $bookingNotesText = 'Booking confirmation: ' . $activity->booking_confirmation_type;
        }

        if (blank($checkoutPolicyText) && $policy && !blank($policy->cancellation_policy_template_id)) {
            $checkoutPolicyText = 'Cancellation policy template: ' . $policy->cancellation_policy_template_id;
        }

        if (blank($termsConditionsText) && $policy && !blank($policy->health_requirements_type) && $policy->health_requirements_type !== 'None') {
            $termsConditionsText = 'Health requirements: ' . $policy->health_requirements_type;
        }

        return [
            'id' => $activity->id,
            'kind' => 'Activity',
            'title' => $locale === 'fr' && !empty($activity->activity_name_fr) ? $activity->activity_name_fr : $activity->activity_name,
            'service_type' => $activity->service_type,
            'meta' => $activity->service_type ?: 'Experience',
            'location' => $location ?: 'Mauritius',
            'region' => $activityRegionValue,
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
            'starting_rate_of_adult' => $startingRateofadult,
            'overview_text' => $overviewText,
            'included_text' => $includedText,
            'rating_score' => $activityRatingSource['score'],
            'rating_display' => $activityRatingSource['score_display'],
            'rating_count' => $activityRatingSource['count'],
            'itinerary_text' => $itineraryText,
            'meeting_point' => $meetingPoint,
            'booking' => $detailed ? $bookingContext : [],
            'available_rooms' => $availableRooms,
            'map_embed_url' => $mapData['embed_url'],
            'map_link' => $mapData['link'],
            'booking_notes_text' => $bookingNotesText,
            'checkout_policy_text' => $checkoutPolicyText,
            'terms_conditions_text' => $termsConditionsText,
            'time_slots' => $timeSlots,
            'allow_adults' => (bool) ($activity->allow_adults ?? true),
            'allow_children' => (bool) ($activity->allow_children ?? true),
            'allow_infants' => (bool) ($activity->allow_infants ?? true),
            'gallery' => $detailed
                ? $galleryImages->merge($vehicleImages)->prepend($primaryImage)->unique()->values()->all()
                : [],
        ];
    }

    private function mapAccommodation(Accommodation $accommodation, bool $detailed = false, ?array $bookingContext = null): array
    {
        $locale = app()->getLocale();
        $media = collect($accommodation->media ?? []);
        $rates = collect($accommodation->relationLoaded('rates') ? $accommodation->rates : []);
        $rooms = collect($accommodation->relationLoaded('rooms') ? $accommodation->rooms : []);
        $inventoryRows = collect($accommodation->relationLoaded('inventory') ? $accommodation->inventory : []);
        $bookings = collect($accommodation->relationLoaded('bookings') ? $accommodation->bookings : []);
        $bookingContext = $bookingContext ?? $this->defaultDetailBookingContext();
        $operatorRating = $this->resolveAccommodationRating($accommodation->operator?->operator_id);

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

        // prefer french fields when locale is french and values exist
        $shortDescription = $this->plainText(($locale === 'fr' ? ($accommodation->short_description_fr ?? null) : null) ?: $accommodation->short_description ?: ($locale === 'fr' ? ($accommodation->property_description_fr ?? null) : null) ?: $accommodation->property_description);
        $fullDescription = $this->plainText(($locale === 'fr' ? ($accommodation->property_description_fr ?? null) : null) ?: $accommodation->property_description ?: ($locale === 'fr' ? ($accommodation->short_description_fr ?? null) : null) ?: $accommodation->short_description);

        $locationParts = [];
        if (!blank($accommodation->region)) {
            $locationParts[] = 'Region: ' . $accommodation->region;
        }
        if (!blank($accommodation->state)) {
            $locationParts[] = 'State: ' . $accommodation->state;
        }
        $locationParts[] = $accommodation->city;
        $locationParts[] = $accommodation->country;
        $location = implode(' • ', array_filter($locationParts));

        $addressParts = [];
        if (!blank($accommodation->address)) $addressParts[] = $accommodation->address;
        if (!blank($accommodation->city)) $addressParts[] = $accommodation->city;
        if (!blank($accommodation->region)) $addressParts[] = $accommodation->region;
        if (!blank($accommodation->state)) $addressParts[] = $accommodation->state;
        if (!blank($accommodation->country)) $addressParts[] = $accommodation->country;
        $addressLine = implode(', ', array_filter($addressParts));

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
                    'max_person_capacity' => $room->max_person_capacity !== null
                        ? (int) $room->max_person_capacity
                        : ((int) ($room->capacity ?? 0) + (int) ($room->children_capacity ?? 0) + max(0, ((int) ($room->infant_capacity ?? 0) - 1))),
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

        $bookingWindowRules = $locale === 'fr' ? ($accommodation->booking_window_rules_fr ?: $accommodation->booking_window_rules) : $accommodation->booking_window_rules;
        $bookingNotesText = $this->plainText($bookingWindowRules);

        if (blank($bookingNotesText) && !blank($accommodation->booking_confirmation_type)) {
            $bookingNotesText = 'Booking confirmation: ' . $accommodation->booking_confirmation_type;
        }

        $checkinCheckoutRules = $locale === 'fr' ? ($accommodation->checkin_checkout_rules_fr ?: $accommodation->checkin_checkout_rules) : $accommodation->checkin_checkout_rules;
        $checkoutPolicyParts = array_values(array_filter([
            $accommodation->checkin_time ? 'Check-in time: ' . substr((string) $accommodation->checkin_time, 0, 5) : null,
            $accommodation->checkout_time ? 'Check-out time: ' . substr((string) $accommodation->checkout_time, 0, 5) : null,
            $this->plainText($checkinCheckoutRules),
        ]));
        $checkoutPolicyText = implode("\n\n", $checkoutPolicyParts);

        $forceTemplatePolicies = $this->shouldForceTemplatePolicies($accommodation->operator?->agreement_type ?? null);

        $houseRules = $this->resolvePolicyText(
            $accommodation->house_rules_type,
            $accommodation->house_rules_template_id,
            $locale === 'fr' ? ($accommodation->house_rules_fr ?: $accommodation->house_rules) : $accommodation->house_rules,
            'accommodation',
            'House Rules',
            $forceTemplatePolicies
        );
        $cancellationPolicy = $this->resolvePolicyText(
            $accommodation->cancellation_policy_type,
            $accommodation->cancellation_policy_template_id,
            $locale === 'fr' ? ($accommodation->cancellation_policy_fr ?: $accommodation->cancellation_policy) : $accommodation->cancellation_policy,
            'accommodation',
            'Cancellation Policy',
            $forceTemplatePolicies
        );
        $amendmentPolicy = $this->resolvePolicyText(
            $accommodation->amendment_policy_type,
            $accommodation->amendment_policy_template_id,
            $locale === 'fr' ? ($accommodation->amendment_policy_fr ?: $accommodation->amendment_policy) : $accommodation->amendment_policy,
            'accommodation',
            'Amendment Policy',
            $forceTemplatePolicies
        );
        $securityDepositPolicy = $this->resolvePolicyText(
            $accommodation->security_deposit_policy_type,
            $accommodation->security_deposit_policy_template_id,
            $locale === 'fr' ? ($accommodation->security_deposit_policy_fr ?: $accommodation->security_deposit_policy) : $accommodation->security_deposit_policy,
            'accommodation',
            'Security Deposit Policy',
            $forceTemplatePolicies
        );

        $termsParts = array_values(array_filter([
            $this->plainText($houseRules),
            $this->plainText($cancellationPolicy),
            $this->plainText($amendmentPolicy),
            $this->plainText($securityDepositPolicy),
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

        $accommodationBookingIds = $accommodation->bookings()->pluck('id')->toArray();
        $accommodationReviewSummary = $this->resolveReviewSummaryByBookingIds($accommodationBookingIds, 'accommodation');
        $operatorRating = $this->resolveAccommodationRating($accommodation->operator?->operator_id);
        $accommodationRatingSource = $accommodationReviewSummary['count'] > 0
            ? $accommodationReviewSummary
            : $operatorRating;

        return [
            'id' => $accommodation->id,
            'kind' => 'Accommodation',
            'title' => $locale === 'fr' && !empty($accommodation->property_name_fr) ? $accommodation->property_name_fr : $accommodation->property_name,
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
            'rating_score' => $accommodationRatingSource['score'],
            'rating_display' => $accommodationRatingSource['score_display'],
            'rating_count' => $accommodationRatingSource['count'],
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

    private function mapTransport(Transport $transport, bool $detailed = false, string $selectedFrom = '', string $selectedTo = ''): array
    {
        $rates = collect($transport->relationLoaded('rates') ? $transport->rates : []);
        $galleryImages = collect($transport->gallery_images ?? [])
            ->filter(fn ($path) => is_string($path) && !blank($path))
            ->map(fn ($path) => $this->storageAsset($path))
            ->filter()
            ->values();

        $primaryImage = $this->storageAsset($transport->hero_banner_image)
            ?? $galleryImages->first()
            ?? asset('images/transport.svg');

        $routes = collect($transport->relationLoaded('routes') ? $transport->routes : [])
            ->map(function ($route) {
                $pricing = is_array($route->pricing) ? $route->pricing : [];
                $defaultPrice = isset($pricing['default_price']) ? (float) $pricing['default_price'] : null;
                $returnPrice = isset($pricing['return_price']) ? (float) $pricing['return_price'] : null;
                $seasonal = collect($pricing['seasonal'] ?? [])
                    ->map(function ($season) {
                        return [
                            'start_date' => $season['start'] ?? $season['start_date'] ?? null,
                            'end_date' => $season['end'] ?? $season['end_date'] ?? null,
                            'price' => isset($season['price']) ? (float) $season['price'] : (isset($season['price_per_person']) ? (float) $season['price_per_person'] : null),
                            'return_price' => isset($season['return_price']) ? (float) $season['return_price'] : null,
                        ];
                    })
                    ->filter(fn($item) => !blank($item['start_date']) && !blank($item['end_date']) && $item['price'] !== null)
                    ->values()
                    ->all();

                $routeFrom = $route->route_from ?? $route->pickup_value;
                $routeTo = $route->route_to ?? $route->dropoff_value;
                $routeName = trim(($routeFrom ? $routeFrom : '') . ' → ' . ($routeTo ? $routeTo : ''));

                return [
                    'route_id' => $route->route_id,
                    'service_type' => $route->service_type ?? 'airport_transfer',
                    'route_from' => $routeFrom,
                    'route_to' => $routeTo,
                    'route_name' => $routeName,
                    'pricing' => [
                        'default_price' => $defaultPrice,
                        'return_price' => $returnPrice,
                        'seasonal' => $seasonal,
                    ],
                ];
            })
            ->filter(fn ($route) => !blank($route['route_from']) && !blank($route['route_to']))
            ->values();

        if ($routes->isEmpty()) {
            $routes = collect($transport->routes_pricing ?? [])
                ->map(function ($route) {
                    $defaultPrice = isset($route['price']) ? (float) $route['price'] : (isset($route['package_price']) ? (float) $route['package_price'] : null);
                    $returnPrice = isset($route['return_price']) ? (float) $route['return_price'] : (isset($route['package_return_price']) ? (float) $route['package_return_price'] : null);
                    $seasonal = collect($route['seasonal'] ?? [])
                        ->map(function ($season) {
                            return [
                                'start_date' => $season['start'] ?? $season['start_date'] ?? null,
                                'end_date' => $season['end'] ?? $season['end_date'] ?? null,
                                'price' => isset($season['price']) ? (float) $season['price'] : null,
                                'return_price' => isset($season['return_price']) ? (float) $season['return_price'] : null,
                            ];
                        })
                        ->filter(fn($item) => !blank($item['start_date']) && !blank($item['end_date']) && $item['price'] !== null)
                        ->values()
                        ->all();

                    $routeFrom = $route['from'] ?? $route['route_from'] ?? '';
                    $routeTo = $route['to'] ?? $route['route_to'] ?? '';
                    $routeName = trim(($routeFrom ? $routeFrom : '') . ' → ' . ($routeTo ? $routeTo : ''));

                    return [
                        'route_id' => $route['route_id'] ?? null,
                        'service_type' => trim((string) ($route['service_type'] ?? 'airport_transfer')) ?: 'airport_transfer',
                        'route_from' => $routeFrom,
                        'route_to' => $routeTo,
                        'route_name' => $routeName,
                        'pricing' => [
                            'default_price' => $defaultPrice,
                            'return_price' => $returnPrice,
                            'seasonal' => $seasonal,
                        ],
                    ];
                })
                ->filter(fn ($route) => !blank($route['route_from']) && !blank($route['route_to']))
                ->values();
        }

        $routes = $this->orderTransportRoutesBySelection($routes, $selectedFrom, $selectedTo);
        $selectedRoute = $this->findRouteByPlaceSelection($routes, $selectedFrom, $selectedTo);
        $matchedRoute = $selectedRoute ?? $routes->first();

        $placeNames = [];
        $placeRegionMap = [];
        if ($detailed && Schema::hasTable('places')) {
            $placeRegionMap = Place::query()
                ->where('is_active', true)
                ->pluck('route_region', 'place_name')
                ->mapWithKeys(fn ($region, $name) => [trim((string) $name) => trim((string) $region)])
                ->filter()
                ->all();

            $placeNames = array_keys($placeRegionMap);
        }

        $startingRate = $rates
            ->filter(fn($rate) => $rate->is_active)
            ->min('price_per_person');

        // If a route was matched and it has a default per-vehicle price, prefer
        // that price for the displayed starting rate (operator expects route price)
        $selectedRouteDefault = null;
        if (!empty($matchedRoute) && isset($matchedRoute['pricing']['default_price']) && $matchedRoute['pricing']['default_price'] !== null) {
            $selectedRouteDefault = (float) $matchedRoute['pricing']['default_price'];
        }

        if ($selectedRouteDefault !== null) {
            $startingRate = $selectedRouteDefault;
        }

        return [
            'id' => $transport->id,
            'service_id' => $transport->service_id,
            'title' => $transport->vehicle_name,
            'kind' => 'Transport',
            'type' => 'transport',
            'vehicle_type' => $transport->vehicle_type,
            'seating_capacity' => $transport->seating_capacity,
            'image' => $primaryImage,
            'excerpt' => $this->plainText($transport->short_description),
            'location' => $transport->operator?->business_name
                ?? $transport->operator?->name
                ?? 'Mauritius',
            'description' => $detailed ? $transport->service_description : '',
            'long_description' => $detailed ? ($transport->long_description ?? '') : '',
            'long_description_fr' => $detailed ? ($transport->long_description_fr ?? '') : '',
            'inclusions' => $detailed ? ($transport->inclusions ?? '') : '',
            'inclusions_fr' => $detailed ? ($transport->inclusions_fr ?? '') : '',
            'exclusions' => $detailed ? ($transport->exclusions ?? '') : '',
            'exclusions_fr' => $detailed ? ($transport->exclusions_fr ?? '') : '',
            'pickup_instructions' => $detailed ? ($transport->pickup_instructions ?? '') : '',
            'pickup_instructions_fr' => $detailed ? ($transport->pickup_instructions_fr ?? '') : '',
            'overview' => $detailed ? $transport->overview : '',
            'amenities' => $transport->amenities ?? [],
            'car_rental_prices' => $transport->car_rental_prices ?? [],
            'routes_pricing' => $routes->toArray(),
            'selected_route' => $matchedRoute ? $matchedRoute : null,
            'place_names' => $placeNames,
            'place_region_map' => $placeRegionMap,
            'selected_transport_from' => $selectedFrom,
            'selected_transport_to' => $selectedTo,
            'starting_rate' => $startingRate,
            'starting_rate_of_adult' => $startingRate,
            'operator' => $transport->operator ? [
                'id' => $transport->operator->id,
                'name' => $transport->operator->business_name ?? $transport->operator->name ?? '',
                'email' => $transport->operator->email,
            ] : null,
            'gallery' => $galleryImages->all(),
            'url' => route('frontend.transports.show', $transport),
            'approval_status' => $transport->approval_status,
            'is_published' => $transport->is_published,
        ];
    }

    private function orderTransportRoutesBySelection($routes, string $selectedFrom, string $selectedTo)
    {
        $selectedFrom = trim($selectedFrom);
        $selectedTo = trim($selectedTo);

        if (blank($selectedFrom) || blank($selectedTo)) {
            return $routes;
        }

        $fromRegion = $this->normalizeSelectedRouteRegion($selectedFrom);
        $toRegion = $this->normalizeSelectedRouteRegion($selectedTo);

        if (blank($fromRegion) || blank($toRegion)) {
            return $routes;
        }

        $selectedIndex = $routes->search(function ($route) use ($fromRegion, $toRegion, $selectedFrom, $selectedTo) {
            $routeFromRegion = $this->normalizeRouteRegion((string) ($route['route_from'] ?? ''));
            $routeToRegion = $this->normalizeRouteRegion((string) ($route['route_to'] ?? ''));

            // Primary: match by normalized region or direct region name
            if (Str::lower($routeFromRegion ?? '') === Str::lower($fromRegion)
                && Str::lower($routeToRegion ?? '') === Str::lower($toRegion)) {
                return true;
            }

            // Fallback: match by exact place name or exact direct values (case-insensitive)
            $routeFromName = trim((string) ($route['route_from'] ?? ''));
            $routeToName = trim((string) ($route['route_to'] ?? ''));
            if ($routeFromName !== '' && $routeToName !== '') {
                if (Str::lower($routeFromName) === Str::lower($selectedFrom)
                    && Str::lower($routeToName) === Str::lower($selectedTo)) {
                    return true;
                }
            }

            return false;
        });

        if ($selectedIndex === false) {
            return $routes;
        }

        $selected = $routes->get($selectedIndex);
        return $routes->filter(fn ($_, $index) => $index !== $selectedIndex)
            ->prepend($selected)
            ->values();
    }

    private function findRouteByPlaceSelection($routes, string $selectedFrom, string $selectedTo)
    {
        $selectedFrom = trim($selectedFrom);
        $selectedTo = trim($selectedTo);

        if (blank($selectedFrom) || blank($selectedTo)) {
            return null;
        }

        $fromRegion = $this->normalizeSelectedRouteRegion($selectedFrom);
        $toRegion = $this->normalizeSelectedRouteRegion($selectedTo);

        if (blank($fromRegion) || blank($toRegion)) {
            return null;
        }

        // First attempt: match by normalized region or direct region name
        $found = $routes->first(function ($route) use ($fromRegion, $toRegion) {
            $routeFromRegion = $this->normalizeRouteRegion((string) ($route['route_from'] ?? ''));
            $routeToRegion = $this->normalizeRouteRegion((string) ($route['route_to'] ?? ''));

            return Str::lower($routeFromRegion ?? '') === Str::lower($fromRegion)
                && Str::lower($routeToRegion ?? '') === Str::lower($toRegion);
        });

        if ($found) {
            return $found;
        }

        // Fallback: match by exact place names (case-insensitive)
        $exact = $routes->first(function ($route) use ($selectedFrom, $selectedTo) {
            $routeFromName = trim((string) ($route['route_from'] ?? ''));
            $routeToName = trim((string) ($route['route_to'] ?? ''));

            if ($routeFromName === '' || $routeToName === '') {
                return false;
            }

            return Str::lower($routeFromName) === Str::lower($selectedFrom)
                && Str::lower($routeToName) === Str::lower($selectedTo);
        });

        if ($exact) {
            return $exact;
        }

        // As a final fallback, try to find a reverse route (operator may only add one direction).
        // If a reverse route exists (B -> A) and user requested A -> B, use the reverse route's pricing
        // but return a copy with `route_from`/`route_to` swapped so the UI shows A → B while using the existing price.
        $reverse = $routes->first(function ($route) use ($selectedFrom, $selectedTo) {
            $routeFromName = trim((string) ($route['route_from'] ?? ''));
            $routeToName = trim((string) ($route['route_to'] ?? ''));
            if ($routeFromName === '' || $routeToName === '') {
                return false;
            }
            return Str::lower($routeFromName) === Str::lower($selectedTo)
                && Str::lower($routeToName) === Str::lower($selectedFrom);
        });

        if ($reverse) {
            // Ensure reverse route actually has pricing before using it as fallback
            $pricing = $reverse['pricing'] ?? [];
            $hasPricing = (isset($pricing['default_price']) && $pricing['default_price'] !== null)
                || (isset($pricing['return_price']) && $pricing['return_price'] !== null)
                || (!empty($pricing['seasonal'] ?? []));

            if (!$hasPricing) {
                // don't use reverse route if no price information present
                return null;
            }
            $copy = $reverse;
            // Swap from/to and route_name for display, keep pricing intact
            $copy['route_from'] = $selectedFrom;
            $copy['route_to'] = $selectedTo;
            $copy['route_name'] = trim(($selectedFrom ? $selectedFrom : '') . ' → ' . ($selectedTo ? $selectedTo : ''));
            return $copy;
        }

        return null;
    }

    private function normalizeSelectedRouteRegion(string $value): ?string
    {
        $value = trim($value);
        if (blank($value)) {
            return null;
        }

        $placeRegion = $this->getPlaceRegion($value);
        if (!blank($placeRegion)) {
            return $placeRegion;
        }

        $lower = Str::lower($value);
        if (in_array($lower, ['north', 'south', 'airport'], true)) {
            return ucfirst($lower);
        }

        return ucfirst($lower);
    }

    private function normalizeRouteRegion(string $value): ?string
    {
        $value = trim($value);

        if (blank($value)) {
            return null;
        }

        $lower = Str::lower($value);
        if (in_array($lower, ['north', 'south', 'airport'], true)) {
            return ucfirst($lower);
        }

        $region = $this->getPlaceRegion($value);
        if (!blank($region)) {
            return $region;
        }

        return $value;
    }

    private function resolvePolicyText(?string $policyType, ?string $templateId, ?string $customText, string $serviceType, string $templatePolicyType, bool $forceTemplate = false): string
    {
        $typeNormalized = strtolower(trim((string) ($policyType ?? '')));
        if ($forceTemplate && !blank($templateId)) {
            $templateText = $this->getPolicyTemplateContent($templateId, $serviceType, $templatePolicyType);
            return $templateText ?: ($customText ?: '');
        }

        if ($typeNormalized === 'template' || (blank($typeNormalized) && !blank($templateId))) {
            $templateText = $this->getPolicyTemplateContent($templateId, $serviceType, $templatePolicyType);
            return $templateText ?: ($customText ?: '');
        }

        return $customText ?: '';
    }

    private function shouldForceTemplatePolicies(?string $agreementType): bool
    {
        $normalized = preg_replace('/\s+/', ' ', strtolower(trim((string) $agreementType)));
        return in_array($normalized, ['oto', 'full agreement', 'full service'], true);
    }

    private function getPolicyTemplateContent(?string $templateId, string $serviceType, string $policyType): ?string
    {
        if (blank($templateId)) {
            return null;
        }

        $template = PolicyTemplate::where('id', $templateId)
            ->where('service_type', $serviceType)
            ->where('policy_type', $policyType)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return null;
        }

        $locale = app()->getLocale();
        if ($locale === 'fr') {
            return $template->content_fr ?: $template->content;
        }

        return $template->content;
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

        if (array_key_exists($operatorExternalId, $this->operatorRatingCache)) {
            return $this->operatorRatingCache[$operatorExternalId];
        }

        $review = OperatorStatusReview::query()
            ->where('operator_id', $operatorExternalId)
            ->latest('created_at')
            ->first();

        if (!$review) {
            return $this->operatorRatingCache[$operatorExternalId] = [
                'score' => null,
                'score_display' => null,
                'count' => 0,
            ];
        }

        $score = (float) ($review->average_rating ?: $review->operator_rating ?: 0);
        $score = $score > 0 ? $score : null;

        return $this->operatorRatingCache[$operatorExternalId] = [
            'score' => $score,
            'score_display' => $score !== null ? number_format($score, 1) : null,
            'count' => (int) ($review->testimonials_count ?? 0),
        ];
    }

    private function resolveReviewRatingSummary($reviewItems): array
    {
        if ($reviewItems->isEmpty()) {
            return [
                'score' => null,
                'score_display' => null,
                'count' => 0,
            ];
        }

        $reviewAverages = $reviewItems->map(function ($reviewItem) {
            $criteria = is_array($reviewItem->criteria) ? $reviewItem->criteria : [];
            $numericCriteria = collect($criteria)->filter(fn ($value) => is_numeric($value) && $value !== null && $value !== '');

            if ($numericCriteria->isEmpty()) {
                return null;
            }

            return $numericCriteria->avg();
        })->filter();

        if ($reviewAverages->isEmpty()) {
            return [
                'score' => null,
                'score_display' => null,
                'count' => $reviewItems->count(),
            ];
        }

        $score = round($reviewAverages->avg(), 1);

        return [
            'score' => $score,
            'score_display' => number_format($score, 1),
            'count' => $reviewItems->count(),
        ];
    }

    private function resolveReviewSummaryByBookingIds(array $bookingIds, string $serviceType): array
    {
        if (empty($bookingIds)) {
            return [
                'score' => null,
                'score_display' => null,
                'count' => 0,
            ];
        }

        $reviewItems = ReviewItem::where('service_type', $serviceType)
            ->whereIn('service_id', $bookingIds)
            ->where('status', 'approved')
            ->get();

        return $this->resolveReviewRatingSummary($reviewItems);
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

        $normalized = trim((string) $path);
        $normalized = preg_replace('#^(storage/|public/)#', '', ltrim($normalized, '/'));

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
            'check_in_display' => $checkIn->format('d/m/Y'),
            'check_out_display' => $checkOut->format('d/m/Y'),
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
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
        $infants = max(0, (int) $request->query('infants', 0));
        $nights = max(1, $checkIn->diffInDays($checkOut));

        return [
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'check_in_display' => $checkIn->format('d/m/Y'),
            'check_out_display' => $checkOut->format('d/m/Y'),
            'adults' => $adults,
            'children' => $children,
            'infants' => $infants,
            'total_guests' => $adults + $children + $infants,
            'nights' => $nights,
        ];
    }

    private function buildActivityBookingContext(Request $request): array
    {
        $activityDate = $this->parseDateInput(
            (string) $request->query('activity_date', now()->format('Y-m-d')),
            now()
        );

        $adults = max(1, (int) $request->query('adults', $request->query('participants', 1)));
        $children = max(0, (int) $request->query('children', 0));
        $infants = max(0, (int) $request->query('infants', 0));
        $participants = max(1, $adults + $children + $infants);

        return [
            'activity_date' => $activityDate->toDateString(),
            'activity_date_display' => $activityDate->format('d/m/Y'),
            'participants' => $participants,
            'adults' => $adults,
            'children' => $children,
            'infants' => $infants,
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

            // Build pricing options grouped by pricing_setting
            $pricingOptions = [];
            $validRates = $candidateRates->filter(fn ($rate) => $this->rateOverlapsStay($rate, $checkIn, $checkOut));

            if ($validRates->isNotEmpty()) {
                $groupedByPricingSetting = $validRates->groupBy(function ($rate) {
                    return (string) ($rate->pricing_setting ?? 'Per Room/Night');
                });

                foreach ($groupedByPricingSetting as $pricingSetting => $settingRates) {
                    $bestRateData = $settingRates
                        ->map(function ($rate) use ($bookingContext, $room) {
                            $nightly = $this->calculateAccommodationNightlyTotal(
                                $rate,
                                (int) $bookingContext['adults'],
                                (int) $bookingContext['children'],
                                (int) ($bookingContext['infants'] ?? 0),
                                (int) ($room->capacity ?? 0),
                                (int) ($room->children_capacity ?? 0),
                                (int) ($room->infant_capacity ?? 0)
                            );

                            return [
                                'rate' => $rate,
                                'nightly' => $nightly,
                            ];
                        })
                        ->filter(fn (array $item) => $item['nightly'] !== null)
                        ->sort(function (array $left, array $right) {
                            // Always prefer is_default=1 rates first
                            $leftDefault = $left['rate']->is_default ? 1 : 0;
                            $rightDefault = $right['rate']->is_default ? 1 : 0;

                            if ($leftDefault !== $rightDefault) {
                                return $rightDefault <=> $leftDefault;
                            }

                            // Then fallback to lowest nightly price
                            return $left['nightly'] <=> $right['nightly'];
                        })
                        ->first();

                    if ($bestRateData) {
                        $pricingOptions[] = [
                            'rate' => $bestRateData['rate'],
                            'nightly' => $bestRateData['nightly'],
                            'pricing_setting' => $pricingSetting,
                        ];
                    }
                }
            }

            // If no rates found, try global rates
            if (empty($pricingOptions) && $roomRates->isNotEmpty() && $globalRates->isNotEmpty()) {
                $validGlobalRates = $globalRates->filter(fn ($rate) => $this->rateOverlapsStay($rate, $checkIn, $checkOut));
                $groupedByPricingSetting = $validGlobalRates->groupBy(function ($rate) {
                    return (string) ($rate->pricing_setting ?? 'Per Room/Night');
                });

                foreach ($groupedByPricingSetting as $pricingSetting => $settingRates) {
                    $bestRateData = $settingRates
                        ->map(function ($rate) use ($bookingContext, $room) {
                            $nightly = $this->calculateAccommodationNightlyTotal(
                                $rate,
                                (int) $bookingContext['adults'],
                                (int) $bookingContext['children'],
                                (int) ($bookingContext['infants'] ?? 0),
                                (int) ($room->capacity ?? 0),
                                (int) ($room->children_capacity ?? 0),
                                (int) ($room->infant_capacity ?? 0)
                            );

                            return [
                                'rate' => $rate,
                                'nightly' => $nightly,
                            ];
                        })
                        ->filter(fn (array $item) => $item['nightly'] !== null)
                        ->sort(function (array $left, array $right) {
                            // Always prefer is_default=1 rates first
                            $leftDefault = $left['rate']->is_default ? 1 : 0;
                            $rightDefault = $right['rate']->is_default ? 1 : 0;

                            if ($leftDefault !== $rightDefault) {
                                return $rightDefault <=> $leftDefault;
                            }

                            // Then fallback to lowest nightly price
                            return $left['nightly'] <=> $right['nightly'];
                        })
                        ->first();

                    if ($bestRateData) {
                        $pricingOptions[] = [
                            'rate' => $bestRateData['rate'],
                            'nightly' => $bestRateData['nightly'],
                            'pricing_setting' => $pricingSetting,
                        ];
                    }
                }
            }

            // Use first pricing option as primary
            $selectedRate = !empty($pricingOptions) ? $pricingOptions[0]['rate'] : null;
            $nightlyPrice = !empty($pricingOptions) ? $pricingOptions[0]['nightly'] : null;

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

            // Add a pricing option for each applicable plan
            if (empty($pricingOptions)) {
                // Fallback: use base_price if no rates available
                $nightlyPrice = $room->base_price !== null ? (float) $room->base_price : null;
                if ($nightlyPrice !== null && $nightlyPrice > 0) {
                    $totalPrice = round($nightlyPrice * (int) $bookingContext['nights'], 2);
                    $results[] = [
                        'room_id' => (int) $room->id,
                        'room_name' => (string) ($room->room_name ?: ($room->room_type ?: 'Room')),
                        'room_type' => $room->room_type,
                        'quantity' => $quantity,
                        'nightly_price' => $nightlyPrice,
                        'total_price' => $totalPrice,
                        'currency' => $accommodation->currency_code ?? 'USD',
                        'pricing_setting' => 'Per Room/Night',
                        'plan_label' => 'Standard Rate',
                        'rate_id' => null,
                        'rate_name' => null,
                        'meal_plan' => null,
                        'inclusions' => null,
                    ];
                }
            } else {
                // Add each pricing option as a separate item
                foreach ($pricingOptions as $option) {
                    $nightlyPrice = $option['nightly'];
                    $pricingSetting = $option['pricing_setting'];
                    $rate = $option['rate'];

                    $planInclusions = $rate->inclusions ?? null;
                    if (is_string($planInclusions)) {
                        $decodedInclusions = json_decode($planInclusions, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedInclusions)) {
                            $planInclusions = $decodedInclusions;
                        }
                    }

                    $totalPrice = $nightlyPrice !== null
                        ? round($nightlyPrice * (int) $bookingContext['nights'], 2)
                        : null;

                    if ($totalPrice !== null) {
                        $planLabelPrefix = $rate->rate_name
                            ? trim((string) $rate->rate_name)
                            : ($pricingSetting === 'Per Person/Night'
                                ? 'Per Person Plan'
                                : ($pricingSetting === 'Per Property/Night' ? 'Property Rate' : 'Per Room Plan'));

                        $planLabel = trim($planLabelPrefix . ($rate->meal_plan ? ' - ' . $rate->meal_plan : ''));

                        $results[] = [
                            'room_id' => (int) $room->id,
                            'room_name' => (string) ($room->room_name ?: ($room->room_type ?: 'Room')),
                            'room_type' => $room->room_type,
                            'quantity' => $quantity,
                            'nightly_price' => $nightlyPrice,
                            'total_price' => $totalPrice,
                            'currency' => $rate->currency ?? $accommodation->currency_code ?? 'USD',
                            'pricing_setting' => $pricingSetting,
                            'plan_label' => $planLabel,
                            'rate_id' => $rate->id,
                            'rate_name' => (string) ($rate->rate_name ?? ''),
                            'meal_plan' => $rate->meal_plan ?? null,
                            'inclusions' => !empty($planInclusions) ? $planInclusions : null,
                        ];
                    }
                }
            }
        }

        usort($results, function (array $left, array $right) {
            $leftValue = $left['total_price'] ?? PHP_INT_MAX;
            $rightValue = $right['total_price'] ?? PHP_INT_MAX;

            return $leftValue <=> $rightValue;
        });

        return $results;
    }

    private function calculateAccommodationNightlyTotal($rate, int $adults, int $children, int $infants = 0, int $includedAdults = 2, int $includedChildren = 0, int $includedInfants = 0): ?float
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
        $infants = max(0, $infants);
        $pricingSetting = (string) ($rate->pricing_setting ?? 'Per Room/Night');

        if ($pricingSetting === 'Per Person/Night') {
            $total = $baseRate * $adults;
            $extraBedRate = (float) ($rate->extra_bed_rate ?? 0);
            $childRate = $rate->children_rate !== null ? (float) $rate->children_rate : $baseRate;
            $infantRate = $rate->infant_rate !== null ? (float) $rate->infant_rate : 0.0;

            if ($childRate <= 0 && $extraBedRate > 0) {
                $childRate = $extraBedRate;
            }

            if ($infantRate <= 0 && $extraBedRate > 0) {
                $infantRate = $extraBedRate;
            }

            if ($children > 0) {
                $total += $childRate * $children;
            }

            if ($infants > 0 && $infantRate > 0) {
                $total += $infantRate * $infants;
            }

            return round($total, 2);
        }

        $includedAdults = max(0, $includedAdults);
        $includedChildren = max(0, $includedChildren);
        $includedInfants = max(0, $includedInfants);

        $extraAdults = max($adults - $includedAdults, 0);
        $extraChildren = max($children - $includedChildren, 0);
        $extraInfants = max($infants - $includedInfants, 0);

        $extraAdultRate = (float) ($rate->extra_adult_rate ?? 0);
        $extraBedRate = (float) ($rate->extra_bed_rate ?? 0);
        $childrenRate = (float) ($rate->children_rate ?? 0);
        $infantRate = (float) ($rate->infant_rate ?? 0);

        if ($childrenRate <= 0 && $extraBedRate > 0) {
            $childrenRate = $extraBedRate;
        }

        if ($infantRate <= 0 && $extraBedRate > 0) {
            $infantRate = $extraBedRate;
        }

        $total = $baseRate;
//return round($extraAdultRate, 2);exit;
//$extraAdults.'------'.$extraAdultRate;exit;
        // Charge extra adults exclusively by extra_adult_rate, not extra_bed_rate.
        if ($extraAdults > 0 && $extraAdultRate > 0) {
            $total += $extraAdults * $extraAdultRate;
        }

        if ($extraChildren > 0 && $childrenRate > 0) {
            $total += $extraChildren * $childrenRate;
        }

        if ($extraInfants > 0 && $infantRate > 0) {
            $total += $extraInfants * $infantRate;
        }

        return round($total, 2);
    }

    private function buildActivityAvailability($variants, $rates, $allotments, array $bookingContext): array
    {
        // For activities, we use a single activity_date instead of check_in/check_out range
        $locale = app()->getLocale();
        $activityDate = Carbon::parse($bookingContext['activity_date'] ?? now()->toDateString())->startOfDay();
        $days = [$activityDate->toDateString()]; // Single date for activities

        $variantItems = $variants;
        if ($variantItems->isNotEmpty()) {
            $variantItems = $variantItems->map(function ($variant) use ($locale) {
                return (object) [
                    'variant_id' => $variant->variant_id,
                    'variant_name' => $locale === 'fr' && !empty($variant->variant_name_fr)
                        ? $variant->variant_name_fr
                        : $variant->variant_name,
                    'allotment' => $variant->allotment,
                    'max_participants' => $variant->max_participants,
                    'max_pax' => $variant->max_pax,
                    'quality_tier' => $variant->quality_tier,
                ];
            });
        }

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
                ->filter(fn ($rate) => $this->rateOverlapsActivityDate($rate, $activityDate))
                ->sortBy(function ($rate) use ($bookingContext) {
                    $value = $this->calculateActivityRateTotal(
                        $rate,
                        (int) $bookingContext['adults'], 
                        (int) $bookingContext['children'], 
                        (int) $bookingContext['infants']
                    );

                    return $value !== null ? $value : PHP_INT_MAX;
                })
                ->first();

            $totalPrice = $this->calculateActivityRateTotal(
                $selectedRate,
                (int) $bookingContext['adults'], 
                (int) $bookingContext['children'], 
                (int) $bookingContext['infants']
            );

            $results[] = [
                'room_id' => $variantId,
                'room_name' => (string) ($variant->variant_name ?: 'Standard Option'),
                'room_type' => $variant->quality_tier,
                'quantity' => $quantity,
                'nightly_price' => null,
                'total_price' => $totalPrice,
                'currency' => 'USD',
                'rate_specificity' => $selectedRate?->rate_specificity,
                'adult_rate' => $selectedRate?->adult_rate,
                'teen_rate' => $selectedRate?->teen_rate,
                'children_rate' => $selectedRate?->children_rate,
                'infant_rate' => $selectedRate?->infant_rate,
                'equipment_rate' => $selectedRate?->equipment_rate,
                'private_exclusive_rate' => $selectedRate?->private_exclusive_rate,
                'max_participants' => $variant->max_participants ?? $variant->max_pax,
            ];
        }

        usort($results, function (array $left, array $right) {
            $leftValue = $left['total_price'] ?? PHP_INT_MAX;
            $rightValue = $right['total_price'] ?? PHP_INT_MAX;

            return $leftValue <=> $rightValue;
        });

        return $results;
    }

    private function rateOverlapsActivityDate($rate, Carbon $activityDate): bool
    {
        if (!$rate) {
            return false;
        }

        $validFrom = $rate->valid_from ? Carbon::parse($rate->valid_from)->startOfDay() : null;
        $validTo = $rate->valid_to ? Carbon::parse($rate->valid_to)->endOfDay() : null;

        if ($validFrom && $activityDate->lt($validFrom)) {
            return false;
        }

        if ($validTo && $activityDate->gt($validTo)) {
            return false;
        }

        return true;
    }

    private function calculateActivityRateTotal($rate, int $adults, int $children, int $infants): ?float
    {
        if (!$rate) {
            return null;
        }

        $adults = max(1, $adults);
        $children = max(0, $children);
        $infants = max(0, $infants);
        $participants = max(1, $adults + $children + $infants);

        $rateSpecificity = (string) ($rate->rate_specificity ?? 'Per Person');

        if ($rateSpecificity === 'Per Equipment') {
            $equipmentRate = $rate->equipment_rate !== null ? (float) $rate->equipment_rate : null;
            $base = $equipmentRate !== null && $equipmentRate > 0
                ? $equipmentRate * $participants
                : null;
        } else {
            $adultRate = $rate->adult_rate !== null ? (float) $rate->adult_rate : null;
            $childrenRate = $rate->children_rate !== null ? (float) $rate->children_rate : $adultRate;
            $infantRate = $rate->infant_rate !== null ? (float) $rate->infant_rate : $adultRate;

            $base = null;
            if ($adultRate !== null && $adultRate > 0) {
                $base = $adultRate * $adults;
            }
            if ($childrenRate !== null) {
                $base = ($base ?? 0) + ($childrenRate * $children);
            }
            if ($infantRate !== null) {
                $base = ($base ?? 0) + ($infantRate * $infants);
            }

            if ($base !== null && $base <= 0) {
                $base = null;
            }
        }

        $privateRate = $rate->private_exclusive_rate !== null ? (float) $rate->private_exclusive_rate : null;
        if ($privateRate !== null && $privateRate > 0) {
            if ($base === null) {
                $base = $privateRate;
            } else {
                $base += $privateRate;
            }
        }

        if ($base === null || $base <= 0) {
            return null;
        }

        return round($base, 2);
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
            'state' => trim((string) $request->query('state', '')),
            'check_in' => (string) $request->query('check_in', now()->format('Y-m-d')),
            'check_out' => (string) $request->query('check_out', now()->addDays(2)->format('Y-m-d')),
            'activity_date' => (string) $request->query('activity_date', now()->format('Y-m-d')),
            'type' => trim((string) $request->query('type', '')),
            'name' => trim((string) $request->query('name', '')),
            'adults' => max(1, (int) $request->query('adults', 2)),
            'children' => max(0, (int) $request->query('children', 0)),
            'infants' => max(0, (int) $request->query('infants', 0)),
            'rooms' => max(1, (int) $request->query('rooms', 1)),
            'participants' => max(1, (int) $request->query('participants', 1)),
            'pickup_region_id' => trim((string) $request->query('pickup_region_id', '')),
            'dropoff_region_id' => trim((string) $request->query('dropoff_region_id', '')),
            'transport_from' => trim((string) $request->query('transport_from', '')),
            'transport_to' => trim((string) $request->query('transport_to', '')),
            'service_type' => in_array(trim((string) $request->query('service_type', 'airport_transfer')), ['airport_transfer', 'activity_transfer', 'hotel_transfer', 'full_day_sightseeing', 'half_day_sightseeing'], true)
                ? trim((string) $request->query('service_type', 'airport_transfer'))
                : 'airport_transfer',
            'arrival_date' => (string) $request->query('arrival_date', ''),
            'arrival_time' => trim((string) $request->query('arrival_time', '')),
            'return_date' => (string) $request->query('return_date', ''),
            'return_time' => trim((string) $request->query('return_time', '')),
            'passengers' => max(1, (int) $request->query('passengers', 2)),
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

        $accommodationStates = $this->approvedAccommodationQuery()
            ->whereNotNull('property_name')
            ->pluck('state')
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
            ->pluck('regions')
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

        $placeNames = [];

        if (Schema::hasTable('places')) {
            $placeNames = Place::query()
                ->where('is_active', true)
                ->orderBy('place_name')
                ->pluck('place_name')
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->reject(fn ($value) => in_array(Str::lower($value), ['pick up', 'pickup', 'drop off'], true))
                ->unique()
                ->values()
                ->all();
        }

        $transportRegionOptions = Region::orderBy('name')
            ->pluck('name', 'id')
            ->all();

        if (request()->query('service_type') === 'hotel_transfer') {
            $transportRegionOptions = array_filter($transportRegionOptions, function ($regionName) {
                return !Str::contains(Str::lower((string) $regionName), 'airport');
            });
        }

        if (empty($transportRegionOptions)) {
            $transportRegionOptions = $placeNames;
        }

        return [
            'accommodation' => [
                // structured regions from regions table (use names)
                'regions' => Region::orderBy('name')->pluck('name')->all(),
                // legacy state text values collected from accommodations.state
                'states' => $accommodationStates,
                'types' => $accommodationTypes,
            ],
            'tours' => [
                // use structured regions from regions table (same as accommodation)
                'regions' => Region::orderBy('name')->pluck('name')->all(),
                'types' => $activityTypes,
            ],
            'transport' => [
                'regions' => array_values($transportRegionOptions),
                'types' => [],
                'froms' => $transportRegionOptions,
                'tos' => $transportRegionOptions,
            ],
        ];
    }

    private function applySearchFilters($items, string $category, array $filters)
    {
        // If a structured region was selected (from regions table), filter by it first
        if (!empty($filters['region']) && Str::lower($filters['region']) !== 'all') {
            $region = Str::lower($filters['region']);
            $items = $items->filter(function (array $item) use ($region) {
                return Str::contains(Str::lower((string) ($item['location'] ?? '')), $region);
            });
        }

        // Then apply state (legacy text) filter if provided
        if (!empty($filters['state']) && Str::lower($filters['state']) !== 'all') {
            $state = Str::lower($filters['state']);
            $items = $items->filter(function (array $item) use ($state) {
                return Str::contains(Str::lower((string) ($item['location'] ?? '')), $state);
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

        if ($category === 'transport') {
            $transportFrom = '';
            $transportTo = '';

            if ($filters['pickup_region_id'] !== '') {
                $transportFrom = $this->getRegionNameFromId($filters['pickup_region_id']);
            } elseif ($filters['transport_from'] !== '') {
                $transportFrom = $this->getPlaceRegion($filters['transport_from']);
            }

            if ($filters['dropoff_region_id'] !== '') {
                $transportTo = $this->getRegionNameFromId($filters['dropoff_region_id']);
            } elseif ($filters['transport_to'] !== '') {
                $transportTo = $this->getPlaceRegion($filters['transport_to']);
            }

            // If both pickup and dropoff provided, accept transports that have either
            // a direct A->B route or a reverse B->A route (we'll use reverse pricing later).
            if (!blank($transportFrom) && !blank($transportTo)) {
                $fromExpected = $this->normalizeSelectedRouteRegion($transportFrom);
                $toExpected = $this->normalizeSelectedRouteRegion($transportTo);

                $items = $items->filter(function (array $item) use ($fromExpected, $toExpected) {
                    return collect($item['routes_pricing'] ?? [])->contains(function ($route) use ($fromExpected, $toExpected) {
                        $routeFromNorm = $this->normalizeRouteRegion((string) ($route['route_from'] ?? ''));
                        $routeToNorm = $this->normalizeRouteRegion((string) ($route['route_to'] ?? ''));
                        if (blank($routeFromNorm) || blank($routeToNorm)) {
                            return false;
                        }

                        // direct match A -> B
                        if (Str::lower($routeFromNorm) === Str::lower($fromExpected)
                            && Str::lower($routeToNorm) === Str::lower($toExpected)) {
                            return true;
                        }

                        // reverse match B -> A (operator only added opposite direction)
                        if (Str::lower($routeFromNorm) === Str::lower($toExpected)
                            && Str::lower($routeToNorm) === Str::lower($fromExpected)) {
                            return true;
                        }

                        return false;
                    });
                });
            } else {
                if (!blank($transportFrom)) {
                    $expected = $this->normalizeSelectedRouteRegion($transportFrom);
                    $items = $items->filter(function (array $item) use ($expected) {
                        return collect($item['routes_pricing'] ?? [])->contains(function ($route) use ($expected) {
                            $routeFromNorm = $this->normalizeRouteRegion((string) ($route['route_from'] ?? ''));
                            $routeToNorm = $this->normalizeRouteRegion((string) ($route['route_to'] ?? ''));
                            return (!blank($routeFromNorm) && Str::lower($routeFromNorm) === Str::lower($expected))
                                || (!blank($routeToNorm) && Str::lower($routeToNorm) === Str::lower($expected));
                        });
                    });
                }

                if (!blank($transportTo)) {
                    $expected = $this->normalizeSelectedRouteRegion($transportTo);
                    $items = $items->filter(function (array $item) use ($expected) {
                        return collect($item['routes_pricing'] ?? [])->contains(function ($route) use ($expected) {
                            $routeFromNorm = $this->normalizeRouteRegion((string) ($route['route_from'] ?? ''));
                            $routeToNorm = $this->normalizeRouteRegion((string) ($route['route_to'] ?? ''));
                            return (!blank($routeToNorm) && Str::lower($routeToNorm) === Str::lower($expected))
                                || (!blank($routeFromNorm) && Str::lower($routeFromNorm) === Str::lower($expected));
                        });
                    });
                }
            }

            if (in_array($filters['service_type'], ['airport_transfer', 'activity_transfer', 'hotel_transfer', 'full_day_sightseeing', 'half_day_sightseeing'], true)) {
                $items = $items->filter(function (array $item) use ($filters) {
                    $serviceType = $filters['service_type'];
                    return collect($item['routes_pricing'] ?? [])->contains(function ($route) use ($serviceType) {
                        return trim((string) ($route['service_type'] ?? 'airport_transfer')) === $serviceType;
                    });
                });
            }

            $requestedPassengers = max(1, (int) ($filters['passengers'] ?? 1));
            $items = $items->filter(function (array $item) use ($requestedPassengers) {
                $capacity = isset($item['seating_capacity']) ? (int) $item['seating_capacity'] : 0;
                if ($capacity <= 0) {
                    return true;
                }
                return $requestedPassengers <= $capacity;
            });
        }

        if ($category === 'accommodation' && $filters['rooms'] > 1) {
            $requestedRooms = $filters['rooms'];
            $items = $items->filter(function (array $item) use ($requestedRooms) {
                $availableRooms = isset($item['available_rooms_count']) ? (int) $item['available_rooms_count'] : null;
                if ($availableRooms === null) {
                    return true; // no date-based availability information available
                }
                return $availableRooms >= $requestedRooms;
            });
        }

        if ($category === 'accommodation') {
            $adults = $filters['adults'];
            $children = $filters['children'];
            $infants = $filters['infants'] ?? 0;
            $effectivePersons = $adults + $children + max(0, $infants - 1);

            $items = $items->filter(function (array $item) use ($adults, $children, $infants, $effectivePersons) {
                $roomCatalog = $item['room_catalog'] ?? [];
                foreach ($roomCatalog as $room) {
                    $roomAdults = (int) ($room['capacity'] ?? 0);
                    $roomChildren = (int) ($room['children_capacity'] ?? 0);
                    $roomInfants = (int) ($room['infant_capacity'] ?? 0);
                    $roomMaxPersons = (int) ($room['max_person_capacity'] ?? ($roomAdults + $roomChildren + max(0, $roomInfants - 1)));

                    if ($roomAdults >= $adults
                        && $roomChildren >= $children
                        && $roomInfants >= $infants
                        && $roomMaxPersons >= $effectivePersons) {
                        return true;
                    }
                }

                return false;
            });
        }

        return $items->values();
    }

    private function getPlaceRegion(string $placeName): ?string
    {
        $placeName = trim($placeName);
        if (blank($placeName)) {
            return null;
        }

        $lower = Str::lower($placeName);
        if (in_array($lower, ['north', 'south', 'airport'], true)) {
            return ucfirst($lower);
        }

        return Place::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(REPLACE(TRIM(place_name),"  "," ")) = ?', [preg_replace('/\s+/u', ' ', $lower)])
            ->value('route_region');
    }

    private function getRegionNameFromId(string $regionId): ?string
    {
        if (blank($regionId) || !is_numeric($regionId)) {
            return null;
        }

        return Region::query()
            ->where('id', (int) $regionId)
            ->value('name');
    }

    private function collectSidebarFilters(Request $request, string $category): array
    {
        return match ($category) {
            'tours' => [
                'service_type' => array_values(array_intersect($this->normalizeFilterValues($request->query('service_type', [])), Activity::SERVICE_TYPES)),
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
            'transport' => [
                'vehicle_type' => $this->normalizeFilterValues($request->query('vehicle_type', [])),
                'seating_capacity' => $this->normalizeFilterValues($request->query('seating_capacity', [])),
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

        

        

        if ($category === 'transport') {
            if (!empty($sidebarSelections['vehicle_type'])) {
                $selected = $sidebarSelections['vehicle_type'];
                $items = $items->filter(fn (array $item) => in_array((string) ($item['vehicle_type'] ?? ''), $selected, true));
            }

            if (!empty($sidebarSelections['seating_capacity'])) {
                $selected = array_map('intval', $sidebarSelections['seating_capacity']);
                // interpret seating_capacity filter as minimum required seats — use the highest selected value
                $minSeats = !empty($selected) ? max($selected) : 0;
                if ($minSeats > 0) {
                    $items = $items->filter(function (array $item) use ($minSeats) {
                        $cap = isset($item['seating_capacity']) ? (int) $item['seating_capacity'] : 0;
                        return $cap >= $minSeats;
                    });
                }
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
                    'label' => $this->translateFilterLabel('property_type', 'Property Type'),
                    'options' => $this->buildCountOptions($items->pluck('property_type'), Accommodation::TYPES, 'property_type'),
                ],
                [
                    'key' => 'meal_plan',
                    'label' => $this->translateFilterLabel('meal_plan', 'Meal Plan'),
                    'options' => $this->buildCountOptions($items->pluck('meal_plans'), AccommodationRate::MEAL_PLANS, 'meal_plan'),
                ],
                [
                    'key' => 'budget',
                    'label' => $this->translateFilterLabel('budget', 'Budget Range'),
                    'options' => $this->buildCountOptions($items->pluck('budget_range'), ['Budget', 'Mid Range', 'Top End'], 'budget'),
                ],
            ], fn (array $definition) => !empty($definition['options'])));
        }

        if ($category === 'transport') {
            $vehicleTypes = $items->pluck('vehicle_type')->filter()->map(fn($v) => trim((string) $v))->unique()->sort()->values()->all();
            $seatCaps = $items->pluck('seating_capacity')->filter()->map(fn($v) => (int) $v)->unique()->sort()->values()->map(fn($v) => (string) $v)->all();

            return array_values(array_filter([
                [
                    'key' => 'vehicle_type',
                    'label' => $this->translateFilterLabel('vehicle_type', 'Vehicle Type'),
                    'options' => $this->buildCountOptions($items->pluck('vehicle_type'), $vehicleTypes, 'vehicle_type'),
                ],
                [
                    'key' => 'seating_capacity',
                    'label' => $this->translateFilterLabel('seating_capacity', 'Seat capacity'),
                    'options' => $this->buildCountOptions($items->pluck('seating_capacity')->map(fn($v) => (string) ($v ?? '')), $seatCaps, 'seating_capacity'),
                ],
            ], fn (array $definition) => !empty($definition['options'])));
        }

        if ($category === 'tours') {
            return array_values(array_filter([
                [
                    'key' => 'service_type',
                    'label' => $this->translateFilterLabel('service_type', 'Service Type'),
                    'options' => $this->buildCountOptions($items->pluck('service_type'), Activity::SERVICE_TYPES, 'service_type'),
                ],
                [
                    'key' => 'physical_level',
                    'label' => $this->translateFilterLabel('physical_level', 'Physical Level'),
                    'options' => $this->buildCountOptions($items->pluck('physical_level'), Activity::PHYSICAL_LEVELS, 'physical_level'),
                ],
                [
                    'key' => 'price_range',
                    'label' => $this->translateFilterLabel('price_range', 'Price Range'),
                    'options' => $this->buildCountOptions($items->pluck('price_range'), Activity::PRICE_RANGES, 'price_range'),
                ],
                [
                    'key' => 'primary_theme',
                    'label' => $this->translateFilterLabel('primary_theme', 'Primary Theme'),
                    'options' => $this->buildCountOptions($items->pluck('primary_themes'), Activity::PRIMARY_THEMES, 'primary_theme'),
                ],
                [
                    'key' => 'team_category',
                    'label' => $this->translateFilterLabel('team_category', 'Team Category'),
                    'options' => $this->buildCountOptions($items->pluck('team_categories'), Activity::TEAM_CATEGORIES, 'team_category'),
                ],
                [
                    'key' => 'booking_confirmation_type',
                    'label' => $this->translateFilterLabel('booking_confirmation_type', 'Confirmation'),
                    'options' => $this->buildCountOptions($items->pluck('booking_confirmation_type'), Activity::BOOKING_CONFIRMATION_TYPES, 'booking_confirmation_type'),
                ],
            ], fn (array $definition) => !empty($definition['options'])));
        }

        return [];
    }

    private function buildCountOptions($values, array $preferredOrder = [], string $definitionKey = null): array
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
                'label' => $definitionKey ? $this->translateFilterOption($definitionKey, $value) : $value,
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

    private function translateFilterLabel(string $definitionKey, string $fallback): string
    {
        $translationKey = "category.filter.{$definitionKey}";
        $translated = __($translationKey);

        return $translated === $translationKey ? $fallback : $translated;
    }

    private function translateFilterOption(string $definitionKey, string $value): string
    {
        $slug = Str::slug($value, '_');
        if ($slug === '') {
            $slug = '_';
        }

        $translationKey = "category.filter_options.{$definitionKey}.{$slug}";
        $translated = __($translationKey);

        return $translated === $translationKey ? $value : $translated;
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

    private function approvedActivityQuery(?string $date = null)
    {
        $today = $date ? \Carbon\Carbon::parse($date)->toDateString() : \Carbon\Carbon::today()->toDateString();

        return Activity::query()
            ->where('approval_status', 'Approved')
            ->where('status', Activity::STATUS_ACTIVE)
            ->whereDoesntHave('blackoutDates', function ($query) use ($today) {
                $query->whereDate('start_date', '<=', $today)
                      ->whereDate('end_date', '>=', $today);
            });
    }

    private function approvedTransportQuery()
    {
        return Transport::query()
            ->where('approval_status', 'Approved')
            ->where('status', Transport::STATUS_ACTIVE);
    }

    private function isAccommodationApprovedForFrontend(Accommodation $accommodation): bool
    {
        return (string) $accommodation->approval_status === 'Approved'
            && (string) $accommodation->status === Accommodation::STATUS_ACTIVE;
    }

    private function isActivityApprovedForFrontend(Activity $activity): bool
    {
        return (string) $activity->approval_status === 'Approved'
            && (string) $activity->status === Activity::STATUS_ACTIVE;
    }

    private function isTransportApprovedForFrontend(Transport $transport): bool
    {
        return (string) $transport->approval_status === 'Approved'
            && (string) $transport->status === Transport::STATUS_ACTIVE;
    }

    private function buildTransportBookingContext(Request $request): array
    {
        $defaults = $this->defaultDetailBookingContext();
        $defaultPickup = now()->format('Y-m-d');
        $pickupDate = $this->parseDateInput(
            (string) $request->query('pickup_date', $defaultPickup),
            Carbon::parse($defaultPickup)
        );

        // Return date is optional. If provided, parse and ensure it's after pickup.
        $returnDate = null;
        $rawReturn = (string) $request->query('return_date', '');
        if (!blank($rawReturn)) {
            $parsedReturn = $this->parseDateInput($rawReturn, $pickupDate->copy()->addDay());
            if ($parsedReturn->lte($pickupDate)) {
                $parsedReturn = $pickupDate->copy()->addDay();
            }
            $returnDate = $parsedReturn;
        }

        $passengers = max(1, (int) $request->query('passengers', 1));

        // Optional times (pickup / return). Trip start time should be provided either
        // via the homepage query or entered on the detail page UI.
        $pickupTime = trim((string) $request->query('pickup_time', ''));
        $returnTime = trim((string) $request->query('return_time', ''));

        return [
            'pickup_date' => $pickupDate->toDateString(),
            'pickup_date_display' => $pickupDate->format('d/m/Y'),
            'return_date' => $returnDate ? $returnDate->toDateString() : '',
            'return_date_display' => $returnDate ? $returnDate->format('d/m/Y') : '',
            'passengers' => $passengers,
            'pickup_time' => $pickupTime,
            'return_time' => $returnTime,
        ];
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
                    // Use per-day inventory available_units if provided, otherwise compute from sellable/sold
                    if (isset($inventory->available_units) && $inventory->available_units !== null) {
                        $perDayAvailable = (int) $inventory->available_units;
                    } else {
                        $sellable = (int) ($inventory->sellable_units ?? $roomQuantity);
                        $sold = (int) ($inventory->sold_units ?? 0);
                        $perDayAvailable = max(0, $sellable - $sold);
                    }

                    $availableForRoom = min($availableForRoom, $perDayAvailable);
                    // Inventory rows already account for sold units, so skip subtracting bookings here
                    continue;
                }

                // No inventory row for this date: subtract confirmed bookings overlapping the date
                $conflictingBookings = $accommodation->bookings->filter(function ($booking) use ($date, $room) {
                    return $booking->room_id == $room->id &&
                           in_array($booking->booking_status, ['Confirmed', 'Pending']) &&
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
