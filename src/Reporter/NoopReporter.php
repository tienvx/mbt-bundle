<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Tienvx\Bundle\MbtBundle\Entity\Bug;

class NoopReporter implements ReporterInterface
{
    public function report(Bug $bug)
    {
        // do nothing
    }

    public static function getName()
    {
        return 'noop';
    }
}
