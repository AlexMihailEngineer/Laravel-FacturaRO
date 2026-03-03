<?php

namespace App\Domain\Invoice\Renderers;

use App\Domain\Invoice\Entities\Invoice;

interface InvoiceRendererInterface
{
    /**
     * Render the invoice into a string (PDF binary, XML, etc.)
     */
    public function render(Invoice $invoice): string;

    /**
     * Get the file extension for the rendered output
     */
    public function getExtension(): string;
}
