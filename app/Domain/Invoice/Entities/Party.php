<?php

namespace App\Domain\Invoice\Entities;

use App\Domain\Invoice\ValueObjects\CompanyIdentifier;

readonly class Party
{
    public function __construct(
        private string $name,
        private string $address,
        private CompanyIdentifier $identifier,
        private ?string $bankAccount = null,
        private ?string $bankName = null
    ) {}

    public function getName(): string { return $this->name; }
    public function getAddress(): string { return $this->address; }
    public function getIdentifier(): CompanyIdentifier { return $this->identifier; }
    public function getBankAccount(): ?string { return $this->bankAccount; }
    public function getBankName(): ?string { return $this->bankName; }
}
