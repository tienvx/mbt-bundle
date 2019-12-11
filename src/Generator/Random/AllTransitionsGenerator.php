<?php

namespace Tienvx\Bundle\MbtBundle\Generator\Random;

use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class AllTransitionsGenerator extends RandomGeneratorTemplate
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
        return 'all-transitions';
    }

    public function getLabel(): string
    {
        return 'All Transitions';
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
            'visitedTransitions' => [],
            'totalTransitions' => count($model->getDefinition()->getTransitions()),
        ];
    }

    protected function updateState(Model $model, SubjectInterface $subject, string $transitionName, array &$state): void
    {
        ++$state['stepsCount'];

        if (!in_array($transitionName, $state['visitedTransitions'])) {
            $state['visitedTransitions'][] = $transitionName;
        }
    }

    protected function canStop(array $state): bool
    {
        return count($state['visitedTransitions']) === $state['totalTransitions'] || $state['stepsCount'] >= $state['maxSteps'];
    }

    protected function randomTransition(Model $model, SubjectInterface $subject, array $state): ?string
    {
        $transitions = $model->getEnabledTransitions($subject);
        if (0 === count($transitions)) {
            return null;
        }

        $unvisitedTransitions = [];
        foreach ($transitions as $transition) {
            if (!in_array($transition->getName(), $state['visitedTransitions'])) {
                $unvisitedTransitions[] = $transition;
            }
        }
        if (count($unvisitedTransitions) > 0) {
            return $unvisitedTransitions[array_rand($unvisitedTransitions)]->getName();
        }

        return $transitions[array_rand($transitions)]->getName();
    }
}
