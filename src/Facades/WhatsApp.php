<?php

namespace Katema\WhatsApp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Katema\WhatsApp\Builders\MessageBuilder to(string $to)
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
 * @method static \Katema\WhatsApp\Testing\WhatsAppFake fake()
 *
 * @see \Katema\WhatsApp\Services\WhatsAppService
 */
class WhatsApp extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return \Katema\WhatsApp\Testing\WhatsAppFake
     */
    public static function fake()
    {
        static::swap($fake = new \Katema\WhatsApp\Testing\WhatsAppFake());

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \Katema\WhatsApp\Services\WhatsAppService::class;
    }
}