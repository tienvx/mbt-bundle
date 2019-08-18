<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Generator;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Entity\Step;
use Tienvx\Bundle\MbtBundle\Entity\Data;
use Tienvx\Bundle\MbtBundle\Helper\Randomizer;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

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
    public function generate(Workflow $workflow, AbstractSubject $subject, GeneratorOptions $generatorOptions = null): Generator
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
                $transitionName = Randomizer::randomByWeight($transitionsWithProbability);

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

    public static function getName(): string
    {
        return 'probability';
    }

    public function getLabel(): string
    {
        return 'Probability';
    }
}
