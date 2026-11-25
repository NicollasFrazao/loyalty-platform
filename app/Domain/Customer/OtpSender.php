<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use SensitiveParameter;

/**
 * Delivers a one-time code to a phone (port; the development adapter writes it to the log, a
 * production one would send an SMS).
 */
interface OtpSender
{
    /**
     * Sends the code to the phone.
     */
    public function send(PhoneNumber $phone, #[SensitiveParameter] string $code): void;
}
