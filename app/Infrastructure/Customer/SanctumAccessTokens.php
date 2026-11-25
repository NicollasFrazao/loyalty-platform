<?php

declare(strict_types=1);

namespace App\Infrastructure\Customer;

use App\Domain\Customer\AccessTokens;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerNotFound;
use App\Infrastructure\Persistence\Eloquent\CustomerRecord;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * The customer app's tokens, as Laravel Sanctum personal access tokens of the customer's row.
 */
final readonly class SanctumAccessTokens implements AccessTokens
{
    /**
     * A new token named after the device.
     *
     * @throws CustomerNotFound when the account was deleted.
     */
    public function issue(CustomerId $customer, string $device): string
    {
        return $this->record($customer)->createToken($device)->plainTextToken;
    }

    /**
     * Deletes the token, when it is one.
     */
    public function revoke(string $token): void
    {
        PersonalAccessToken::findToken($token)?->delete();
    }

    /**
     * Deletes every token of the customer.
     */
    public function revokeAll(CustomerId $customer): void
    {
        CustomerRecord::query()->find($customer->value)?->tokens()->delete();
    }

    /**
     * The customer's row, unless the account was deleted.
     *
     * @throws CustomerNotFound when it was, or there is none.
     */
    private function record(CustomerId $customer): CustomerRecord
    {
        return CustomerRecord::query()->whereNull('deleted_at')->find($customer->value) ?? throw CustomerNotFound::withId($customer);
    }
}
