<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Generator;
use Symfony\Component\Workflow\Exception\TransitionException;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\Path;
use Tienvx\Bundle\MbtBundle\Entity\StepData;
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

    /**
     * @param AbstractSubject $subject
     * @param Path            $path
     *
     * @return Generator
     */
    public function getTransitionsFromPath(AbstractSubject $subject, Path $path): Generator
    {
        foreach ($path->getSteps() as $index => $step) {
            $transition = $step->getTransition();
            $data = $step->getData();
            if ($transition) {
                if ($data instanceof StepData) {
                    $subject->setData($data);
                    $subject->setNeedData(false);
                } else {
                    $subject->setNeedData(true);
                }
                yield $transition;
            }
        }
    }
}
