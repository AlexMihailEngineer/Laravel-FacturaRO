<?php

namespace App\Domain\Invoice\Renderers;

use App\Domain\Invoice\Contracts\InvoiceRendererInterface;
use App\Domain\Invoice\Entities\Invoice;
use App\Domain\Invoice\Entities\InvoiceLine;
use Barryvdh\DomPDF\Facade\Pdf;

class DomPdfRendererStrategy implements InvoiceRendererInterface
{
    public function render(Invoice $invoice): string
    {
        // Internal transformation: Domain -> View Data
        $data = $this->transformToViewData($invoice);

        // Encapsulate PDF configuration
        $pdf = Pdf::loadView('invoice.pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }

    public function getExtension(): string
    {
        return 'pdf';
    }

    /**
     * Maps the Invoice aggregate and its Value Objects to a flat array for Blade.
     */
    private function transformToViewData(Invoice $invoice): array
    {
        return [
            'series'     => $invoice->getSeries(),
            'number'     => $invoice->getNumber(),
            'issue_date' => $invoice->getIssueDate()->format('d.m.Y'),
            'supplier'   => [
                'name'    => $invoice->getSupplier()->getName(),
                'address' => $invoice->getSupplier()->getAddress(),
                'cui'     => $invoice->getSupplier()->getIdentifier()->getFullVatId(),
                'reg_com' => $invoice->getSupplier()->getIdentifier()->getRegistrationNumber(),
            ],
            'customer'   => [
                'name'    => $invoice->getCustomer()->getName(),
                'address' => $invoice->getCustomer()->getAddress(),
                'cui'     => $invoice->getCustomer()->getIdentifier()->getFullVatId(),
            ],
            'items'      => array_map(fn(InvoiceLine $line) => [
                'description' => $line->getDescription(),
                'quantity'    => $line->getQuantity(),
                'unit_price'  => $line->getUnitPrice()->getAmount(),
                'net_total'   => $line->getNetTotal()->getAmount(),
                'vat_rate'    => $line->getVatPercentage(),
                'vat_amount'  => $line->getVatAmount()->getAmount(),
            ], $invoice->getLines()),
            'totals'     => [
                'net'   => $invoice->getTotalNet()->getAmount(),
                'vat'   => $invoice->getTotalVat()->getAmount(),
                'gross' => $invoice->getTotalGross()->getAmount(),
            ],
        ];
    }
}
