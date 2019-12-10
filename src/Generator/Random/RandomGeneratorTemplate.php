<?php

namespace Tienvx\Bundle\MbtBundle\Generator\Random;

use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

abstract class RandomGeneratorTemplate implements GeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(Model $model, SubjectInterface $subject, GeneratorOptions $generatorOptions): iterable
    {
        $state = $this->initState($model, $generatorOptions);

        while (!$this->canStop($state)) {
            $transitionName = $this->randomTransition($model, $subject, $state);
            if (is_null($transitionName)) {
                break;
            }

            yield new Step($transitionName, new Data());

            $this->updateState($model, $subject, $transitionName, $state);
        }
    }

    protected function initState(Model $model, GeneratorOptions $generatorOptions): array
    {
        return [];
    }

    protected function updateState(Model $model, SubjectInterface $subject, string $transitionName, array &$state): void
    {
    }

    protected function canStop(array $state): bool
    {
        return true;
    }

    protected function randomTransition(Model $model, SubjectInterface $subject, array $state): ?string
    {
        return null;
    }
}
