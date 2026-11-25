<?php

declare(strict_types=1);

namespace App\Domain\Level;


/**
 * Computes a customer's level at a company from the ladder and their progress, and tells when it
 * changed (domain service).
 */
final class LevelCalculator
{
    /**
     * The event of the customer's new level, or null when they stay where they are.
     *
     * @param  string|null  $current  The name of the level recorded for the customer, if any.
     */
    public static function reached(LevelLadder $ladder, Progress $progress, ?string $current, Standing $standing): ?LevelReached
    {
        $level = $ladder->levelFor($progress);

        if ($level->name->value === $current) {
            return null;
        }

        return new LevelReached($standing->company, $standing->customer, $level, $current);
    }
}
