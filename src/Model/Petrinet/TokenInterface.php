<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Petrinet\Model\TokenInterface as BaseTokenInterface;

interface TokenInterface extends BaseTokenInterface
{
    public function setId(int $id): void;
}
