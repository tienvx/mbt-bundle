<?php

namespace Tienvx\Bundle\MbtBundle\Service\Model;

use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

interface ModelHelperInterface
{
    /**
     * Get start transition id.
     */
    public function getStartTransitionId(RevisionInterface $revision): int;

    /**
     * Get start place ids and their tokens count (1).
     */
    public function getStartPlaceIds(RevisionInterface $revision): array;
}
