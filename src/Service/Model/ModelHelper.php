<?php

namespace Tienvx\Bundle\MbtBundle\Service\Model;

use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

class ModelHelper implements ModelHelperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getStartTransitionId(ModelInterface $model): int
    {
        foreach ($model->getTransitions() as $index => $transition) {
            if ($transition instanceof TransitionInterface && 0 === count($transition->getFromPlaces())) {
                return $index;
            }
        }

        throw new RuntimeException('Missing start transition');
    }

    /**
     * {@inheritdoc}
     */
    public function getStartPlaceIds(ModelInterface $model): array
    {
        foreach ($model->getTransitions() as $transition) {
            if ($transition instanceof TransitionInterface && 0 === count($transition->getFromPlaces())) {
                return array_fill_keys($transition->getToPlaces(), 1);
            }
        }

        throw new RuntimeException('Missing start transition');
    }
}
