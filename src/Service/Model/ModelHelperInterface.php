<?php

namespace Tienvx\Bundle\MbtBundle\Service\Model;

use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

interface ModelHelperInterface
{
    /**
     * Get starting places and their tokens count (1).
     */
    public function getStartPlaces(ModelInterface $model): array;
}
