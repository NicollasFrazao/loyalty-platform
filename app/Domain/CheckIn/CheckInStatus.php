<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

/**
 * Where a check-in stands: waiting for a staff member, counted, or turned down.
 */
enum CheckInStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';
}
