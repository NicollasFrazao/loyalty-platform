<?php

declare(strict_types=1);

namespace App\Infrastructure\Events;

use App\Domain\Shared\DomainEvent;
use App\Domain\Shared\EventPublisher;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Publishes domain events through Laravel's dispatcher, where the providers register the listeners.
 */
final readonly class LaravelEventPublisher implements EventPublisher
{
    /**
     * Takes the dispatcher.
     */
    public function __construct(private Dispatcher $dispatcher) {}

    /**
     * Dispatches each event, in order.
     */
    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->dispatcher->dispatch($event);
        }
    }
}
