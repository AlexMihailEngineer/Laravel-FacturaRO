<?php

namespace Tests\Unit\Domain\Invoice\Services;

use App\Domain\Invoice\Entities\Invoice;
use App\Domain\Invoice\Entities\InvoiceLine;
use App\Domain\Invoice\Entities\Party;
use App\Domain\Invoice\Renderers\UblRendererStrategy;
use App\Domain\Invoice\Services\InvoiceArchiveService;
use App\Domain\Invoice\ValueObjects\Money;
use App\Domain\Invoice\ValueObjects\CompanyIdentifier;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoiceArchiveServiceTest extends TestCase
{
    public function test_it_archives_invoice_as_ubl_xml()
    {
        Storage::fake('local');

        $supplier = new Party(
            'Nova Tech', 
            'Street 1', 
            new CompanyIdentifier('123456', 'J40/1/2026', true)
        );
        $customer = new Party(
            'Client', 
            'Street 2', 
            new CompanyIdentifier('654321', null, false)
        );
        
        $line = new InvoiceLine(
            'Test Item',
            1,
            new Money('100.00'),
            19,
            new Money('100.00'),
            new Money('19.00'),
            new Money('119.00')
        );

        $invoice = new Invoice(
            'UBL',
            '001',
            new \DateTimeImmutable('2026-03-03'),
            $supplier,
            $customer,
            [$line],
            new Money('100.00'),
            new Money('19.00'),
            new Money('119.00')
        );

        $service = new InvoiceArchiveService();
        $renderer = new UblRendererStrategy();

        $path = $service->archive($invoice, $renderer);

        Storage::disk('local')->assertExists($path);
        $this->assertStringContainsString('<cbc:ID>UBL001</cbc:ID>', Storage::disk('local')->get($path));
        $this->assertStringContainsString('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', Storage::disk('local')->get($path));
        $this->assertStringContainsString('<cbc:CompanyID>RO123456</cbc:CompanyID>', Storage::disk('local')->get($path));
    }
}
