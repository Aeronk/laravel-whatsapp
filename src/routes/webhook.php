<?php

use Illuminate\Support\Facades\Route;

Route::match(['GET','POST'], '/whatsapp/webhook',
    WebhookController::class
);
