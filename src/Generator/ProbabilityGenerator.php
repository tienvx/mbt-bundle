<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Generator;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Helper\Randomizer;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class ProbabilityGenerator extends AbstractGenerator
{
    /**
     * @var int
     */
    protected $maxPathLength = 300;

    public function setMaxPathLength(int $maxPathLength)
    {
        $this->maxPathLength = $maxPathLength;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableTransitions(Workflow $workflow, AbstractSubject $subject, GeneratorOptions $generatorOptions = null): Generator
    {
        $pathLength = 0;
        $maxPathLength = $generatorOptions->getMaxPathLength() ?? $this->maxPathLength;

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

                yield $transitionName;

                // Update current state.
                ++$pathLength;

                if ($pathLength >= $maxPathLength) {
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
}
