<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

/**
 * Whether a company is open for business on the platform.
 */
enum CompanyStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
}
