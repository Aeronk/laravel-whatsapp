<?php

namespace Katema\WhatsApp\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'whatsapp:install';
    protected $description = 'Install the Laravel WhatsApp package';

    public function handle(): void
    {
        $this->info('Installing Laravel WhatsApp Package...');

        $this->publishConfig();
        $this->publishMigrations();
        $this->registerEnvVariables();

        $this->info('Installation complete!');
    }

    protected function publishConfig(): void
    {
        $this->call('vendor:publish', [
            '--provider' => 'Katema\WhatsApp\WhatsAppServiceProvider',
            '--tag' => 'whatsapp-config'
        ]);
    }

    protected function publishMigrations(): void
    {
        $this->call('vendor:publish', [
            '--provider' => 'Katema\WhatsApp\WhatsAppServiceProvider',
            '--tag' => 'whatsapp-migrations'
        ]);
    }

    protected function registerEnvVariables(): void
    {
        if (File::exists(base_path('.env'))) {
            file_put_contents(base_path('.env'), PHP_EOL . $this->getEnvStubs(), FILE_APPEND);
            $this->info('Added WhatsApp environment variables to .env');
        }
    }

    protected function getEnvStubs(): string
    {
        return <<<EOT
WHATSAPP_ACCESS_TOKEN=
WHATSAPP_PHONE_NUMBER_ID=
WHATSAPP_BUSINESS_ACCOUNT_ID=
WHATSAPP_APP_SECRET=
WHATSAPP_VERIFY_TOKEN=
EOT;
    }
}
