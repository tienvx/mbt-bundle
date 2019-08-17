<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Generator;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Entity\Step;
use Tienvx\Bundle\MbtBundle\Entity\Data;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class RandomGenerator extends AbstractGenerator
{
    /**
     * @var int
     */
    protected $maxSteps = 300;

    /**
     * @var float
     */
    protected $transitionCoverage = 100;

    /**
     * @var float
     */
    protected $placeCoverage = 100;

    public function setMaxSteps(int $maxSteps)
    {
        $this->maxSteps = $maxSteps;
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
        // Number of steps, include the first step (transition = null, places = initial places)
        $stepsCount = 1;
        $visitedTransitions = [];
        $visitedPlaces = $workflow->getDefinition()->getInitialPlaces();
        $transitionCoverage = $generatorOptions->getTransitionCoverage() ?? $this->transitionCoverage;
        $placeCoverage = $generatorOptions->getPlaceCoverage() ?? $this->placeCoverage;
        $maxSteps = $generatorOptions->getMaxSteps() ?? $this->maxSteps;

        while (true) {
            /** @var Transition[] $transitions */
            $transitions = $workflow->getEnabledTransitions($subject);
            if (!empty($transitions)) {
                $index = array_rand($transitions);
                $transitionName = $transitions[$index]->getName();

                yield new Step($transitionName, new Data());

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
                ++$stepsCount;

                if (($currentTransitionCoverage >= $transitionCoverage && $currentPlaceCoverage >= $placeCoverage)) {
                    break;
                } elseif ($stepsCount >= $maxSteps) {
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
