<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\CheckIn\CheckIn;
use App\Domain\CheckIn\CheckInId;
use App\Domain\CheckIn\CheckInSettings;
use App\Domain\CheckIn\CheckInStatus;
use App\Domain\CheckIn\GeoPoint;
use App\Domain\CheckIn\InvalidCheckInSettings;
use App\Domain\CheckIn\NoGeofence;
use App\Domain\CheckIn\RadiusGeofence;
use App\Domain\Customer\CustomerId;
use App\Infrastructure\Persistence\Eloquent\CheckInRecord;
use App\Infrastructure\Persistence\Eloquent\CheckInSettingsRecord;
use UnexpectedValueException;
use ValueError;

/**
 * Maps the rows of `check_in_settings` and `check_ins` to the domain and back, for
 * {@see EloquentCheckInRepository}.
 */
final class CheckInRows
{
    /**
     * The rules a row stands for; the defaults when there is no row.
     *
     * @throws UnexpectedValueException when the row holds a value no form writes.
     */
    public static function toSettings(?CheckInSettingsRecord $record): CheckInSettings
    {
        if (! $record instanceof CheckInSettingsRecord) {
            return CheckInSettings::defaults();
        }

        try {
            $fence = $record->fence_latitude === null || $record->fence_longitude === null || $record->fence_radius_metres === null
                ? new NoGeofence
                : RadiusGeofence::around(GeoPoint::at((float) $record->fence_latitude, (float) $record->fence_longitude), $record->fence_radius_metres);

            return CheckInSettings::of($record->cooldown_minutes, $record->requires_confirmation, $fence);
        } catch (InvalidCheckInSettings $invalid) {
            throw new UnexpectedValueException("The check-in settings of company {$record->company_id} hold a value no form writes.", 0, $invalid);
        }
    }

    /**
     * The columns of the rules.
     *
     * @return array{cooldown_minutes: int, requires_confirmation: bool, fence_latitude: float|null, fence_longitude: float|null, fence_radius_metres: int|null}
     */
    public static function settingsColumns(CheckInSettings $settings): array
    {
        $fence = $settings->geofence instanceof RadiusGeofence ? $settings->geofence : null;

        return [
            'cooldown_minutes' => $settings->cooldownMinutes,
            'requires_confirmation' => $settings->requiresConfirmation,
            'fence_latitude' => $fence?->centre->latitude,
            'fence_longitude' => $fence?->centre->longitude,
            'fence_radius_metres' => $fence?->radiusMetres,
        ];
    }

    /**
     * The check-in a row stands for.
     *
     * @throws UnexpectedValueException when the row holds a status no code writes.
     */
    public static function toCheckIn(CheckInRecord $record): CheckIn
    {
        try {
            $status = CheckInStatus::from($record->status);
        } catch (ValueError $invalid) {
            throw new UnexpectedValueException("The check-in {$record->id} holds a status no code writes.", 0, $invalid);
        }

        return new CheckIn(new CheckInId($record->id), new CustomerId($record->customer_id), $record->checked_in_at, $status);
    }
}
