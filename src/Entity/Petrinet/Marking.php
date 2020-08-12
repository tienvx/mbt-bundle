<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Petrinet;

use Doctrine\ORM\Mapping as ORM;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\Marking as BaseMarking;

/**
 * @ORM\Entity
 */
class Marking extends BaseMarking
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\PlaceMarking", cascade={"persist"})
     * @ORM\JoinTable(
     *  name="marking_place_marking_xref",
     *  joinColumns={@ORM\JoinColumn(name="marking_id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="place_marking_id", unique=true)}
     * )
     */
    protected $placeMarkings;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    protected $color;
}
