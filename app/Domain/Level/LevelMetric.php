<?php

declare(strict_types=1);

namespace App\Domain\Level;

/**
 * What a company's levels are climbed by: confirmed check-ins, or money spent (in cents).
 */
enum LevelMetric: string
{
    case CheckIns = 'check_ins';
    case Spend = 'spend';
}
