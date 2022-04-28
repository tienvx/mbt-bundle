<?php

namespace Tienvx\Bundle\MbtBundle\Service\Petrinet;

use SingleColorPetrinet\Model\PetrinetInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

interface PetrinetHelperInterface
{
    public function build(RevisionInterface $revision): PetrinetInterface;
}
