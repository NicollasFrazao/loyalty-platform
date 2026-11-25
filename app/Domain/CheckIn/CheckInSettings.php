<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

/**
 * A company's check-in rules: the cooldown between two check-ins of a customer, whether a staff
 * member confirms each one, and the geofence.
 */
final readonly class CheckInSettings
{
    /**
     * The cooldown of a company that configured none: four hours.
     */
    public const int DEFAULT_COOLDOWN = 240;

    /**
     * The longest cooldown: a week.
     */
    public const int MAX_COOLDOWN = 10_080;

    /**
     * Holds the rules; use {@see CheckInSettings::of()} to build them.
     */
    private function __construct(
        public int $cooldownMinutes,
        public bool $requiresConfirmation,
        public Geofence $geofence,
    ) {}

    /**
     * The rules of a company that configured none: four hours, no confirmation, no fence.
     */
    public static function defaults(): self
    {
        return new self(self::DEFAULT_COOLDOWN, false, new NoGeofence);
    }

    /**
     * The rules, checked.
     *
     * @throws InvalidCheckInSettings when the cooldown is negative or longer than a week.
     */
    public static function of(int $cooldownMinutes, bool $requiresConfirmation, Geofence $geofence): self
    {
        if (
            $cooldownMinutes < 0
            || $cooldownMinutes > self::MAX_COOLDOWN
        ) {
            throw InvalidCheckInSettings::of(CheckInField::Cooldown, 'between 0 and '.self::MAX_COOLDOWN.' minutes');
        }

        return new self($cooldownMinutes, $requiresConfirmation, $geofence);
    }

    /**
     * The status a new check-in starts with.
     */
    public function initialStatus(): CheckInStatus
    {
        return $this->requiresConfirmation ? CheckInStatus::Pending : CheckInStatus::Confirmed;
    }
}
