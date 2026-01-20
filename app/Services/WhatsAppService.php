<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $token;
    protected $phoneNumberId;
    protected $businessAccountId;
    protected $apiUrl = 'https://graph.facebook.com/v18.0';

    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->businessAccountId = config('services.whatsapp.business_account_id');
    }

    public function sendMessage(string $to, string $message): array
    {
        try {
            $to = preg_replace('/[^0-9]/', '', $to);

            $response = Http::withToken($this->token)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'                => $to,
                    'type'              => 'text',
                    'text'              => [
                        'preview_url' => false,
                        'body'        => $message,
                    ],
                ]);

            return [
                'success' => $response->successful(),
                'data'    => $response->json(),
                'status'  => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp API Error: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'status'  => 500,
            ];
        }
    }

    public function sendTemplate(
        string $to,
        string $templateName,
        array $components = [],
        string $language = 'es_MX'
    ): array {
        try {
            $to = preg_replace('/[^0-9]/', '', $to);

            $payload = [
                'messaging_product' => 'whatsapp',
                'to'                => $to,
                'type'              => 'template',
                'template'          => [
                    'name'     => $templateName,
                    'language' => [
                        'code' => $language,
                    ],
                ],
            ];

            if (!empty($components)) {
                $payload['template']['components'] = $components;
            }

            $response = Http::withToken($this->token)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", $payload);

            return [
                'success' => $response->successful(),
                'data'    => $response->json(),
                'status'  => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Template Error: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'status'  => 500,
            ];
        }
    }
}
