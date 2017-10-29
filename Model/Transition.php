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

    /**
     * @var array
     */
    protected $data;

    public function __construct($name, $froms, $tos, $weight, $label)
    {
        $this->weight = $weight;
        $this->label = $label;
        $this->data = [];

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

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}
