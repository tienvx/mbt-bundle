<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Petrinet;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PetrinetInterface;
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
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    protected string $label;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Tienvx\Bundle\MbtBundle\Entity\Selenium\Command",
     *   mappedBy="place",
     *   orphanRemoval=true,
     *   cascade={"persist", "remove"}
     * )
     */
    protected Collection $assertions;

    /**
     * @ORM\ManyToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Petrinet", inversedBy="places")
     */
    protected PetrinetInterface $petrinet;
}
