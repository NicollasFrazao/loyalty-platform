<?php

declare(strict_types=1);

use App\Domain\Customer\PhoneNumber;
use App\Infrastructure\Customer\LogOtpSender;
use Psr\Log\AbstractLogger;

it('writes the code to the log, where the developer reads it', function (): void {
    $log = new class extends AbstractLogger
    {
        /**
         * The lines written.
         *
         * @var list<string>
         */
        public array $lines = [];

        /**
         * Keeps the line.
         *
         * @param  array<array-key, mixed>  $context  Unused.
         */
        public function log(mixed $level, string|Stringable $message, array $context = []): void
        {
            $this->lines[] = (is_string($level) ? $level : 'unknown').': '.$message;
        }
    };

    new LogOtpSender($log)->send(PhoneNumber::from('+5511912345678'), '042137');

    expect($log->lines)->toBe(['info: One-time code for +5511912345678: 042137']);
});
