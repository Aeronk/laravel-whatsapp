# Usage Examples

## Complete E-Commerce Chatbot Example

```php
namespace App\Listeners;

use Katema\WhatsApp\Events\MessageReceived;
use Katema\WhatsApp\Services\Chatbot\ChatbotEngine;
use Katema\WhatsApp\Facades\WhatsApp;
use App\Models\Product;

class EcommerceChatbot
{
    public function __construct(protected ChatbotEngine $chatbot)
    {
    }

    public function handle(MessageReceived $event): void
    {
        $this->chatbot
            // Greeting
            ->addRule(
                fn($msg) => in_array(strtolower($msg->content['body'] ?? ''), ['hi', 'hello', 'hey']),
                function($msg, $user, $session, $whatsapp) {
                    $whatsapp->sendInteractive($user->phone_number, [
                        'type' => 'button',
                        'body' => ['text' => "Hi {$user->profile_name}! 👋\n\nWelcome to our store!"],
                        'action' => [
                            'buttons' => [
                                ['type' => 'reply', 'reply' => ['id' => 'browse', 'title' => '🛍️ Browse Products']],
                                ['type' => 'reply', 'reply' => ['id' => 'cart', 'title' => '🛒 View Cart']],
                                ['type' => 'reply', 'reply' => ['id' => 'support', 'title' => '💬 Support']],
                            ],
                        ],
                    ]);
                    $session->setContext('last_action', 'greeting');
                },
                priority: 10
            )
            
            // Browse Products
            ->addRule(
                fn($msg) => ($msg->content['button_reply']['id'] ?? '') === 'browse',
                function($msg, $user, $session, $whatsapp) {
                    $products = Product::active()->take(10)->get();
                    
                    $rows = $products->map(fn($p) => [
                        'id' => "product_{$p->id}",
                        'title' => $p->name,
                        'description' => $p->price_formatted,
                    ])->toArray();
                    
                    $whatsapp->sendInteractive($user->phone_number, [
                        'type' => 'list',
                        'body' => ['text' => 'Choose a product to view details:'],
                        'action' => [
                            'button' => 'View Products',
                            'sections' => [
                                [
                                    'title' => 'Available Products',
                                    'rows' => $rows,
                                ],
                            ],
                        ],
                    ]);
                    $session->setContext('last_action', 'browsing');
                },
                priority: 20
            )
            
            // Product Details
            ->addRule(
                fn($msg) => str_starts_with($msg->content['list_reply']['id'] ?? '', 'product_'),
                function($msg, $user, $session, $whatsapp) {
                    $productId = str_replace('product_', '', $msg->content['list_reply']['id']);
                    $product = Product::find($productId);
                    
                    if (!$product) {
                        $whatsapp->sendMessage($user->phone_number, 'Product not found');
                        return;
                    }
                    
                    $whatsapp->sendImage(
                        $user->phone_number,
                        $product->image_url,
                        "*{$product->name}*\n\n{$product->description}\n\n💰 {$product->price_formatted}"
                    );
                    
                    $whatsapp->sendInteractive($user->phone_number, [
                        'type' => 'button',
                        'body' => ['text' => 'Would you like to add this to cart?'],
                        'action' => [
                            'buttons' => [
                                ['type' => 'reply', 'reply' => ['id' => "add_{$productId}", 'title' => 'Add to Cart']],
                                ['type' => 'reply', 'reply' => ['id' => 'browse', 'title' => 'Back to Products']],
                            ],
                        ],
                    ]);
                    
                    $session->setContext('current_product', $productId);
                },
                priority: 30
            )
            
            // Add to Cart
            ->addRule(
                fn($msg) => str_starts_with($msg->content['button_reply']['id'] ?? '', 'add_'),
                function($msg, $user, $session, $whatsapp) {
                    $productId = str_replace('add_', '', $msg->content['button_reply']['id']);
                    
                    // Add to cart logic here
                    $cart = $session->getContext('cart', []);
                    $cart[] = $productId;
                    $session->setContext('cart', $cart);
                    
                    $whatsapp->sendMessage(
                        $user->phone_number,
                        "✅ Product added to cart!\n\nCart items: " . count($cart)
                    );
                    
                    $whatsapp->sendInteractive($user->phone_number, [
                        'type' => 'button',
                        'body' => ['text' => 'What next?'],
                        'action' => [
                            'buttons' => [
                                ['type' => 'reply', 'reply' => ['id' => 'browse', 'title' => 'Continue Shopping']],
                                ['type' => 'reply', 'reply' => ['id' => 'checkout', 'title' => 'Checkout']],
                            ],
                        ],
                    ]);
                },
                priority: 40
            )
            
            // Checkout
            ->addRule(
                fn($msg) => ($msg->content['button_reply']['id'] ?? '') === 'checkout',
                function($msg, $user, $session, $whatsapp) use ($flowService) {
                    $cart = $session->getContext('cart', []);
                    
                    if (empty($cart)) {
                        $whatsapp->sendMessage($user->phone_number, 'Your cart is empty!');
                        return;
                    }
                    
                    // Create checkout flow
                    $flow = app(\Katema\WhatsApp\Services\FlowService::class);
                    $screens = [
                        $flow->buildScreen('CHECKOUT', 'Checkout', [
                            $flow->buildForm([
                                $flow->buildTextInput('name', 'Full Name', required: true),
                                $flow->buildTextInput('phone', 'Phone', required: true, inputType: 'phone'),
                                $flow->buildTextArea('address', 'Delivery Address', required: true),
                                $flow->buildDropdown('payment', 'Payment Method', [
                                    ['id' => 'mpesa', 'title' => 'M-Pesa'],
                                    ['id' => 'card', 'title' => 'Card'],
                                    ['id' => 'cod', 'title' => 'Cash on Delivery'],
                                ], required: true),
                                $flow->buildFooter('Complete Order', 'complete'),
                            ]),
                        ]),
                    ];
                    
                    $checkoutFlow = $flow->createFlow('Checkout', $screens);
                    $whatsapp->sendFlow($user->phone_number, $checkoutFlow->flow_id);
                    
                    $session->setContext('checkout_started', true);
                },
                priority: 50
            )
            
            ->process($event->message, $event->user);
    }
}
```

## Customer Support Bot

```php
namespace App\Listeners;

use Katema\WhatsApp\Events\MessageReceived;
use Katema\WhatsApp\Services\Chatbot\ChatbotEngine;
use Katema\WhatsApp\Facades\WhatsApp;
use App\Models\SupportTicket;

class SupportChatbot
{
    public function handle(MessageReceived $event): void
    {
        $chatbot = app(ChatbotEngine::class);
        
        $chatbot
            // Create ticket
            ->addRule(
                fn($msg) => strtolower($msg->content['body'] ?? '') === 'support',
                function($msg, $user, $session, $whatsapp) {
                    $whatsapp->sendInteractive($user->phone_number, [
                        'type' => 'list',
                        'body' => ['text' => 'What can we help you with?'],
                        'action' => [
                            'button' => 'Select Issue',
                            'sections' => [
                                [
                                    'title' => 'Common Issues',
                                    'rows' => [
                                        ['id' => 'payment', 'title' => 'Payment Issue'],
                                        ['id' => 'delivery', 'title' => 'Delivery Problem'],
                                        ['id' => 'product', 'title' => 'Product Question'],
                                        ['id' => 'account', 'title' => 'Account Help'],
                                        ['id' => 'other', 'title' => 'Other'],
                                    ],
                                ],
                            ],
                        ],
                    ]);
                    $session->setContext('awaiting_issue_type', true);
                }
            )
            
            // Handle issue type selection
            ->addRule(
                fn($msg, $user, $session) => 
                    $session->getContext('awaiting_issue_type') && 
                    isset($msg->content['list_reply']),
                function($msg, $user, $session, $whatsapp) {
                    $issueType = $msg->content['list_reply']['id'];
                    $session->setContext('issue_type', $issueType);
                    $session->setContext('awaiting_issue_type', false);
                    
                    $whatsapp->sendMessage(
                        $user->phone_number,
                        "Please describe your {$issueType} issue in detail:"
                    );
                    $session->setContext('awaiting_description', true);
                }
            )
            
            // Collect description and create ticket
            ->addRule(
                fn($msg, $user, $session) => 
                    $session->getContext('awaiting_description') && 
                    isset($msg->content['body']),
                function($msg, $user, $session, $whatsapp) {
                    $ticket = SupportTicket::create([
                        'user_id' => $user->id,
                        'type' => $session->getContext('issue_type'),
                        'description' => $msg->content['body'],
                        'status' => 'open',
                    ]);
                    
                    $session->setContext('awaiting_description', false);
                    $session->setContext('ticket_id', $ticket->id);
                    
                    $whatsapp->sendMessage(
                        $user->phone_number,
                        "✅ Ticket created!\n\n" .
                        "Ticket #: {$ticket->id}\n" .
                        "Our team will respond shortly."
                    );
                }
            )
            
            ->process($event->message, $event->user);
    }
}
```

## Appointment Booking Bot

```php
use Katema\WhatsApp\Services\FlowService;

$flow = app(FlowService::class);

$screens = [
    $flow->buildScreen('BOOKING', 'Book Appointment', [
        $flow->buildForm([
            $flow->buildTextInput('name', 'Full Name', required: true),
            $flow->buildTextInput('email', 'Email', required: true, inputType: 'email'),
            $flow->buildDatePicker('date', 'Preferred Date', required: true),
            $flow->buildDropdown('time', 'Preferred Time', [
                ['id' => '09:00', 'title' => '9:00 AM'],
                ['id' => '10:00', 'title' => '10:00 AM'],
                ['id' => '11:00', 'title' => '11:00 AM'],
                ['id' => '14:00', 'title' => '2:00 PM'],
                ['id' => '15:00', 'title' => '3:00 PM'],
                ['id' => '16:00', 'title' => '4:00 PM'],
            ], required: true),
            $flow->buildDropdown('service', 'Service Type', [
                ['id' => 'consultation', 'title' => 'Consultation'],
                ['id' => 'checkup', 'title' => 'General Checkup'],
                ['id' => 'followup', 'title' => 'Follow-up'],
            ], required: true),
            $flow->buildTextArea('notes', 'Additional Notes', required: false),
            $flow->buildFooter('Book Now', 'complete'),
        ]),
    ]),
];

$bookingFlow = $flow->createFlow('Appointment Booking', $screens);
```

## Survey/Feedback Bot

```php
$flow = app(FlowService::class);

$screens = [
    $flow->buildScreen('SURVEY', 'Customer Feedback', [
        $flow->buildForm([
            $flow->buildRadioButtonsGroup('satisfaction', 'Overall Satisfaction', [
                ['id' => '5', 'title' => '😍 Excellent'],
                ['id' => '4', 'title' => '😊 Good'],
                ['id' => '3', 'title' => '😐 Average'],
                ['id' => '2', 'title' => '😞 Poor'],
                ['id' => '1', 'title' => '😠 Very Poor'],
            ], required: true),
            
            $flow->buildCheckboxGroup('aspects', 'What did you like?', [
                ['id' => 'speed', 'title' => 'Fast Delivery'],
                ['id' => 'quality', 'title' => 'Product Quality'],
                ['id' => 'service', 'title' => 'Customer Service'],
                ['id' => 'price', 'title' => 'Fair Pricing'],
            ], required: false),
            
            $flow->buildTextArea('comments', 'Additional Comments', required: false, maxLength: 500),
            
            $flow->buildRadioButtonsGroup('recommend', 'Would you recommend us?', [
                ['id' => 'yes', 'title' => 'Yes'],
                ['id' => 'no', 'title' => 'No'],
                ['id' => 'maybe', 'title' => 'Maybe'],
            ], required: true),
            
            $flow->buildFooter('Submit Feedback', 'complete'),
        ]),
    ]),
];

$surveyFlow = $flow->createFlow('Customer Survey', $screens);
```

## Multi-language Support

```php
namespace App\Listeners;

use Katema\WhatsApp\Events\MessageReceived;

class MultiLanguageBot
{
    protected array $translations = [
        'en' => [
            'greeting' => 'Hello! How can I help you?',
            'goodbye' => 'Thank you! Have a great day!',
        ],
        'sw' => [
            'greeting' => 'Habari! Naweza kukusaidia vipi?',
            'goodbye' => 'Asante! Siku njema!',
        ],
    ];
    
    public function handle(MessageReceived $event): void
    {
        $user = $event->user;
        $language = $user->language ?? 'en';
        
        $chatbot = app(ChatbotEngine::class);
        
        $chatbot
            ->addRule(
                fn($msg) => in_array(strtolower($msg->content['body'] ?? ''), ['hi', 'hello', 'habari']),
                function($msg, $user, $session, $whatsapp) use ($language) {
                    $greeting = $this->translations[$language]['greeting'];
                    $whatsapp->sendMessage($user->phone_number, $greeting);
                }
            )
            ->process($event->message, $user);
    }
}