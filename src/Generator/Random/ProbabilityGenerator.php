<?php

namespace Tienvx\Bundle\MbtBundle\Generator\Random;

use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class ProbabilityGenerator extends RandomGeneratorTemplate
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
        return 'probability';
    }

    public function getLabel(): string
    {
        return 'Probability';
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
        ];
    }

    protected function updateState(Workflow $workflow, SubjectInterface $subject, string $transitionName, array &$state): void
    {
        ++$state['stepsCount'];
    }

    protected function canStop(array $state): bool
    {
        return $state['stepsCount'] >= $state['maxSteps'];
    }

    protected function randomTransition(Workflow $workflow, SubjectInterface $subject): ?string
    {
        $transitions = $workflow->getEnabledTransitions($subject);
        if (count($transitions) > 0) {
            $transitionsWithProbability = [];
            foreach ($transitions as $index => $transition) {
                $transitionMetadata = $workflow->getDefinition()->getMetadataStore()->getTransitionMetadata($transition);
                $transitionsWithProbability[$transition->getName()] = $transitionMetadata['probability'] ?? 1;
            }

            return $this->randomByProbability($transitionsWithProbability);
        }

        return null;
    }

    /**
     * Random transition name by probabilty https://stackoverflow.com/a/11872928.
     *
     * @param array $transitions [transition-name => probability]
     *
     * @return string random transition name
     */
    protected function randomByProbability(array $transitions): string
    {
        $maxRand = (int) array_sum($transitions);
        if (0 === $maxRand) {
            $rand = mt_rand(0, count($transitions) - 1);

            return array_keys($transitions)[$rand];
        }

        $rand = mt_rand(1, $maxRand);
        foreach ($transitions as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }

        return array_keys($transitions)[0];
    }
}
