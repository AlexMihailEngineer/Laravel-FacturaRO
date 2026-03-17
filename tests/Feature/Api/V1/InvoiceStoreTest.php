<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceStoreTest extends TestCase
{
    use RefreshDatabase;

    private function getValidSupplierData(): array
    {
        return [
            'name' => 'Supplier SA',
            'cui' => '12345674',
            'registration_number' => 'J40/123/2024',
            'address' => 'Strada Exemplu 1, Bucuresti',
        ];
    }

    private function getValidCustomerData(): array
    {
        return [
            'name' => 'Customer SRL',
            'cui' => 'RO12345674',
            'address' => 'Calea Victoriei 10, Bucuresti',
            'registration_number' => 'J40/999/2024',
        ];
    }

    public function test_invoice_store_validation_fails_with_invalid_cui()
    {
        $response = $this->postJson('/api/v1/facturi', [
            'series' => 'ABC',
            'number' => '123',
            'issue_date' => '2026-03-03',
            'supplier' => array_merge($this->getValidSupplierData(), ['cui' => '15901201']),
            'customer' => array_merge($this->getValidCustomerData(), ['cui' => '4406394']),
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
            'supplier' => $this->getValidSupplierData(),
            'customer' => $this->getValidCustomerData(),
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
        $response->assertJson(['message' => 'Invoice generation started.']);
    }

    public function test_invoice_store_fails_with_mismatched_total()
    {
        $response = $this->postJson('/api/v1/facturi', [
            'series' => 'ABC',
            'number' => '125',
            'issue_date' => '2026-03-03',
            'supplier' => $this->getValidSupplierData(),
            'customer' => $this->getValidCustomerData(),
            'items' => [
                [
                    'name' => 'Product 1',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'vat_rate' => 19,
                ]
            ],
            'total_amount' => 200,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['total_amount']);
    }

    public function test_invoice_store_fails_with_structural_failures()
    {
        // Items is a string instead of array
        $response = $this->postJson('/api/v1/facturi', [
            'series' => 'ABC',
            'number' => '126',
            'issue_date' => '2026-03-03',
            'supplier' => $this->getValidSupplierData(),
            'customer' => $this->getValidCustomerData(),
            'items' => 'This is not an array!',
            'total_amount' => 119,
        ]);
        $response->assertJsonValidationErrors(['items']);

        // Missing address fields
        $response = $this->postJson('/api/v1/facturi', [
            'series' => 'ABC',
            'number' => '127',
            'issue_date' => '2026-03-03',
            'supplier' => ['name' => 'S1', 'cui' => '12345674'],
            'customer' => ['name' => 'C1', 'cui' => '12345674'],
            'items' => [['name' => 'P1', 'quantity' => 1, 'unit_price' => 100, 'vat_rate' => 19]],
            'total_amount' => 119,
        ]);
        $response->assertJsonValidationErrors([
            'supplier.address',
            'supplier.registration_number',
            'customer.address'
        ]);
    }
}
