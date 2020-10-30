<?php

namespace Tienvx\Bundle\MbtBundle\Channel;

use Symfony\Component\Notifier\Bridge\Twilio\TwilioTransport;

class TwilioChannel extends AbstractChannel
{
    public static function getName(): string
    {
        return 'sms/twilio';
    }

    public static function isSupported(): bool
    {
        return class_exists(TwilioTransport::class);
    }
}
