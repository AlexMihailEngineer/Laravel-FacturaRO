<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class InvoiceStoreTest extends TestCase
{
    public function test_invoice_store_validation_fails_with_invalid_cui()
    {
        $response = $this->postJson('/api/v1/facturi', [
            'series' => 'ABC',
            'number' => '123',
            'issue_date' => '2026-03-03',
            'supplier' => [
                'name' => 'Supplier SA',
                'cui' => '15901201', // Invalid
            ],
            'customer' => [
                'name' => 'Customer SRL',
                'cui' => '4406394', // Invalid
            ],
            'items' => [
                [
                    'name' => 'Product 1',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'vat_rate' => 19,
                ]
            ],
            'total_amount' => 119,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['supplier.cui', 'customer.cui']);
    }

    public function test_invoice_store_validation_passes_with_valid_cui()
    {
        $response = $this->postJson('/api/v1/facturi', [
            'series' => 'ABC',
            'number' => '124',
            'issue_date' => '2026-03-03',
            'supplier' => [
                'name' => 'Supplier SA',
                'cui' => '12345674', // Valid (example from user)
            ],
            'customer' => [
                'name' => 'Customer SRL',
                'cui' => 'RO12345674', // Valid (with prefix)
            ],
            'items' => [
                [
                    'name' => 'Product 1',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'vat_rate' => 19,
                ]
            ],
            'total_amount' => 119,
        ]);

        $response->assertStatus(202);
        $response->assertJsonStructure(['message', 'job_id']);
    }

    public function test_invoice_store_fails_with_mismatched_total()
    {
        $response = $this->postJson('/api/v1/facturi', [
            'series' => 'ABC',
            'number' => '125',
            'issue_date' => '2026-03-03',
            'supplier' => [
                'name' => 'Supplier SA',
                'cui' => '12345674',
            ],
            'customer' => [
                'name' => 'Customer SRL',
                'cui' => '12345674',
            ],
            'items' => [
                [
                    'name' => 'Product 1',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'vat_rate' => 19,
                ]
            ],
            'total_amount' => 200, // Should be 119
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['total_amount']);
    }

    /**
     * Provides data for structural validation failures (Initial Rules).
     * @return array<array{name: string, data: array, expected_errors: array<string>}>
     */
    public function structuralFailureDataProvider(): array
    {
        return [
            [
                'name' => 'Items is a string instead of array',
                'data' => [
                    'series' => 'ABC',
                    'number' => '126',
                    'issue_date' => '2026-03-03',
                    'supplier' => ['name' => 'Supplier SA', 'cui' => '12345674'],
                    'customer' => ['name' => 'Customer SRL', 'cui' => '12345674'],
                    'items' => 'This is not an array of items, but a string!',
                    'total_amount' => 119,
                ],
                'expected_errors' => ['items'],
            ],
            [
                'name' => 'Supplier is a string instead of object',
                'data' => [
                    'series' => 'ABC',
                    'number' => '127',
                    'issue_date' => '2026-03-03',
                    'supplier' => 'This is a string, not an object.',
                    'customer' => ['name' => 'Customer SRL', 'cui' => '12345674'],
                    'items' => [
                        ['name' => 'P1', 'quantity' => 1, 'unit_price' => 100, 'vat_rate' => 19],
                    ],
                    'total_amount' => 119,
                ],
                'expected_errors' => ['supplier.name', 'supplier.cui'],
            ],
            [
                'name' => 'Invalid date format',
                'data' => [
                    'series' => 'ABC',
                    'number' => '129',
                    'issue_date' => '03-03-2026', // Invalid format
                    'supplier' => ['name' => 'Supplier SA', 'cui' => '12345674'],
                    'customer' => ['name' => 'Customer SRL', 'cui' => '12345674'],
                    'items' => [
                        ['name' => 'P1', 'quantity' => 1, 'unit_price' => 100, 'vat_rate' => 19],
                    ],
                    'total_amount' => 119,
                ],
                'expected_errors' => ['issue_date'],
            ],
            [
                'name' => 'Date too far in the future',
                'data' => [
                    'series' => 'ABC',
                    'number' => '130',
                    'issue_date' => '2126-03-07', // 100 years in future
                    'supplier' => ['name' => 'S1', 'cui' => '12345674'],
                    'customer' => ['name' => 'C1', 'cui' => '12345674'],
                    'items' => [['name' => 'P1', 'quantity' => 1, 'unit_price' => 100, 'vat_rate' => 19]],
                    'total_amount' => 119,
                ],
                'expected_errors' => ['issue_date'],
            ],
            [
                'name' => 'Date too far in the past',
                'data' => [
                    'series' => 'ABC',
                    'number' => '131',
                    'issue_date' => '1900-01-01', // Too far in past
                    'supplier' => ['name' => 'S1', 'cui' => '12345674'],
                    'customer' => ['name' => 'C1', 'cui' => '12345674'],
                    'items' => [['name' => 'P1', 'quantity' => 1, 'unit_price' => 100, 'vat_rate' => 19]],
                    'total_amount' => 119,
                ],
                'expected_errors' => ['issue_date'],
            ],
        ];
    }

    public function test_invoice_store_fails_with_structural_failures()
    {
        foreach ($this->structuralFailureDataProvider() as $testCase) {
            $response = $this->postJson('/api/v1/facturi', $testCase['data']);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors($testCase['expected_errors']);
        }
    }
}
