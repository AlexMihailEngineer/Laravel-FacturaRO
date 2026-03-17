<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InvoiceStatusController extends Controller
{
    public function __invoke(string $id): JsonResponse
    {
        $invoiceRequest = DB::table('invoice_requests')
            ->where('id', $id)
            ->first();

        if (!$invoiceRequest) {
            return response()->json([
                'message' => 'Invoice request not found.'
            ], 404);
        }

        $response = [
            'id' => $invoiceRequest->id,
            'status' => $invoiceRequest->status,
            'created_at' => $invoiceRequest->created_at,
        ];

        // Add the download link only if the status is completed
        if ($invoiceRequest->status === 'completed') {
            $response['download_url'] = route('api.v1.facturi.download', ['id' => $id]);
        }

        // Include error context if the job failed
        if ($invoiceRequest->status === 'failed') {
            $response['message'] = 'Generation failed. Please check input data or contact support.';
        }

        return response()->json($response);
    }
}
