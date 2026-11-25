<?php

declare(strict_types=1);

namespace Tests\Support\Customer;

use App\Domain\Customer\OtpSender;
use App\Domain\Customer\PhoneNumber;
use LogicException;
use SensitiveParameter;

/**
 * Keeps the codes it is asked to send, so a test can type the one a phone received.
 */
final class RecordingOtpSender implements OtpSender
{
    /**
     * The last code sent to each phone.
     *
     * @var array<string, string>
     */
    private array $sent = [];

    /**
     * Keeps the code.
     */
    public function send(PhoneNumber $phone, #[SensitiveParameter] string $code): void
    {
        $this->sent[$phone->value] = $code;
    }

    /**
     * The last code the phone received, the phone typed in any form.
     *
     * @throws LogicException when it received none.
     */
    public function codeFor(string $phone): string
    {
        return $this->sent[PhoneNumber::from($phone)->value] ?? throw new LogicException("No code was sent to {$phone}.");
    }
}
