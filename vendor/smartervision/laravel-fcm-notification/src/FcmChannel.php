<?php

namespace Benwilkins\FCM;

use GuzzleHttp\Client;
use Illuminate\Notifications\Notification;

/**
 * Class FcmChannel.
 */
class FcmChannel
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $projectId;

    /**
     * @param Client $client
     * @param string $accessToken
     * @param string $projectId
     */
    public function __construct(Client $client, $accessToken, $projectId)
    {
        $this->client = $client;
        $this->accessToken = $accessToken;
        $this->projectId = $projectId;
    }

    /**
     * Build the dynamic API URI.
     *
     * @return string
     */
    private function getApiUri()
    {
        return 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';
    }

    /**
     * @param mixed $notifiable
     * @param Notification $notification
     * @return mixed
     */
    public function send($notifiable, Notification $notification)
    {
        /** @var FcmMessage $message */
        $message = $notification->toFcm($notifiable);

        if (is_null($message->getTo()) && is_null($message->getCondition())) {
            if (!$to = $notifiable->routeNotificationFor('fcm', $notification)) {
                return;
            }

            $message->to($to);
        }

        $response_array = [];

        if (is_array($message->getTo())) {
            $chunks = array_chunk($message->getTo(), 1000);
            foreach ($chunks as $chunk) {
                $message->to($chunk);

                $response = $this->client->post($this->getApiUri(), [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'body' => $message->formatData(),
                ]);

                array_push($response_array, \GuzzleHttp\json_decode($response->getBody(), true));
            }
        } else {
            $response = $this->client->post($this->getApiUri(), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'body' => $message->formatData(),
            ]);

            array_push($response_array, \GuzzleHttp\json_decode($response->getBody(), true));
        }

        return $response_array;
    }
}
