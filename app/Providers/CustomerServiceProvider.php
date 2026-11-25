<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Customer\CodeChallenges;
use App\Domain\Customer\CustomerRepository;
use App\Domain\Customer\Following;
use App\Domain\Shared\Clock;
use App\Infrastructure\Persistence\EloquentCodeChallenges;
use App\Infrastructure\Persistence\EloquentCustomerRepository;
use App\Infrastructure\Persistence\EloquentFollowing;
use App\Infrastructure\Shared\SystemClock;
use Illuminate\Support\ServiceProvider;

/**
 * Composition root of the customers' storage, and of the clock every use case reads (the codes are
 * OneTimeCodeServiceProvider's, the API's session CustomerApiServiceProvider's).
 */
final class CustomerServiceProvider extends ServiceProvider
{
    /**
     * Binds the customers, the codes sent, the following and the clock.
     */
    public function register(): void
    {
        $this->app->bind(CustomerRepository::class, static fn (): CustomerRepository => new EloquentCustomerRepository);
        $this->app->bind(CodeChallenges::class, static fn (): CodeChallenges => new EloquentCodeChallenges);
        $this->app->bind(Following::class, static fn (): Following => new EloquentFollowing);
        $this->app->singleton(Clock::class, static fn (): Clock => new SystemClock);
    }
}
