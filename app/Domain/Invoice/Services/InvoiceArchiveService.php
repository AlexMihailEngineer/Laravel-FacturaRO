<?php

namespace App\Domain\Invoice\Services;

use App\Domain\Invoice\Entities\Invoice;
use App\Domain\Invoice\Renderers\InvoiceRendererInterface;
use Illuminate\Support\Facades\Storage;

class InvoiceArchiveService
{
    public function archive(Invoice $invoice, InvoiceRendererInterface $renderer): string
    {
        $content = $renderer->render($invoice);
        $extension = $renderer->getExtension();
        
        $fileName = sprintf(
            'facturi/%s/%s_%s.%s',
            $invoice->getIssueDate()->format('Y-m'),
            $invoice->getSeries(),
            $invoice->getNumber(),
            $extension
        );

        Storage::disk('local')->put($fileName, $content);

        return $fileName;
    }
}
