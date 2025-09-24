<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WebhookController extends Controller
{
    /**
     * Receive webhook data from n8n
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function receiveWebhookData(Request $request): JsonResponse
    {
        try {
            // Log the incoming request for debugging
            Log::info('Webhook received from n8n', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);

            // Validate the incoming JSON data
            $validator = Validator::make($request->all(), [
                'city' => 'nullable|string|max:255',
                'propertyType' => 'nullable|string|max:255',
                'price' => 'nullable|string|max:255',
                'priceRange' => 'nullable|string|max:255',
                'room' => 'nullable|string|max:255',
                'area' => 'nullable|string|max:255',
                'currency' => 'nullable|string|max:10',
                'listingType' => 'nullable|string|max:255',
                'bathrooms' => 'nullable|string|max:255',
                'viewType' => 'nullable|string|max:255',
                'bedrooms' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                Log::error('Webhook validation failed', [
                    'errors' => $validator->errors(),
                    'data' => $request->all()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get validated data
            $validatedData = $validator->validated();

            // Log the processed data
            Log::info('Webhook data processed successfully', [
                'processed_data' => $validatedData,
                'timestamp' => now()
            ]);

            // Here you can add your business logic to process the data
            // For example: save to database, trigger other processes, etc.
            $this->processWebhookData($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Webhook data received and processed successfully',
                'data' => $validatedData,
                'timestamp' => now()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error while processing webhook',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process the webhook data (add your business logic here)
     *
     * @param array $data
     * @return void
     */
    private function processWebhookData(array $data): void
    {
        // Add your custom processing logic here
        // Examples:
        // - Save search data to database
        // - Trigger property search
        // - Send notifications
        // - Update analytics
        
        Log::info('Processing webhook data', [
            'city' => $data['city'] ?? null,
            'propertyType' => $data['propertyType'] ?? null,
            'listingType' => $data['listingType'] ?? null,
            'processing_timestamp' => now()
        ]);
    }

    /**
     * Health check endpoint for webhook
     *
     * @return JsonResponse
     */
    public function healthCheck(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Webhook endpoint is active',
            'timestamp' => now()
        ], 200);
    }
}