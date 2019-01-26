<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class WorkflowHelper
{
    public static function get(Registry $registry, string $model): Workflow
    {
        $subject = new class extends AbstractSubject {
            public static function support(): string
            {
                return '';
            }
        };
        return $registry->get($subject, $model);
    }
}
