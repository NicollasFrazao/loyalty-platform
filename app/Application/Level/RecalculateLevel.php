<?php

declare(strict_types=1);

namespace App\Application\Level;

use App\Domain\CheckIn\CheckInCounted;
use App\Domain\Level\CustomerStandings;
use App\Domain\Level\LadderRepository;
use App\Domain\Level\LevelCalculator;
use App\Domain\Level\LevelReached;
use App\Domain\Level\Standing;
use App\Domain\Shared\EventPublisher;

/**
 * Recalculates a customer's level at a company when their progress changes, and publishes the level
 * they reach (use case; listens to {@see CheckInCounted}).
 */
final readonly class RecalculateLevel
{
    /**
     * Takes the ladders, the standings and the publisher.
     */
    public function __construct(
        private LadderRepository $ladders,
        private CustomerStandings $standings,
        private EventPublisher $events,
    ) {}

    /**
     * Records the customer's level when it changed, publishing {@see LevelReached}.
     */
    public function handle(Standing $standing): void
    {
        $reached = LevelCalculator::reached(
            $this->ladders->for($standing->company),
            $this->standings->progress($standing),
            $this->standings->level($standing),
            $standing,
        );

        if (! $reached instanceof LevelReached) {
            return;
        }

        $this->standings->recordLevel($standing, $reached->level);
        $this->events->publish($reached);
    }

    /**
     * Recalculates on a counted check-in.
     */
    public function onCheckIn(CheckInCounted $event): void
    {
        $this->handle(new Standing($event->company(), $event->customer()));
    }
}
