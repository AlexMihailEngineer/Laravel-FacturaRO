<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class CompanyData extends Data
{
    public function __construct(
        public string $name,
        public string $cui,
        public string $address,
        // This will map from 'registration_number' in the JSON
        public ?string $registrationNumber = null,
    ) {}
}
