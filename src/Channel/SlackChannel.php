<?php

namespace Tienvx\Bundle\MbtBundle\Channel;

use Symfony\Component\Notifier\Bridge\Slack\SlackTransport;

class SlackChannel extends AbstractChannel
{
    public static function getName(): string
    {
        return 'chat/slack';
    }

    public static function isSupported(): bool
    {
        return class_exists(SlackTransport::class);
    }
}
