<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use App\Domain\Shared\Identifier;

/**
 * The identity of a customer, kept after the account is deleted so the ledger still adds up.
 */
final readonly class CustomerId extends Identifier {}
