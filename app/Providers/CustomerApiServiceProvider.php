<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Customer\AccessTokens;
use App\Domain\Customer\CustomerId;
use App\Infrastructure\Customer\SanctumAccessTokens;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * The customer API's sessions: the tokens, and the signed-in customer resolved from the request's
 * token alone. A controller that takes a `CustomerId` acts on that customer and no other.
 */
final class CustomerApiServiceProvider extends ServiceProvider
{
    /**
     * Binds the tokens and the signed-in customer, resolved each time it is asked for.
     */
    public function register(): void
    {
        $this->app->bind(AccessTokens::class, static fn (): AccessTokens => new SanctumAccessTokens);
        $this->app->bind(CustomerId::class, static function (Application $app): CustomerId {
            $id = $app->make(AuthFactory::class)->guard('sanctum')->id();

            if (! is_int($id)) {
                throw new AuthenticationException;
            }

            return new CustomerId($id);
        });
    }
}
