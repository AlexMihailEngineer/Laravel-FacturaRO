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
}
