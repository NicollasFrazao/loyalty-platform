<?php

declare(strict_types=1);

namespace App\Domain\Level;

/**
 * How far a customer has come at a company: confirmed check-ins and money spent, in cents.
 */
final readonly class Progress
{
    /**
     * Describes the progress.
     */
    public function __construct(
        public int $checkIns,
        public int $spentCents,
    ) {}

    /**
     * The amount the metric measures.
     */
    public function on(LevelMetric $metric): int
    {
        return match ($metric) {
            LevelMetric::CheckIns => $this->checkIns,
            LevelMetric::Spend => $this->spentCents,
        };
    }
}
