<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Exception;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Helper\StepsBuilder;

class LoopReducer extends AbstractReducer
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
            !array_diff($fromPlaces, $toPlaces) &&
            !array_diff($toPlaces, $fromPlaces))) {
            return;
        }

        $newSteps = StepsBuilder::createWithoutLoop($steps, $from, $to);
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
            $easy = floor($steps->getLength());
            $hard = floor($steps->getLength() / 8);

            return array_merge(array_slice($pairs, -$easy, $easy), array_slice($pairs, 0, $hard));
        }
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
