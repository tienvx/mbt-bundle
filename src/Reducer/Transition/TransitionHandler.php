<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Transition;

use Symfony\Component\Workflow\Definition;
use Tienvx\Bundle\MbtBundle\Reducer\HandlerTemplate;
use Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy\RemoveTransitionStrategy;
use Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy\StrategyInterface as StepsBuilderStrategy;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

class TransitionHandler extends HandlerTemplate
{
    public static function getReducerName(): string
    {
        return TransitionReducer::getName();
    }

    protected function extraValidate(Steps $steps, int $from, int $to): bool
    {
        $fromPlaces = $steps->getPlacesAt($from);
        $toPlaces = $steps->getPlacesAt($to);

        // Workflow only, does not work with state machine
        return $fromPlaces && $toPlaces &&
            count($fromPlaces) > 1 && count($toPlaces) > 1 &&
            1 === count(array_diff($fromPlaces, $toPlaces)) &&
            1 === count(array_diff($toPlaces, $fromPlaces));
    }

    protected function getStepsBuilderStrategy(Definition $definition): StepsBuilderStrategy
    {
        return new RemoveTransitionStrategy();
    }
}
