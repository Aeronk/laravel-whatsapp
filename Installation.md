# Installation Guide

## Prerequisites

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- Composer
- Meta WhatsApp Business Account
- Meta Developer App with WhatsApp product

## Step 1: Install Package

```bash
composer require katema/laravel-whatsapp
```

## Step 2: Publish Configuration and Migrations

```bash
php artisan vendor:publish --tag=whatsapp-config
php artisan vendor:publish --tag=whatsapp-migrations
```

## Step 3: Run Migrations

```bash
php artisan migrate
```

## Step 4: Configure Environment Variables

Copy the contents from `.env.example` and add to your `.env` file:

```env
WHATSAPP_ACCESS_TOKEN=your_access_token
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_BUSINESS_ACCOUNT_ID=your_business_account_id
WHATSAPP_VERIFY_TOKEN=your_custom_verify_token
```

### Getting Your Credentials

#### Access Token
1. Go to [Meta for Developers](https://developers.facebook.com/)
2. Select your app
3. Navigate to WhatsApp > API Setup
4. Copy the temporary access token (valid 24 hours)
5. For permanent token: WhatsApp > Configuration > Generate system user token

#### Phone Number ID
1. In your Meta App Dashboard
2. Go to WhatsApp > API Setup
3. Copy the "Phone number ID" under "Send and receive messages"

#### Business Account ID
1. In your Meta App Dashboard
2. Go to WhatsApp > API Setup
3. Find "WhatsApp Business Account ID"

#### Verify Token
Create a random string (e.g., `my_secure_verify_token_2024`)

## Step 5: Set Up Webhook

### Local Development with ngrok

```bash
# Install ngrok
brew install ngrok  # macOS
# or download from https://ngrok.com

# Start your Laravel app
php artisan serve

# In another terminal, start ngrok
ngrok http 8000

# Copy the HTTPS URL (e.g., https://abc123.ngrok.io)
```

### Configure Webhook in Meta Dashboard

1. Go to your Meta App Dashboard
2. Navigate to WhatsApp > Configuration
3. Click "Edit" under Webhook
4. Enter callback URL: `https://yourdomain.com/whatsapp/webhook`
5. Enter verify token: (from your `.env`)
6. Click "Verify and save"
7. Subscribe to webhook fields:
   - `messages`
   - `message_status` (optional but recommended)

## Step 6: Optional - AI Integration

### For OpenAI

```env
WHATSAPP_AI_PROVIDER=openai
OPENAI_API_KEY=sk-...your-key-here
```

Get API key from [OpenAI Platform](https://platform.openai.com/api-keys)

### For Google Gemini

```env
WHATSAPP_AI_PROVIDER=gemini
GEMINI_API_KEY=...your-key-here
```

Get API key from [Google AI Studio](https://makersuite.google.com/app/apikey)

## Step 7: Create Event Listener (Optional)

If you want custom chatbot logic:

```bash
php artisan make:listener HandleWhatsAppMessage
```

Edit `app/Listeners/HandleWhatsAppMessage.php`:

```php
namespace App\Listeners;

use Katema\WhatsApp\Events\MessageReceived;
use Katema\WhatsApp\Services\Chatbot\ChatbotEngine;

class HandleWhatsAppMessage
{
    public function __construct(protected ChatbotEngine $chatbot)
    {
    }

    public function handle(MessageReceived $event): void
    {
        $this->chatbot
            ->addRule(
                fn($msg) => strtolower($msg->content['body'] ?? '') === 'hi',
                fn($msg, $user, $session, $whatsapp) => 
                    $whatsapp->sendMessage($user->phone_number, 'Hello! 👋'),
                priority: 10
            )
            ->process($event->message, $event->user);
    }
}
```

Register in `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    \Katema\WhatsApp\Events\MessageReceived::class => [
        \App\Listeners\HandleWhatsAppMessage::class,
    ],
];
```

## Step 8: Test Your Setup

### Send a Test Message

Create a test route in `routes/web.php`:

```php
use Katema\WhatsApp\Facades\WhatsApp;

Route::get('/test-whatsapp', function () {
    WhatsApp::sendMessage('1234567890', 'Hello from Laravel!');
    return 'Message sent!';
});
```

Visit: `http://localhost:8000/test-whatsapp`

### Test Webhook

Send a message to your WhatsApp number from the test phone number in Meta Dashboard.

Check logs:
```bash
tail -f storage/logs/laravel.log
```

## Troubleshooting

### Webhook Not Receiving Messages

1. Check webhook URL is accessible (use ngrok for local)
2. Verify webhook is subscribed to `messages`
3. Check Laravel logs for errors
4. Ensure CSRF is disabled for webhook endpoint (already handled)

### Message Send Fails

1. Verify access token is valid
2. Check phone number ID is correct
3. Ensure recipient number is registered in test numbers (during development)
4. Check Meta API status

### Database Errors

```bash
php artisan migrate:fresh
```

### Permission Errors

```bash
chmod -R 775 storage bootstrap/cache
```

## Production Deployment

### 1. Generate Permanent Access Token

Instead of temporary token:
1. Create a System User in Meta Business Settings
2. Assign WhatsApp app to system user
3. Generate permanent token

### 2. Secure Your Webhook

Update `config/whatsapp.php`:

```php
'webhook' => [
    'middleware' => ['api', 'throttle:60,1'],
],
```

### 3. Enable Message Retention

Configure in `.env`:

```env
WHATSAPP_MESSAGES_RETENTION_DAYS=90
```

Create a scheduled command:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        \Katema\WhatsApp\Models\WhatsAppMessage::where(
            'created_at',
            '<',
            now()->subDays(config('whatsapp.storage.messages_retention_days'))
        )->delete();
    })->daily();
}
```

### 4. Set Up Queue Workers

For high-volume applications:

```env
QUEUE_CONNECTION=redis
```

Queue the message processing:

```php
use Katema\WhatsApp\Events\MessageReceived;

Event::listen(MessageReceived::class, function ($event) {
    dispatch(new ProcessWhatsAppMessage($event->message, $event->user));
});
```

### 5. Monitor Your Application

- Set up error tracking (Sentry, Bugsnag)
- Monitor webhook failures
- Track message delivery rates
- Set up alerts for API quota limits

## Next Steps

- Read [README.md](README.md) for usage examples
- Check [EXAMPLES.md](EXAMPLES.md) for complete implementations
- Review [config/whatsapp.php](config/whatsapp.php) for all options
- Build your first chatbot!

## Support

For issues and questions:
- GitHub Issues: [github.com/katema/laravel-whatsapp/issues]
- Documentation: [docs.laravel-whatsapp.com]