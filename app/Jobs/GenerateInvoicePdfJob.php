<?php

namespace App\Jobs;

use App\Domain\Invoice\Renderers\InvoiceGeneratorService;
use App\Domain\Invoice\Renderers\DomPdfRendererStrategy;
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
        protected array $invoiceData
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // For this exercise, we map the array to the Entities/Builder 
        // to demonstrate the Domain-Driven approach.
        // In a real app, this would be cleaner with a DTO.
        
        // Let's keep it simple for now as requested and use the Strategy
        // If we really want to use the Strategy, we'd need to convert back to Entity
        // or have the Strategy accept an array (which is less 'clean').
        
        // For the sake of completing Step 4 as 'Archival and Extension ready':
        $renderer = new DomPdfRendererStrategy();
        $generator = new InvoiceGeneratorService($renderer);

        // [Note: To fully use the Entity approach here, we'd invoke the InvoiceBuilder]
        // But for this session, I will leave the Job as is (direct DomPDF or simple Strategy)
        // because we haven't implemented the array->entity mapper yet.
        
        $pdf = Pdf::loadView('invoice.pdf', $this->invoiceData);
        $pdf->setPaper('A4', 'portrait');

        $fileName = 'factura_' . $this->invoiceData['series'] . '_' . $this->invoiceData['number'] . '.pdf';
        
        Storage::disk('local')->put('facturi/' . $fileName, $pdf->output());
    }
}
