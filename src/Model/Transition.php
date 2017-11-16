<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use Symfony\Component\Workflow\Transition as BaseTransition;

class Transition extends BaseTransition
{
    /**
     * @var int
     */
    protected $weight;

    /**
     * @var string
     */
    protected $label;

    public function __construct($name, $froms, $tos, $weight, $label)
    {
        $this->weight = $weight;
        $this->label = $label;

        parent::__construct($name, $froms, $tos);
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function getLabel()
    {
        return $this->label;
    }
}
