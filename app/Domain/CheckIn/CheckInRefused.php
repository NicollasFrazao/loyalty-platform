<?php

declare(strict_types=1);

namespace App\Domain\CheckIn;

use App\Domain\Shared\DomainError;

/**
 * A check-in broke one of the company's anti-abuse rules and was not recorded.
 */
abstract class CheckInRefused extends DomainError {}
