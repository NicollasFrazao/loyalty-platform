<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

/**
 * A circle around the company: a check-in must come from inside it, with the place reported.
 */
final readonly class RadiusGeofence implements Geofence
{
    /**
     * The smallest circle, below what a phone's position can tell.
     */
    public const int MIN_RADIUS = 20;

    /**
     * The largest circle.
     */
    public const int MAX_RADIUS = 5_000;

    /**
     * Holds the circle; use {@see RadiusGeofence::around()} to build one.
     */
    private function __construct(
        public GeoPoint $centre,
        public int $radiusMetres,
    ) {}

    /**
     * The circle of the radius around the point.
     *
     * @throws InvalidCheckInSettings when the radius is outside MIN_RADIUS to MAX_RADIUS metres.
     */
    public static function around(GeoPoint $centre, int $radiusMetres): self
    {
        if (
            $radiusMetres < self::MIN_RADIUS
            || $radiusMetres > self::MAX_RADIUS
        ) {
            throw InvalidCheckInSettings::of(CheckInField::Radius, 'between '.self::MIN_RADIUS.' and '.self::MAX_RADIUS.' metres');
        }

        return new self($centre, $radiusMetres);
    }

    /**
     * Accepts a reported place inside the circle.
     */
    public function allows(?GeoPoint $where): bool
    {
        return $where instanceof GeoPoint && $where->distanceTo($this->centre) <= $this->radiusMetres;
    }
}
