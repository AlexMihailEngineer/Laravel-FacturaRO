<?php

namespace App\Domain\Invoice\Renderers;

use App\Domain\Invoice\Entities\Invoice;
use App\Domain\Invoice\Contracts\InvoiceRendererInterface;

class InvoiceGeneratorService
{
    public function __construct(
        protected InvoiceRendererInterface $renderer
    ) {}

    public function generate(Invoice $invoice): string
    {
        return $this->renderer->render($invoice);
    }

    public function getFileName(Invoice $invoice): string
    {
        return 'factura_' . $invoice->getSeries() . '_' . $invoice->getNumber() . '.' . $this->renderer->getExtension();
    }
}
