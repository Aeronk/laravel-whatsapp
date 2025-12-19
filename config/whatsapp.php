<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Meta WhatsApp Cloud API Configuration
    |--------------------------------------------------------------------------
    */
    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
    'app_secret' => env('WHATSAPP_APP_SECRET'),
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN', 'your_verify_token'),

    'api_version' => env('WHATSAPP_API_VERSION', 'v21.0'),
    'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */
    'webhook' => [
        'enabled' => env('WHATSAPP_WEBHOOK_ENABLED', true),
        'path' => env('WHATSAPP_WEBHOOK_PATH', 'whatsapp/webhook'),
        'middleware' => ['api'],
        'verify_signature' => env('WHATSAPP_VERIFY_SIGNATURE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Integration
    |--------------------------------------------------------------------------
    */
    'ai' => [
        'default' => env('WHATSAPP_AI_PROVIDER', 'openai'), // openai, gemini, or null

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
            'max_tokens' => env('OPENAI_MAX_TOKENS', 500),
            'temperature' => env('OPENAI_TEMPERATURE', 0.7),
        ],

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-pro'),
            'max_tokens' => env('GEMINI_MAX_TOKENS', 500),
            'temperature' => env('GEMINI_TEMPERATURE', 0.7),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Chatbot Configuration
    |--------------------------------------------------------------------------
    */
    'chatbot' => [
        'enabled' => env('WHATSAPP_CHATBOT_ENABLED', true),
        'session_timeout' => env('WHATSAPP_SESSION_TIMEOUT', 1800), // 30 minutes
        'default_language' => env('WHATSAPP_DEFAULT_LANGUAGE', 'en'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Flow Configuration
    |--------------------------------------------------------------------------
    */
    'flows' => [
        'version' => env('WHATSAPP_FLOW_VERSION', '7.3'),
        'encryption_enabled' => env('WHATSAPP_FLOW_ENCRYPTION_ENABLED', false),
        'private_key' => env('WHATSAPP_FLOW_PRIVATE_KEY'),
        'public_key' => env('WHATSAPP_FLOW_PUBLIC_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'messages_retention_days' => env('WHATSAPP_MESSAGES_RETENTION_DAYS', 90),
        'media_disk' => env('WHATSAPP_MEDIA_DISK', 'local'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'enabled' => env('WHATSAPP_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('WHATSAPP_RATE_LIMIT_MAX_ATTEMPTS', 60),
        'decay_minutes' => env('WHATSAPP_RATE_LIMIT_DECAY_MINUTES', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('WHATSAPP_LOGGING_ENABLED', true),
        'channel' => env('WHATSAPP_LOG_CHANNEL', 'stack'),
        'level' => env('WHATSAPP_LOG_LEVEL', 'info'),
    ],
];