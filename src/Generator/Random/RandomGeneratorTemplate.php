<?php

namespace Tienvx\Bundle\MbtBundle\Generator\Random;

use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Model\SubjectInterface;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;

abstract class RandomGeneratorTemplate implements GeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(Workflow $workflow, SubjectInterface $subject, GeneratorOptions $generatorOptions): iterable
    {
        $state = $this->initState($workflow, $generatorOptions);

        while (!$this->canStop($state)) {
            $transitionName = $this->randomTransition($workflow, $subject, $state);
            if (is_null($transitionName)) {
                break;
            }

            yield new Step($transitionName, new Data());

            $this->updateState($workflow, $subject, $transitionName, $state);
        }
    }

    protected function initState(Workflow $workflow, GeneratorOptions $generatorOptions): array
    {
        return [];
    }

    protected function updateState(Workflow $workflow, SubjectInterface $subject, string $transitionName, array &$state): void
    {
    }

    protected function canStop(array $state): bool
    {
        return true;
    }

    protected function randomTransition(Workflow $workflow, SubjectInterface $subject, array $state): ?string
    {
        return null;
    }
}
