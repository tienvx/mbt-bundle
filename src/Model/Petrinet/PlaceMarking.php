<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Petrinet\Model\PlaceMarking as BasePlaceMarking;

class PlaceMarking extends BasePlaceMarking implements PlaceMarkingInterface
{
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function __clone()
    {
        $tokens = [];
        foreach ($this->tokens->toArray() as $token) {
            $tokens[] = clone $token;
        }
        $this->setTokens($tokens);
    }
}
