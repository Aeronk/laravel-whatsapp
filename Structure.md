# Package Structure

Complete directory structure for the Laravel WhatsApp package:

```
katema/laravel-whatsapp/
│
├── .gitignore
├── .env.example
├── composer.json
├── phpunit.xml
├── README.md
├── INSTALLATION.md
├── EXAMPLES.md
├── CHANGELOG.md
├── LICENSE
│
├── config/
│   └── whatsapp.php                    # Package configuration
│
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_whatsapp_users_table.php
│       ├── 2024_01_01_000002_create_whatsapp_sessions_table.php
│       ├── 2024_01_01_000003_create_whatsapp_messages_table.php
│       └── 2024_01_01_000004_create_whatsapp_flows_table.php
│
├── routes/
│   └── whatsapp.php                    # Webhook routes
│
├── src/
│   ├── WhatsAppServiceProvider.php     # Service provider
│   │
│   ├── Contracts/
│   │   └── AIServiceInterface.php      # AI service contract
│   │
│   ├── Exceptions/
│   │   ├── WhatsAppException.php       # Main exception
│   │   ├── AIServiceException.php      # AI exceptions
│   │   └── FlowException.php           # Flow exceptions
│   │
│   ├── Events/
│   │   ├── MessageReceived.php         # Incoming message event
│   │   ├── MessageStatusUpdated.php    # Status change event
│   │   └── FlowResponseReceived.php    # Flow response event
│   │
│   ├── Facades/
│   │   └── WhatsApp.php                # WhatsApp facade
│   │
│   ├── Http/
│   │   └── Controllers/
│   │       └── WebhookController.php   # Webhook handler
│   │
│   ├── Listeners/
│   │   └── ProcessIncomingMessage.php  # Default message processor
│   │
│   ├── Models/
│   │   ├── WhatsAppUser.php            # User model
│   │   ├── WhatsAppSession.php         # Session model
│   │   ├── WhatsAppMessage.php         # Message model
│   │   └── WhatsAppFlow.php            # Flow model
│   │
│   └── Services/
│       ├── WhatsAppService.php         # Main API service
│       ├── WebhookHandler.php          # Webhook processor
│       ├── FlowService.php             # Flow builder
│       │
│       ├── Chatbot/
│       │   └── ChatbotEngine.php       # Rule engine
│       │
│       └── AI/
│           ├── AIServiceManager.php    # AI orchestrator
│           ├── OpenAIService.php       # OpenAI integration
│           └── GeminiService.php       # Gemini integration
│
├── tests/
│   ├── Feature/
│   │   ├── WhatsAppServiceTest.php
│   │   ├── WebhookTest.php
│   │   ├── ChatbotEngineTest.php
│   │   └── FlowServiceTest.php
│   │
│   └── Unit/
│       ├── MessageModelTest.php
│       ├── SessionModelTest.php
│       └── UserModelTest.php
│
└── examples/
    └── ExampleWhatsAppController.php   # Usage examples
```

## Core Components

### 1. Service Provider (`WhatsAppServiceProvider.php`)
- Registers services
- Publishes config and migrations
- Loads routes
- Registers event listeners

### 2. Configuration (`config/whatsapp.php`)
- API credentials
- Webhook settings
- AI configuration
- Chatbot settings
- Flow settings
- Storage and logging

### 3. Database Models

#### WhatsAppUser
- Stores user information
- Tracks interactions
- Manages blocked status

#### WhatsAppSession
- Manages conversation sessions
- Stores context data
- Handles expiry

#### WhatsAppMessage
- Records all messages
- Tracks status changes
- Stores metadata

#### WhatsAppFlow
- Stores flow definitions
- Manages versions
- Tracks status

### 4. Services

#### WhatsAppService
Main API integration:
- Send messages (text, media, interactive)
- Send templates
- Send flows
- Mark as read
- Download media
- Webhook verification

#### WebhookHandler
Processes incoming webhooks:
- Message parsing
- Status updates
- User management
- Event dispatching

#### FlowService
Flow builder utilities:
- Create flows
- Build screens
- Form components
- Validation

#### ChatbotEngine
Rule-based chatbot:
- Rule management
- Session handling
- AI fallback
- Context storage

#### AI Services
- AIServiceManager: Orchestrates AI providers
- OpenAIService: OpenAI integration
- GeminiService: Gemini integration

### 5. Events
- `MessageReceived`: New message arrives
- `MessageStatusUpdated`: Status changes
- `FlowResponseReceived`: Flow submission

### 6. HTTP Controllers
- `WebhookController`: Handles webhook requests

### 7. Facades
- `WhatsApp`: Convenient API access

## Usage Flow

```
1. Incoming Message
   ↓
2. Webhook receives POST
   ↓
3. WebhookController validates
   ↓
4. WebhookHandler processes
   ↓
5. Creates/updates User
   ↓
6. Creates Message record
   ↓
7. Fires MessageReceived event
   ↓
8. ProcessIncomingMessage listener
   ↓
9. ChatbotEngine processes
   ↓
10. Checks custom rules
    ↓
11. Falls back to AI if no match
    ↓
12. Sends response via WhatsAppService
```

## Extending the Package

### Add Custom Rules

```php
use Katema\WhatsApp\Services\Chatbot\ChatbotEngine;

$chatbot = app(ChatbotEngine::class);
$chatbot->addRule($condition, $action, $priority);
```

### Listen to Events

```php
Event::listen(MessageReceived::class, function ($event) {
    // Your custom logic
});
```

### Create Custom AI Provider

```php
use Katema\WhatsApp\Contracts\AIServiceInterface;

class CustomAIService implements AIServiceInterface
{
    public function chat(string $message, array $history = []): string
    {
        // Your implementation
    }
    
    public function completion(string $prompt, array $options = []): string
    {
        // Your implementation
    }
}
```

### Override Models

Extend the base models and bind in your service provider:

```php
use Katema\WhatsApp\Models\WhatsAppUser;

class CustomWhatsAppUser extends WhatsAppUser
{
    // Your customizations
}

// In AppServiceProvider
$this->app->bind(
    WhatsAppUser::class,
    CustomWhatsAppUser::class
);
```

## File Size Reference

- Config: ~300 lines
- Main Service: ~250 lines
- Models: ~100 lines each
- Tests: ~50 lines each
- Total: ~2,000 lines of code

## Dependencies

### Required
- PHP 8.1+
- Laravel 10.x|11.x
- Guzzle HTTP client

### Development
- PHPUnit
- Orchestra Testbench
- Mockery

## Testing

Run tests:
```bash
composer test
```

With coverage:
```bash
composer test-coverage
```