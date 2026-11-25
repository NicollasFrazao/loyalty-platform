<?php

declare(strict_types=1);

namespace App\Application\Level;

/**
 * One level as the back-office form sends it.
 */
final readonly class LevelInput
{
    /**
     * Holds the values as typed; the cashback is a percentage such as "2.5".
     */
    public function __construct(
        public string $name,
        public int $threshold,
        public string $cashbackPercent,
    ) {}
}
