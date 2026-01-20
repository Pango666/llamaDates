<?php

namespace App\Http\Controllers;

use App\Facades\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestWhatsAppController extends Controller
{
    /**
     * Send a test WhatsApp message
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendTestMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $response = WhatsApp::sendMessage(
                $request->phone,
                $request->message
            );

            return response()->json([
                'success' => $response['success'],
                'data' => $response['data'] ?? null,
                'message' => $response['success'] 
                    ? 'Message sent successfully!' 
                    : 'Failed to send message.',
            ], $response['status'] ?? 200);

        } catch (\Exception $e) {
            Log::error('Test WhatsApp Error: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending the message.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Send a test WhatsApp template message
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendTestTemplate(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'template_name' => 'required|string',
            'language' => 'string|default:es_MX',
            'components' => 'array|nullable',
        ]);

        try {
            $response = WhatsApp::sendTemplate(
                $request->phone,
                $request->template_name,
                $request->components ?? [],
                $request->language ?? 'es_MX'
            );

            return response()->json([
                'success' => $response['success'],
                'data' => $response['data'] ?? null,
                'message' => $response['success'] 
                    ? 'Template message sent successfully!' 
                    : 'Failed to send template message.',
            ], $response['status'] ?? 200);

        } catch (\Exception $e) {
            Log::error('Test WhatsApp Template Error: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending the template message.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
