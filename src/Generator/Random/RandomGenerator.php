<?php

namespace Tienvx\Bundle\MbtBundle\Generator\Random;

use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class RandomGenerator extends RandomGeneratorTemplate
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

    public function setMaxSteps(int $maxSteps): void
    {
        $this->maxSteps = $maxSteps;
    }

    public function setTransitionCoverage(float $transitionCoverage): void
    {
        $this->transitionCoverage = $transitionCoverage;
    }

    public function setPlaceCoverage(int $placeCoverage): void
    {
        $this->placeCoverage = $placeCoverage;
    }

    public static function getName(): string
    {
        return 'random';
    }

    public function getLabel(): string
    {
        return 'Random';
    }

    public static function support(): bool
    {
        return true;
    }

    protected function initState(Model $model, GeneratorOptions $generatorOptions): array
    {
        return [
            'stepsCount' => 1,
            'visitedTransitions' => [],
            'visitedPlaces' => $model->getDefinition()->getInitialPlaces(),
            'transitionCoverage' => $generatorOptions->getTransitionCoverage() ?? $this->transitionCoverage,
            'placeCoverage' => $generatorOptions->getPlaceCoverage() ?? $this->placeCoverage,
            'maxSteps' => $generatorOptions->getMaxSteps() ?? $this->maxSteps,
            'currentTransitionCoverage' => 0,
            'currentPlaceCoverage' => 0,
        ];
    }

    protected function updateState(Model $model, SubjectInterface $subject, string $transitionName, array &$state): void
    {
        ++$state['stepsCount'];

        // Update visited places and transitions.
        foreach ($model->getMarking($subject)->getPlaces() as $place => $status) {
            if ($status && !in_array($place, $state['visitedPlaces'])) {
                $state['visitedPlaces'][] = $place;
            }
        }
        if (!in_array($transitionName, $state['visitedTransitions'])) {
            $state['visitedTransitions'][] = $transitionName;
        }

        // Update current coverage.
        $state['currentTransitionCoverage'] = count($state['visitedTransitions']) / count($model->getDefinition()->getTransitions()) * 100;
        $state['currentPlaceCoverage'] = count($state['visitedPlaces']) / count($model->getDefinition()->getPlaces()) * 100;
    }

    protected function canStop(array $state): bool
    {
        if (($state['currentTransitionCoverage'] >= $state['transitionCoverage'] && $state['currentPlaceCoverage'] >= $state['placeCoverage'])) {
            return true;
        }

        if ($state['stepsCount'] >= $state['maxSteps']) {
            return true;
        }

        return false;
    }

    protected function randomTransition(Model $model, SubjectInterface $subject, array $state): ?string
    {
        $transitions = $model->getEnabledTransitions($subject);
        if (count($transitions) > 0) {
            $key = array_rand($transitions);

            return $transitions[$key]->getName();
        }

        return null;
    }
}
