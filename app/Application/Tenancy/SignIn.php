<?php

declare(strict_types=1);

namespace App\Application\Tenancy;

use App\Domain\Tenancy\EmailAddress;
use App\Domain\Tenancy\InvalidCredentials;
use App\Domain\Tenancy\InvalidStaffData;
use App\Domain\Tenancy\PasswordHasher;
use App\Domain\Tenancy\StaffCredentials;
use App\Domain\Tenancy\StaffMemberId;
use App\Domain\Tenancy\StaffRepository;
use SensitiveParameter;

/**
 * Checks a staff member's email address and password and says who signed in (use case). An unknown
 * email costs one hash check too, so the time taken does not tell which emails have an account.
 */
final readonly class SignIn
{
    /**
     * Takes the staff and the password hasher.
     */
    public function __construct(
        private StaffRepository $staff,
        private PasswordHasher $hasher,
    ) {}

    /**
     * The staff member the email and the password open.
     *
     * @throws InvalidCredentials when no staff member has the email or the password is wrong.
     */
    public function handle(string $email, #[SensitiveParameter] string $password): StaffMemberId
    {
        try {
            $credentials = $this->staff->credentialsFor(EmailAddress::from($email));
        } catch (InvalidStaffData) {
            $credentials = null;
        }

        if (! $credentials instanceof StaffCredentials) {
            $this->hasher->spendCheck($password);

            throw InvalidCredentials::create();
        }

        if (! $this->hasher->matches($password, $credentials->password)) {
            throw InvalidCredentials::create();
        }

        return $credentials->member->id;
    }
}
