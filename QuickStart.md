# Quick Start Guide

Get your WhatsApp chatbot running in 5 minutes!

## Installation

```bash
composer require katema/laravel-whatsapp
php artisan vendor:publish --tag=whatsapp-config
php artisan vendor:publish --tag=whatsapp-migrations
php artisan migrate
```

## Configuration

Add to `.env`:

```env
WHATSAPP_ACCESS_TOKEN=your_token
WHATSAPP_PHONE_NUMBER_ID=your_phone_id
WHATSAPP_VERIFY_TOKEN=your_verify_token
```

## Your First Message

```php
use Katema\WhatsApp\Facades\WhatsApp;

WhatsApp::sendMessage('1234567890', 'Hello from Laravel! 🚀');
```

## Simple Chatbot

Create `app/Listeners/MyWhatsAppBot.php`:

```php
<?php

namespace App\Listeners;

use Katema\WhatsApp\Events\MessageReceived;
use Katema\WhatsApp\Services\Chatbot\ChatbotEngine;

class MyWhatsAppBot
{
    public function __construct(protected ChatbotEngine $chatbot)
    {
    }

    public function handle(MessageReceived $event): void
    {
        $this->chatbot
            // Rule 1: Greeting
            ->addRule(
                fn($msg) => in_array(strtolower($msg->content['body'] ?? ''), ['hi', 'hello']),
                fn($msg, $user, $session, $whatsapp) => 
                    $whatsapp->sendMessage($user->phone_number, "Hi! I'm your bot 🤖"),
                priority: 10
            )
            
            // Rule 2: Help
            ->addRule(
                fn($msg) => strtolower($msg->content['body'] ?? '') === 'help',
                fn($msg, $user, $session, $whatsapp) => 
                    $whatsapp->sendMessage(
                        $user->phone_number,
                        "Available commands:\n• help\n• price\n• contact"
                    ),
                priority: 20
            )
            
            // Rule 3: Pricing
            ->addRule(
                fn($msg) => str_contains(strtolower($msg->content['body'] ?? ''), 'price'),
                fn($msg, $user, $session, $whatsapp) => 
                    $whatsapp->sendMessage($user->phone_number, "Our prices start at $10/month"),
                priority: 30
            )
            
            ->process($event->message, $event->user);
    }
}
```

Register in `app/Providers/EventServiceProvider.php`:

```php
use Katema\WhatsApp\Events\MessageReceived;
use App\Listeners\MyWhatsAppBot;

protected $listen = [
    MessageReceived::class => [
        MyWhatsAppBot::class,
    ],
];
```

## Interactive Buttons

```php
use Katema\WhatsApp\Facades\WhatsApp;

WhatsApp::sendInteractive('1234567890', [
    'type' => 'button',
    'body' => ['text' => 'Choose an option:'],
    'action' => [
        'buttons' => [
            ['type' => 'reply', 'reply' => ['id' => 'opt1', 'title' => 'Option 1']],
            ['type' => 'reply', 'reply' => ['id' => 'opt2', 'title' => 'Option 2']],
        ],
    ],
]);
```

## List Menu

```php
WhatsApp::sendInteractive('1234567890', [
    'type' => 'list',
    'body' => ['text' => 'Select from menu:'],
    'action' => [
        'button' => 'View Menu',
        'sections' => [
            [
                'title' => 'Products',
                'rows' => [
                    ['id' => 'prod1', 'title' => 'Product 1', 'description' => '$10'],
                    ['id' => 'prod2', 'title' => 'Product 2', 'description' => '$20'],
                ],
            ],
        ],
    ],
]);
```

## Send Media

```php
// Image
WhatsApp::sendImage(
    '1234567890',
    'https://example.com/image.jpg',
    'Check this out!'
);

// Document
WhatsApp::sendDocument(
    '1234567890',
    'https://example.com/file.pdf',
    'invoice.pdf'
);

// Location
WhatsApp::sendLocation('1234567890', -1.2921, 36.8219, 'Nairobi', 'Kenya');
```

## Create a Form Flow

```php
use Katema\WhatsApp\Services\FlowService;

$flow = app(FlowService::class);

$screens = [
    $flow->buildScreen('FORM', 'Contact Form', [
        $flow->buildForm([
            $flow->buildTextInput('name', 'Your Name', required: true),
            $flow->buildTextInput('email', 'Email', required: true, inputType: 'email'),
            $flow->buildTextArea('message', 'Your Message', required: true),
            $flow->buildFooter('Submit', 'complete'),
        ]),
    ]),
];

$contactFlow = $flow->createFlow('Contact Form', $screens);

// Send to user
WhatsApp::sendFlow('1234567890', $contactFlow->flow_id);
```

## Enable AI Responses

Add to `.env`:

```env
WHATSAPP_AI_PROVIDER=openai
OPENAI_API_KEY=sk-your-key-here
```

Now unmatched messages automatically get AI responses!

## Set Up Webhook

### Local Development (ngrok)

```bash
# Terminal 1
php artisan serve

# Terminal 2
ngrok http 8000
```

Copy the ngrok HTTPS URL (e.g., `https://abc123.ngrok.io`)

### Configure in Meta

1. Go to Meta Developer Dashboard
2. Select your app
3. WhatsApp > Configuration
4. Webhook URL: `https://abc123.ngrok.io/whatsapp/webhook`
5. Verify Token: (from your `.env`)
6. Subscribe to `messages`

## Test It!

1. Send a message to your WhatsApp test number
2. Type "hi" → Bot responds!
3. Type "help" → See commands
4. Type "price" → Get pricing info

## Common Commands

```php
// Get user
$user = WhatsAppUser::where('phone_number', '1234567890')->first();

// Get messages
$messages = $user->messages()->incoming()->latest()->take(10)->get();

// Get active session
$session = $user->activeSession()->first();

// Store data in session
$session->setContext('step', 'awaiting_payment');
$session->setContext('amount', 100);

// Retrieve data
$step = $session->getContext('step');

// Block user
$user->block();
```

## Next Steps

- Read the full [README.md](README.md)
- Check [EXAMPLES.md](EXAMPLES.md) for complete implementations
- Review [INSTALLATION.md](INSTALLATION.md) for production setup
- Join our community for support

## Need Help?

- Documentation: [docs.laravel-whatsapp.com]
- Issues: [github.com/katema/laravel-whatsapp/issues]
- Discussions: [github.com/katema/laravel-whatsapp/discussions]

Happy coding! 🚀