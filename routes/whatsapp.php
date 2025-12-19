<?php

use Illuminate\Support\Facades\Route;
use Katema\WhatsApp\Http\Controllers\WebhookController;

Route::prefix(config('whatsapp.webhook.path'))
    ->middleware(array_merge(
        (array) config('whatsapp.webhook.middleware'),
        ['whatsapp.verify_signature']
    ))
    ->group(function () {
        Route::get('/', [WebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
        Route::post('/', [WebhookController::class, 'handle'])->name('whatsapp.webhook.handle');
    });