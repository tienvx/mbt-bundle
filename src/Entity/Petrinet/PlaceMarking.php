<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Petrinet;

use Doctrine\ORM\Mapping as ORM;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PlaceMarking as BasePlaceMarking;

/**
 * @ORM\Entity
 * @ORM\Table(name="place_marking")
 */
class PlaceMarking extends BasePlaceMarking
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Place")
     */
    protected $place;

    /**
     * @ORM\ManyToMany(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Token", cascade={"persist"})
     * @ORM\JoinTable(
     *  name="place_marking_token_xref",
     *  joinColumns={@ORM\JoinColumn(name="place_marking_id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="token_id", unique=true)}
     * )
     */
    protected $tokens;
}
