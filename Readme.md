# Laravel WhatsApp Automation Package

A comprehensive Laravel package for building WhatsApp chatbots, flows, and AI-powered automation using Meta's WhatsApp Cloud API.

## Features

✅ **Meta WhatsApp Cloud API Integration** - Full API support  
✅ **Webhook Handling** - Automatic message and status processing  
✅ **WhatsApp Flows v7.3+** - Build interactive forms and experiences  
✅ **Chatbot Engine** - Rule-based logic with AI fallback  
✅ **AI Integration** - OpenAI & Google Gemini support  
✅ **Database Models** - Messages, users, sessions, and flows  
✅ **Event-Driven** - Laravel events for extensibility  
✅ **Clean API** - Intuitive, Laravel-native interface

## Installation

```bash
# Easy installation command
php artisan whatsapp:install

# Or manually publish
php artisan vendor:publish --tag=whatsapp-config
php artisan vendor:publish --tag=whatsapp-migrations
php artisan migrate
```

## Configuration

Add to your `.env`:

```env
WHATSAPP_ACCESS_TOKEN=your_access_token
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_BUSINESS_ACCOUNT_ID=your_business_account_id
WHATSAPP_VERIFY_TOKEN=your_verify_token

# Optional: AI Integration
WHATSAPP_AI_PROVIDER=openai
OPENAI_API_KEY=your_openai_key
# OR
WHATSAPP_AI_PROVIDER=gemini
GEMINI_API_KEY=your_gemini_key
```

## Quick Start

#### Fluent Builder (Recommended)

```php
use Katema\WhatsApp\Facades\WhatsApp;

WhatsApp::to('1234567890')->text('Hello from Laravel!')->send();
```

#### Basic Methods

```php
// Send text message
WhatsApp::sendMessage('1234567890', 'Hello from Laravel!');

// Send local image (Auto-uploads to Meta)
WhatsApp::sendImage('1234567890', storage_path('app/public/image.jpg'), 'Local Image');

// Send remote image
WhatsApp::sendImage('1234567890', 'https://example.com/remote.jpg', 'Remote Image');
```

### 2. Send Interactive Messages

```php
// Buttons
WhatsApp::sendInteractive('1234567890', [
    'type' => 'button',
    'body' => ['text' => 'Choose an option:'],
    'action' => [
        'buttons' => [
            ['type' => 'reply', 'reply' => ['id' => 'btn_1', 'title' => 'Option 1']],
            ['type' => 'reply', 'reply' => ['id' => 'btn_2', 'title' => 'Option 2']],
        ],
    ],
]);

// List
WhatsApp::sendInteractive('1234567890', [
    'type' => 'list',
    'body' => ['text' => 'Select from menu:'],
    'action' => [
        'button' => 'View Menu',
        'sections' => [
            [
                'title' => 'Main Menu',
                'rows' => [
                    ['id' => 'item_1', 'title' => 'Item 1', 'description' => 'Description'],
                    ['id' => 'item_2', 'title' => 'Item 2', 'description' => 'Description'],
                ],
            ],
        ],
    ],
]);
```

### 3. Build a Chatbot

Create an event listener:

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
                // Condition
                fn($msg) => strtolower($msg->content['body'] ?? '') === 'hi',
                // Action
                fn($msg, $user, $session, $whatsapp) => 
                    $whatsapp->sendMessage($user->phone_number, 'Hello! How can I help?'),
                priority: 10
            )
            ->addRule(
                fn($msg) => str_contains(strtolower($msg->content['body'] ?? ''), 'price'),
                fn($msg, $user, $session, $whatsapp) => 
                    $whatsapp->sendMessage($user->phone_number, 'Our prices start at $10'),
                priority: 20
            )
            ->process($event->message, $event->user);
    }
}
```

Register in `EventServiceProvider`:

```php
use Katema\WhatsApp\Events\MessageReceived;
use App\Listeners\HandleWhatsAppMessage;

protected $listen = [
    MessageReceived::class => [
        HandleWhatsAppMessage::class,
    ],
];
```

### 4. Create WhatsApp Flows

```php
use Katema\WhatsApp\Services\FlowService;

$flow = app(FlowService::class);

// Build a registration form
$screens = [
    $flow->buildScreen('WELCOME', 'Registration', [
        $flow->buildForm([
            $flow->buildTextInput('name', 'Full Name', required: true),
            $flow->buildTextInput('email', 'Email', required: true, inputType: 'email'),
            $flow->buildTextInput('phone', 'Phone Number', required: true, inputType: 'phone'),
            $flow->buildDropdown('country', 'Country', [
                ['id' => 'ke', 'title' => 'Kenya'],
                ['id' => 'ug', 'title' => 'Uganda'],
                ['id' => 'tz', 'title' => 'Tanzania'],
            ], required: true),
            $flow->buildFooter('Submit', 'complete'),
        ]),
    ]),
];

$whatsappFlow = $flow->createFlow('Registration Form', $screens);

// Send flow to user
WhatsApp::sendFlow('1234567890', $whatsappFlow->flow_id);
```

### 5. AI-Powered Responses

With AI enabled, the chatbot automatically handles unmatched messages. You can now define **Personas** (System Prompts):

```php
// Define a specific persona for the chatbot
$chatbot->setPersona('You are a helpful travel agent specialized in African safaris. Respond in a professional and enthusiastic tone.');

// Or use tools (OpenAI Function Calling)
$chatbot->withTools([
    [
        'type' => 'function',
        'function' => [
            'name' => 'get_package_price',
            'description' => 'Get the price of a travel package',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'package' => ['type' => 'string']
                ]
            ]
        ]
    ]
]);
```

## Advanced Usage

### Session Management

```php
use Katema\WhatsApp\Models\WhatsAppSession;

$session = WhatsAppSession::active()
    ->where('whatsapp_user_id', $userId)
    ->first();

// Store context
$session->setContext('step', 'awaiting_payment');
$session->setContext('amount', 100);

// Retrieve context
$step = $session->getContext('step');

// Extend session
$session->extend(30); // 30 minutes
```

### Working with Models

```php
use Katema\WhatsApp\Models\WhatsAppUser;
use Katema\WhatsApp\Models\WhatsAppMessage;

// Find user
$user = WhatsAppUser::where('phone_number', '1234567890')->first();

// Get messages
$messages = $user->messages()
    ->incoming()
    ->latest()
    ->take(10)
    ->get();

// Block/unblock user
$user->block();
$user->unblock();
```

### Custom Flow Components

```php
$flow->buildCheckboxGroup('interests', 'Select Interests', [
    ['id' => 'tech', 'title' => 'Technology'],
    ['id' => 'sports', 'title' => 'Sports'],
    ['id' => 'music', 'title' => 'Music'],
], required: true);

$flow->buildDatePicker('dob', 'Date of Birth', required: true);

$flow->buildRadioButtonsGroup('gender', 'Gender', [
    ['id' => 'male', 'title' => 'Male'],
    ['id' => 'female', 'title' => 'Female'],
]);
```

### Event Handling

Available events:

```php
use Katema\WhatsApp\Events\MessageReceived;
use Katema\WhatsApp\Events\MessageStatusUpdated;
use Katema\WhatsApp\Events\FlowResponseReceived;

### WhatsApp Flow Security

For Flow "Endpoints", common logic requires decryption of the incoming payload.

```php
use Katema\WhatsApp\Facades\Flow;

$decrypted = Flow::handleEndpointRequest($request->all());

// Perform logic...

$response = Flow::encryptResponse([
    'screen' => 'SUCCESS',
    'data' => ['message' => 'Processed!']
], $decrypted['aes_key'], $decrypted['iv']);

return response()->json([
    'encrypted_flow_data' => $response,
    'encrypted_aes_key' => $decrypted['aes_key'], // Usually returned as received
    'initial_vector' => $decrypted['iv']
]);
```

## Webhook Setup

Your webhook URL will be: `https://yourdomain.com/whatsapp/webhook`

Configure in Meta App Dashboard:
1. Go to WhatsApp > Configuration
2. Set Callback URL: `https://yourdomain.com/whatsapp/webhook`
3. Set Verify Token: (from your `.env` WHATSAPP_VERIFY_TOKEN)
4. Subscribe to: `messages`, `message_status`

## Testing

Use `WhatsApp::fake()` to prevent actual API calls and assert that messages were sent.

```php
public function test_it_sends_a_greeting()
{
    $fake = WhatsApp::fake();

    // Trigger your logic...
    WhatsApp::to('1234567890')->text('Hi!')->send();

    // Assertions
    $fake->assertSentTo('1234567890', function ($msg) {
        return $msg['message'] === 'Hi!';
    });
}
```

## Security

Never commit your `.env` file. Keep API keys secure.

## License

MIT License

## Support

For issues and questions, please use GitHub Issues.