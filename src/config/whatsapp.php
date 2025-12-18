<?php

return [
    'meta' => [
        'token' => env('WHATSAPP_META_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
    ],

    'ai' => [
        'default' => 'openai',
        'openai_key' => env('OPENAI_API_KEY'),
        'gemini_key' => env('GEMINI_API_KEY'),
    ],
];
