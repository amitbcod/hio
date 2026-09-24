<?php

namespace App\Services;

use App\Models\Transport;
use App\Models\TransportBooking;
use App\Models\TransportServiceRoutePair;
use App\Models\TransportVehicle;
use App\Models\OperatorDriver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class TransportAvailabilityService
{
    public function bookingWindow(array $bookingData): ?array
    {
        $routeFrom = trim((string) ($bookingData['route_from'] ?? ''));
        $routeTo = trim((string) ($bookingData['route_to'] ?? ''));
        $pickupDate = $bookingData['pickup_date'] ?? null;
        $pickupTime = $bookingData['pickup_time'] ?? null;

        if ($pickupDate === null || $pickupTime === null || $routeFrom === '' || $routeTo === '') {
            return null;
        }

        $pair = TransportServiceRoutePair::query()
            ->where('is_active', true)
            ->where(function ($query) use ($routeFrom, $routeTo) {
                $query->where(function ($q) use ($routeFrom, $routeTo) {
                    $q->whereRaw('LOWER(TRIM(route_from)) = ?', [strtolower($routeFrom)])
                        ->whereRaw('LOWER(TRIM(route_to)) = ?', [strtolower($routeTo)]);
                })->orWhere(function ($q) use ($routeFrom, $routeTo) {
                    $q->whereRaw('LOWER(TRIM(route_from)) = ?', [strtolower($routeTo)])
                        ->whereRaw('LOWER(TRIM(route_to)) = ?', [strtolower($routeFrom)]);
                });
            })->first();

        $totalMinutes = ((int) ($pair->trip_time_minutes ?? 0)) + ((int) ($pair->buffer_time_minutes ?? 0));
        if ($totalMinutes <= 0) {
            $totalMinutes = 90;
        }

        $start = Carbon::parse($pickupDate . ' ' . $pickupTime);
        return [
            'start' => $start,
            'end' => $start->copy()->addMinutes($totalMinutes),
            'trip_minutes' => (int) ($pair->trip_time_minutes ?? 60),
            'buffer_minutes' => (int) ($pair->buffer_time_minutes ?? 30),
        ];
    }

    public function overlappingBookings(Transport $transport, array $bookingData, ?int $ignoreBookingId = null)
    {
        $window = $this->bookingWindow($bookingData);
        if (!$window) {
            return collect();
        }

        $bookings = $transport->bookings()
            ->where('booking_status', '!=', 'Cancelled')
            ->whereNotNull('pickup_date')
            ->whereNotNull('pickup_time')
            ->when($ignoreBookingId, fn ($query) => $query->where('id', '!=', $ignoreBookingId))
            ->get();

        return $bookings->filter(function (TransportBooking $booking) use ($window) {
            $bookingWindow = $this->bookingWindow([
                'route_from' => $booking->route_from,
                'route_to' => $booking->route_to,
                'pickup_date' => $booking->pickup_date?->toDateString(),
                'pickup_time' => $booking->pickup_time,
            ]);

            return $bookingWindow
                && $window['start']->lt($bookingWindow['end'])
                && $window['end']->gt($bookingWindow['start']);
        });
    }

    public function availableVehicles(Transport $transport, array $bookingData = [], ?int $ignoreBookingId = null): int
    {
        if (!Schema::hasTable('transport_vehicles')) {
            return $transport->bookings()
                ->where('booking_status', '!=', 'Cancelled')
                ->when($ignoreBookingId, fn ($query) => $query->where('id', '!=', $ignoreBookingId))
                ->count() > 0 ? 0 : 1;
        }

        $vehicles = $transport->vehicles()->active()->where(function ($query) {
            $query->whereNull('license_expiry_date')
                ->orWhereDate('license_expiry_date', '>=', now()->toDateString());
        })->where(function ($query) {
            $query->whereNull('insurance_expiry_date')
                ->orWhereDate('insurance_expiry_date', '>=', now()->toDateString());
        })->get();
        if ($vehicles->isEmpty()) {
            return $transport->bookings()
                ->where('booking_status', '!=', 'Cancelled')
                ->when($ignoreBookingId, fn ($query) => $query->where('id', '!=', $ignoreBookingId))
                ->count() > 0 ? 0 : 1;
        }

        $overlapping = $this->overlappingBookings($transport, $bookingData, $ignoreBookingId);
        $bookedVehicleIds = $overlapping->pluck('transport_vehicle_id')->filter()->unique();
        $legacyUnassigned = $overlapping->whereNull('transport_vehicle_id')->count();

        return max(0, $vehicles->count() - $bookedVehicleIds->count() - $legacyUnassigned);
    }

    public function assignAvailableVehicle(Transport $transport, array $bookingData, ?int $ignoreBookingId = null): ?TransportVehicle
    {
        if (!Schema::hasTable('transport_vehicles')) {
            return null;
        }

        $overlapping = $this->overlappingBookings($transport, $bookingData, $ignoreBookingId);
        $bookedVehicleIds = $overlapping->pluck('transport_vehicle_id')->filter()->unique();

        return $transport->vehicles()->active()
            ->where(function ($query) {
                $query->whereNull('license_expiry_date')
                    ->orWhereDate('license_expiry_date', '>=', now()->toDateString());
            })
            ->where(function ($query) {
                $query->whereNull('insurance_expiry_date')
                    ->orWhereDate('insurance_expiry_date', '>=', now()->toDateString());
            })
            ->whereNotIn('id', $bookedVehicleIds)
            ->orderBy('id')
            ->first();
    }

    public function availableVehicleModels(Transport $transport, array $bookingData, ?int $ignoreBookingId = null)
    {
        if (!Schema::hasTable('transport_vehicles')) {
            return collect();
        }

        $overlapping = $this->overlappingBookings($transport, $bookingData, $ignoreBookingId);
        $bookedVehicleIds = $overlapping->pluck('transport_vehicle_id')->filter()->unique();

        return $transport->vehicles()->active()
            ->where(function ($query) {
                $query->whereNull('license_expiry_date')
                    ->orWhereDate('license_expiry_date', '>=', now()->toDateString());
            })
            ->where(function ($query) {
                $query->whereNull('insurance_expiry_date')
                    ->orWhereDate('insurance_expiry_date', '>=', now()->toDateString());
            })
            ->whereNotIn('id', $bookedVehicleIds)
            ->orderBy('license_number')
            ->get();
    }

    public function availableVehicleModelsForBooking(Transport $transport, TransportBooking $booking)
    {
        if (!Schema::hasTable('transport_vehicles')) {
            return collect();
        }

        $requestedWindows = $this->bookingWindows($booking);
        $overlapping = $transport->bookings()
            ->where('id', '!=', $booking->id)
            ->where('booking_status', '!=', 'Cancelled')
            ->with('vehicle')
            ->get()
            ->filter(function (TransportBooking $existing) use ($requestedWindows) {
                foreach ($this->bookingWindows($existing) as $existingWindow) {
                    foreach ($requestedWindows as $window) {
                        if ($window['start']->lt($existingWindow['end']) && $window['end']->gt($existingWindow['start'])) {
                            return true;
                        }
                    }
                }
                return false;
            });

        $bookedVehicleIds = $overlapping->pluck('transport_vehicle_id')->filter()->unique();

        return $transport->vehicles()->active()
            ->where(function ($query) {
                $query->whereNull('license_expiry_date')
                    ->orWhereDate('license_expiry_date', '>=', now()->toDateString());
            })
            ->where(function ($query) {
                $query->whereNull('insurance_expiry_date')
                    ->orWhereDate('insurance_expiry_date', '>=', now()->toDateString());
            })
            ->whereNotIn('id', $bookedVehicleIds)
            ->orderBy('license_number')
            ->get();
    }

    public function bookingWindows(TransportBooking $booking): array
    {
        $windows = [];
        $pickupWindow = $this->bookingWindow([
            'route_from' => $booking->route_from,
            'route_to' => $booking->route_to,
            'pickup_date' => $booking->pickup_date?->toDateString(),
            'pickup_time' => $booking->pickup_time,
        ]);
        if ($pickupWindow) {
            $windows[] = $pickupWindow;
        }

        if ($booking->return_date && $booking->return_time) {
            $returnWindow = $this->bookingWindow([
                'route_from' => $booking->route_to,
                'route_to' => $booking->route_from,
                'pickup_date' => $booking->return_date->toDateString(),
                'pickup_time' => $booking->return_time,
            ]);
            if ($returnWindow) {
                $windows[] = $returnWindow;
            }
        }

        return $windows;
    }

    public function availableDrivers($operator, TransportBooking $booking): \Illuminate\Support\Collection
    {
        $windows = $this->bookingWindows($booking);
        if (empty($windows)) {
            return collect();
        }

        $drivers = OperatorDriver::query()
            ->where('driver_status', OperatorDriver::STATUS_ACTIVE)
            ->where(function ($query) use ($operator) {
                $query->where('operator_id', $operator->operator_id);
                if (!empty($operator->business_id)) {
                    $query->orWhere('business_id', $operator->business_id);
                }
            })
            ->where(function ($query) {
                $query->whereNull('license_expiry_date')
                    ->orWhereDate('license_expiry_date', '>=', now()->toDateString());
            })
            ->get();

        $bookings = TransportBooking::query()
            ->where('id', '!=', $booking->id)
            ->where('booking_status', '!=', 'Cancelled')
            ->where(function ($query) use ($drivers) {
                $ids = $drivers->pluck('id');
                $query->whereIn('driver_id', $ids)
                    ->orWhereIn('pickup_driver_id', $ids)
                    ->orWhereIn('return_driver_id', $ids);
            })
            ->get();

        $unavailable = $bookings->filter(function (TransportBooking $existing) use ($windows) {
            foreach ($this->bookingWindows($existing) as $existingWindow) {
                foreach ($windows as $window) {
                    if ($window['start']->lt($existingWindow['end']) && $window['end']->gt($existingWindow['start'])) {
                        return true;
                    }
                }
            }
            return false;
        })->flatMap(fn (TransportBooking $existing) => [
            $existing->driver_id,
            $existing->pickup_driver_id,
            $existing->return_driver_id,
        ])->filter()->unique();

        return $drivers->whereNotIn('id', $unavailable->all())->values();
    }
}