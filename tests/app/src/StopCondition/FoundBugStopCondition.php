<?php

namespace Tienvx\Bundle\MbtBundle\Tests\StopCondition;

use Tienvx\Bundle\MbtBundle\StopCondition\FoundBugStopCondition as BaseFoundBugStopCondition;

class FoundBugStopCondition extends BaseFoundBugStopCondition
{
    public $bugFound = true;

    public function meet(array $context): bool
    {
        if ($context['pathLength'] >= $this->maxPathLength) {
            $this->bugFound = false;
        }
        return parent::meet($context);
    }

    public static function getName()
    {
        return 'modified-found-bug';
    }
}
