<?php

namespace App\Domain\Invoice\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object for Romanian Company Identifiers (CUI/CIF and Reg. Com.).
 */
readonly class CompanyIdentifier
{
    public function __construct(
        private string $cui,
        private ?string $registrationNumber = null, // J40/1234/2026
        private bool $isVatPayer = false
    ) {
        $this->validate($cui);
    }

    private function validate(string $cui): void
    {
        $cleanCui = preg_replace('/[^0-9]/', '', $cui);
        if (strlen($cleanCui) < 2 || strlen($cleanCui) > 10) {
            throw new InvalidArgumentException("Invalid CUI length.");
        }
    }

    public function getFullVatId(): string
    {
        return ($this->isVatPayer ? 'RO' : '') . $this->cui;
    }

    public function getCui(): string
    {
        return $this->cui;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function isVatPayer(): bool
    {
        return $this->isVatPayer;
    }
}
