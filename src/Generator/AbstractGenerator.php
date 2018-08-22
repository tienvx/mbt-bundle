<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Generator;
use Symfony\Component\Workflow\Exception\TransitionException;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

abstract class AbstractGenerator implements GeneratorInterface
{
    public function getAvailableTransitions(Workflow $workflow, Subject $subject): Generator
    {
        while (true) {
            /** @var Transition[] $transitions */
            $transitions = $workflow->getEnabledTransitions($subject);
            if (!empty($transitions)) {
                yield $transitions[0]->getName();
            } else {
                break;
            }
        }
    }

    /**
     * @param Workflow $workflow
     * @param Subject $subject
     * @param string $transitionName
     * @return bool
     */
    public function applyTransition(Workflow $workflow, Subject $subject, string $transitionName): bool
    {
        try {
            $workflow->apply($subject, $transitionName);
        } catch (TransitionException $exception) {
            return false;
        }
        return true;
    }
}
