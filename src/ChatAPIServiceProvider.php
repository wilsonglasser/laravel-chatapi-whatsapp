<?php

namespace NotificationChannels\ChatAPI;

use Illuminate\Support\ServiceProvider;

class ChatAPIServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->when(ChatAPIChannel::class)
            ->needs(ChatAPI::class)
            ->give(function () {
                $config = config('services.chatapi');
                return new ChatAPI(
                    $config['token'],
                    $config['api_url']
                );
            });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
