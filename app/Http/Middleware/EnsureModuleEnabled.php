<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Application\Tenancy\ActingStaff;
use App\Domain\Tenancy\CompanySuspended;
use App\Domain\Tenancy\Module;
use App\Domain\Tenancy\ModuleDisabled;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Lets a request through only when the acting staff member's company is active and has enabled the
 * module: `->middleware('module:vouchers')` on every route of a module.
 */
final readonly class EnsureModuleEnabled
{
    /**
     * Passes the request on, or answers 403.
     *
     * @param  Closure(Request): Response  $next  The rest of the pipeline.
     * @param  string  $module  The module's value, as in {@see Module}.
     *
     * @throws AccessDeniedHttpException when the company is suspended or the module is not enabled.
     * @throws NotFoundHttpException when the route names a module that does not exist.
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $required = Module::tryFrom($module) ?? throw new NotFoundHttpException("No module is named \"{$module}\".");

        try {
            app(ActingStaff::class)->company->ensureServes($required);
        } catch (ModuleDisabled|CompanySuspended $refused) {
            throw new AccessDeniedHttpException($refused->getMessage(), $refused);
        }

        return $next($request);
    }
}
