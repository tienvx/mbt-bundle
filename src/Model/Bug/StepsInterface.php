<?php

namespace Tienvx\Bundle\MbtBundle\Model\Bug;

use Doctrine\Common\Collections\Collection;

interface StepsInterface
{
    public function getSteps(): Collection;

    /**
     * @param $steps
     */
    public function setSteps($steps): void;

    public function getLength(): int;
}
