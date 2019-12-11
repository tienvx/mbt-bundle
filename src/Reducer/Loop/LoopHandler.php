<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Loop;

use Symfony\Component\Workflow\Definition;
use Tienvx\Bundle\MbtBundle\Reducer\HandlerTemplate;
use Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy\RemoveLoopStrategy;
use Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy\StrategyInterface as StepsBuilderStrategy;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

class LoopHandler extends HandlerTemplate
{
    public static function getReducerName(): string
    {
        return LoopReducer::getName();
    }

    protected function extraValidate(Steps $steps, int $from, int $to): bool
    {
        $fromPlaces = $steps->getPlacesAt($from);
        $toPlaces = $steps->getPlacesAt($to);

        return $fromPlaces && $toPlaces &&
            !array_diff($fromPlaces, $toPlaces) &&
            !array_diff($toPlaces, $fromPlaces);
    }

    protected function getStepsBuilderStrategy(Definition $definition): StepsBuilderStrategy
    {
        return new RemoveLoopStrategy();
    }
}
