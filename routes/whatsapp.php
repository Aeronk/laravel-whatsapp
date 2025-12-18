<?php

use Illuminate\Support\Facades\Route;
use Katema\WhatsApp\Http\Controllers\WebhookController;

Route::prefix(config('whatsapp.webhook.path'))
    ->middleware(config('whatsapp.webhook.middleware'))
    ->group(function () {
        Route::get('/', [WebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
        Route::post('/', [WebhookController::class, 'handle'])->name('whatsapp.webhook.handle');
    });