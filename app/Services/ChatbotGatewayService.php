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
                ->asJson()
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
        $appUserId = data_get($data, 'app_user_id')
            ?: data_get($data, 'data.app_user_id')
            ?: data_get($data, 'user.app_user_id')
            ?: data_get($data, 'data.user.app_user_id');
        $isValid = data_get($data, 'valid') === true
            || data_get($data, 'success') === true
            || data_get($data, 'status') === 'valid';

        if (!$isValid || empty($appUserId)) {
            return ['valid' => false];
        }

        return [
            'valid' => true,
            'app_user_id' => (string) $appUserId,
        ];
    }
}
