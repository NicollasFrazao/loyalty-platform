<?php

declare(strict_types=1);

namespace App\Domain\Shared;

/**
 * Something that happened in the domain, which later phases react to (a level to recalculate, a
 * notification to send). Events are immutable and named in the past tense.
 */
interface DomainEvent {}
