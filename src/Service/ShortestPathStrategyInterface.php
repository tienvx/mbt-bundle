<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PetrinetInterface;

interface ShortestPathStrategyInterface
{
    public function run(PetrinetInterface $petrinet, StepInterface $fromStep, StepInterface $toStep): iterable;
}
