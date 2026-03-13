<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Activity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
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

        $location = implode(' • ', array_filter([
            $activity->destination,
            $activity->region,
            $activity->town,
        ]));

        return [
            'id' => $activity->id,
            'kind' => 'Activity',
            'title' => $activity->activity_name,
            'meta' => $activity->service_type ?: 'Experience',
            'status' => $activity->approval_status ?? $activity->status,
            'location' => $location ?: 'Mauritius',
            'excerpt' => Str::limit($shortDescription ?: $overviewText ?: 'New activity listing added by operator.', 130),
            'image' => $primaryImage,
            'url' => route('frontend.activities.show', $activity),
            'duration' => $activity->duration,
            'booking_confirmation_type' => $activity->booking_confirmation_type,
            'languages' => array_values($activity->languages_offered ?? []),
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

        return [
            'id' => $accommodation->id,
            'kind' => 'Accommodation',
            'title' => $accommodation->property_name,
            'property_type' => $accommodation->property_type,
            'meta' => $accommodation->property_type,
            'status' => $accommodation->approval_status ?? $accommodation->status,
            'location' => $location ?: 'Mauritius',
            'excerpt' => Str::limit($shortDescription ?: 'New accommodation listing added by operator.', 130),
            'image' => $primaryImage,
            'url' => route('frontend.accommodations.show', $accommodation),
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
}
