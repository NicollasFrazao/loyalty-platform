<?php

declare(strict_types=1);

namespace App\Application\Tenancy;

use App\Domain\Tenancy\Role;
use SensitiveParameter;

/**
 * A new staff member as the form sends them: name, email address, role and first password.
 */
final readonly class StaffInput
{
    /**
     * Holds the values as typed.
     */
    public function __construct(
        public string $name,
        public string $email,
        public Role $role,
        #[SensitiveParameter] public string $password,
    ) {}
}
