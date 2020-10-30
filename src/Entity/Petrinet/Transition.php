<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Petrinet;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\Transition as BaseTransition;

/**
 * @ORM\Entity
 * @ORM\Table(name="transition")
 */
class Transition extends BaseTransition
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\InputArc",
     *   mappedBy="transition",
     *   orphanRemoval=true,
     *   cascade={"persist", "remove"}
     * )
     */
    protected $inputArcs;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\OutputArc",
     *   mappedBy="transition",
     *   orphanRemoval=true,
     *   cascade={"persist", "remove"}
     * )
     */
    protected $outputArcs;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $guard = null;

    /**
     * @ORM\Column(type="string")
     */
    protected string $label = '';

    /**
     * @ORM\OneToMany(
     *   targetEntity="Tienvx\Bundle\MbtBundle\Entity\Selenium\Command",
     *   orphanRemoval=true,
     *   cascade={"persist", "remove"}
     * )
     */
    protected ArrayCollection $actions;
}
