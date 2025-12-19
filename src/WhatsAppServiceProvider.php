<?php

namespace Katema\WhatsApp;

use Illuminate\Support\ServiceProvider;
use Katema\WhatsApp\Services\WhatsAppService;
use Katema\WhatsApp\Services\FlowService;
use Katema\WhatsApp\Services\AI\AIServiceManager;

class WhatsAppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/whatsapp.php', 'whatsapp');

        $this->app->singleton(WhatsAppService::class, function ($app) {
            return new WhatsAppService(
                config('whatsapp.access_token'),
                config('whatsapp.phone_number_id'),
                config('whatsapp.verify_token')
            );
        });

        $this->app->singleton(FlowService::class);
        $this->app->singleton(\Katema\WhatsApp\Services\Chatbot\FlowCrypto::class);
        $this->app->singleton(AIServiceManager::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/whatsapp.php');

        // Register middleware
        $this->app['router']->aliasMiddleware(
            'whatsapp.verify_signature',
            \Katema\WhatsApp\Http\Middleware\VerifyWebhookSignature::class
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Katema\WhatsApp\Console\InstallCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/whatsapp.php' => config_path('whatsapp.php'),
            ], 'whatsapp-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'whatsapp-migrations');
        }

        // Register event listener
        $this->app->make('events')->listen(
            \Katema\WhatsApp\Events\MessageReceived::class,
            \Katema\WhatsApp\Listeners\ProcessIncomingMessage::class
        );
    }
}