<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\DataTransferObjects\InvoiceData;
use App\Jobs\GenerateInvoicePdfJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceStoreController extends Controller
{
    public function __invoke(StoreInvoiceRequest $request)
    {
        // 1. Transform Request to DTO
        $dto = InvoiceData::from($request->validated());

        // 2. Persist the Intent (Database Record)
        // This acts as the "Source of Truth" for Phase 2 Status Tracking
        DB::table('invoice_requests')->insert([
            'id' => (string) Str::uuid(),
            'status' => 'pending',
            'series' => $dto->series,
            'number' => $dto->number,
            'issue_date' => $dto->issueDate,

            'supplier_name' => $dto->supplier->name,
            'supplier_cui' => $dto->supplier->cui,
            'supplier_registration_number' => $dto->supplier->registrationNumber,
            'supplier_address' => $dto->supplier->address,

            'customer_name' => $dto->customer->name,
            'customer_cui' => $dto->customer->cui,
            'customer_registration_number' => $dto->customer->registrationNumber,
            'customer_address' => $dto->customer->address,

            'total_net' => $request->input('total_amount'), // Simplified for now
            'total_vat' => 0, // Should be calculated or passed
            'total_gross' => $request->input('total_amount'),

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Dispatch the Job
        GenerateInvoicePdfJob::dispatch($dto);

        return response()->json([
            'message' => 'Invoice generation started.',
            'status' => 'accepted'
        ], 202);
    }
}
