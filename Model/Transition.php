<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use Symfony\Component\Workflow\Transition as BaseTransition;

class Transition extends BaseTransition
{
    private $weight;

    public function __construct($name, $froms, $tos, $weight)
    {
        $this->weight = $weight;

        parent::__construct($name, $froms, $tos);
    }

    public function getWeight()
    {
        return $this->weight;
    }
}
