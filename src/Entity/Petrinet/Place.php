<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Petrinet;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\Place as BasePlace;

/**
 * @ORM\Entity
 * @ORM\Table(name="place")
 */
class Place extends BasePlace
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\OutputArc",
     *   mappedBy="place",
     *   orphanRemoval=true,
     *   cascade={"persist", "remove"}
     * )
     */
    protected $inputArcs;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\InputArc",
     *   mappedBy="place",
     *   orphanRemoval=true,
     *   cascade={"persist", "remove"}
     * )
     */
    protected $outputArcs;

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
     * @ORM\JoinTable(
     *  name="place_command_xref",
     *  joinColumns={@ORM\JoinColumn(name="place_id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="command_id", unique=true)}
     * )
     */
    protected ArrayCollection $assertions;
}
