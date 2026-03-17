<?php

namespace App\Domain\Invoice\Contracts;

use App\Domain\Invoice\Entities\Invoice;

interface InvoiceRendererInterface
{
    /**
     * Renders a domain Invoice entity into a specific format string.
     */
    public function render(Invoice $invoice): string;

    /**
     * Returns the file extension for the rendered format (e.g., 'pdf', 'xml').
     */
    public function getExtension(): string;
}
