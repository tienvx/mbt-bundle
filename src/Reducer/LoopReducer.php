<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Exception;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy\RemoveLoopStrategy;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Steps\StepsBuilder;

class LoopReducer extends AbstractReducer
{
    /**
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
            !array_diff($fromPlaces, $toPlaces) &&
            !array_diff($toPlaces, $fromPlaces))) {
            return;
        }

        $stepsBuilder = new StepsBuilder();
        $stepsBuilder->setStrategy(new RemoveLoopStrategy());
        $newSteps = $stepsBuilder->create($steps, $from, $to);
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
            for ($j = $i + 1; $j < $steps->getLength(); ++$j) {
                if (!array_diff($steps->getPlacesAt($i), $steps->getPlacesAt($j)) &&
                    !array_diff($steps->getPlacesAt($j), $steps->getPlacesAt($i))) {
                    $distance = $j - $i;
                    $pairs[] = [$i, $j, $distance];
                }
            }
        }

        usort($pairs, function ($a, $b) {
            // Sort by distance ascending
            return $a[2] - $b[2];
        });

        if (count($pairs) - $steps->getLength() < 0) {
            // If number of pairs is small enough, we handle all pairs
            return $pairs;
        } else {
            // If number of pairs is large, we handle a bit of easy pairs, and a bit of hard pairs
            // Hard pairs sit at the beginning of the array, easy at the end.
            $total = count($pairs);
            $limitEasy = $steps->getLength();
            $easy = array_slice($pairs, -$limitEasy, $limitEasy);
            $hard = array_slice($pairs, -$total, $total - $limitEasy);
            $limitHard = floor(sqrt($steps->getLength()));

            return array_merge($easy, $this->randomPairs($hard, $limitHard));
        }
    }

    protected function randomPairs(array $pairs, int $limit): array
    {
        $randomPairs = [];
        while (count($randomPairs) < $limit && count($pairs) > 0) {
            $key = array_rand($pairs);
            $pair = $pairs[$key];
            list($i, $j) = $pair;
            // $pair will be removed from $pairs too
            $pairs = array_filter($pairs, function (array $pair) use ($i, $j) {
                return $pair[1] <= $i || $pair[0] >= $j;
            });
            $randomPairs[] = $pair;
        }

        return $randomPairs;
    }

    public static function getName(): string
    {
        return 'loop';
    }

    public function getLabel(): string
    {
        return 'Loop';
    }
}
