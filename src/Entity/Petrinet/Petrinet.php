<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Petrinet;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\Petrinet as BasePetrinet;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PlaceInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="petrinet")
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

    /**
     * @Assert\IsTrue()
     */
    public function isInitPlaceIdsValid()
    {
        $placeIds = $this->places->map(fn (PlaceInterface $place) => $place->getId())->getValues();

        return !empty($this->initPlaceIds) && empty(array_diff($this->initPlaceIds, $placeIds));
    }
}
