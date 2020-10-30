<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Petrinet;

use Doctrine\ORM\Mapping as ORM;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\Petrinet as BasePetrinet;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

/**
 * @ORM\Entity
 * @ORM\Table(name="petrinet")
 * @MbtAssert\InitPlaces
 */
class Petrinet extends BasePetrinet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    protected array $initPlaceIds = [];

    /**
     * @ORM\OneToMany(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Place", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\JoinTable(
     *  name="petrinet_place_xref",
     *  joinColumns={@ORM\JoinColumn(name="petrinet_id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="place_id", unique=true)}
     * )
     */
    protected $places;

    /**
     * @ORM\OneToMany(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Transition", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\JoinTable(
     *  name="petrinet_transition_xref",
     *  joinColumns={@ORM\JoinColumn(name="petrinet_id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="transition_id", unique=true)}
     * )
     */
    protected $transitions;
}
