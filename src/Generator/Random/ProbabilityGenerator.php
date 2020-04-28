<?php

namespace Tienvx\Bundle\MbtBundle\Generator\Random;

use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Model\SubjectInterface;

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

    protected function randomTransition(Workflow $workflow, SubjectInterface $subject, array $state): ?string
    {
        $transitions = $workflow->getEnabledTransitions($subject);
        if (0 === count($transitions)) {
            return null;
        }

        $visibilities = [];
        foreach ($transitions as $index => $transition) {
            $transitionMetadata = $workflow->getMetadataStore()->getTransitionMetadata($transition);
            $visibilities[$transition->getName()] = $transitionMetadata['probability'] ?? 1;
        }

        return $this->randomByVisibility($visibilities);
    }

    /**
     * Random transition name by probabilty https://stackoverflow.com/a/11872928.
     */
    protected function randomByVisibility(array $visibilities): ?string
    {
        $maxRand = (int) array_sum($visibilities);
        if (0 === $maxRand) {
            return array_rand($visibilities);
        }

        $rand = mt_rand(1, $maxRand);
        foreach ($visibilities as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }

        return null;
    }
}
