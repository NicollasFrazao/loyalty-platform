<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Tenancy\ActingStaff;
use App\Application\Tenancy\IdentifyStaff;
use App\Domain\Tenancy\StaffMemberId;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Resolves the acting staff member, the scope of every back-office use case, from the session alone:
 * a controller or middleware that takes `ActingStaff` acts on the signed-in staff member's company and
 * no other.
 */
final class BackOfficeServiceProvider extends ServiceProvider
{
    /**
     * Binds the acting staff member, resolved from the guard each time it is asked for.
     */
    public function register(): void
    {
        $this->app->bind(ActingStaff::class, static function (Application $app): ActingStaff {
            $id = $app->make(AuthFactory::class)->guard()->id();

            if (! is_int($id)) {
                throw new AuthenticationException;
            }

            return $app->make(IdentifyStaff::class)->handle(new StaffMemberId($id));
        });
    }
}
