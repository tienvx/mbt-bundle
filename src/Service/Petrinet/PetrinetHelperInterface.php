<?php

namespace Tienvx\Bundle\MbtBundle\Service\Petrinet;

use Petrinet\Model\PetrinetInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

interface PetrinetHelperInterface
{
    public function build(RevisionInterface $revision): PetrinetInterface;
}
