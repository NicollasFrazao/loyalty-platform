<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

/**
 * The values of a check-in and of a company's check-in settings, named as the forms and the API
 * name them.
 */
enum CheckInField: string
{
    case Latitude = 'latitude';
    case Longitude = 'longitude';
    case Cooldown = 'cooldown_minutes';
    case Radius = 'radius_metres';
}
