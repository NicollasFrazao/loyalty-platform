<?php

declare(strict_types=1);

namespace App\Domain\Level;

use App\Domain\Shared\Name;

/**
 * A company's levels, from the first (reached at zero) up, climbed by one metric.
 */
final readonly class LevelLadder
{
    /**
     * The most levels a ladder has.
     */
    public const int MAX_LEVELS = 10;

    /**
     * The levels, by threshold.
     *
     * @var non-empty-list<Level>
     */
    private array $levels;

    /**
     * Holds checked levels; use {@see LevelLadder::of()} to build one.
     */
    private function __construct(public LevelMetric $metric, Level $first, Level ...$others)
    {
        $this->levels = [$first, ...array_values($others)];
    }

    /**
     * The ladder of a company that configured none: one level, "Member", with no cashback.
     */
    public static function default(): self
    {
        return new self(LevelMetric::CheckIns, new Level(Name::from('Member'), 0, CashbackRate::of(0)));
    }

    /**
     * The levels, checked.
     *
     * @throws InvalidLadder when there are no levels or more than MAX_LEVELS, the first is not reached at
     *                       zero, the thresholds do not grow, or two levels share a name.
     */
    public static function of(LevelMetric $metric, Level ...$levels): self
    {
        $levels = array_values($levels);

        if ($levels === [] || count($levels) > self::MAX_LEVELS) {
            throw InvalidLadder::because('a company has from 1 to '.self::MAX_LEVELS.' levels');
        }

        if ($levels[0]->threshold !== 0) {
            throw InvalidLadder::because('the first level is reached at 0');
        }

        foreach (array_slice($levels, 1) as $index => $level) {
            if ($level->threshold <= $levels[$index]->threshold) {
                throw InvalidLadder::because('each level needs more than the one before');
            }
        }

        $names = array_map(static fn (Level $level): string => mb_strtolower($level->name->value), $levels);

        if (count(array_unique($names)) !== count($names)) {
            throw InvalidLadder::because('two levels share a name');
        }

        return new self($metric, ...$levels);
    }

    /**
     * The levels, by threshold.
     *
     * @return non-empty-list<Level>
     */
    public function levels(): array
    {
        return $this->levels;
    }

    /**
     * The highest level the progress reaches.
     */
    public function levelFor(Progress $progress): Level
    {
        $amount = $progress->on($this->metric);
        $reached = $this->levels[0];

        foreach ($this->levels as $level) {
            if ($level->threshold > $amount) {
                break;
            }

            $reached = $level;
        }

        return $reached;
    }

    /**
     * The level after the one reached by the progress, if any.
     */
    public function nextFor(Progress $progress): ?Level
    {
        $amount = $progress->on($this->metric);

        foreach ($this->levels as $level) {
            if ($level->threshold > $amount) {
                return $level;
            }
        }

        return null;
    }
}
