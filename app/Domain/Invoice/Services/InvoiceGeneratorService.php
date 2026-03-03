<?php

namespace App\Domain\Invoice\Services;

use App\Domain\Invoice\Contracts\InvoiceRendererInterface;
use App\Domain\Invoice\Entities\Invoice;
use Illuminate\Support\Facades\Storage;

class InvoiceGeneratorService
{
    /**
     * @param InvoiceRendererInterface[] $renderers
     */
    public function __construct(
        private array $renderers = []
    ) {}

    public function generateAllFormats(Invoice $invoice): void
    {
        foreach ($this->renderers as $renderer) {
            $content = $renderer->render($invoice);
            // Storage logic will go here in the Infrastructure layer integration
        }
    }
}
