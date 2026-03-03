<?php

namespace App\Domain\Invoice\Contracts;

use App\Domain\Invoice\Entities\Invoice;

interface InvoiceRendererInterface
{
    /**
     * Renders an invoice into a specific format (PDF, XML, etc.)
     * 
     * @param Invoice $invoice
     * @return string The rendered content
     */
    public function render(Invoice $invoice): string;
}
