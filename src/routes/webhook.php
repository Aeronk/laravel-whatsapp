<?php

Route::match(['GET','POST'], '/whatsapp/webhook',
    \Katema\WhatsApp\Http\Controllers\WebhookController::class
);
