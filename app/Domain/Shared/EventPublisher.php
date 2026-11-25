<?php

declare(strict_types=1);

namespace App\Domain\Shared;

/**
 * Hands domain events to whoever listens (port; implemented in Infrastructure with Laravel's
 * dispatcher).
 */
interface EventPublisher
{
    /**
     * Publishes the events, in order.
     */
    public function publish(DomainEvent ...$events): void;
}
