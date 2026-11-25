<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

/**
 * A place on Earth in decimal degrees (WGS 84), as a phone reports it.
 */
final readonly class GeoPoint
{
    /**
     * The mean radius of the Earth, in metres.
     */
    private const int EARTH_RADIUS = 6_371_000;

    /**
     * The largest latitude, north or south.
     */
    private const int MAX_LATITUDE = 90;

    /**
     * The largest longitude, east or west.
     */
    private const int MAX_LONGITUDE = 180;

    /**
     * Holds the degrees; use {@see GeoPoint::at()} to build one.
     */
    private function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    /**
     * The point at the coordinates.
     *
     * @throws InvalidCheckInSettings when a coordinate is outside its range.
     */
    public static function at(float $latitude, float $longitude): self
    {
        if (abs($latitude) > self::MAX_LATITUDE) {
            throw InvalidCheckInSettings::of(CheckInField::Latitude, 'between -90 and 90');
        }

        if (abs($longitude) > self::MAX_LONGITUDE) {
            throw InvalidCheckInSettings::of(CheckInField::Longitude, 'between -180 and 180');
        }

        return new self($latitude, $longitude);
    }

    /**
     * The great-circle distance to another point, in metres (haversine).
     */
    public function distanceTo(self $other): float
    {
        $latitudeDelta = deg2rad($other->latitude - $this->latitude);
        $longitudeDelta = deg2rad($other->longitude - $this->longitude);
        $chord = (sin($latitudeDelta / 2) ** 2)
            + (cos(deg2rad($this->latitude)) * cos(deg2rad($other->latitude)) * (sin($longitudeDelta / 2) ** 2));

        return 2 * self::EARTH_RADIUS * asin(min(1.0, sqrt($chord)));
    }
}
