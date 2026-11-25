<?php

declare(strict_types=1);

namespace App\Application\CheckIn;

/**
 * The check-in settings as the back-office form sends them; the fence is off when its fields are
 * null.
 */
final readonly class CheckInSettingsInput
{
    /**
     * Holds the values.
     */
    public function __construct(
        public int $cooldownMinutes,
        public bool $requiresConfirmation,
        public ?FenceInput $fence,
    ) {}
}
