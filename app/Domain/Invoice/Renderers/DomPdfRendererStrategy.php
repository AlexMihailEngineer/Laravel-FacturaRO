<?php

namespace App\Domain\Invoice\Renderers;

use App\Domain\Invoice\Entities\Invoice;
use App\Domain\Invoice\Entities\InvoiceLine;
use Barryvdh\DomPDF\Facade\Pdf;

class DomPdfRendererStrategy implements InvoiceRendererInterface
{
    public function render(Invoice $invoice): string
    {
        // Convert Entity to simple array for the view
        $data = $this->transformToArray($invoice);

        $pdf = Pdf::loadView('invoice.pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }

    public function getExtension(): string
    {
        return 'pdf';
    }

    protected function transformToArray(Invoice $invoice): array
    {
        return [
            'series' => $invoice->getSeries(),
            'number' => $invoice->getNumber(),
            'issue_date' => $invoice->getIssueDate()->format('Y-m-d'),
            'supplier' => [
                'name' => $invoice->getSupplier()->getName(),
                'cui' => $invoice->getSupplier()->getIdentifier()->getFullVatId(),
                'address' => $invoice->getSupplier()->getAddress(),
                'reg_com' => $invoice->getSupplier()->getIdentifier()->getRegistrationNumber(),
            ],
            'customer' => [
                'name' => $invoice->getCustomer()->getName(),
                'cui' => $invoice->getCustomer()->getIdentifier()->getFullVatId(),
                'address' => $invoice->getCustomer()->getAddress(),
            ],
            'items' => array_map(function (InvoiceLine $line) {
                return [
                    'name' => $line->getDescription(),
                    'quantity' => $line->getQuantity(),
                    'unit_price' => $line->getUnitPrice()->getAmount(),
                    'vat_rate' => $line->getVatPercentage(),
                ];
            }, $invoice->getLines()),
        ];
    }
}
