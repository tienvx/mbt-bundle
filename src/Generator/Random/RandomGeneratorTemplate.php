<?php

namespace Tienvx\Bundle\MbtBundle\Generator\Random;

use Symfony\Component\Workflow\Transition;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Helper\GuardHelper;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

abstract class RandomGeneratorTemplate implements GeneratorInterface
{
    /**
     * @var GuardHelper
     */
    protected $guardHelper;

    public function __construct(GuardHelper $guardHelper)
    {
        $this->guardHelper = $guardHelper;
    }

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

    protected function getEnabledTransitions(Model $model, SubjectInterface $subject): array
    {
        $enabledTransitions = [];

        foreach ($model->getDefinition()->getTransitions() as $transition) {
            if ($this->isEnabled($model, $subject, $transition)) {
                $enabledTransitions[] = $transition;
            }
        }

        return $enabledTransitions;
    }

    protected function isEnabled(Model $model, SubjectInterface $subject, Transition $transition): bool
    {
        foreach ($transition->getFroms() as $place) {
            if (!$model->getMarking($subject)->has($place)) {
                return false;
            }
        }

        return $this->guardHelper->can($subject, $model->getName(), $transition->getName());
    }
}
