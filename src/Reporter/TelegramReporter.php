<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

class TelegramReporter implements ReporterInterface
{
    public static function getName(): string
    {
        return 'chat/telegram';
    }

    public function getLabel(): string
    {
        return 'Telegram';
    }

    public static function support(): bool
    {
        return class_exists('Symfony\Component\Notifier\Bridge\Telegram\TelegramTransport');
    }
}
