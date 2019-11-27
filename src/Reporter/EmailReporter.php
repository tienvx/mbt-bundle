<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

class EmailReporter implements ReporterInterface
{
    public static function getName(): string
    {
        return 'email';
    }

    public function getLabel(): string
    {
        return 'Email';
    }

    public static function support(): bool
    {
        return true;
    }
}
