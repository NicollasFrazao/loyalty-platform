<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * The composition root: binds the ports of the domain to their implementations as the phases add them.
 *
 * The providers in app/Providers are the only classes that may depend on every layer
 * (docs/adr/0001-layered-architecture.md).
 */
final class AppServiceProvider extends ServiceProvider {}
