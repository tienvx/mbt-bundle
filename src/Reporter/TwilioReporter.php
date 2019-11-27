<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

class TwilioReporter implements ReporterInterface
{
    public static function getName(): string
    {
        return 'sms/twilio';
    }

    public function getLabel(): string
    {
        return 'Twilio';
    }

    public static function support(): bool
    {
        return class_exists('Symfony\Component\Notifier\Bridge\Twilio\TwilioTransport');
    }
}
