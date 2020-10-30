<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Petrinet\Model\Token as BaseToken;

class Token extends BaseToken implements TokenInterface
{
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
