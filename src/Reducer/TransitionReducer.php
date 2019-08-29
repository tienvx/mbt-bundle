<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Exception;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\BugHelper;
use Tienvx\Bundle\MbtBundle\Helper\StepsBuilder;
use Tienvx\Bundle\MbtBundle\Helper\StepsRunner;
use Tienvx\Bundle\MbtBundle\Message\FinishReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;

class TransitionReducer extends AbstractReducer
{
    /**
     * @param Bug      $bug
     * @param Workflow $workflow
     * @param int      $length
     * @param int      $from
     * @param int      $to
     *
     * @throws Exception
     * @throws Throwable
     */
    public function handle(Bug $bug, Workflow $workflow, int $length, int $from, int $to)
    {
        $steps = $bug->getSteps();
        $model = $bug->getModel()->getName();

        if ($steps->getLength() === $length) {
            // The reproduce path has not been reduced.
            $fromPlaces = $steps->getPlacesAt($from);
            $toPlaces = $steps->getPlacesAt($to);
            if (count($fromPlaces) > 1 && count($toPlaces) > 1 && 1 === count(array_diff($fromPlaces, $toPlaces)) &&
                1 === count(array_diff($toPlaces, $fromPlaces))) {
                $newSteps = StepsBuilder::createWithoutTransition($steps, $from, $to);
                // Make sure new path shorter than old path.
                if ($newSteps->getLength() < $steps->getLength()) {
                    try {
                        $subject = $this->subjectManager->createSubject($model);
                        StepsRunner::run($newSteps, $workflow, $subject);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                            BugHelper::updateSteps($this->entityManager, $bug, $newSteps);
                            $this->messageBus->dispatch(new ReduceBugMessage($bug->getId(), static::getName()));
                        }
                    }
                }
            }
        }

        $this->messageBus->dispatch(new FinishReduceStepsMessage($bug->getId()));
    }

    /**
     * @param Bug $bug
     *
     * @return int
     *
     * @throws Exception
     */
    public function dispatch(Bug $bug): int
    {
        $steps = $bug->getSteps();
        $messagesCount = 0;

        for ($i = 0; $i < $steps->getLength() - 1; ++$i) {
            $j = $i + 1;
            $fromPlaces = $steps->getPlacesAt($i);
            $toPlaces = $steps->getPlacesAt($j);
            if (count($fromPlaces) > 1 && count($toPlaces) > 1 && 1 === count(array_diff($fromPlaces, $toPlaces)) &&
                1 === count(array_diff($toPlaces, $fromPlaces))) {
                $message = new ReduceStepsMessage($bug->getId(), static::getName(), $steps->getLength(), $i, $j);
                $this->messageBus->dispatch($message);
                ++$messagesCount;
                if ($messagesCount >= $steps->getLength()) {
                    break;
                }
            }
        }

        return $messagesCount;
    }

    public static function getName(): string
    {
        return 'transition';
    }

    public function getLabel(): string
    {
        return 'Transition';
    }
}
