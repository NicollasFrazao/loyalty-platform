<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Customer\OneTimeCodes;
use App\Domain\Customer\OtpSender;
use App\Infrastructure\Customer\HmacOneTimeCodes;
use App\Infrastructure\Customer\LogOtpSender;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Encryption\MissingAppKeyException;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

/**
 * Composition root of the one-time codes: how they are made and hashed, and how they reach a phone.
 */
final class OneTimeCodeServiceProvider extends ServiceProvider
{
    /**
     * Binds the codes, hashed under the application key, and the sender.
     */
    public function register(): void
    {
        $this->app->singleton(OneTimeCodes::class, static function (Application $app): OneTimeCodes {
            $key = $app->make(Config::class)->get('app.key');

            if (
                ! is_string($key)
                || $key === ''
            ) {
                throw new MissingAppKeyException;
            }

            return new HmacOneTimeCodes($key);
        });
        $this->app->bind(OtpSender::class, static fn (Application $app): OtpSender => new LogOtpSender($app->make(LoggerInterface::class)));
    }
}
