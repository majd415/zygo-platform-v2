<?php

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    /**
     * Send a push notification via FCM HTTP v1 API.
     */
    public static function sendNotification($token, $title, $body, $data = [])
    {
        Log::info("FCM Initialized: Sending to token $token | Title: $title");
        try {
            $serviceAccountPath = storage_path('app/firebase/service-account.json');
            
            if (!file_exists($serviceAccountPath)) {
                Log::error("FCM Error: Service account file not found at $serviceAccountPath");
                return false;
            }

            $client = new Client();
            $client->setAuthConfig($serviceAccountPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();
            $accessToken = $client->getAccessToken()['access_token'];

            $projectId = json_decode(file_get_contents($serviceAccountPath), true)['project_id'];

            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'channel_id' => 'high_importance_channel',
                            'sound' => 'default',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'priority' => 10,
                            ],
                        ],
                    ],
                ],
            ];

            if (!empty($data)) {
                $payload['message']['data'] = array_map('strval', $data);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/$projectId/messages:send", $payload);

            if ($response->successful()) {
                return true;
            } else {
                Log::error("FCM Error: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("FCM Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple tokens.
     */
    public static function sendBatchNotification($tokens, $title, $body, $data = [])
    {
        foreach ($tokens as $token) {
            self::sendNotification($token, $title, $body, $data);
        }
    }
}
