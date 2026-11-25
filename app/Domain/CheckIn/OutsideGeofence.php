<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

/**
 * The check-in came from outside the company's fence, or without the place the fence needs.
 */
final class OutsideGeofence extends CheckInRefused
{
    /**
     * The error.
     */
    public static function create(): self
    {
        return new self('Check in from the company: your location is outside its area or was not sent.');
    }
}
