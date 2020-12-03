<?php

namespace Tienvx\Bundle\MbtBundle\Service\Model;

use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

interface ModelHelperInterface
{
    /**
     * Get init places and their tokens count (1).
     */
    public function getInitPlaces(ModelInterface $model): array;
}
