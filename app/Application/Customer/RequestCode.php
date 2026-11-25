<?php

declare(strict_types=1);

namespace App\Application\Customer;

use App\Domain\Customer\CodeChallenges;
use App\Domain\Customer\CodePolicy;
use App\Domain\Customer\InvalidCustomerData;
use App\Domain\Customer\OneTimeCodes;
use App\Domain\Customer\OtpSender;
use App\Domain\Customer\PhoneNumber;
use App\Domain\Customer\TooManyCodes;
use App\Domain\Shared\Clock;

/**
 * Sends a one-time code to a phone, to sign up or sign in, within the policy's limit (use case).
 */
final readonly class RequestCode
{
    /**
     * Takes the challenges, the codes, the sender and the clock.
     */
    public function __construct(
        private CodeChallenges $challenges,
        private OneTimeCodes $codes,
        private OtpSender $sender,
        private Clock $clock,
    ) {}

    /**
     * Keeps a new code's hash and sends the code.
     *
     * @throws InvalidCustomerData when the phone is not an international mobile number.
     * @throws TooManyCodes when the phone received the most codes the window allows.
     */
    public function handle(string $phone): void
    {
        $number = PhoneNumber::from($phone);
        $now = $this->clock->now();

        if ($this->challenges->countSince($number, CodePolicy::windowStart($now)) >= CodePolicy::MAX_CODES) {
            throw TooManyCodes::create();
        }

        $issued = $this->codes->issue();
        $this->challenges->open($number, $issued->hash, $now);
        $this->sender->send($number, $issued->code);
    }
}
