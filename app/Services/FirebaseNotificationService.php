<?php
namespace App\Services;

use App\Models\User;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\UserDevice;
use App\Notifications\PushNotification;

class FirebaseNotificationService
{
    /**
     * Generate access token for Firebase Cloud Messaging.
     *
     * @return string|null
     */

    private function generateAccessToken()
    {
        // Check if the token exists in cache
        if (Cache::has('firebase_access_token')) {
            return Cache::get('firebase_access_token');
        }
        try {
            // Path to the service_account.json file
            $credentialsFilePath = storage_path('app/private/service_account.json');
            // Create credentials object
            $credentials = new ServiceAccountCredentials(
                ['https://www.googleapis.com/auth/firebase.messaging'],
                $credentialsFilePath
            );
            // Fetch the token
            $token = $credentials->fetchAuthToken();
            $accessToken = $token['access_token'];
            // Cache the token for 55 minutes
            Cache::put('firebase_access_token', $accessToken, now()->addMinutes(55));
            return $accessToken;
        } catch (\Exception $e) {
            response()->json( ['Error generating access token: ' . $e->getMessage()]);
            return null;
        }
    }
    /**
     * Send push notifications via Firebase Cloud Messaging.
     *
     * @param $to
     * @param string $title
     * @param string $body
     */
    public function sendPushNotificationSync($userIds, $title, $body,$log = false,$notification_type = 'detail',array $data = [])
    {
        // Generate access token for Firebase
        $access_token = $this->generateAccessToken();
        
        // Retrieve the user's device details
        $devices = UserDevice::whereIn('user_id', $userIds)
                ->orderBy('created_at', 'DESC')
                ->get()
                ->groupBy('user_id'); // group devices by user

        $projectId   = config('services.fcm.projectId');
        $fcmEndpoint = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        $clean_description = strip_tags($body);
        foreach ($devices as $userId => $userDevices) {
            $user = User::find($userId);
            if (!$user) {
                continue;
            }
            $id = \Illuminate\Support\Str::uuid()->toString();
            $user->notify(new PushNotification($title, $body, [
                'device_ids' => $userDevices->pluck('id')->toArray(),
                'entity' => $data['entity'],
                'entity_id' => $data['entity_id']
            ], $id));
            $notificationId = $id;
            $fcmData = array_map(
                fn ($value) => is_scalar($value)
                    ? (string) $value
                    : json_encode($value),
                array_merge([
                    'type' => 'notification',
                    'notification_type' => $notification_type,
                    'notification_id' => $notificationId,
                ], $data)
            );
            foreach ($userDevices as $device) {
                try {
                    $message = [
                        'message' => [
                            'token' => $device->device_token,
                            'notification' => [
                                'title' => $title,
                                'body'  => $clean_description,
                            ],
                            'data' => $fcmData,
                            'android' => [
                                'notification' => [
                                    'icon' => asset('img/logo.png'),
                                    'channel_id' => 'jacklap',
                                ],
                            ],
                            'apns' => [
                                'payload' => [
                                    'aps' => [
                                        'sound' => 'default',
                                    ],
                                ],
                            ],
                        ],
                    ];

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $access_token,
                        'Content-Type'  => 'application/json',
                    ])->post($fcmEndpoint, $message);

                    if ($log) {
                        if ($response->status() == 200) {
                            Log::info('Notification sent successfully', [
                                'user_id'   => $user->id,
                                'device_id' => $device->id,
                            ]);
                        } else {
                            Log::error('Error sending FCM notification', [
                                'user_id' => $user->id,
                                'error'   => $response->body(),
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    if ($log) {
                        Log::error('Error sending FCM notification', [
                            'user_id' => $user->id,
                            'error'   => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

    }
}
