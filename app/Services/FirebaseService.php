<?php


namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        // Use ServiceAccount for proper initialization
        $serviceAccount = storage_path('firebase.json');

        // Create the Firebase factory using service account credentials
        $firebase = (new Factory)->withServiceAccount($serviceAccount);
        $this->messaging = $firebase->createMessaging();
    }

    public function sendPushNotification($token, $title, $body, $data = null)
    {
        $message = CloudMessage::new()
            ->withTarget('token', $token)
            ->withNotification(['title' => $title, 'body' => $body]);

        try {
            $this->messaging->send($message);
        } catch (\Exception $e) {
            // Handle error
            dd($e->getMessage());
        }
    }
}
