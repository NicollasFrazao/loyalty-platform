<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

use DateInterval;
use DateTimeImmutable;

/**
 * The anti-abuse rules a check-in passes before it is recorded (domain service).
 */
final class CheckInRules
{
    /**
     * Passes when the cooldown since the customer's last check-in is over and the place is inside the
     * fence.
     *
     * @param  DateTimeImmutable|null  $last  The customer's last check-in at the company that was not
     *                                        rejected, if any.
     *
     * @throws CheckInTooSoon when the cooldown is not over.
     * @throws OutsideGeofence when the fence does not accept the place.
     */
    public static function ensureAllowed(CheckInSettings $settings, ?DateTimeImmutable $last, NewCheckIn $checkIn): void
    {
        if ($last instanceof DateTimeImmutable) {
            $nextAllowedAt = $last->add(new DateInterval("PT{$settings->cooldownMinutes}M"));

            if ($checkIn->at < $nextAllowedAt) {
                throw CheckInTooSoon::until($nextAllowedAt);
            }
        }

        if (! $settings->geofence->allows($checkIn->where)) {
            throw OutsideGeofence::create();
        }
    }
}
