# Laravel client and channel for  WhatsApp API (by Chat API)


[![Latest Version on Packagist](https://img.shields.io/packagist/v/wilsonglasser/laravel-chatapi-whatsapp.svg?style=flat-square&r=1)](https://packagist.org/packages/wilsonglasser/laravel-chatapi-whatsapp)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://poser.pugx.org/wilsonglasser/laravel-chatapi-whatsapp/downloads.png)](https://packagist.org/packages/wilsonglasser/laravel-chatapi-whatsapp)

This package makes it easy to send WhatsApp messages using [Chat API](https://chat-api.com/) with Laravel 5.

## Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Available Message methods](#available-message-methods)

## Installation

You can install the package via composer:

``` bash
composer require wilsonglasser/laravel-chatapi-whatsapp
```

You must install the service provider:

```php
// config/app.php
'providers' => [
    ...
    NotificationChannels\ChatAPI\ChatAPIServiceProvider::class,
],
```

## Configuration

Configure your credentials:

```php
// config/services.php
...
'chatapi' => [
    'token'          => env('CHATAPI_TOKEN', ''),
    'api_url'       => env('CHATAPI_URL', ''),
],
...
```

## Usage

You can now use the channel in your `via()` method inside the Notification class.

``` php
use NotificationChannels\ChatAPI\ChatAPIChannel;
use NotificationChannels\ChatAPI\ChatAPIMessage;
use Illuminate\Notifications\Notification;

class InvoicePaid extends Notification
{
    public function via($notifiable)
    {
        return [ChatAPIChannel::class];
    }

    public function toZenvia($notifiable)
    {
        return ChatAPIMessage::create()
            ->to($notifiable->phone) // your user phone
            ->file('/path/to/file','My Photo.jpg');
            ->content('Your invoice has been paid');
    }
}
```

### Routing a message

You can either send the notification by providing with the chat id of the recipient to the to($phone) method like shown in the above example or add a routeNotificationForChatAPI() method in your notifiable model:

```php
...
/**
 * Route notifications for the Telegram channel.
 *
 * @return int
 */
public function routeNotificationForChatAPI()
{
    return $this->phone;
}
...
```

### Available Message methods

- `to($phone)`: (integer) Recipient's phone.
- `content('message')`: (string) Message.
- `file('/path/to/file','My Photo.jpg')`: (string) File real path, you can also send the file contents and pass two additional params for file name and file mime type (required)
- `file('/path/to/file','My Photo.jpg','image/jpg')`
