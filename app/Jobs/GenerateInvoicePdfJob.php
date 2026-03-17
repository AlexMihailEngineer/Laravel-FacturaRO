<?php

namespace App\Jobs;

use App\DataTransferObjects\InvoiceData;
use App\DataTransferObjects\CompanyData;
use App\DataTransferObjects\InvoiceLineData;
use App\Domain\Invoice\Builders\InvoiceBuilder;
use App\Domain\Invoice\Entities\InvoiceLine;
use App\Domain\Invoice\Entities\Party;
use App\Domain\Invoice\ValueObjects\CompanyIdentifier;
use App\Domain\Invoice\ValueObjects\Money;
use App\Domain\Invoice\Renderers\InvoiceGeneratorService;
use App\Domain\Invoice\Renderers\DomPdfRendererStrategy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use DateTimeImmutable;
use PhpParser\Node\Expr\Throw_;
use Throwable;
use Illuminate\Support\Facades\DB;

class GenerateInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected InvoiceData $invoiceData,
        protected string $requestId
    ) {}

    public function handle(InvoiceBuilder $builder): void
    {
        try {
            $this->updateStatus('processing');

            // 1. Map DTOs to Domain Entities
            $supplier = $this->mapCompanyToParty($this->invoiceData->supplier);
            $customer = $this->mapCompanyToParty($this->invoiceData->customer);

            // 2. Build the Invoice Aggregate
            $builder->setMetadata(
                series: $this->invoiceData->series,
                number: $this->invoiceData->number,
                issueDate: new DateTimeImmutable($this->invoiceData->issueDate)
            )
                ->setSupplier($supplier)
                ->setCustomer($customer);

            foreach ($this->invoiceData->items as $item) {
                $builder->addLine($this->mapLineToEntity($item));
            }

            $invoice = $builder->build();

            // 3. Render using the Service and Strategy
            // Note: In a production app, we might resolve the strategy from the container
            // or a factory based on user preference (PDF vs XML).
            $renderer = new DomPdfRendererStrategy();
            $generator = new InvoiceGeneratorService($renderer);

            $content = $generator->generate($invoice);

            // 4. Store the Result
            $path = "invoices/{$this->requestId}.pdf";
            Storage::disk('local')->put($path, $content);

            // D. Mark as Completed
            DB::table('invoice_requests')
                ->where('id', $this->requestId)
                ->update([
                    'status' => 'completed',
                    'file_path' => $path,
                    'updated_at' => now(),
                ]);
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }

    private function mapCompanyToParty(CompanyData $data): Party
    {
        return new Party(
            name: $data->name,
            address: $data->address,
            identifier: new CompanyIdentifier(
                cui: $data->cui,
                // Dynamic data from DTO (mapped from registration_number in JSON)
                registrationNumber: $data->registrationNumber ?? '',
                isVatPayer: str_starts_with(strtoupper($data->cui), 'RO')
            )
        );
    }

    private function mapLineToEntity(InvoiceLineData $data): InvoiceLine
    {
        return new InvoiceLine(
            description: $data->name,
            quantity: $data->quantity,
            unitPrice: Money::fromAmount($data->unitPrice),
            vatPercentage: $data->vatRate
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $this->updateStatus('failed');
        // Optional: Log the specific error for internal debugging
    }

    private function updateStatus(string $status): void
    {
        DB::table('invoice_requests')
            ->where('id', $this->requestId)
            ->update([
                'status' => $status,
                'updated_at' => now()
            ]);
    }
}
