<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class WorkflowHelper
{
    /**
     * @param Registry $registry
     * @param string   $model
     *
     * @return Workflow
     */
    public static function get(Registry $registry, string $model): Workflow
    {
        $subject = static::fakeSubject();

        return $registry->get($subject, $model);
    }

    /**
     * @param Registry $registry
     *
     * @return Workflow[]
     */
    public static function all(Registry $registry): array
    {
        $subject = static::fakeSubject();

        return $registry->all($subject);
    }

    private static function fakeSubject()
    {
        return new class() extends AbstractSubject {
            public static function support(): string
            {
                return '';
            }
        };
    }
}
