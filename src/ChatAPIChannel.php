<?php

namespace NotificationChannels\ChatAPI;

use Illuminate\Notifications\Notification;

class ChatAPIChannel
{
    /**
     * @var ChatAPI
     */
    protected $chatapi;

    /**
     * Channel constructor.
     *
     * @param ChatAPI $Zenvia
     */
    public function __construct(ChatAPI $chatapi)
    {
        $this->chatapi = $chatapi;
    }

    /**
     * Send the given notification.
     *
     * @param mixed        $notifiable
     * @param Notification $notification
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toChatAPI($notifiable);

        if (! $to = $notifiable->routeNotificationFor('chatapi')) {
            $to = $message->to;
        }

        $params = $message->toArray();

        // check if msg is empty
        if (!empty($message->msg)) {
            $this->chatapi->sendMessage($to, $message->msg);
        }

        if ($params['file'] !== false) {
            $file = $params['file'];
            $this->chatapi->sendFile($to, $file['body'], $file['filename'], $file['mimetype']);
        }
    }
}
