<?php

declare(strict_types=1);

namespace App\Application\Customer;

use App\Domain\Customer\CodeChallenge;
use App\Domain\Customer\CodeChallenges;
use App\Domain\Customer\CodeRefused;
use App\Domain\Customer\CustomerId;
use App\Domain\Customer\CustomerRepository;
use App\Domain\Customer\InvalidCustomerData;
use App\Domain\Customer\OneTimeCodes;
use App\Domain\Customer\PhoneNumber;
use App\Domain\Shared\Clock;
use SensitiveParameter;

/**
 * Opens the account of a phone with the code sent to it: signs the customer in, or up the first time
 * (use case).
 */
final readonly class VerifyCode
{
    /**
     * Takes the challenges, the codes, the customers and the clock.
     */
    public function __construct(
        private CodeChallenges $challenges,
        private OneTimeCodes $codes,
        private CustomerRepository $customers,
        private Clock $clock,
    ) {}

    /**
     * The customer the code opens; a wrong guess counts against the code.
     *
     * @throws InvalidCustomerData when the phone is not an international mobile number.
     * @throws CodeRefused when the code is wrong, expired, used or out of attempts.
     */
    public function handle(string $phone, #[SensitiveParameter] string $code): CustomerId
    {
        $number = PhoneNumber::from($phone);
        $now = $this->clock->now();
        $challenge = $this->challenges->latest($number);

        if (
            ! $challenge instanceof CodeChallenge
            || ! $challenge->isOpenAt($now)
        ) {
            throw CodeRefused::create();
        }

        if (! $this->codes->matches($code, $challenge->codeHash)) {
            $this->challenges->recordAttempt($challenge);

            throw CodeRefused::create();
        }

        $this->challenges->close($challenge, $now);

        return $this->customers->findByPhone($number) ?? $this->customers->register($number, $now);
    }
}
