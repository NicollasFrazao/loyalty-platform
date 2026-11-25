<?php

declare(strict_types=1);

namespace App\Domain\Customer;

/**
 * The API tokens the customer app signs requests with (port; implemented in Infrastructure with
 * Laravel Sanctum).
 */
interface AccessTokens
{
    /**
     * A new token for the customer's device; only this answer ever shows it.
     */
    public function issue(CustomerId $customer, string $device): string;

    /**
     * Revokes the token a request was signed with, when the customer signs out of that device.
     */
    public function revoke(string $token): void;

    /**
     * Revokes every token of the customer, when the account is deleted.
     */
    public function revokeAll(CustomerId $customer): void;
}
