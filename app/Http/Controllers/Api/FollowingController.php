<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Customer\FollowCompanies;
use App\Domain\Customer\CustomerId;
use App\Domain\Tenancy\CompanyId;
use App\Domain\Tenancy\CompanyNotFound;
use App\Domain\Tenancy\CompanySuspended;
use App\Http\Resources\CustomerJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The companies the signed-in customer follows (`/me/companies`).
 */
final readonly class FollowingController
{
    /**
     * Takes the use case.
     */
    public function __construct(private FollowCompanies $following) {}

    /**
     * The companies followed, in the order they were followed.
     */
    public function index(CustomerId $customer): JsonResponse
    {
        return response()->json([
            'data' => array_map(CustomerJson::company(...), $this->following->list($customer)),
        ]);
    }

    /**
     * Follows the company; following it again changes nothing.
     *
     * @throws NotFoundHttpException when no active company has the id.
     */
    public function update(CustomerId $customer, int $company): Response
    {
        try {
            $this->following->follow($customer, new CompanyId($company));
        } catch (CompanyNotFound|CompanySuspended $missing) {
            throw new NotFoundHttpException($missing->getMessage(), $missing);
        }

        return response()->noContent();
    }

    /**
     * Stops following the company.
     */
    public function destroy(CustomerId $customer, int $company): Response
    {
        $this->following->unfollow($customer, new CompanyId($company));

        return response()->noContent();
    }
}
