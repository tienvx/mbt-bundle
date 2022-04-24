<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step;

use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

interface StepHelperInterface
{
    public function cloneAndResetSteps(array $steps, RevisionInterface $revision): array;
}
