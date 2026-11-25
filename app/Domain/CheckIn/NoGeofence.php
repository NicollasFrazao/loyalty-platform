<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

/**
 * No fence: a check-in is accepted from anywhere, and the place is not asked for.
 */
final readonly class NoGeofence implements Geofence
{
    /**
     * Accepts every place.
     */
    public function allows(?GeoPoint $where): bool
    {
        return true;
    }
}
