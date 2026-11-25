<?php

declare(strict_types=1);

namespace App\Domain\Level;

use App\Domain\Shared\Name;

/**
 * A rank of a company's ladder: reached at the threshold of the ladder's metric, it sets the
 * customer's cashback.
 */
final readonly class Level
{
    /**
     * Describes the level.
     *
     * @param  int  $threshold  Check-ins, or cents spent, from which the level is reached.
     */
    public function __construct(
        public Name $name,
        public int $threshold,
        public CashbackRate $cashback,
    ) {}
}
