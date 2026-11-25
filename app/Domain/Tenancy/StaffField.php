<?php

declare(strict_types=1);

namespace App\Domain\Tenancy;

/**
 * The values of a staff member someone types, named as the forms name them.
 */
enum StaffField: string
{
    case Name = 'name';
    case Email = 'email';
    case Password = 'password';
}
