<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Illuminate\Support\Facades\Log;
use App\Models\DeviceToken;

class PushNotificationService
{
    protected $messaging;

    public function __construct()
    {
        try {
            // Auto-discovery handles storage/app/firebase.json if env configured
            $factory = (new Factory)->withServiceAccount(storage_path('app/firebase.json'));
            $this->messaging = $factory->createMessaging();
        } catch (\Throwable $e) {
            Log::error("Firebase Init Error: " . $e->getMessage());
            $this->messaging = null; // Fail safe
        }
    }

    /**
     * Send push notification to a specific user
     */
    public function sendToUser($userId, $title, $body, $data = [])
    {
        if (!$this->messaging) return false;

        $tokens = DeviceToken::where('user_id', $userId)->pluck('token')->toArray();

        if (empty($tokens)) return false;

        $notification = Notification::create($title, $body);
        $successCount = 0;

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification)
                    ->withData($data);

                $this->messaging->send($message);
                $successCount++;
            } catch (NotFound $e) {
                // Token invalid, remove it
                DeviceToken::where('token', $token)->delete();
            } catch (\Throwable $e) {
                Log::error("FCM Send Error: " . $e->getMessage());
            }
        }

        return $successCount > 0;
    }
}
