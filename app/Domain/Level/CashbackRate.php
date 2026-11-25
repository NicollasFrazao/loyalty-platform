<?php

declare(strict_types=1);

namespace App\Domain\Level;

/**
 * The share of a purchase a level credits back, in basis points (1 % = 100), so it is exact.
 */
final readonly class CashbackRate
{
    /**
     * A hundred per cent.
     */
    public const int WHOLE = 10_000;

    /**
     * The basis points of one per cent.
     */
    private const int PER_PERCENT = 100;

    /**
     * Holds the basis points; use {@see CashbackRate::fromPercent()} or {@see CashbackRate::of()}.
     */
    private function __construct(public int $basisPoints) {}

    /**
     * The rate of the basis points.
     *
     * @throws InvalidLadder when they are not between 0 and 10 000.
     */
    public static function of(int $basisPoints): self
    {
        if (
            $basisPoints < 0
            || $basisPoints > self::WHOLE
        ) {
            throw InvalidLadder::because('a cashback must be between 0 % and 100 %');
        }

        return new self($basisPoints);
    }

    /**
     * Reads a percentage as typed: "5", "5.5" or "12.25", with a dot or a comma.
     *
     * @throws InvalidLadder when it is not a number with up to two decimals between 0 and 100.
     */
    public static function fromPercent(string $text): self
    {
        $percent = str_replace(',', '.', trim($text));

        if (preg_match('/^(\d{1,3})(?:\.(\d{1,2}))?$/', $percent, $parts) !== 1) {
            throw InvalidLadder::because('a cashback is a percentage such as 5 or 2.5');
        }

        return self::of(((int) $parts[1] * self::PER_PERCENT) + (int) str_pad($parts[2] ?? '', 2, '0'));
    }

    /**
     * The percentage, with the decimals it needs: "5", "5.5", "12.25".
     */
    public function percent(): string
    {
        return rtrim(rtrim(sprintf('%d.%02d', intdiv($this->basisPoints, self::PER_PERCENT), $this->basisPoints % self::PER_PERCENT), '0'), '.');
    }
}
