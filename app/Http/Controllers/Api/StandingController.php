<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Level\ShowStanding;
use App\Domain\Customer\CustomerId;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\CompanyNotFound;
use App\Domain\Tenancy\CompanySuspended;
use App\Domain\Tenancy\ModuleDisabled;
use App\Http\Resources\CheckInJson;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Where the signed-in customer stands at a company (`/me/companies/{company}/standing`).
 */
final readonly class StandingController
{
    /**
     * Takes the use case.
     */
    public function __construct(private ShowStanding $standing) {}

    /**
     * The level, the progress and the next level.
     *
     * @throws NotFoundHttpException when no active company has the id.
     * @throws AccessDeniedHttpException when the company has not enabled check-ins.
     */
    public function show(CustomerId $customer, int $company): JsonResponse
    {
        try {
            return response()->json(CheckInJson::standing($this->standing->handle($customer, new CompanyId($company))));
        } catch (CompanyNotFound|CompanySuspended $missing) {
            throw new NotFoundHttpException($missing->getMessage(), $missing);
        } catch (ModuleDisabled $off) {
            throw new AccessDeniedHttpException($off->getMessage(), $off);
        }
    }
}
