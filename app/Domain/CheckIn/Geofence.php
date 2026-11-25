<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

/**
 * Where a customer must be to check in at a company: the pluggable part of the check-in rules. A new
 * kind of fence (a polygon, a beacon) is a new implementation.
 */
interface Geofence
{
    /**
     * Tells whether a check-in from the point, or from an unknown place, is accepted.
     */
    public function allows(?GeoPoint $where): bool;
}
