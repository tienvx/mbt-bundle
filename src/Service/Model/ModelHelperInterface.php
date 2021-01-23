<?php

namespace Tienvx\Bundle\MbtBundle\Service\Model;

use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

interface ModelHelperInterface
{
    /**
     * Get start transition id.
     */
    public function getStartTransitionId(ModelInterface $model): int;

    /**
     * Get start place ids and their tokens count (1).
     */
    public function getStartPlaceIds(ModelInterface $model): array;
}
