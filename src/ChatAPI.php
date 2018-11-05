<?php

namespace NotificationChannels\ChatAPI;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use NotificationChannels\ChatAPI\Exceptions\CouldNotSendNotification;

class ChatAPI
{
    /** @var HttpClient HTTP Client */
    protected $http;

    /** @var null|string token from Chat API. */
    protected $token = null;
    protected $api_url = null;

    /**
     * @param null $token
     */
    public function __construct($token = null, $api_url = null)
    {
        $this->api_url      = $api_url;
        $this->token        = $token;
    }

    /**
     * Get HttpClient.
     *
     * @return HttpClient
     */
    protected function httpClient()
    {
        return new HttpClient([
            //'base_uri' => $this->api_url,
            'headers' => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json'
            ]
        ]);
    }

    private function uriWithToken($uri) {
        return rtrim($this->api_url,'/').$uri.'?token='.$this->token;
    }


    /**
     * Creates a group and sends the message to the created group. If the host is iPhone, then the presence of all in the contact list is required.
     *
     * The group will be added to the queue for sending and sooner or later it will be created, even if the phone is disconnected from the Internet or the authorization is not passed.
     * chatId parameter will be returned if group was created on your phone within 20 seconds.
     * @param string $groupName Group name, string, mandatory.
     * @param array $phones An array of phones starting with the country code. You do not need to add your number.
    USA example: ['17472822486'].
     * @param string $message Message text
     * @return array
     */
    public function group($groupName,$phones,$message)
    {
        if (empty($phones)) {
            throw CouldNotSendNotification::receiverNotProvided();
        }
        try {
            $data = [
                'messageText' => $message,
                'phones' => $phones,
                'groupName' => $groupName
            ];

            $res = $this->httpClient()->post($this->uriWithToken('/group'), ['json' => $data]);

            return json_decode($res->getBody()->getContents(), true);
        } catch (ClientException $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithChatAPI($exception->getMessage());
        }
    }

    /**
     * @param $phone integer A phone number starting with the country code. You do not need to add your number.
    USA example: 17472822486. You can also send a chatID
     * @param $message
     * @return array
     */
    public function sendMessage($phone,$message)
    {
        if (empty($phone)) {
            throw CouldNotSendNotification::receiverNotProvided();
        }
        try {
            $data = [
                'body' => $message
            ];

            if (strpos($phone,'@') === false)
                $data['phone'] = preg_replace('/[^0-9]/','',$phone);
            else
                $data['chatId'] = $phone;

            $res = $this->httpClient()->post($this->uriWithToken('/sendMessage'), ['json' => $data]);

           
            return json_decode($res->getBody()->getContents(), true);
        } catch (ClientException $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithChatAPI($exception->getMessage());
        }
    }

    /**
     * Send a file to a new or existing chat
     * Only one of two parameters is needed to determine the destination - chatId or phone.
     *
     * @param string $phone Send the user phone or chatId
     * @param string $file You can send a file url, full path or file contents
     * @param string|null $filename The file named received by the user
     * @param string|null $mimetype The file mime type
     * @return array
     */
    public function sendFile($phone,$file, $filename=null, $mimetype=null)
    {
        if (empty($phone)) {
            throw CouldNotSendNotification::receiverNotProvided();
        }
        if (empty($file)) {
            throw CouldNotSendNotification::fileNotProvided();
        }
        try {
            $data = [];

            if (filter_var($file, FILTER_VALIDATE_URL) !== FALSE) {


                $data['body'] = $file;

                if ($filename === null) {
                    $filename = pathinfo($file, PATHINFO_BASENAME);
                }
            } else {
                if (file_exists($file)) {
                    if ($filename === null) {
                        $filename = pathinfo($file, PATHINFO_BASENAME);
                    }

                    if ($mimetype === null) {
                        $mimetype = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file);
                    }

                    $file = file_get_contents($file);
                }

                $data['body'] = 'data:'.$mimetype.';base64,'.base64_encode($file);
            }

            $data['filename'] = $filename;

            if (strpos($phone,'@') === false)
                $data['phone'] = preg_replace('/[^0-9]/','',$phone);
            else
                $data['chatId'] = $phone;

            $res = $this->httpClient()->post($this->uriWithToken('/sendFile'), ['json' => $data]);

            return json_decode($res->getBody()->getContents(), true);
        } catch (ClientException $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithChatAPI($exception->getMessage());
        }
    }

    /**
     * @param int $byQuantity  The lastMessageNumber parameter from the last response
     * @param integer $byLastMessageNumber  Displays the last 100 messages. If this parameter is passed, then lastMessageNumber is ignored.
     * @return array
     */
    public function messages($byQuantity=100, $byLastMessageNumber=null)
    {
        try {
            $data = [
                'token' => $this->token
            ];

            if ($byQuantity > 0) {
                $data['last'] = $byQuantity;
            } else {
                $data['lastMessageNumber'] = $byLastMessageNumber;
            }

            $res = $this->httpClient()->get($this->api_url.'/messages?'.http_build_query($data));

            return json_decode($res->getBody()->getContents(), true);

        } catch (ClientException $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithChatAPI($exception->getMessage());
        }
    }

    /**
     * Sets the URL for receiving webhook notifications of new messages and message delivery events (ack).
     * @param string $webhook_url Http or https URL for receiving notifications. For testing, we recommend using requestb.in.
     * @param bool $set
     * @return array
     */
    public function setWebhook($webhook_url, $set=true)
    {
        if (empty($phone)) {
            throw CouldNotSendNotification::receiverNotProvided();
        }
        try {
            $data = [
                'set' => $set,
                'webhookUrl' => $webhook_url
            ];
            $res = $this->httpClient()->post($this->uriWithToken('/webhook'), ['json' => $data]);

            return json_decode($res->getBody()->getContents(), true);
        } catch (ClientException $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithChatAPI($exception->getMessage());
        }
    }

    /**
     * Returns current webhook url.
     * @return array
     */
    public function getWebhook()
    {
        try {
            $res = $this->httpClient()->get($this->uriWithToken('/webhook'));

            return json_decode($res->getBody()->getContents(), true);

        } catch (ClientException $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithChatAPI($exception->getMessage());
        }
    }

    /**
     * Turn on/off ack (message delivered and message viewed) notifications in webhooks. GET method works for the same address.
     * @param bool $ackNotificationsOn
     * @return array
     */
    public function ackNotifications( $ackNotificationsOn=true)
    {
        if (empty($phone)) {
            throw CouldNotSendNotification::receiverNotProvided();
        }
        try {
            $data = [
                'ackNotificationsOn' => $ackNotificationsOn ? 1 : 0
            ];
            $res = $this->httpClient()->post($this->uriWithToken('/settings/ackNotificationsOn'), ['json' => $data]);

            return json_decode($res->getBody()->getContents(), true);
        } catch (ClientException $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithChatAPI($exception->getMessage());
        }
    }

    /**
     * Logout from WhatsApp Web to get new QR code.
     * @return array
     */
    public function logout()
    {
        try {
            $res = $this->httpClient()->get($this->uriWithToken('/logout'));

            return json_decode($res->getBody()->getContents(), true);

        } catch (ClientException $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithChatAPI($exception->getMessage());
        }
    }

    /**
     * Reboot your WhatsApp instance.
     * @return array
     */
    public function reboot()
    {
        try {
            $res = $this->httpClient()->get($this->uriWithToken('/reboot'));

            return json_decode($res->getBody()->getContents(), true);

        } catch (ClientException $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithChatAPI($exception->getMessage());
        }
    }


    /**
     * Get outbound messages queue.
     * @return array
     */
    public function showMessagesQueue()
    {
        try {
            $res = $this->httpClient()->get($this->uriWithToken('/showMessagesQueue'));

            return json_decode($res->getBody()->getContents(), true);

        } catch (ClientException $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithChatAPI($exception->getMessage());
        }
    }

    /**
     * Clear outbound messages queue. This method is needed when you accidentally sent thousands of messages in a row.
     * @return array
     */
    public function clearMessagesQueue()
    {
        try {
            $res = $this->httpClient()->get($this->uriWithToken('/clearMessagesQueue'));

            return json_decode($res->getBody()->getContents(), true);

        } catch (ClientException $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithChatAPI($exception->getMessage());
        }
    }

    /**
     * Get the account status and QR code for authorization. Reauthorization is necessary only in case of changing the device or manually pressing "Logout on all devices" on the phone. Keep the WhastsApp application open during authorization.
     * @return array
     */
    public function status()
    {
        try {
            $res = $this->httpClient()->get($this->uriWithToken('/status'));

            return json_decode($res->getBody()->getContents(), true);

        } catch (ClientException $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithChatAPI($exception->getMessage());
        }
    }

    /**
     * Direct link to QR-code in the form of an image, not base64.
     * @return array
     */
    public function qrCode()
    {
        try {
            $res = $this->httpClient()->get($this->uriWithToken('/qr_code'));

            return json_decode($res->getBody()->getContents(), true);

        } catch (ClientException $exception) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithChatAPI($exception->getMessage());
        }
    }
}
