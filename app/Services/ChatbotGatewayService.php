<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChatbotGatewayService
{
    public function validateMagicToken(string $token): array
    {
        $validateUrl = config('chatbot.validate_url');
        $internalApiKey = config('chatbot.internal_api_key');
        $applicationCode = config('chatbot.application_code', 'wfh');

        if (!$validateUrl || !$internalApiKey) {
            return ['valid' => false];
        }

        try {
            $response = Http::timeout(5)
                ->asForm()
                ->withHeaders([
                    'X-INTERNAL-API-KEY' => $internalApiKey,
                    'Accept' => 'application/json',
                ])
                ->post($validateUrl, [
                    'token' => $token,
                    'application_code' => $applicationCode,
                ]);
        } catch (\Throwable $e) {
            return ['valid' => false];
        }

        if (!$response->successful()) {
            return ['valid' => false];
        }

        $data = $response->json() ?: [];

        if (empty($data['valid']) || empty($data['app_user_id'])) {
            return ['valid' => false];
        }

        return [
            'valid' => true,
            'app_user_id' => (string) $data['app_user_id'],
        ];
    }
}
