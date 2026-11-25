<?php

declare(strict_types=1);

namespace Tests\Support\Customer;

use App\Domain\Customer\AccessTokens;
use App\Domain\Customer\CustomerId;

/**
 * Tokens in memory, "customer|device", so a test reads whose they are.
 */
final class InMemoryAccessTokens implements AccessTokens
{
    /**
     * The tokens alive.
     *
     * @var list<string>
     */
    public private(set) array $tokens = [];

    /**
     * A token for the customer's device.
     */
    public function issue(CustomerId $customer, string $device): string
    {
        $token = "{$customer->value}|{$device}";
        $this->tokens[] = $token;

        return $token;
    }

    /**
     * Drops the token.
     */
    public function revoke(string $token): void
    {
        $this->tokens = array_values(array_diff($this->tokens, [$token]));
    }

    /**
     * Drops the customer's tokens.
     */
    public function revokeAll(CustomerId $customer): void
    {
        $this->tokens = array_values(array_filter(
            $this->tokens,
            static fn (string $token): bool => ! str_starts_with($token, "{$customer->value}|"),
        ));
    }
}
