<?php

namespace App\Services;

use App\Models\Transport;
use App\Models\TransportVehicleName;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransportListingEditor
{
    public function vehicleNamesFor(Transport $transport)
    {
        return TransportVehicleName::query()
            ->where(function ($query) use ($transport): void {
                $query->where(function ($activeQuery): void {
                    $activeQuery->active()
                        ->whereHas('vehicleType', fn ($typeQuery) => $typeQuery->active());
                })->orWhere(function ($currentQuery) use ($transport): void {
                    if ($transport->vehicle_name_id) {
                        $currentQuery->whereKey($transport->vehicle_name_id);
                    } else {
                        $currentQuery->where('name', trim((string) $transport->vehicle_name))
                            ->whereHas('vehicleType', fn ($typeQuery) => $typeQuery
                                ->where('name', trim((string) $transport->vehicle_type)));
                    }
                });
            })
            ->with('vehicleType')
            ->orderBy('name')
            ->get();
    }

    public function update(Transport $transport, Request $request): void
    {
        $data = $request->validate([
            'vehicle_name_id' => ['required', 'integer', 'exists:transport_vehicle_names,id'],
            'registration_number' => ['nullable', 'string', 'max:50'],
            'service_description' => ['nullable', 'string', 'max:65535'],
            'overview' => ['nullable', 'string'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:25'],
            'contact_email' => ['nullable', 'email', 'max:191'],
        ]);

        $vehicleName = TransportVehicleName::query()
            ->whereKey($data['vehicle_name_id'])
            ->with('vehicleType')
            ->firstOrFail();
        $isCurrentVehicleName = (int) $vehicleName->id === (int) $transport->vehicle_name_id
            || ($vehicleName->name === trim((string) $transport->vehicle_name)
                && $vehicleName->vehicleType?->name === trim((string) $transport->vehicle_type));
        if ((! $vehicleName->is_active || ! $vehicleName->vehicleType?->is_active) && ! $isCurrentVehicleName) {
            throw ValidationException::withMessages([
                'vehicle_name_id' => 'Select an active vehicle name and vehicle type.',
            ]);
        }

        $attributes = [
            ...$data,
            'vehicle_name' => $vehicleName->name,
            'vehicle_type' => $vehicleName->vehicleType->name,
            'vehicle_name_id' => $vehicleName->id,
        ];
        if ($transport->operator?->country_id) {
            $attributes['country_id'] = $transport->operator->country_id;
        }

        $transport->update($attributes);
    }
}
