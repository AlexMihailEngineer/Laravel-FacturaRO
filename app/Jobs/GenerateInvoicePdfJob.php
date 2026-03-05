<?php

namespace App\Jobs;

use App\Domain\Invoice\Renderers\InvoiceGeneratorService;
use App\Domain\Invoice\Renderers\DomPdfRendererStrategy;
use App\DataTransferObjects\InvoiceData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected InvoiceData $invoiceData
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $renderer = new DomPdfRendererStrategy();
        $generator = new InvoiceGeneratorService($renderer);

        // Structure the DTO data into an array format expected by the DomPDF view
        $viewData = [
            'series' => $this->invoiceData->series,
            'number' => $this->invoiceData->number,
            'issue_date' => $this->invoiceData->issueDate,
            'supplier' => $this->invoiceData->supplier, // Pass the CompanyData DTO object
            'customer' => $this->invoiceData->customer, // Pass the CompanyData DTO object
            'items' => $this->invoiceData->items, // This contains InvoiceLineData objects
            'total_amount' => $this->invoiceData->totalAmount,
        ];
        
        $pdf = Pdf::loadView('invoice.pdf', $viewData);
        $pdf->setPaper('A4', 'portrait');

        $fileName = 'factura_' . $this->invoiceData->series . '_' . $this->invoiceData->number . '.pdf';
        
        Storage::disk('local')->put('facturi/' . $fileName, $pdf->output());
    }
}
