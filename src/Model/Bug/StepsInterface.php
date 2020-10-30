<?php

namespace Tienvx\Bundle\MbtBundle\Model\Bug;

use Doctrine\Common\Collections\ArrayCollection;

interface StepsInterface
{
    public function getSteps(): ArrayCollection;

    /**
     * @param $steps
     */
    public function setSteps($steps): void;

    public function getLength(): int;
}
