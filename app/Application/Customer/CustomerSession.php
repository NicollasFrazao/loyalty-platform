<?php

declare(strict_types=1);

namespace App\Application\Customer;

use App\Domain\Customer\AccessTokens;
use App\Domain\Customer\CodeRefused;
use App\Domain\Customer\InvalidCustomerData;
use SensitiveParameter;

/**
 * The customer app's sessions: a verified code opens one on a device, and signing out closes it (use
 * case).
 */
final readonly class CustomerSession
{
    /**
     * Takes the code check and the tokens.
     */
    public function __construct(
        private VerifyCode $verify,
        private AccessTokens $tokens,
    ) {}

    /**
     * The token of a new session on the device, for the customer the code opens.
     *
     * @throws InvalidCustomerData when the phone is not an international mobile number.
     * @throws CodeRefused when the code is wrong, expired, used or out of attempts.
     */
    public function open(string $phone, #[SensitiveParameter] string $code, string $device): string
    {
        return $this->tokens->issue($this->verify->handle($phone, $code), $device);
    }

    /**
     * Ends the session the token belongs to.
     */
    public function close(#[SensitiveParameter] string $token): void
    {
        $this->tokens->revoke($token);
    }
}
