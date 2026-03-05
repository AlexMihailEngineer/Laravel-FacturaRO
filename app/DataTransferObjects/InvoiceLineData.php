<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class InvoiceLineData extends Data
{
    public function __construct(
        public string $name,
        public float $quantity,
        public float $unitPrice,
        public int $vatRate
    ) {}
}
