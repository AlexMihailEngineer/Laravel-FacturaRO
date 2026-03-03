<?php

namespace App\Domain\Invoice\Entities;

use App\Domain\Invoice\ValueObjects\Money;

readonly class InvoiceLine
{
    public function __construct(
        private string $description,
        private float $quantity,
        private Money $unitPrice,
        private int $vatPercentage, // e.g. 19, 9, 5
        private ?string $unitOfMeasure = 'buc'
    ) {}

    public function getNetTotal(): Money
    {
        return $this->unitPrice->multiply($this->quantity);
    }

    public function getVatAmount(): Money
    {
        return $this->getNetTotal()->multiply($this->vatPercentage / 100);
    }

    public function getTotal(): Money
    {
        return $this->getNetTotal()->add($this->getVatAmount());
    }

    public function getDescription(): string { return $this->description; }
    public function getQuantity(): float { return $this->quantity; }
    public function getUnitPrice(): Money { return $this->unitPrice; }
    public function getVatPercentage(): int { return $this->vatPercentage; }
}
