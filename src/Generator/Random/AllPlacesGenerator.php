<?php

namespace Tienvx\Bundle\MbtBundle\Generator\Random;

use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class AllPlacesGenerator extends RandomGeneratorTemplate
{
    /**
     * @var int
     */
    protected $maxSteps = 300;

    public function setMaxSteps(int $maxSteps): void
    {
        $this->maxSteps = $maxSteps;
    }

    public static function getName(): string
    {
        return 'all-places';
    }

    public function getLabel(): string
    {
        return 'All Places';
    }

    public static function support(): bool
    {
        return true;
    }

    protected function initState(Model $model, GeneratorOptions $generatorOptions): array
    {
        return [
            'stepsCount' => 1,
            'maxSteps' => $generatorOptions->getMaxSteps() ?? $this->maxSteps,
            'visitedPlaces' => $model->getDefinition()->getInitialPlaces(),
            'totalPlaces' => count($model->getDefinition()->getPlaces()),
        ];
    }

    protected function updateState(Model $model, SubjectInterface $subject, string $transitionName, array &$state): void
    {
        ++$state['stepsCount'];

        foreach ($model->getMarking($subject)->getPlaces() as $place => $status) {
            if ($status && !in_array($place, $state['visitedPlaces'])) {
                $state['visitedPlaces'][] = $place;
            }
        }
    }

    protected function canStop(array $state): bool
    {
        return count($state['visitedPlaces']) === $state['totalPlaces'] || $state['stepsCount'] >= $state['maxSteps'];
    }

    protected function randomTransition(Model $model, SubjectInterface $subject, array $state): ?string
    {
        $transitions = $model->getEnabledTransitions($subject);
        if (0 === count($transitions)) {
            return null;
        }

        $unvisitedTransitions = [];
        foreach ($transitions as $transition) {
            if (array_diff($transition->getTos(), $state['visitedPlaces'])) {
                $unvisitedTransitions[] = $transition;
            }
        }
        if (count($unvisitedTransitions) > 0) {
            return $unvisitedTransitions[array_rand($unvisitedTransitions)]->getName();
        }

        return $transitions[array_rand($transitions)]->getName();
    }
}
