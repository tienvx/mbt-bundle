<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Generator;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

class RandomGenerator extends AbstractGenerator
{
    /**
     * @var int
     */
    protected $maxPathLength = 300;

    /**
     * @var float
     */
    protected $transitionCoverage = 100;

    /**
     * @var float
     */
    protected $placeCoverage = 100;

    public function setMaxPathLength(int $maxPathLength)
    {
        $this->maxPathLength = $maxPathLength;
    }

    public function setTransitionCoverage(float $transitionCoverage)
    {
        $this->transitionCoverage = $transitionCoverage;
    }

    public function setPlaceCoverage(int $placeCoverage)
    {
        $this->placeCoverage = $placeCoverage;
    }

    public function getAvailableTransitions(Workflow $workflow, Subject $subject): Generator
    {
        $pathLength         = 0;
        $visitedTransitions = [];
        $visitedPlaces      = [];
        $transitionCoverage = $workflow->getMetadataStore()->getMetadata('transition_coverage') ?? $this->transitionCoverage;
        $placeCoverage      = $workflow->getMetadataStore()->getMetadata('place_coverage') ?? $this->placeCoverage;
        $maxPathLength      = $workflow->getMetadataStore()->getMetadata('max_path_length') ?? $this->maxPathLength;

        while (true) {
            /** @var Transition[] $transitions */
            $transitions = $workflow->getEnabledTransitions($subject);
            if (!empty($transitions)) {
                $index = array_rand($transitions);
                $transitionName = $transitions[$index]->getName();

                yield $transitionName;

                // Update visited places and transitions.
                foreach ($workflow->getMarking($subject)->getPlaces() as $place) {
                    if (!in_array($place, $visitedPlaces)) {
                        $visitedPlaces[] = $place;
                    }
                }
                if (!in_array($transitionName, $visitedTransitions)) {
                    $visitedTransitions[] = $transitionName;
                }

                // Update current state.
                $currentTransitionCoverage = count($visitedTransitions) / count($workflow->getDefinition()->getTransitions()) * 100;
                $currentPlaceCoverage      = count($visitedPlaces) / count($workflow->getDefinition()->getPlaces()) * 100;
                $pathLength++;

                if (($currentTransitionCoverage >= $transitionCoverage && $currentPlaceCoverage >= $placeCoverage)) {
                    break;
                } elseif ($pathLength >= $maxPathLength) {
                    break;
                }
            } else {
                break;
            }
        }
    }

    public static function getName()
    {
        return 'random';
    }
}
