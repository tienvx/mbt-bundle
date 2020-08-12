<?php

namespace Tienvx\Bundle\MbtBundle\Channel;

use Symfony\Component\Notifier\Bridge\Telegram\TelegramTransport;

class TelegramChannel extends AbstractChannel
{
    public static function getName(): string
    {
        return 'chat/telegram';
    }

    public static function isSupported(): bool
    {
        return class_exists(TelegramTransport::class);
    }
}
