<?php

namespace App\Domain\Invoice\Builders;

use App\Domain\Invoice\Entities\Invoice;
use App\Domain\Invoice\Entities\InvoiceLine;
use App\Domain\Invoice\Entities\Party;
use App\Domain\Invoice\ValueObjects\Money;
use DateTimeInterface;
use RuntimeException;

class InvoiceBuilder
{
    private ?string $series = null;
    private ?string $number = null;
    private ?DateTimeInterface $issueDate = null;
    private ?Party $supplier = null;
    private ?Party $customer = null;
    /** @var InvoiceLine[] */
    private array $lines = [];

    public function setMetadata(string $series, string $number, DateTimeInterface $issueDate): self
    {
        $this->series = $series;
        $this->number = $number;
        $this->issueDate = $issueDate;
        return $this;
    }

    public function setSupplier(Party $supplier): self
    {
        $this->supplier = $supplier;
        return $this;
    }

    public function setCustomer(Party $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    public function addLine(InvoiceLine $line): self
    {
        $this->lines[] = $line;
        return $this;
    }

    public function build(): Invoice
    {
        $this->validate();

        $totalNet = new Money(0);
        $totalVat = new Money(0);

        foreach ($this->lines as $line) {
            $totalNet = $totalNet->add($line->getNetTotal());
            $totalVat = $totalVat->add($line->getVatAmount());
        }

        return new Invoice(
            $this->series,
            $this->number,
            $this->issueDate,
            $this->supplier,
            $this->customer,
            $this->lines,
            $totalNet,
            $totalVat,
            $totalNet->add($totalVat)
        );
    }

    private function validate(): void
    {
        if (!$this->series || !$this->number || !$this->issueDate) {
            throw new RuntimeException("Invoice metadata is missing.");
        }
        if (!$this->supplier || !$this->customer) {
            throw new RuntimeException("Supplier or Customer information is missing.");
        }
        if (empty($this->lines)) {
            throw new RuntimeException("Invoice must have at least one line.");
        }
    }
}
