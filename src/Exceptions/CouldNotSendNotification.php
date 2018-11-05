<?php

namespace NotificationChannels\ChatAPI\Exceptions;

use GuzzleHttp\Exception\ClientException;

class CouldNotSendNotification extends \Exception
{
    /**
     * Thrown when there's a bad request and an error is responded.
     *
     * @param ClientException $exception
     *
     * @return static
     */
    public static function serviceRespondedWithAnError(ClientException $exception)
    {
        $statusCode  = $exception->getResponse()->getStatusCode();
        $description = $exception->getMessage();

        if ($result = json_decode($exception->getResponse()->getBody())) {
            $description = $result->description ?: $description;
        }

        return new static("ChatAPI responded with an error `{$statusCode} - {$description}`");
    }

    /**
     * Thrown when we're unable to communicate with Zenvia.
     *
     * @return static
     */
    public static function couldNotCommunicateWithChatAPI($message)
    {
        return new static($message);
    }

    /**
     * Thrown when there is no receiver provided
     *
     * @return static
     */
    public static function receiverNotProvided()
    {
        return new static('Chat receiver not provided');
    }


    /**
     * Thrown when there is no receiver provided
     *
     * @return static
     */
    public static function fileNotProvided()
    {
        return new static('Chat file not provided');
    }


}
