<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Tenancy\CompanyRepository;
use App\Domain\Tenancy\PasswordHasher;
use App\Domain\Tenancy\StaffRepository;
use App\Infrastructure\Persistence\EloquentCompanyRepository;
use App\Infrastructure\Persistence\EloquentStaffRepository;
use App\Infrastructure\Tenancy\FrameworkPasswordHasher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Composition root of the tenancy: binds its ports (the acting staff member is BackOfficeServiceProvider's).
 */
final class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Binds the companies, the staff and the hasher.
     */
    public function register(): void
    {
        $this->app->bind(
            CompanyRepository::class,
            static fn (Application $app): CompanyRepository => new EloquentCompanyRepository($app->make(ConnectionInterface::class)),
        );
        $this->app->bind(StaffRepository::class, static fn (): StaffRepository => new EloquentStaffRepository);
        $this->app->singleton(
            PasswordHasher::class,
            static fn (Application $app): PasswordHasher => new FrameworkPasswordHasher($app->make(Hasher::class)),
        );
    }
}
