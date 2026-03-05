<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapName;
use App\DataTransferObjects\CompanyData;

class InvoiceData extends Data
{
    /**
     * @param InvoiceLineData[] $items
     */
    public function __construct(
        public string $series,
        public string $number,
        #[MapName('issue_date')]
        public string $issueDate,
        public CompanyData $supplier,
        public CompanyData $customer,
        public array $items,
        #[MapName('total_amount')]
        public float $totalAmount
    ) {}
}
