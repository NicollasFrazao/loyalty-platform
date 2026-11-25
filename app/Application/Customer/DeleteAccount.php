<?php

declare(strict_types=1);

namespace App\Application\Customer;

use App\Domain\Customer\AccessTokens;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerNotFound;
use App\Domain\Customer\CustomerRepository;
use App\Domain\Customer\Following;
use App\Domain\Shared\Clock;

/**
 * Deletes the signed-in customer's account as the LGPD asks: the personal data is erased, the
 * companies are unfollowed, the tokens revoked, and the anonymous row stays for the ledger (use
 * case).
 */
final readonly class DeleteAccount
{
    /**
     * Takes the customers, the following, the tokens and the clock.
     */
    public function __construct(
        private CustomerRepository $customers,
        private Following $following,
        private AccessTokens $tokens,
        private Clock $clock,
    ) {}

    /**
     * Anonymises the account.
     *
     * @throws CustomerNotFound when the account was deleted already.
     */
    public function handle(CustomerId $id): void
    {
        $this->customers->anonymise($id, $this->clock->now());
        $this->following->forget($id);
        $this->tokens->revokeAll($id);
    }
}
