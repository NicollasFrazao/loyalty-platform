<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

use App\Domain\Shared\Identifier;

/**
 * The identity of a company, the tenant every other piece of data belongs to.
 */
final readonly class CompanyId extends Identifier {}
