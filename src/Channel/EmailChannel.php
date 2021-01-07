<?php

namespace Tienvx\Bundle\MbtBundle\Channel;

use Symfony\Component\Mailer\Transport as MailerTransport;

class EmailChannel extends AbstractChannel
{
    public static function getName(): string
    {
        return 'email';
    }

    public static function isSupported(): bool
    {
        return class_exists(MailerTransport::class);
    }
}
