<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Application\Level\StandingView;
use App\Domain\CheckIn\CheckIn;
use App\Domain\CheckIn\CheckInRefused;
use App\Domain\CheckIn\CheckInTooSoon;
use App\Domain\Level\Level;
use DateTimeInterface;

/**
 * The JSON of check-ins and standings, as `docs/api/openapi.json` describes it.
 */
final class CheckInJson
{
    /**
     * A check-in.
     *
     * @return array{id: int, status: string, checked_in_at: string}
     */
    public static function checkIn(CheckIn $checkIn): array
    {
        return [
            'id' => $checkIn->id->value,
            'status' => $checkIn->status->value,
            'checked_in_at' => $checkIn->at->format(DateTimeInterface::ATOM),
        ];
    }

    /**
     * A refused check-in: the message, and when the next one opens if the cooldown refused it.
     *
     * @return array{message: string, next_allowed_at: string|null}
     */
    public static function refusal(CheckInRefused $refused): array
    {
        return [
            'message' => $refused->getMessage(),
            'next_allowed_at' => $refused instanceof CheckInTooSoon ? $refused->nextAllowedAt->format(DateTimeInterface::ATOM) : null,
        ];
    }

    /**
     * A customer's standing at a company.
     *
     * @return array{metric: string, check_ins: int, spent_cents: int, level: array{name: string, threshold: int, cashback_percent: string}, next_level: array{name: string, threshold: int, cashback_percent: string}|null, remaining: int|null}
     */
    public static function standing(StandingView $standing): array
    {
        return [
            'metric' => $standing->metric->value,
            'check_ins' => $standing->progress->checkIns,
            'spent_cents' => $standing->progress->spentCents,
            'level' => self::level($standing->level),
            'next_level' => $standing->next instanceof Level ? self::level($standing->next) : null,
            'remaining' => $standing->remaining(),
        ];
    }

    /**
     * A level.
     *
     * @return array{name: string, threshold: int, cashback_percent: string}
     */
    private static function level(Level $level): array
    {
        return ['name' => $level->name->value, 'threshold' => $level->threshold, 'cashback_percent' => $level->cashback->percent()];
    }
}
