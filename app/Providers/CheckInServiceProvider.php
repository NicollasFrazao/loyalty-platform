<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Level\RecalculateLevel;
use App\Domain\CheckIn\CheckInConfirmed;
use App\Domain\CheckIn\CheckInRepository;
use App\Domain\Level\CustomerStandings;
use App\Domain\Level\LadderRepository;
use App\Domain\Shared\EventPublisher;
use App\Infrastructure\Events\LaravelEventPublisher;
use App\Infrastructure\Persistence\EloquentCheckInRepository;
use App\Infrastructure\Persistence\EloquentCustomerStandings;
use App\Infrastructure\Persistence\EloquentLadderRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Composition root of check-ins and levels: binds their ports and the event publisher, and
 * recalculates a customer's level each time a check-in counts.
 */
final class CheckInServiceProvider extends ServiceProvider
{
    /**
     * Binds the check-ins, the ladders, the standings and the publisher.
     */
    public function register(): void
    {
        $this->app->bind(CheckInRepository::class, static fn (): CheckInRepository => new EloquentCheckInRepository);
        $this->app->bind(
            LadderRepository::class,
            static fn (Application $app): LadderRepository => new EloquentLadderRepository($app->make(ConnectionInterface::class)),
        );
        $this->app->bind(CustomerStandings::class, static fn (): CustomerStandings => new EloquentCustomerStandings);
        $this->app->bind(EventPublisher::class, static fn (Application $app): EventPublisher => new LaravelEventPublisher($app->make(Dispatcher::class)));
    }

    /**
     * Listens to the counted check-ins.
     */
    public function boot(Dispatcher $events): void
    {
        $events->listen(
            CheckInConfirmed::class,
            fn (CheckInConfirmed $event) => $this->app->make(RecalculateLevel::class)->onCheckIn($event),
        );
    }
}
