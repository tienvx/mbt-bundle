<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Generator;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Entity\Step;
use Tienvx\Bundle\MbtBundle\Entity\StepData;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

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

    /**
     * {@inheritdoc}
     */
    public function generate(Workflow $workflow, AbstractSubject $subject, GeneratorOptions $generatorOptions = null): Generator
    {
        $pathLength = 0;
        $visitedTransitions = [];
        $visitedPlaces = $workflow->getDefinition()->getInitialPlaces();
        $transitionCoverage = $generatorOptions->getTransitionCoverage() ?? $this->transitionCoverage;
        $placeCoverage = $generatorOptions->getPlaceCoverage() ?? $this->placeCoverage;
        $maxPathLength = $generatorOptions->getMaxPathLength() ?? $this->maxPathLength;

        while (true) {
            /** @var Transition[] $transitions */
            $transitions = $workflow->getEnabledTransitions($subject);
            if (!empty($transitions)) {
                $index = array_rand($transitions);
                $transitionName = $transitions[$index]->getName();

                yield new Step($transitionName, new StepData());

                // Update visited places and transitions.
                foreach ($workflow->getMarking($subject)->getPlaces() as $place => $status) {
                    if ($status && !in_array($place, $visitedPlaces)) {
                        $visitedPlaces[] = $place;
                    }
                }
                if (!in_array($transitionName, $visitedTransitions)) {
                    $visitedTransitions[] = $transitionName;
                }

                // Update current state.
                $currentTransitionCoverage = count($visitedTransitions) / count($workflow->getDefinition()->getTransitions()) * 100;
                $currentPlaceCoverage = count($visitedPlaces) / count($workflow->getDefinition()->getPlaces()) * 100;
                ++$pathLength;

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

    public static function getName(): string
    {
        return 'random';
    }

    public function getLabel(): string
    {
        return 'Random';
    }
}
