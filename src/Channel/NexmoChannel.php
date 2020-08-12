<?php

namespace Tienvx\Bundle\MbtBundle\Channel;

use Symfony\Component\Notifier\Bridge\Nexmo\NexmoTransport;

class NexmoChannel extends AbstractChannel
{
    public static function getName(): string
    {
        return 'sms/nexmo';
    }

    public static function isSupported(): bool
    {
        return class_exists(NexmoTransport::class);
    }
}
