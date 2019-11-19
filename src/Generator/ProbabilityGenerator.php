<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class ProbabilityGenerator extends AbstractGenerator
{
    /**
     * @var int
     */
    protected $maxSteps = 300;

    public function setMaxSteps(int $maxSteps)
    {
        $this->maxSteps = $maxSteps;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Workflow $workflow, SubjectInterface $subject, GeneratorOptions $generatorOptions = null): iterable
    {
        // Number of steps, include the first step (transition = null, places = initial places)
        $stepsCount = 1;
        $maxSteps = $generatorOptions->getMaxSteps() ?? $this->maxSteps;

        while (true) {
            /** @var Transition[] $transitions */
            $transitions = $workflow->getEnabledTransitions($subject);
            if (!empty($transitions)) {
                $transitionsWithProbability = [];
                foreach ($transitions as $index => $transition) {
                    $transitionMetadata = $workflow->getDefinition()->getMetadataStore()->getTransitionMetadata($transition);
                    $transitionsWithProbability[$transition->getName()] = $transitionMetadata['probability'] ?? 1;
                }
                $transitionName = $this->randomByWeight($transitionsWithProbability);

                yield new Step($transitionName, new Data());

                // Update current state.
                ++$stepsCount;

                if ($stepsCount >= $maxSteps) {
                    break;
                }
            } else {
                break;
            }
        }
    }

    /**
     * https://stackoverflow.com/a/11872928.
     *
     * @param array $values [key => weight]
     *
     * @return mixed random key from weighted array
     */
    protected function randomByWeight(array $values)
    {
        $maxRand = (int) array_sum($values);
        if (0 === $maxRand) {
            $rand = mt_rand(0, count($values) - 1);

            return array_keys($values)[$rand];
        } else {
            $rand = mt_rand(1, $maxRand);
            foreach ($values as $key => $value) {
                $rand -= $value;
                if ($rand <= 0) {
                    return $key;
                }
            }
            // Make PHP happy by return the first key.
            return array_keys($values)[0];
        }
    }

    public static function getName(): string
    {
        return 'probability';
    }

    public function getLabel(): string
    {
        return 'Probability';
    }
}
