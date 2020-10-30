<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Petrinet;

use Doctrine\ORM\Mapping as ORM;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\OutputArc as BaseOutputArc;

/**
 * @ORM\Entity
 * @ORM\Table(name="output_arc")
 */
class OutputArc extends BaseOutputArc
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Place", inversedBy="inputArcs")
     */
    protected $place;

    /**
     * @ORM\ManyToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Transition", inversedBy="outputArcs")
     */
    protected $transition;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $weight;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $expression;
}
