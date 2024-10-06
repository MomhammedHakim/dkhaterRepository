<?php

namespace Benwilkins\FCM;

use GuzzleHttp\Client;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Google_Client;
use Illuminate\Support\Facades\DB;
/**
 * Class FcmNotificationServiceProvider.
 */
class FcmNotificationServiceProvider extends ServiceProvider
{
    /**
     * Register.
     */
    public function register()
    {

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('fcm', function () {
                $accessToken = $this->getAccessToken();
                $projectId = DB::table('app_settings')->where('key', 'firebase_project_id')->value('value');
                return new FcmChannel(app(Client::class), $accessToken, $projectId);
            });
        });
    }



    private function getAccessToken()
    {
        // Load your service account credentials
        $credentialsPath = config('services.fcm.service_account');
        $client = new Google_Client();

        // Set the service account credentials
        $client->setAuthConfig($credentialsPath);

        // Add the required scope for Firebase Cloud Messaging
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        // Fetch the access token
        $accessToken = $client->fetchAccessTokenWithAssertion();

        // Return the access token
        return $accessToken['access_token'];
    }

}
