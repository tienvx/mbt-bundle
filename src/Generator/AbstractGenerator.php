<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Symfony\Component\Workflow\Exception\TransitionException;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

abstract class AbstractGenerator implements GeneratorInterface
{
    public static function support(): bool
    {
        return true;
    }

    /**
     * @param Workflow        $workflow
     * @param AbstractSubject $subject
     * @param string          $transitionName
     *
     * @return bool
     */
    public function applyTransition(Workflow $workflow, AbstractSubject $subject, string $transitionName): bool
    {
        try {
            $workflow->apply($subject, $transitionName);
        } catch (TransitionException $exception) {
            return false;
        }

        return true;
    }
}
