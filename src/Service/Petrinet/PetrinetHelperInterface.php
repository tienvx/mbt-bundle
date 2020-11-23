<?php

namespace Tienvx\Bundle\MbtBundle\Service\Petrinet;

use Petrinet\Model\PetrinetInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

interface PetrinetHelperInterface
{
    public function build(ModelInterface $model): PetrinetInterface;
}
