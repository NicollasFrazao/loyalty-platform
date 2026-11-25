<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\CheckIn\CheckInAtCompany;
use App\Domain\CheckIn\CheckInRefused;
use App\Domain\CheckIn\GeoPoint;
use App\Domain\CheckIn\InvalidCheckInSettings;
use App\Domain\Customer\CustomerId;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\CompanyNotFound;
use App\Domain\Tenancy\CompanySuspended;
use App\Domain\Tenancy\ModuleDisabled;
use App\Http\Requests\Api\CheckInRequest;
use App\Http\Resources\CheckInJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The signed-in customer checks in at a company (`/companies/{company}/check-ins`).
 */
final readonly class CheckInController
{
    /**
     * Takes the use case.
     */
    public function __construct(private CheckInAtCompany $checkIn) {}

    /**
     * Records the check-in, or answers 409 with the rule it broke.
     *
     * @throws ValidationException when a coordinate is out of range.
     * @throws NotFoundHttpException when no active company has the id.
     * @throws AccessDeniedHttpException when the company has not enabled check-ins.
     */
    public function store(CheckInRequest $request, CustomerId $customer, int $company): JsonResponse
    {
        try {
            $position = $request->position();
            $where = $position === null ? null : GeoPoint::at(...$position);
            $checkIn = $this->checkIn->handle($customer, new CompanyId($company), $where);
        } catch (InvalidCheckInSettings $error) {
            throw ValidationException::withMessages([$error->field->value => $error->getMessage()]);
        } catch (CompanyNotFound|CompanySuspended $missing) {
            throw new NotFoundHttpException($missing->getMessage(), $missing);
        } catch (ModuleDisabled $off) {
            throw new AccessDeniedHttpException($off->getMessage(), $off);
        } catch (CheckInRefused $refused) {
            return response()->json(CheckInJson::refusal($refused), Response::HTTP_CONFLICT);
        }

        return response()->json(CheckInJson::checkIn($checkIn), Response::HTTP_CREATED);
    }
}
