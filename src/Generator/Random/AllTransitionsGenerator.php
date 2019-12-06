<?php

namespace Tienvx\Bundle\MbtBundle\Generator\Random;

use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
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

    protected function initState(Workflow $workflow, GeneratorOptions $generatorOptions): array
    {
        return [
            'stepsCount' => 1,
            'maxSteps' => $generatorOptions->getMaxSteps() ?? $this->maxSteps,
            'visitedTransitions' => [],
            'totalTransitions' => count($workflow->getDefinition()->getTransitions()),
        ];
    }

    protected function updateState(Workflow $workflow, SubjectInterface $subject, string $transitionName, array &$state): void
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

    protected function randomTransition(Workflow $workflow, SubjectInterface $subject, array $state): ?string
    {
        $enabledTransitions = $workflow->getEnabledTransitions($subject);
        if (0 === count($enabledTransitions)) {
            return null;
        }

        $transitions = [];
        foreach ($enabledTransitions as $index => $transition) {
            if (!in_array($transition->getName(), $state['visitedTransitions'])) {
                $transitions[] = $transition;
            }
        }

        if (0 === count($transitions)) {
            $transitions = $enabledTransitions;
        }
        $index = array_rand($enabledTransitions);

        return $enabledTransitions[$index]->getName();
    }
}
