<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Customer\DeleteAccount;
use App\Application\Customer\ManageProfile;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerNotFound;
use App\Domain\Customer\InvalidCustomerData;
use App\Http\Requests\Api\ProfileRequest;
use App\Http\Resources\CustomerJson;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * The signed-in customer's own account (`/me`): reading it, changing the profile, deleting it.
 */
final readonly class ProfileController
{
    /**
     * Takes the account use cases.
     */
    public function __construct(
        private ManageProfile $profile,
        private DeleteAccount $delete,
    ) {}

    /**
     * The customer.
     *
     * @throws AuthenticationException when the account was deleted.
     */
    public function show(CustomerId $customer): JsonResponse
    {
        try {
            return response()->json(CustomerJson::customer($this->profile->show($customer)));
        } catch (CustomerNotFound) {
            throw new AuthenticationException;
        }
    }

    /**
     * Replaces the profile.
     *
     * @throws ValidationException when a value is refused.
     * @throws AuthenticationException when the account was deleted.
     */
    public function update(ProfileRequest $request, CustomerId $customer): JsonResponse
    {
        try {
            return response()->json(CustomerJson::customer($this->profile->update($customer, $request->toInput())));
        } catch (InvalidCustomerData $error) {
            throw ValidationException::withMessages([$error->field->value => $error->getMessage()]);
        } catch (CustomerNotFound) {
            throw new AuthenticationException;
        }
    }

    /**
     * Deletes the account: the personal data is erased and every token revoked.
     *
     * @throws AuthenticationException when the account was deleted already.
     */
    public function destroy(CustomerId $customer): Response
    {
        try {
            $this->delete->handle($customer);
        } catch (CustomerNotFound) {
            throw new AuthenticationException;
        }

        return response()->noContent();
    }
}
