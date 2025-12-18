<?php

namespace Katema\WhatsApp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array sendMessage(string $to, string $message, ?string $messageId = null)
 * @method static array sendTemplate(string $to, string $templateName, string $languageCode = 'en', array $components = [])
 * @method static array sendInteractive(string $to, array $interactive, ?string $messageId = null)
 * @method static array sendImage(string $to, string $imageUrl, ?string $caption = null)
 * @method static array sendDocument(string $to, string $documentUrl, ?string $filename = null, ?string $caption = null)
 * @method static array sendAudio(string $to, string $audioUrl)
 * @method static array sendVideo(string $to, string $videoUrl, ?string $caption = null)
 * @method static array sendLocation(string $to, float $latitude, float $longitude, ?string $name = null, ?string $address = null)
 * @method static array sendFlow(string $to, string $flowId, array $flowData = [], string $mode = 'draft')
 * @method static array markAsRead(string $messageId)
 * @method static string downloadMedia(string $mediaId)
 *
 * @see \Katema\WhatsApp\Services\WhatsAppService
 */
class WhatsApp extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Katema\WhatsApp\Services\WhatsAppService::class;
    }
}