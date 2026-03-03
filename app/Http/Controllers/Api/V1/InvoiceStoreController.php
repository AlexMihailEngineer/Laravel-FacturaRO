<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Jobs\GenerateInvoicePdfJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class InvoiceStoreController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreInvoiceRequest $request): JsonResponse
    {
        $jobId = Str::uuid()->toString();

        // Dispatch job in the background
        GenerateInvoicePdfJob::dispatch($request->validated());
        
        return response()->json([
            'message' => 'Invoice accepted for processing',
            'job_id' => $jobId,
        ], 202);
    }
}
