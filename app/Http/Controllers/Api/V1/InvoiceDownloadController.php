<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\JsonResponse;

class InvoiceDownloadController extends Controller
{
    /**
     * Streams the generated PDF to the client.
     */
    public function __invoke(string $id): StreamedResponse|JsonResponse
    {
        $invoiceRequest = DB::table('invoice_requests')
            ->where('id', $id)
            ->first();

        // 1. Guard: Does the record exist?
        if (!$invoiceRequest) {
            return response()->json(['message' => 'Invoice record not found.'], 404);
        }

        // 2. Guard: Is it actually ready?
        if ($invoiceRequest->status !== 'completed' || empty($invoiceRequest->file_path)) {
            // 425 Too Early is the semantically correct HTTP code for this
            return response()->json(['message' => 'Invoice is still processing.'], 425);
        }

        // 3. Guard: Does the file exist in private storage?
        if (!Storage::disk('local')->exists($invoiceRequest->file_path)) {
            return response()->json(['message' => 'File not found on server.'], 404);
        }

        // 4. Stream the file
        // We use the series and number for the filename so the user gets a friendly name
        $downloadName = sprintf(
            'factura_%s_%s.pdf',
            $invoiceRequest->series,
            $invoiceRequest->number
        );

        return Storage::download($invoiceRequest->file_path, $downloadName);
    }
}
