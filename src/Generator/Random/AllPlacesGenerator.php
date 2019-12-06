<?php

namespace Tienvx\Bundle\MbtBundle\Generator\Random;

use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
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

    protected function initState(Workflow $workflow, GeneratorOptions $generatorOptions): array
    {
        return [
            'stepsCount' => 1,
            'maxSteps' => $generatorOptions->getMaxSteps() ?? $this->maxSteps,
            'visitedPlaces' => $workflow->getDefinition()->getInitialPlaces(),
            'totalPlaces' => count($workflow->getDefinition()->getPlaces()),
        ];
    }

    protected function updateState(Workflow $workflow, SubjectInterface $subject, string $transitionName, array &$state): void
    {
        ++$state['stepsCount'];

        foreach ($workflow->getMarking($subject)->getPlaces() as $place => $status) {
            if ($status && !in_array($place, $state['visitedPlaces'])) {
                $state['visitedPlaces'][] = $place;
            }
        }
    }

    protected function canStop(array $state): bool
    {
        return count($state['visitedPlaces']) === $state['totalPlaces'] || $state['stepsCount'] >= $state['maxSteps'];
    }

    protected function randomTransition(Workflow $workflow, SubjectInterface $subject, array $state): ?string
    {
        $enabledTransitions = $workflow->getEnabledTransitions($subject);
        if (0 === count($enabledTransitions)) {
            return null;
        }

        $transitions = [];
        foreach ($enabledTransitions as $index => $transition) {
            if (array_diff($transition->getTos(), $state['visitedPlaces'])) {
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
