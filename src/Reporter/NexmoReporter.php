<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

class NexmoReporter implements ReporterInterface
{
    public static function getName(): string
    {
        return 'sms/nexmo';
    }

    public function getLabel(): string
    {
        return 'Nexmo';
    }

    public static function support(): bool
    {
        return class_exists('Symfony\Component\Notifier\Bridge\Nexmo\NexmoTransport');
    }
}
