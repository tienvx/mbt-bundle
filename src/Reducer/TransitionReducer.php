<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Exception;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Helper\StepsBuilder;

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
    public function handle(Bug $bug, Workflow $workflow, int $length, int $from, int $to): void
    {
        $steps = $bug->getSteps();
        $model = $bug->getModel()->getName();

        if ($steps->getLength() !== $length) {
            // The reproduce path has been reduced.
            return;
        }

        $fromPlaces = $steps->getPlacesAt($from);
        $toPlaces = $steps->getPlacesAt($to);
        if (!($fromPlaces && $toPlaces &&
            count($fromPlaces) > 1 && count($toPlaces) > 1 &&
            1 === count(array_diff($fromPlaces, $toPlaces)) &&
            1 === count(array_diff($toPlaces, $fromPlaces)))) {
            return;
        }

        $newSteps = StepsBuilder::createWithoutTransition($steps, $from, $to);
        if ($newSteps->getLength() >= $steps->getLength()) {
            // New path is longer than or equals old path.
            return;
        }

        $this->run($model, $newSteps, $bug, $workflow);
    }

    protected function getPairs(Steps $steps): array
    {
        $pairs = [];

        for ($i = 0; $i < $steps->getLength() - 1; ++$i) {
            $j = $i + 1;
            $fromPlaces = $steps->getPlacesAt($i);
            $toPlaces = $steps->getPlacesAt($j);
            // Workflow only, does not work with state machine
            if ($fromPlaces && $toPlaces &&
                count($fromPlaces) > 1 && count($toPlaces) > 1 &&
                1 === count(array_diff($fromPlaces, $toPlaces)) &&
                1 === count(array_diff($toPlaces, $fromPlaces))) {
                $pairs[] = [$i, $j];
            }
        }

        return $pairs;
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
