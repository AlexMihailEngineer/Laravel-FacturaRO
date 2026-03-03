<?php

namespace App\Domain\Invoice\Entities;

use App\Domain\Invoice\ValueObjects\Money;
use DateTimeInterface;

readonly class Invoice
{
    /**
     * @param InvoiceLine[] $lines
     */
    public function __construct(
        private string $series,
        private string $number,
        private DateTimeInterface $issueDate,
        private Party $supplier,
        private Party $customer,
        private array $lines,
        private Money $totalNet,
        private Money $totalVat,
        private Money $totalGross
    ) {}

    public function getSeries(): string { return $this->series; }
    public function getNumber(): string { return $this->number; }
    public function getIssueDate(): DateTimeInterface { return $this->issueDate; }
    public function getSupplier(): Party { return $this->supplier; }
    public function getCustomer(): Party { return $this->customer; }
    public function getLines(): array { return $this->lines; }
    public function getTotalNet(): Money { return $this->totalNet; }
    public function getTotalVat(): Money { return $this->totalVat; }
    public function getTotalGross(): Money { return $this->totalGross; }
}
