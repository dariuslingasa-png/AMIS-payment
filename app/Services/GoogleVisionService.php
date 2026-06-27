<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleVisionService
{
    /**
     * Perform Document Text Detection (OCR) on an image file path.
     *
     * @param string $filePath Absolute path to the image file
     * @return array
     */
    public function scanReceipt(string $filePath): array
    {
        $apiKey = env('GOOGLE_VISION_KEY');

        if (empty($apiKey)) {
            Log::warning('Google Vision OCR skipped: GOOGLE_VISION_KEY is not configured in .env');
            return [
                'success' => false,
                'status' => 'skipped',
                'raw_text' => null,
                'detected_ref' => null,
                'detected_amount' => null,
            ];
        }

        if (!file_exists($filePath)) {
            Log::error("Google Vision OCR failed: File not found at {$filePath}");
            return [
                'success' => false,
                'status' => 'error',
                'raw_text' => 'File not found locally for OCR processing.',
                'detected_ref' => null,
                'detected_amount' => null,
            ];
        }

        try {
            $imageBytes = file_get_contents($filePath);
            $base64Image = base64_encode($imageBytes);

            $payload = [
                'requests' => [
                    [
                        'image' => [
                            'content' => $base64Image,
                        ],
                        'features' => [
                            [
                                'type' => 'DOCUMENT_TEXT_DETECTION',
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://vision.googleapis.com/v1/images:annotate?key={$apiKey}", $payload);

            if ($response->failed()) {
                Log::error('Google Vision OCR API error: ' . $response->body());
                return [
                    'success' => false,
                    'status' => 'failed',
                    'raw_text' => 'API returned error status: ' . $response->status(),
                    'detected_ref' => null,
                    'detected_amount' => null,
                ];
            }

            $data = $response->json();
            $textAnnotations = $data['responses'][0]['textAnnotations'] ?? [];

            if (empty($textAnnotations)) {
                return [
                    'success' => true,
                    'status' => 'no_text',
                    'raw_text' => '',
                    'detected_ref' => null,
                    'detected_amount' => null,
                ];
            }

            // The first element contains the entire raw text string detected in the image
            $rawText = $textAnnotations[0]['description'] ?? '';

            // Clean up the text for parser regex checking
            $cleanText = str_replace(["\r", "\n"], " ", $rawText);

            // Extract Reference Number (GCash, Maya, BDO)
            $detectedRef = $this->parseReferenceNumber($cleanText);

            // Extract Amounts
            $detectedAmount = $this->parseAmount($cleanText);

            return [
                'success' => true,
                'status' => 'processed',
                'raw_text' => $rawText,
                'detected_ref' => $detectedRef,
                'detected_amount' => $detectedAmount,
            ];

        } catch (\Exception $e) {
            Log::error('Google Vision OCR Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 'failed',
                'raw_text' => 'Exception occurred: ' . $e->getMessage(),
                'detected_ref' => null,
                'detected_amount' => null,
            ];
        }
    }

    /**
     * Attempt to extract the transaction reference number using common Philippine e-wallet/bank regex formats.
     */
    private function parseReferenceNumber(string $text): ?string
    {
        // 1. GCash Reference Number: Typically 13 consecutive digits starting with 5 or 9
        // e.g. 5012 3456 7890 1, or 9012345678901
        // Let's strip spaces/dashes between digits when checking for 13-digit numbers
        $normalizedText = preg_replace('/(?<=\d)\s+(?=\d)/', '', $text); // remove spaces between digits
        if (preg_match('/\b(5\d{12}|9\d{12})\b/', $normalizedText, $matches)) {
            return $matches[1];
        }

        // 2. Generic "Ref No / Reference No" labels
        // matches "Ref. No: 12345678" or "Ref No 90123456"
        if (preg_match('/Ref(?:erence)?\s*(?:No\.?)?\s*[:\-]?\s*([A-Za-z0-9]+)/i', $text, $matches)) {
            return $matches[1];
        }

        // 3. Any 9 to 15 digit consecutive number block as a fallback
        if (preg_match('/\b(\d{9,15})\b/', $text, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Attempt to extract decimal amounts in the text (like ₱46,500.00 or 46500.00).
     */
    private function parseAmount(string $text): ?float
    {
        // Matches values with currency symbol first: e.g. ₱46,500.00 or PHP 46,500.00
        if (preg_match('/(?:₱|PHP|Php|PHP\s*|Php\s*)\s*([\d,]+\.\d{2})\b/', $text, $matches)) {
            $cleanNum = str_replace(',', '', $matches[1]);
            return (float) $cleanNum;
        }

        // Matches generic numbers with two decimals: e.g. 46,500.00 or 500.00
        if (preg_match('/\b(\d{1,3}(?:,\d{3})*\.\d{2})\b/', $text, $matches)) {
            $cleanNum = str_replace(',', '', $matches[1]);
            return (float) $cleanNum;
        }

        return null;
    }
}
