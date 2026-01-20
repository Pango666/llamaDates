<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array sendMessage(string $to, string $message)
 * @method static array sendTemplate(string $to, string $templateName, array $components = [], string $language = 'es_MX')
 * 
 * @see \App\Services\WhatsAppService
 */
class WhatsApp extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'whatsapp';
    }
}
