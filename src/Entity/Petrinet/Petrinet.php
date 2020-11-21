<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Petrinet;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\Petrinet as BasePetrinet;

/**
 * @ORM\Entity
 * @ORM\Table(name="petrinet")
 * @ORM\HasLifecycleCallbacks
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *   targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Place",
     *   mappedBy="petrinet",
     *   orphanRemoval=true,
     *   cascade={"persist", "remove"}
     * )
     */
    protected $places;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *   targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Transition",
     *   mappedBy="petrinet",
     *   orphanRemoval=true,
     *   cascade={"persist", "remove"}
     * )
     */
    protected $transitions;
}
