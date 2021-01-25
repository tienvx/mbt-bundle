<?php

namespace Tienvx\Bundle\MbtBundle\Service\Model;

use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

class ModelHelper implements ModelHelperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getStartTransitionId(RevisionInterface $revision): int
    {
        foreach ($revision->getTransitions() as $index => $transition) {
            if ($transition instanceof TransitionInterface && 0 === count($transition->getFromPlaces())) {
                return $index;
            }
        }

        throw new RuntimeException('Missing start transition');
    }

    /**
     * {@inheritdoc}
     */
    public function getStartPlaceIds(RevisionInterface $revision): array
    {
        foreach ($revision->getTransitions() as $transition) {
            if ($transition instanceof TransitionInterface && 0 === count($transition->getFromPlaces())) {
                return array_fill_keys($transition->getToPlaces(), 1);
            }
        }

        throw new RuntimeException('Missing start transition');
    }
}
