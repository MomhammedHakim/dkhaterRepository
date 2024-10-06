<?php

namespace Benwilkins\FCM;

/**
 * Class FcmMessage.
 */
class FcmMessage
{
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';

    /**
     * @var string|array
     */
    private $to;

    /**
     * @var array
     */
    private $notification;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string normal|high
     */
    private $priority = self::PRIORITY_NORMAL;

    /**
     * @var string
     */
    private $condition;

    /**
     * @var string
     */
    private $collapseKey;

    /**
     * @var int
     */
    private $timeToLive;

    /**
     * @var string
     */
    private $packageName;

    /**
     * @param string|array $recipient
     * @param bool $recipientIsTopic
     * @return $this
     */
    public function to($recipient, $recipientIsTopic = false)
    {
        if ($recipientIsTopic && is_string($recipient)) {
            $this->to = '/topics/' . $recipient;
        } else {
            $this->to = $recipient;
        }

        return $this;
    }

    /**
     * @return string|array|null
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * The notification object to send to FCM. `title` and `body` are required.
     * @param array $params ['title' => '', 'body' => '', 'sound' => '', 'icon' => '', 'click_action' => '']
     * @return $this
     */
    public function content(array $params)
    {
        $this->notification = $params;

        return $this;
    }

    /**
     * @param array|null $data
     * @return $this
     */
    public function data($data = null)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param string $priority
     * @return $this
     */
    public function priority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param string $condition
     * @return $this
     */
    public function condition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @return string
     */
    public function getCollapseKey()
    {
        return $this->collapseKey;
    }

    /**
     * @param string $collapseKey
     * @return $this
     */
    public function collapseKey($collapseKey)
    {
        $this->collapseKey = $collapseKey;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeToLive()
    {
        return $this->timeToLive;
    }

    /**
     * @param int $timeToLive
     * @return $this
     */
    public function timeToLive($timeToLive)
    {
        $this->timeToLive = $timeToLive;

        return $this;
    }

    /**
     * @return string
     */
    public function formatData()
    {
        $payload = [
            'message' => [
                'token' => $this->to,
                'notification' => [
                    'title' => $this->notification['title'],
                    'body' => $this->notification['body'],
                ],
                'data' => $this->data,
                'android' => [
                    'notification' => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                ],
            ],
        ];
        return \GuzzleHttp\json_encode($payload);
    }
}
