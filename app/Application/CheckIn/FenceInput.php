<?php

declare(strict_types=1);

namespace App\Application\CheckIn;

/**
 * A circular fence as the back-office form sends it.
 */
final readonly class FenceInput
{
    /**
     * Holds the values.
     */
    public function __construct(
        public float $latitude,
        public float $longitude,
        public int $radiusMetres,
    ) {}
}
