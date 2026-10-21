<?php

namespace App\Support;

class TransportVoucherDisplay
{
    public static function routeLabel(?string $from, ?string $to): string
    {
        $locations = array_values(array_filter(
            array_map(static fn (?string $location): string => trim((string) $location), [$from, $to]),
            static fn (string $location): bool => $location !== ''
        ));

        return $locations === [] ? 'N/A' : implode(' - ', $locations);
    }
}
