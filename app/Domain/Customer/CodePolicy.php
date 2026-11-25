<?php

declare(strict_types=1);

namespace App\Domain\Customer;

use DateInterval;
use DateTimeImmutable;

/**
 * The rules of one-time codes: a code lives five minutes and survives five wrong guesses, and a phone
 * gets at most three codes in fifteen minutes.
 */
final class CodePolicy
{
    /**
     * How long a code can be used.
     */
    public const string LIFETIME = 'PT5M';

    /**
     * Wrong guesses a code survives; the next one closes it.
     */
    public const int MAX_ATTEMPTS = 5;

    /**
     * Codes a phone may receive within the window.
     */
    public const int MAX_CODES = 3;

    /**
     * The window the codes of a phone are counted in.
     */
    public const string WINDOW = 'PT15M';

    /**
     * When a code issued now stops working.
     */
    public static function expiryOf(DateTimeImmutable $issuedAt): DateTimeImmutable
    {
        return $issuedAt->add(new DateInterval(self::LIFETIME));
    }

    /**
     * Since when the codes of a phone count against the limit.
     */
    public static function windowStart(DateTimeImmutable $now): DateTimeImmutable
    {
        return $now->sub(new DateInterval(self::WINDOW));
    }
}
