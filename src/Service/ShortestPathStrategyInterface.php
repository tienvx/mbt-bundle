<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Petrinet\Model\PetrinetInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;

interface ShortestPathStrategyInterface
{
    public function run(PetrinetInterface $petrinet, StepInterface $fromStep, StepInterface $toStep): iterable;
}
