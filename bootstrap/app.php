<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureModuleEnabled;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(static function (Middleware $middleware): void {
        // A guest is sent to the sign-in page, a signed-in staff member away from it.
        $middleware->redirectGuestsTo(static fn (): string => route('login'));
        $middleware->redirectUsersTo(static fn (): string => route('back-office'));
        // `->middleware('module:vouchers')` lets a route answer only while the company has the module.
        $middleware->alias(['module' => EnsureModuleEnabled::class]);
    })
    ->withExceptions(static function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            static fn (Request $request): bool => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
