<?php

namespace App\Domain\Invoice\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object for monetary values to prevent float precision issues.
 * Uses BCMath for high precision calculations.
 */
readonly class Money
{
    private string $amount;
    private int $decimals;

    public function __construct(string|float|int $amount, int $decimals = 2)
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException("Amount must be numeric.");
        }

        // Normalize to string with fixed decimals
        $this->amount = bcadd((string)$amount, '0', $decimals);
        $this->decimals = $decimals;
    }

    public static function fromAmount(string|float|int $amount, int $decimals = 2): self
    {
        return new self($amount, $decimals);
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function add(Money $other): self
    {
        $this->assertSameDecimals($other);
        return new self(bcadd($this->amount, $other->getAmount(), $this->decimals), $this->decimals);
    }

    public function multiply(string|float|int $multiplier): self
    {
        // Multiply with extra precision then round to current decimal scale
        $result = bcmul($this->amount, (string)$multiplier, $this->decimals + 1);
        return self::round($result, $this->decimals);
    }

    public static function round(string|float|int $amount, int $decimals = 2): self
    {
        $amountStr = (string)$amount;
        $isNegative = str_starts_with($amountStr, '-');
        $absoluteAmount = ltrim($amountStr, '-');

        // Shift decimal point to right by (decimals + 1)
        $shifted = bcmul($absoluteAmount, bcpow('10', (string)($decimals + 1)), 0);
        
        // Extract the last digit for half-up rounding
        $lastDigit = bcmod($shifted, '10');
        $base = bcdiv($shifted, '10', 0);

        if ((int)$lastDigit >= 5) {
            $base = bcadd($base, '1', 0);
        }

        // Shift decimal point back to the left
        $rounded = bcdiv($base, bcpow('10', (string)$decimals), $decimals);
        return new self($isNegative ? '-' . $rounded : $rounded, $decimals);
    }

    public function getFormatted(): string
    {
        return number_format((float)$this->amount, $this->decimals, '.', '');
    }

    private function assertSameDecimals(Money $other): void
    {
        if ($this->decimals !== $other->decimals) {
            throw new InvalidArgumentException("Cannot operate on Money objects with different decimal scales.");
        }
    }

    public function __toString(): string
    {
        return $this->amount;
    }
}
