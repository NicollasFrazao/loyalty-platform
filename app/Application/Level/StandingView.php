<?php

declare(strict_types=1);

namespace App\Application\Level;

use App\Domain\Level\Level;
use App\Domain\Level\LevelMetric;
use App\Domain\Level\Progress;

/**
 * Where a customer stands at a company: their level, their progress and the next level, if any.
 */
final readonly class StandingView
{
    /**
     * Describes the standing.
     */
    public function __construct(
        public Level $level,
        public LevelMetric $metric,
        public Progress $progress,
        public ?Level $next,
    ) {}

    /**
     * What the customer still needs, on the ladder's metric, to reach the next level.
     */
    public function remaining(): ?int
    {
        return $this->next instanceof Level ? $this->next->threshold - $this->progress->on($this->metric) : null;
    }
}
