<?php

declare(strict_types=1);

namespace App\Infrastructure\Customer;

use App\Domain\Customer\OtpSender;
use App\Domain\Customer\PhoneNumber;
use Psr\Log\LoggerInterface;
use SensitiveParameter;

/**
 * The development double of an SMS gateway: writes the code to the log, where the developer reads it.
 * No production gateway exists yet (tech debt TD-06).
 */
final readonly class LogOtpSender implements OtpSender
{
    /**
     * Takes the log.
     */
    public function __construct(private LoggerInterface $log) {}

    /**
     * Writes the code to the log.
     */
    public function send(PhoneNumber $phone, #[SensitiveParameter] string $code): void
    {
        $this->log->info("One-time code for {$phone->value}: {$code}");
    }
}
