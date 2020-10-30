<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Petrinet;

use Doctrine\ORM\Mapping as ORM;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\InputArc as BaseInputArc;

/**
 * @ORM\Entity
 * @ORM\Table(name="input_arc")
 */
class InputArc extends BaseInputArc
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Place", inversedBy="outputArcs")
     */
    protected $place;

    /**
     * @ORM\ManyToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Transition", inversedBy="inputArcs")
     */
    protected $transition;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $weight;
}
